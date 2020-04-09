<?php

/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

/**
* Description of DRMImportCsvEdi
*
* @author mathurin
*/
class DRMImportCsvEdi extends DRMCsvEdi {

  protected $configuration = null;
  protected $mouvements = array();
  protected $csvDoc = null;
  protected $fromEdi = false;
  protected $noSave = false;

  public function __construct($file, DRM $drm = null, $fromEdi = false) {
    $this->fromEdi = $fromEdi;
    if($this->fromEdi){
      parent::__construct($file, $drm);
      $drmInfos = $this->getDRMInfosFromFile();
      if(!$drmInfos){
          throw new sfException("La DRM n'a pu être initialisée depuis le fichier csv : l'identifiant ou/et la periode n'ont pas été trouvés");
      }
      try{
        $drm = DRMClient::getInstance()->findOrCreateFromEdiByIdentifiantAndPeriode($drmInfos['identifiant'],$drmInfos['periode'], true);
      }catch(sfException $e){
        echo "\"#Niveau erreur\";\"Numéro ligne de l'erreur\";\"Parametre en erreur \";\"Diagnostic\"\n";
        echo "Error;1;".$drmInfos['identifiant'].";Le numéro de compte n'est pas connu\n";
        return;
      }
    }

    $this->initConf($drm);
    $this->drmPrecedente = DRMClient::getInstance()->findMasterByIdentifiantAndPeriode($drm->identifiant, DRMClient::getInstance()->getPeriodePrecedente($drm->periode));
    if(is_null($this->csvDoc)) {
      $this->csvDoc = CSVDRMClient::getInstance()->createOrFindDocFromDRM($file, $drm);
    }
    parent::__construct($file, $drm);
  }

  private function getDRMInfosFromFile(){
    if($this->getCsv()){
      foreach ($this->getCsv() as $keyRow => $csvRow) {
        if((KeyInflector::slugify($csvRow[self::CSV_TYPE]) == self::TYPE_CAVE)
        || (KeyInflector::slugify($csvRow[self::CSV_TYPE]) == self::TYPE_CRD)
        || (KeyInflector::slugify($csvRow[self::CSV_TYPE]) == self::TYPE_ANNEXE)){
          if (!preg_match('/^[0-9]+$/', KeyInflector::slugify($csvRow[self::CSV_IDENTIFIANT]))) {
            continue;
          }
          if (!preg_match('/^[0-9]{6}$/', KeyInflector::slugify($csvRow[self::CSV_PERIODE]))) {
            continue;
          }
          return array('identifiant' => sprintf("%08d",KeyInflector::slugify($csvRow[self::CSV_IDENTIFIANT])), 'periode' => KeyInflector::slugify($csvRow[self::CSV_PERIODE]));
        }
      }
    }
    return null;
  }

  public function getDrm(){
    return $this->drm;
  }
  public function getCsvDoc() {

    return $this->csvDoc;
  }

  public function getCsvArrayErreurs(){
    $csvErreurs = array();
    $csvErreurs[] = array("#Niveau erreur","Numéro ligne de l'erreur","Parametre en erreur ","Diagnostic");
    if($this->getCsvDoc()->hasErreurs()){
      $erreursRows = $this->getCsvDoc()->getErreurs();
      foreach ($erreursRows as $erreur) {
        $erreurLevel = ($erreur->exist('level'))? CSVDRMClient::$levelErrorsLibelle[$erreur->level] : "";
        $csvErreurs[] = array($erreurLevel,"".$erreur->num_ligne,"".$erreur->csv_erreur,$erreur->diagnostic);
      }
    }
    return $csvErreurs;
  }

  protected function initConf($drm) {
    $this->configuration = ConfigurationClient::getCurrent();
    if($drm) {
      $this->configuration = $drm->getConfig();
    }
    $this->mouvements = $this->buildAllMouvements();
  }

  public function getDocRows() {
    return $this->getCsv($this->csvDoc->getFileContent());
  }

  /**
  * CHECK DU CSV
  */
  public function checkCSV() {
    $this->csvDoc->clearErreurs();
    $this->checkCSVIntegrity();
    if ($this->csvDoc->hasErreurs()) {
      $this->csvDoc->setStatut(self::STATUT_ERREUR);
      $this->csvDoc->save();
      return false;
    }
    // Check mouvements
    $this->createCacheProduits();
    $this->checkImportMouvementsFromCSV();
    // Check annexes
    $this->checkImportAnnexesFromCSV();
    // Check Crds
    $this->checkImportCrdsFromCSV();
    // Check Crds
    //$this->checkHorsRegionFromCSV();
    if ($this->csvDoc->hasErreurs()) {
      $this->csvDoc->setStatut(self::STATUT_WARNING);
      $this->csvDoc->save();
      return false;
    }
    $this->csvDoc->setStatut(self::STATUT_VALIDE);
    $this->csvDoc->save();
    return true;
  }

  private function getCacheKeyFromData($datas) {
      return $datas[self::CSV_CAVE_CERTIFICATION].'-'.
              $datas[self::CSV_CAVE_GENRE].'-'.
              $datas[self::CSV_CAVE_APPELLATION].'-'.
              $datas[self::CSV_CAVE_MENTION].'-'.
              $datas[self::CSV_CAVE_LIEU].'-'.
              $datas[self::CSV_CAVE_COULEUR].'-'.
              $datas[self::CSV_CAVE_CEPAGE].'-'.
              $datas[self::CSV_CAVE_LIBELLE_COMPLEMENTAIRE].'-'.
              $datas[self::CSV_CAVE_LIBELLE_PRODUIT].'-'.
              $datas[self::CSV_CAVE_TYPE_DRM];
  }

  public function createCacheProduits() {
      $this->cache = array();
      $this->cache2datas = array();

      $aggregatedEdiList = null;
      if(DRMConfiguration::getInstance()->hasAggregatedEdi()){
        $aggregatedEdiList = DRMConfiguration::getInstance()->getAggregatedEdi();
      }
      $has_default_hash = DRMConfiguration::getInstance()->hasEdiDefaultProduitHash();

      if ($this->drm->canSetStockDebutMois()) {
          $this->drm->remove('declaration');
          $this->drm->add('declaration');
      }


      foreach ($this->getDocRows() as $datas) {
        if (KeyInflector::slugify(trim($datas[self::CSV_TYPE])) != self::TYPE_CAVE) {
            continue;
        }
        if (strtolower($datas[self::CSV_CAVE_CATEGORIE_MOUVEMENT] != 'stocks_debut')) {
            continue;
        }
        $cacheid = $this->getCacheKeyFromData($datas);
        if (isset($this->cache2datas[$cacheid])) {
            continue;
        }
        if(!isset($datas[self::CSV_CAVE_VOLUME]) || $datas[self::CSV_CAVE_VOLUME] === "") {
            continue;
        }

        $this->cache2datas[$cacheid] = $datas;
        $this->cache2datas[$cacheid][self::CSV_CAVE_VOLUME] = $this->convertNumber($this->cache2datas[$this->getCacheKeyFromData($datas)][self::CSV_CAVE_VOLUME]);
      }

      $cacheProduitTav = array();
      # Premier parcours des lignes du csv pour créer un tableau de hashage : produit / tav
      foreach ($this->getDocRows() as $datas) {
        if (KeyInflector::slugify(trim($datas[self::CSV_TYPE])) != self::TYPE_CAVE) {
            continue;
        }
        if(strtoupper(KeyInflector::slugify($datas[self::CSV_CAVE_CATEGORIE_MOUVEMENT])) != self::COMPLEMENT){
            continue;
        }
        if(strtoupper(KeyInflector::slugify($datas[self::CSV_CAVE_TYPE_COMPLEMENT_PRODUIT])) != self::COMPLEMENT_TAV) {
            continue;
        }
        $tav = $this->convertNumber($datas[self::CSV_CAVE_VALEUR_COMPLEMENT_PRODUIT]);
        if(!$tav) {
            continue;
        }
        $cacheProduitTav[$this->getCacheKeyFromData($datas)] = $tav;
      }

      $num_ligne = 0;
      foreach ($this->getDocRows() as $datas) {
          $num_ligne++;
          if (!isset($all_produits)) {
              $all_produits = $this->configuration->declaration->getProduits($datas[self::CSV_PERIODE]);
          }
          if (preg_match('/^(...)?#/', $datas[self::CSV_TYPE])) {
              continue;
          }
          if (strtoupper(KeyInflector::slugify(trim($datas[self::CSV_TYPE]))) != self::TYPE_CAVE) {
              continue;
          }
          if (isset($this->cache[$this->getCacheKeyFromData($datas)])) {
              continue;
          }

          $founded_produit = null;
          $is_default_produit = false;

          $tav = isset($cacheProduitTav[$this->getCacheKeyFromData($datas)]) ? $cacheProduitTav[$this->getCacheKeyFromData($datas)] : null;

          $csvLibelleProductArray = $this->buildLibellesArrayWithRow($datas, true);
          $csvLibelleProductComplet = $this->slugifyProduitArrayOrString($csvLibelleProductArray);

          $keys_libelle = preg_replace("/[ ]+/", " ", sprintf("%s %s %s %s %s %s %s", $datas[self::CSV_CAVE_CERTIFICATION], $datas[self::CSV_CAVE_GENRE], $datas[self::CSV_CAVE_APPELLATION], $datas[self::CSV_CAVE_MENTION], $datas[self::CSV_CAVE_LIEU], $datas[self::CSV_CAVE_COULEUR], $datas[self::CSV_CAVE_CEPAGE]));

          $keys_libelle_mention_fin = preg_replace("/[ ]+/", " ", sprintf("%s %s %s %s %s %s %s", $datas[self::CSV_CAVE_CERTIFICATION], $datas[self::CSV_CAVE_GENRE], $datas[self::CSV_CAVE_APPELLATION], $datas[self::CSV_CAVE_LIEU], $datas[self::CSV_CAVE_COULEUR], $datas[self::CSV_CAVE_CEPAGE],$datas[self::CSV_CAVE_MENTION]));

          if (!$founded_produit && $idDouane = $this->getIdDouane($datas)) {
          	$produits = $this->configuration->identifyProductByCodeDouane($idDouane);
          	if (count($produits) == 1) {
          		$founded_produit = $produits[0];
          	} else {
          		$libelle = preg_replace('/([a-zA-Z0-9\ \-\_]*)\(([a-zA-Z0-9\ \-\_]*)\)/', '${1}', trim($datas[self::CSV_CAVE_LIBELLE_PRODUIT]));
          		foreach($produits as $p) {
          			if (!$founded_produit) {
          				$founded_produit = $p;
          			}
          			if (KeyInflector::slugify(str_replace(" ", "", $p->getLibelleFormat())) == KeyInflector::slugify(str_replace(" ", "", $libelle))) {
          				$founded_produit = $p;
          				break;
          			}
          		}
          	}
          }

          if(!$founded_produit && ($keys_libelle != '      ')) {
            $founded_produit = $this->configuration->identifyProductByLibelle(KeyInflector::slugify(str_replace("AOC AOC","AOC",$keys_libelle)));
          }
          if(!$founded_produit && ($keys_libelle != '      ')) {
            $founded_produit = $this->configuration->identifyProductByLibelle(KeyInflector::slugify(str_replace("AOC AOC","AOC",$keys_libelle_mention_fin)));
          }

          if(!$founded_produit && preg_match('/(.*) *\(([^\)]+)\)/', $datas[self::CSV_CAVE_LIBELLE_PRODUIT], $m)) {
            $produits = $this->configuration->identifyProductByCodeDouane(trim($m[2]));
            if (count($produits) == 1) {
              $founded_produit = $produits[0];
            }else {
              foreach($produits as $p) {
                if (preg_match('/'.preg_replace('/[\/\(\)]/', '.', $m[1]).'/', $p->getLibelleFormat())) {
                  $founded_produit = $p;
                  break;
                }
              }
            }
            if (count($produits) > 1) {
                $founded_produit = $produits[0];
            }
          }

          if(!$founded_produit) {
            $founded_produit = $this->configuration->identifyProductByLibelle(trim(preg_replace('/ *\(.*/', '', preg_replace("/[ ]+/", " ", $datas[self::CSV_CAVE_LIBELLE_PRODUIT] . ' ' . $datas[self::CSV_CAVE_MENTION]))));
          }

          if (!$founded_produit) {
            foreach ($all_produits as $produit) {
              if ($founded_produit) {
                break;
              }
              $produitConfLibelleAOC = $this->slugifyProduitConf($produit);
              $produitConfLibelleAOP = $this->slugifyProduitConf($produit,true);
              $produitConfLibelleAOCWithoutGenre = $this->slugifyProduitConf($produit, false, false);
              $libelleCompletConfAOC = $this->slugifyProduitArrayOrString($produitConfLibelleAOC);
              $libelleCompletConfAOP = $this->slugifyProduitArrayOrString($produitConfLibelleAOP);
              $libelleCompletConfAOCWithoutGenre = $this->slugifyProduitArrayOrString($produitConfLibelleAOCWithoutGenre);
              $libelleCompletEnCsv = $this->slugifyProduitArrayOrString($datas[self::CSV_CAVE_LIBELLE_PRODUIT]);

              $isEmptyArray = $this->isEmptyArray($csvLibelleProductArray);

              if ($isEmptyArray){
                if(($libelleCompletConfAOC != $csvLibelleProductComplet) && ($libelleCompletConfAOP != $csvLibelleProductComplet)
                && ($libelleCompletConfAOC != $libelleCompletEnCsv) && ($libelleCompletConfAOP != $libelleCompletEnCsv)
                && ($this->slugifyProduitArrayOrString($produit->getLibelleFormat()) != $libelleCompletEnCsv)) {
                  continue;
                }
              }elseif((count(array_diff($csvLibelleProductArray, $produitConfLibelleAOC))) && (count(array_diff($csvLibelleProductArray, $produitConfLibelleAOP)))
              && ($libelleCompletConfAOC != $csvLibelleProductComplet) && ($libelleCompletConfAOP != $csvLibelleProductComplet)
              && ($libelleCompletConfAOC != $libelleCompletEnCsv) && ($libelleCompletConfAOP != $libelleCompletEnCsv)
              && ($libelleCompletConfAOCWithoutGenre != $csvLibelleProductComplet)
              && ($libelleCompletConfAOCWithoutGenre != $libelleCompletEnCsv)
              && ($this->slugifyProduitArrayOrString($produit->getLibelleFormat()) != $libelleCompletEnCsv) ) {
                continue;
              }
              $founded_produit = $produit;

              $date = $this->drm->getPeriode().'01';
              if($founded_produit->getTauxCVO($date) == "-1" && $founded_produit->getTauxDouane($date) == "-1"){

                if($aggregatedEdiList && count($aggregatedEdiList) && count($aggregatedEdiList[0])
                && isset($aggregatedEdiList[0][$founded_produit->getHash()])){
                  $founded_produit = $all_produits[$aggregatedEdiList[0][$founded_produit->getHash()]];
                }else{
                  $founded_produit = $produit->getProduitSiblingWithTaux($date);
                }
              }
            }
          }
          if((!$founded_produit) && $has_default_hash && ($default_produit_inao = $this->getIdDouane($datas))) {
              $is_default_produit = true;
              if (preg_match('/(.*[^ ]) *\(([^\)]+)\)/', $datas[self::CSV_CAVE_LIBELLE_PRODUIT], $m)) {
                  $default_produit_libelle = $m[1];
              }else{
                  $default_produit_libelle = $datas[self::CSV_CAVE_LIBELLE_PRODUIT];
              }
              $default_produit_hash = DRMConfiguration::getInstance()->getEdiDefaultProduitHash($default_produit_inao);
              if ($this->configuration->exist($default_produit_hash)) {
                  $founded_produit = $this->configuration->getProduit($default_produit_hash);
              }
          }

          if($founded_produit && $aggregatedEdiList && count($aggregatedEdiList) && count($aggregatedEdiList[0])
          && isset($aggregatedEdiList[0][$founded_produit->getHash()])){
            $founded_produit = $all_produits[$aggregatedEdiList[0][$founded_produit->getHash()]];
          }

          if (!$founded_produit) {
            $this->csvDoc->addErreur($this->productNotFoundError($num_ligne, $datas));
            continue;
          }

          if ($founded_produit && !$founded_produit->isActif($datas[self::CSV_PERIODE].'-01')) {
            $this->csvDoc->addErreur($this->productNotFoundError($num_ligne, $datas));
            continue;
          }

          //Gestion du produit non connu
          if((!$founded_produit)  && ($default_produit_inao = $this->getIdDouane($datas))) {
              $is_default_produit = true;
              if (preg_match('/(.*[^ ]) *\(([^\)]+)\)/', $datas[self::CSV_CAVE_LIBELLE_PRODUIT], $m)) {
                  $default_produit_libelle = $m[1];
              }else{
                  $default_produit_libelle = $datas[self::CSV_CAVE_LIBELLE_PRODUIT];
              }
              $default_produit_hash = self::getEdiDefaultFromInao($default_produit_inao);
              $founded_produit = $this->configuration->get($default_produit_hash);
          }

          $denomination_complementaire = (trim($datas[self::CSV_CAVE_LIBELLE_COMPLEMENTAIRE]))? trim($datas[self::CSV_CAVE_LIBELLE_COMPLEMENTAIRE]) : false;
          if ($is_default_produit) {
              $denomination_complementaire = ($denomination_complementaire) ? $default_produit_libelle." ".$denomination_complementaire : $default_produit_libelle;
          }

          if(!$founded_produit) {
              $this->csvDoc->addErreur($this->productNotFoundError($num_ligne, $datas));
              continue;
          }

          /// CREATION DU DETAILS
          $produit =  $this->drm->addProduit($founded_produit->getHash(),DRMClient::$types_node_from_libelles[KeyInflector::slugify(strtoupper($datas[self::CSV_CAVE_TYPE_DRM]))], $denomination_complementaire, $tav);

          //Gestion du produit non connu
          if ($is_default_produit) {
              $produit->code_inao = $default_produit_inao;
              $produit->produit_libelle = $default_produit_libelle;
          }

          $cacheid = $this->getCacheKeyFromData($datas);
          $this->cache[$cacheid] = $produit;
          if (!isset($this->cache2datas[$cacheid])) {
              $this->cache2datas[$cacheid] = $datas;
          }
          $this->cache2datas[$cacheid]['hash'] = $founded_produit->getHash();
          $this->cache2datas[$cacheid]['hash_detail'] = $produit->getHash();
          $this->cache2datas[$cacheid]['details_type'] = DRMClient::$types_node_from_libelles[KeyInflector::slugify(strtoupper($datas[self::CSV_CAVE_TYPE_DRM]))];
          $this->cache2datas[$cacheid]['denomination_complementaire'] = $denomination_complementaire;
          $this->cache2datas[$cacheid]['founded_produit'] = $founded_produit;
          $this->cache2datas[$cacheid]['tav'] = $tav;
          $this->cache2datas[$cacheid]['produit_libelle'] = $produit->produit_libelle;
      }
      //avec le reorder, les référence vers les details sautent, on les re-récupère donc ici :
      // (il est possible que le produit ait du tav ou du volume mais ne soit pas reconnu, donc il faut le supprimer du cache => $delete)
      $delete = array();
      foreach($this->cache2datas as $cacheid => $params) {
          if (isset($params['hash_detail']) && $params['hash_detail']) {
              $this->cache[$cacheid] = $this->drm->get($params['hash_detail']);
          }else{
              $delete[] = $cacheid;
          }
      }
      foreach($delete as $cacheid){
          unset($this->cache2datas[$cacheid]);
          unset($this->cache[$cacheid]);

      }
      //on prépare les vérifications
      $check = array();
      foreach ($this->cache as $cacheid => $produit) {
          if (!isset($check[$produit->getHash()])) {
              $check[$produit->getHash()] = array();
          }
          $check[$produit->getHash()][$cacheid] = 1;
      }
      // Cas d'un nouveau produit avec label ou complement et où un produit DEFAUT existe
      foreach ($check as $hash => $array) {
          if (count($array) <= 1) {
              continue;
          }
          ksort($array);
          $isfirst = true;
          foreach($array as $cacheid => $null) {
              if ($isfirst) {
                  $isfirst = false;
                  continue;
              }
              $p = $this->drm->addProduit($this->cache2datas[$cacheid]['hash'], $this->cache2datas[$cacheid]['details_type'], $this->cache2datas[$cacheid]['denomination_complementaire'], $this->cache2datas[$cacheid]['tav']);
              $p->produit_libelle = $this->cache2datas[$cacheid]['produit_libelle'];
              $this->cache[$cacheid] = $p;
              $this->cache2datas[$cacheid]['hash'] = $p->getConfig()->getHash();
              $this->cache2datas[$cacheid]['hash_detail'] = $p->getHash();
          }
      }
      //avec le reorder, les référence vers les details sautent, on les re-récupère donc ici :
      foreach($this->cache2datas as $cacheid => $params) {
          if (isset($params['hash_detail'])) {
              $this->cache[$cacheid] = $this->drm->get($params['hash_detail']);
          }
      }
      $couleurs = array();
      foreach ($this->cache as $cacheid => $produit) {
          if (!isset($couleurs[$produit->getCouleur()->getHash()])) {
              $couleurs[$produit->getCouleur()->getHash()] = array();
          }
          $couleurs[$produit->getCouleur()->getHash()][$cacheid] = 1;
      }
      //gestion des multidetails sur la base stock final de la DRM précédente
      //+ preparation de la comparaison tav + denom
      $cepagedenomtav = array();
      foreach($couleurs as $hash => $array_cache) {
          $volume2hash = array();
          if($this->drmPrecedente && $this->drmPrecedente->exist($hash)) {
              foreach($this->drmPrecedente->get($hash)->getProduits() as $k => $p) {
                  foreach($p->getProduitsDetails(true) as $kd => $d) {
                      //préparation de l'étape suivante sur la comparaison sur la base du tav et de la denom
                      if ($d->denomination_complementaire || $d->tav) {
                          $cepagedenomtav[$d->getCepage()->getHash().'-'.$d->getParent()->getKey().'-'.$d->denomination_complementaire.'-'.$d->tav] = $d->getHash();
                      }
                      $total_fin_mois = $d->stocks_fin->final * 1;
                      if (!$total_fin_mois) {
                          continue;
                      }
                      if (!isset($volume2hash["$total_fin_mois"])) {
                          $volume2hash["$total_fin_mois"] = array();
                      }
                      $volume2hash["$total_fin_mois"][$d->getHash()] = 1;
                  }
              }
          }
          foreach($array_cache as $cacheid => $null)  {
              if (!isset($this->cache2datas[$cacheid][self::CSV_CAVE_VOLUME])){
                  continue;
              }
              $total_debut_mois = $this->convertNumber($this->cache2datas[$cacheid][self::CSV_CAVE_VOLUME])."";
              if (!$total_debut_mois) {
                  continue;
              }
              if (!isset($volume2hash["$total_debut_mois"]))  {
                  continue;
              }
              if (isset($volume2hash["$total_debut_mois"][$this->cache[$cacheid]->getHash()])) {
                  continue;
              }
              if (count(array_keys($volume2hash["$total_debut_mois"])) > 1) {
                  $current_cepage_hash = $this->cache[$cacheid]->getCepage()->getHash();
                  $nb = 0;
                  foreach(array_keys($volume2hash["$total_debut_mois"]) as $a_hash_volume) {
                      if (preg_replace('/.details.[^\/]*$/', '', $a_hash_volume) == $current_cepage_hash) {
                          $nb++;
                          $new_hash = $a_hash_volume;
                      }
                  }
                  if ($nb === 1) {
                      unset($volume2hash["$total_debut_mois"][$new_hash]);
                  }else{
                      continue;
                  }
              }else{
                  $new_hashes = array_keys($volume2hash["$total_debut_mois"]);
                  $new_hash = array_shift($new_hashes);
                  unset($volume2hash["$total_debut_mois"][$new_hash]);
              }

              if (isset($this->cache2datas[$cacheid]['tav'])  && (
                       ! $this->drmPrecedente->exist($new_hash)
                    || ! $this->drmPrecedente->get($new_hash)->exist('tav')
                    || ($this->cache2datas[$cacheid]['tav'] != $this->drmPrecedente->get($new_hash)->tav)
                ) ) {
                        continue;
                    }
              if (!$this->drmPrecedente->exist($this->cache[$cacheid]->getCepage()->getHash())
                 || !$this->drmPrecedente->get($this->cache[$cacheid]->getCepage()->getHash())->getDetailsNoeud($this->cache2datas[$cacheid]['details_type'])
                 || !$this->drmPrecedente->get($this->cache[$cacheid]->getCepage()->getHash())->getDetailsNoeud($this->cache2datas[$cacheid]['details_type'])->exist($this->cache[$cacheid]->getKey())
                 ) {
                  if ($this->drm->get($this->cache[$cacheid]->getCepage()->getHash())->getDetailsNoeud($this->cache2datas[$cacheid]['details_type'])) {
                      $this->drm->get($this->cache[$cacheid]->getCepage()->getHash())->getDetailsNoeud($this->cache2datas[$cacheid]['details_type'])->remove($this->cache[$cacheid]->getKey());
                  }
              }

              $this->cache[$cacheid] = $this->drm->getOrAdd($new_hash);
              $this->cache2datas[$cacheid][self::CSV_CAVE_VOLUME] = $this->convertNumber($this->cache2datas[$cacheid][self::CSV_CAVE_VOLUME]);
              $this->cache2datas[$cacheid]['founded_produit'] = $this->cache[$cacheid]->getConfig();
              $this->cache2datas[$cacheid]['hash'] = $this->cache2datas[$cacheid]['founded_produit']->getHash();
              $this->cache2datas[$cacheid]['hash_detail'] = $this->cache[$cacheid]->getHash();
          }
      }
      //avec le reorder, les référence vers les details sautent, on les re-récupère donc ici :
      foreach($this->cache2datas as $cacheid => $params) {
          if (isset($params['hash_detail'])) {
              $this->cache[$cacheid] = $this->drm->get($params['hash_detail']);
          }
      }
      // On tente une dernière mise en cohérence en comparant les denomination complémentaires
      // et les tav de la drm preecente
      foreach($this->cache2datas as $cacheid => $cachedata) {
          if (!$cachedata['denomination_complementaire'] && !(isset($cachedata['tav']) && $cachedata['tav'])) {
             continue;
          }
          $id_cepagedenomtav = $this->cache[$cacheid]->getCepage()->getHash().'-'.$cachedata['details_type'].'-'.$cachedata['denomination_complementaire'].'-'.$cachedata['tav'];
          if (!isset($cepagedenomtav[$id_cepagedenomtav])) {
              continue;
          }
          if (!$this->drmPrecedente->exist($this->cache[$cacheid]->getCepage()->getHash())
             || !$this->drmPrecedente->get($this->cache[$cacheid]->getCepage()->getHash())->getDetailsNoeud($cachedata['details_type'])
             || !$this->drmPrecedente->get($this->cache[$cacheid]->getCepage()->getHash())->getDetailsNoeud($cachedata['details_type'])->exist($this->cache[$cacheid]->getKey())
             ) {
              if ($this->drm->get($this->cache[$cacheid]->getCepage()->getHash())->getDetailsNoeud($cachedata['details_type'])) {
                  $this->drm->get($this->cache[$cacheid]->getCepage()->getHash())->getDetailsNoeud($cachedata['details_type'])->remove($this->cache[$cacheid]->getKey());
              }
          }
          $this->cache[$cacheid] = $this->drm->getOrAdd($cepagedenomtav[$id_cepagedenomtav]);
          $this->cache2datas[$cacheid]['founded_produit'] = $this->cache[$cacheid]->getConfig();
          $this->cache2datas[$cacheid]['hash'] = $this->cache2datas[$cacheid]['founded_produit']->getHash();
          $this->cache2datas[$cacheid]['hash_detail'] = $this->cache[$cacheid]->getHash();
      }
      //avec le reorder, les référence vers les details sautent, on les re-récupère donc ici :
      foreach($this->cache2datas as $cacheid => $params) {
          if (isset($params['hash_detail'])) {
              $this->cache[$cacheid] = $this->drm->get($params['hash_detail']);
          }
      }
      $num_ligne = 0;
      foreach ($this->getDocRows() as $datas) {
          $num_ligne++;
          if (!isset($all_produits)) {
              $all_produits = $this->configuration->declaration->getProduits($datas[self::CSV_PERIODE]);
          }
          if (preg_match('/^(...)?#/', $datas[self::CSV_TYPE])) {
              continue;
          }
          if (strtoupper($datas[self::CSV_TYPE]) != self::TYPE_CAVE) {
              continue;
          }
          if (strtolower($datas[self::CSV_CAVE_CATEGORIE_MOUVEMENT] != 'stocks_debut')) {
              continue;
          }
          if (KeyInflector::slugify(trim($datas[self::CSV_TYPE])) != self::TYPE_CAVE) {
            continue;
          }
          $produit = isset($this->cache[$this->getCacheKeyFromData($datas)]) ? $this->cache[$this->getCacheKeyFromData($datas)] : null;
          if (!$produit) {
              continue;
          }

          $details_type = $this->cache2datas[$this->getCacheKeyFromData($datas)]['details_type'];
          $denomination_complementaire = $this->cache2datas[$this->getCacheKeyFromData($datas)]['denomination_complementaire'];
          $founded_produit = $this->cache2datas[$this->getCacheKeyFromData($datas)]['founded_produit'];

          if ($this->drmPrecedente && $this->drmPrecedente->teledeclare && !$this->drm->canSetStockDebutMois()
           && $this->drmPrecedente->exist($produit->getHash()) && isset($this->cache2datas[$this->getCacheKeyFromData($datas)][self::CSV_CAVE_VOLUME])) {

              $details_precedent = $this->drmPrecedente->get($produit->getHash());
              if(($this->convertNumber($this->cache2datas[$this->getCacheKeyFromData($datas)][self::CSV_CAVE_VOLUME]) != $details_precedent->getOrAdd('stocks_fin')->getOrAdd('final'))) {

                $this->csvDoc->addErreur($this->stockVolumeIncoherentError($num_ligne, $datas));
                continue;
              }
          }
      }

  }

  public function getProduitFromCache($datas) {
      if (!isset($this->cache[$this->getCacheKeyFromData($datas)])) {
          return null;
      }
      return $this->cache[$this->getCacheKeyFromData($datas)];
  }

  /**
  * IMPORT DEPUIS LE CSV
  */
  public function importCSV($withSave = true) {

    $this->importAnnexesFromCSV();
    $this->createCacheProduits();
    $this->importMouvementsFromCSV();
    $this->importCrdsFromCSV();
    $this->drm->etape = ($this->fromEdi)? DRMClient::ETAPE_VALIDATION_EDI : DRMClient::ETAPE_VALIDATION;
    $this->drm->type_creation = DRMClient::DRM_CREATION_EDI;
    $this->drm->buildFavoris();
    $this->drm->storeDeclarant();
    $this->updateAndControlCoheranceStocks();
    if($withSave) {
      $this->drm->save();
    }
  }

  public function updateAndControlCoheranceStocks() {
    /*$stocks = array();
    foreach($this->drm->getProduitsDetails() as $detail) {
    $stocks[$detail->getHash()] = $detail->stocks_fin->final;
  }*/

  $this->drm->update();

  /*foreach($this->drm->getProduitsDetails() as $detail) {
  if(!array_key_exists($detail->getHash(), $stocks) || is_null($stocks[$detail->getHash()])) {
  continue;
}

if(round($stocks[$detail->getHash()], 2) == round($detail->stocks_fin->final, 2)) {
continue;
}
$this->csvDoc->addErreur($this->createError(1, sprintf("%s %0.2f hl (CSV) / %0.2f hl (calculé)", $detail->produit_libelle, $stocks[$detail->getHash()], $detail->stocks_fin->final), "Le stock fin de mois du CSV différent du calculé"));
}*/

if ($this->csvDoc->hasErreurs()) {
  $this->csvDoc->setStatut(self::STATUT_WARNING);
  $this->csvDoc->save();
}
}

private function checkCSVIntegrity() {
  $ligne_num = 1;
  foreach ($this->getDocRows() as $csvRow) {
    if(count($csvRow) < 17){
      $this->csvDoc->addErreur($this->createWrongFormatFieldCountError($ligne_num, $csvRow));
      $ligne_num++;
      continue;
    }
    if ($ligne_num == 1 && KeyInflector::slugify($csvRow[self::CSV_TYPE]) == 'TYPE') {
      $ligne_num++;
      continue;
    }
    if (!in_array(KeyInflector::slugify($csvRow[self::CSV_TYPE]), self::$permitted_types)) {
      $this->csvDoc->addErreur($this->createWrongFormatTypeError($ligne_num, $csvRow));
    }
    if (!preg_match('/^[0-9]{6}$/', KeyInflector::slugify($csvRow[self::CSV_PERIODE]))) {
      $this->csvDoc->addErreur($this->createWrongFormatPeriodeError($ligne_num, $csvRow));
    }
    if (!preg_match('/^FR0[0-9A-Z]{10}$/', KeyInflector::slugify($csvRow[self::CSV_NUMACCISE]))) {
      //$this->csvDoc->addErreur($this->createWrongFormatNumAcciseError($ligne_num, $csvRow));
    }
    if($this->drm->getIdentifiant() != KeyInflector::slugify($csvRow[self::CSV_IDENTIFIANT]) && ($this->drm->getEtablissementObject()->getSociete()->identifiant != KeyInflector::slugify($csvRow[self::CSV_IDENTIFIANT]) && (!$csvRow[self::CSV_NUMACCISE] || $this->drm->getEtablissementObject()->no_accises != $csvRow[self::CSV_NUMACCISE]))) {
      $this->csvDoc->addErreur($this->otherNumeroCompteError($ligne_num, $csvRow));
    }
    if($this->drm->getPeriode() != KeyInflector::slugify($csvRow[self::CSV_PERIODE])){
      $this->csvDoc->addErreur($this->otherPeriodeError($ligne_num, $csvRow));
    }
    $ligne_num++;
  }
}

private function checkImportMouvementsFromCSV() {
  return $this->importMouvementsFromCSV(true);
}

private function checkImportCrdsFromCSV() {
  return $this->importCrdsFromCSV(true);
}

private function checkHorsRegionFromCSV() {
  $etablissementObj = $this->drm->getEtablissementObject();
  if ($etablissementObj->region == EtablissementClient::REGION_HORS_CVO) {
    $this->csvDoc->addErreur($this->importHorsRegionError());
  }
}

private function checkImportAnnexesFromCSV() {
  return $this->importAnnexesFromCSV(true);
}

private function importMouvementsFromCSV($just_check = false) {
  $date = new DateTime($this->drm->getDate());

  $has_default_hash = DRMConfiguration::getInstance()->hasEdiDefaultProduitHash();


  $num_ligne = 0;
  foreach ($this->getDocRows() as $csvRow) {
      $num_ligne++;
      if (KeyInflector::slugify(trim($csvRow[self::CSV_TYPE])) != self::TYPE_CAVE) {
        continue;
      }

      ///cache
      $drmDetails = $this->getProduitFromCache($csvRow);
      if (!$drmDetails) {
          continue;
      }
      $founded_produit = $this->cache[$this->getCacheKeyFromData($csvRow)]->getCepage()->getConfig();

    $cat_mouvement = KeyInflector::slugify($csvRow[self::CSV_CAVE_CATEGORIE_MOUVEMENT]);
    if(strtoupper(KeyInflector::slugify($cat_mouvement)) == self::COMPLEMENT){
      $this->importComplementMvt($csvRow,$founded_produit,$num_ligne, $just_check);
      continue;
    }

    $type_douane_drm = KeyInflector::slugify($csvRow[self::CSV_CAVE_TYPE_DRM]);
    $type_douane_drm_key = $this->getDetailsKeyFromDRMType($type_douane_drm);

    if(!$type_douane_drm_key) {
        $this->csvDoc->addErreur($this->DRMTypeNotFoundError($num_ligne, $csvRow));
        continue;
    }

    $type_mouvement = KeyInflector::slugify($csvRow[self::CSV_CAVE_TYPE_MOUVEMENT]);

    if (!array_key_exists($cat_mouvement, $this->mouvements[$type_douane_drm_key])) {
      $this->csvDoc->addErreur($this->categorieMouvementNotFoundError($num_ligne, $csvRow));
      continue;
    }
    if (!array_key_exists($type_mouvement, $this->mouvements[$type_douane_drm_key][$cat_mouvement])) {
      $this->csvDoc->addErreur($this->typeMouvementNotFoundError($num_ligne, $csvRow));
      continue;
    }
    $confDetailMvt = $this->mouvements[$type_douane_drm_key][$cat_mouvement][$type_mouvement];

    if ($confDetailMvt->hasDetails() && $confDetailMvt->getDetails() == ConfigurationDetailLigne::DETAILS_EXPORT) {
        $pays = ConfigurationClient::getInstance()->findCountry($csvRow[self::CSV_CAVE_EXPORTPAYS]);
        if (!$pays) {
          $this->csvDoc->addErreur($this->exportPaysNotFoundError($num_ligne, $csvRow));
          continue;
        }
    }

    if($just_check && $confDetailMvt->hasDetails()) {
      if ($confDetailMvt->getDetails() == ConfigurationDetailLigne::DETAILS_VRAC) {
        if ($csvRow[self::CSV_CAVE_CONTRATID] == "" && DRMConfiguration::getInstance()->hasSansContratOption()) {
          continue;
        }

        if (!$csvRow[self::CSV_CAVE_CONTRATID]) {
          $this->csvDoc->addErreur($this->contratIDEmptyError($num_ligne, $csvRow));
          continue;
        }
        $confDetailMvt = $this->mouvements[$type_douane_drm_key][$cat_mouvement][$type_mouvement];

        $vrac_id = $this->findContratDocId($csvRow);

        if(!$vrac_id && $founded_produit->isCVOActif($date)) {
          $this->csvDoc->addErreur($this->contratIDNotFoundError($num_ligne, $csvRow));
          continue;
        }
      }
    }


    $detailTotalVol = $this->convertNumber($csvRow[self::CSV_CAVE_VOLUME]);
    $volume = $this->convertNumber($csvRow[self::CSV_CAVE_VOLUME]);

    $cat_key = $confDetailMvt->getParent()->getKey();
    $type_key = $confDetailMvt->getKey();

    if(!preg_match("/stocks/", $cat_key) && $volume < 0) {
        $this->csvDoc->addErreur($this->mouvementVolumeNegatifError($num_ligne, $csvRow));
        continue;
    }

    if($just_check) {
      continue;
    }

    if($cat_key == "stocks_debut" && !$drmDetails->canSetStockDebutMois()) {
      continue;
    }
    if($csvRow[self::CSV_CAVE_VOLUME] === "") {
      continue;
    }

    if ($confDetailMvt->hasDetails()) {
      $detailTotalVol += $this->convertNumber($drmDetails->getOrAdd($cat_key)->getOrAdd($type_key));

      if ($confDetailMvt->getDetails() == ConfigurationDetailLigne::DETAILS_REINTEGRATION) {
        $dateReplacement = $this->getDateReplacementObject($csvRow[self::CSV_CAVE_EXPORTPAYS]);
        $reintegration = DRMESDetailReintegration::freeInstance($this->drm);
        $reintegration->volume = $volume;
        $reintegration->date = ($dateReplacement)? $dateReplacement->format('Y-m-d') : null;
        $drmDetails->getOrAdd($cat_key)->getOrAdd($type_key . '_details')->addDetail($reintegration);
      }
      if ($confDetailMvt->getDetails() == ConfigurationDetailLigne::DETAILS_EXPORT) {
        $pays = ConfigurationClient::getInstance()->findCountry($csvRow[self::CSV_CAVE_EXPORTPAYS]);
        $export = DRMESDetailExport::freeInstance($this->drm);
        $export->volume = $volume;
        $export->identifiant = $pays;
        $drmDetails->getOrAdd($cat_key)->getOrAdd($type_key . '_details')->addDetail($export);
      }

      if ($confDetailMvt->getDetails() == ConfigurationDetailLigne::DETAILS_VRAC) {
        if ($founded_produit->isCVOActif($date)) {
            $vrac_id = $this->findContratDocId($csvRow);
        } elseif(!$csvRow[self::CSV_CAVE_CONTRATID] && DRMConfiguration::getInstance()->hasSansContratOption()){
            $vrac_id = DRMESDetailVrac::CONTRAT_SANS_NUMERO;
        } else {
            $vrac_id = $csvRow[self::CSV_CAVE_CONTRATID];
        }

        $detailNode = $drmDetails->getOrAdd($cat_key)->getOrAdd($type_key . '_details')->add($vrac_id);
        if ($detailNode->volume) {
          $volume+=$detailNode->volume;
        }
        $detailNode->volume = $volume;
        $detailNode->identifiant = $vrac_id;
        $detailNode->date_enlevement = $date->format('Y-m-d');
      }
      if($confDetailMvt->getDetails() == ConfigurationDetailLigne::DETAILS_CREATIONVRAC){
        $creationvrac = DRMESDetailCreationVrac::freeInstance($this->drm);
        $creationvrac->volume = $volume;
        $creationvrac->prixhl = floatval($csvRow[self::CSV_CAVE_CONTRAT_PRIXHL]);
        $nego = EtablissementClient::getInstance()->findByNoAccise($csvRow[self::CSV_CAVE_CONTRAT_ACHETEUR_ACCISES]);
        if (!$nego) {
          $nego = EtablissementClient::getInstance()->retrieveByName(str_replace(".", "", $csvRow[self::CSV_CAVE_CONTRAT_ACHETEUR_NOM]));
        }
        $creationvrac->acheteur = $nego->identifiant;
        $creationvrac->type_contrat = ($type_key == 'creationvrac')? VracClient::TYPE_TRANSACTION_VIN_VRAC : VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE;
        $drmDetails->getOrAdd($cat_key)->getOrAdd($type_key . '_details')->addDetail($creationvrac);
      }
    } else {
      $oldVolume = $drmDetails->getOrAdd($cat_key)->getOrAdd($type_key);
      if($cat_key == "stocks_debut" && !is_null($oldVolume) && $oldVolume != "") {
        if ($drmDetails->canSetStockDebutMois()) {
            $drmDetails->getOrAdd($cat_key)->add($type_key, $detailTotalVol);
        }else {
            $this->drm->commentaire .= sprintf("IMPORT de %s le stock_debut %s de %s hl n'a pas été pris en compte\n", $drmDetails->getLibelle(), $type_key, $detailTotalVol);
        }
      } else {
        $drmDetails->getOrAdd($cat_key)->add($type_key, $oldVolume + $detailTotalVol);
      }
    }

    if(isset($csvRow[self::CSV_CAVE_COMMENTAIRE]) && $csvRow[self::CSV_CAVE_COMMENTAIRE] && trim($csvRow[self::CSV_CAVE_COMMENTAIRE])) {
      $this->drm->commentaire .= str_replace("\\n", "\n", trim($csvRow[self::CSV_CAVE_COMMENTAIRE]));
      if(!preg_match("/\n$/", $this->drm->commentaire)) {
        $this->drm->commentaire .= "\n";
      }
    }

    $dateReplacement = $this->getDateReplacementObject($csvRow[self::CSV_CAVE_EXPORTPAYS]);
    if($dateReplacement) {
      $drmDetails->add("replacement_date", $dateReplacement->format("Y-m-d"));
    }
  }
}

private function getDateReplacementObject($csvDate) {
    $dateReplacement = null;
    if (preg_match('/^2\d\d\d-\d\d-\d\d$/', $csvDate)) {
      $dateReplacement = new DateTime($csvDate);
    }

    if (preg_match('/^(2\d\d\d)(\d\d)$/', $csvDate, $matches)) {
      $dateReplacement = new DateTime($matches[1]."-".$matches[2]."-01");
      $dateReplacement->modify("last day of this month");
    }

    return $dateReplacement;
}
private function importComplementMvt($csvRow, $founded_produit, $num_ligne, $just_check  = false){
  $type_complement = strtoupper(KeyInflector::slugify($csvRow[self::CSV_CAVE_TYPE_COMPLEMENT_PRODUIT]));
  if(!in_array($type_complement, self::$types_complement)){
    $this->csvDoc->addErreur($this->typeComplementNotFoundError($num_ligne, $csvRow));
    return;
  }
  $valeur_complement = $csvRow[self::CSV_CAVE_VALEUR_COMPLEMENT_PRODUIT];
  if(!$valeur_complement){
    $this->csvDoc->addErreur($this->valueComplementVide($num_ligne, $csvRow));
    return;
  }
  if(!$just_check){
    $valeur_complement = $csvRow[self::CSV_CAVE_VALEUR_COMPLEMENT_PRODUIT];
    $value = null;
    switch ($type_complement) {
      case self::COMPLEMENT_TAV:
      $value = $this->convertNumber($valeur_complement);
      break;
      case self::COMPLEMENT_OBSERVATIONS:
      $value = $valeur_complement;
      break;
      case self::COMPLEMENT_PREMIX:
      $value = boolval($valeur_complement);
      break;
    }
    $denomination_complementaire = (trim($csvRow[self::CSV_CAVE_LIBELLE_COMPLEMENTAIRE]))? trim($csvRow[self::CSV_CAVE_LIBELLE_COMPLEMENTAIRE]) : false;
    $drmDetails = $this->getProduitFromCache($csvRow);
    $field = strtolower($type_complement);
    $drmDetails->add($field, $value);
  }
}

private static function cdrreversekeyid($regime, $genre, $couleur, $libelle) {
    if (!$couleur) {
        $couleur = 'DEFAUT';
    }
    return $regime.'-'.$genre.'-'.$couleur.'-'.DRMClient::convertCRDLitrage($libelle);
}

private function importCrdsFromCSV($just_check = false) {
   if ($this->drm->canSetStockDebutMois()) {
       $this->drm->remove('crds');
       $this->drm->add('crds');
   }
  $num_ligne = 0;
  $edited = false;
  $etablissementObj = $this->drm->getEtablissementObject();

  $crd_regime = ($etablissementObj->exist('crd_regime'))? $etablissementObj->get('crd_regime') : EtablissementClient::REGIME_CRD_COLLECTIF_SUSPENDU;
  $all_contenances = VracConfiguration::getInstance()->getContenancesSlugified();

  $crd_precedente = array();
  foreach($this->drmPrecedente->crds as $regime => $crds) {
      foreach($crds as $key => $crd) {
          $kid = self::cdrreversekeyid($regime, $crd->genre, $crd->couleur, $crd->detail_libelle);
          if (!isset($crd_precedente[$kid])) {
              $crd_precedente[$kid] = array();
          }
          $crd_precedente[$kid][] = $crd->getKey();
      }
  }

  foreach ($this->getDocRows() as $csvRow) {
    $num_ligne++;
    if (KeyInflector::slugify($csvRow[self::CSV_TYPE] != self::TYPE_CRD)) {
      continue;
    }
    $genre = DRMClient::convertCRDGenre($csvRow[self::CSV_CRD_GENRE]);
    $couleur = DRMClient::convertCRDCouleur($csvRow[self::CSV_CRD_COULEUR]);
    $litrageKey = DRMClient::convertCRDLitrage($csvRow[self::CSV_CRD_CENTILITRAGE]);

    $crd_regime = DRMClient::convertCRDRegime($csvRow[self::CSV_CRD_REGIME]);
    $categorie_key = DRMClient::convertCRDCategorie($csvRow[self::CSV_CRD_CATEGORIE_KEY]);
    $type_key = DRMClient::convertCRDtype($csvRow[self::CSV_CRD_TYPE_KEY]);

    $quantite = KeyInflector::slugify($csvRow[self::CSV_CRD_QUANTITE]);
    $fieldNameCrd = $categorie_key;
    if ($categorie_key != "stock_debut" && $categorie_key != "stock_fin") {
      $fieldNameCrd.="_" . $type_key;
    }
    if (!isset($all_contenances[$litrageKey]))  {
      $this->csvDoc->addErreur($this->centiCRDNotFoundError($num_ligne, $csvRow));
      continue;
    }
    if ($csvRow[self::CSV_CRD_COULEUR] && !$couleur) {
      $this->csvDoc->addErreur($this->couleurCRDNotFoundError($num_ligne, $csvRow));
      continue;
    }
    if (!$genre) {
      $this->csvDoc->addErreur($this->genreCRDNotFoundError($num_ligne, $csvRow));
      continue;
    }
    if (!$categorie_key) {
      $this->csvDoc->addErreur($this->categorieCRDNotFoundError($num_ligne, $csvRow));
      continue;
    }
    if (!$type_key) {
      $this->csvDoc->addErreur($this->typeCRDNotFoundError($num_ligne, $csvRow));
      continue;
    }

    $centilitrage = $all_contenances[$litrageKey];
    $litrageLibelle = DRMClient::getInstance()->getLibelleCRD($litrageKey);
    $regimeNode = $this->drm->getOrAdd('crds')->getOrAdd($crd_regime);
    $keyNode = null;
    $reverseKey = self::cdrreversekeyid($regime, $genre, $couleur, $litrageLibelle);
    if (isset($crd_precedente[$reverseKey])) {
        $keyNode = array_pop($crd_precedente[$reverseKey]);
    }
    if (!$keyNode) {
        $keyNode = $regimeNode->constructKey($genre, $couleur, $centilitrage, $litrageLibelle);
    }

    if(!in_array($fieldNameCrd, array('stock_debut', 'entrees_achats', 'entrees_excedents', 'entrees_retours', 'sorties_destructions', 'sorties_manquants', 'sorties_utilisations', 'stock_fin'))) {
        $this->csvDoc->addErreur($this->typeCRDNotFoundError($num_ligne, $csvRow));
        continue;
    }

    $drmPrecedente = DRMClient::getInstance()->find("DRM-".$this->drm->identifiant."-".DRMClient::getInstance()->getPeriodePrecedente($this->drm->periode));
    if ($drmPrecedente) {
        if  ($fieldNameCrd == 'stock_debut') {
          if ($quantite && (!$drmPrecedente->crds->exist($crd_regime) || !$drmPrecedente->crds->get($crd_regime)->exist($keyNode))) {
            $this->csvDoc->addErreur($this->previousCRDProductError($num_ligne, $csvRow));
            continue;
          }

          if ($drmPrecedente->crds->exist($crd_regime)  && $drmPrecedente->crds->get($crd_regime)->exist($keyNode) && !$this->drm->canSetStockDebutMois()) {
            if ($drmPrecedente->crds->get($crd_regime)->get($keyNode)->stock_fin != $quantite) {
              $this->csvDoc->addErreur($this->previousCRDStockError($num_ligne, $csvRow));
              continue;
            }
          }
        }
    }

    if (!$just_check) {
      if (!$regimeNode->exist($keyNode)) {
        $regimeNode->getOrAddCrdNode($genre, $couleur, $centilitrage, $litrageLibelle);
      }
      if (!preg_match('/^stock/', $fieldNameCrd) || $regimeNode->getOrAdd($keyNode)->{$fieldNameCrd} == null || ($this->drm->canSetStockDebutMois() && preg_match('/debut/', $fieldNameCrd))) {
        $regimeNode->getOrAdd($keyNode)->{$fieldNameCrd} += intval($quantite);
        $edited = true;
      }
    }
  }
  if ($edited) {
      $this->drm->updateStockFinDeMoisAllCrds();
  }
  return $this->csvDoc->hasErreurs();
}

private function importAnnexesFromCSV($just_check = false) {
  $num_ligne = 0;
  $typesAnnexes = array_keys($this->type_annexes);
  foreach ($this->getDocRows() as $csvRow) {
    $num_ligne++;
    if (KeyInflector::slugify($csvRow[self::CSV_TYPE] != self::TYPE_ANNEXE)) {
      continue;
    }
    switch (KeyInflector::slugify($csvRow[self::CSV_ANNEXE_TYPEANNEXE])) {
      case self::TYPE_ANNEXE_NONAPUREMENT:
      $numero_document = trim(KeyInflector::slugify($csvRow[self::CSV_ANNEXE_NUMERODOCUMENT]));
      $date_emission = KeyInflector::slugify($csvRow[self::CSV_ANNEXE_NONAPUREMENTDATEEMISSION]);
      $dt = DateTime::createFromFormat("Y-m-d", $date_emission);
      if (!$dt) {
          $dt = DateTime::createFromFormat("d-m-Y", $date_emission);
      }

      $numero_accise = KeyInflector::slugify($csvRow[self::CSV_ANNEXE_NONAPUREMENTACCISEDEST]);
      if (!$numero_document) {
        if ($just_check) {
          $this->csvDoc->addErreur($this->annexesNumeroDocumentError($num_ligne, $csvRow));
        }
        break;
      }
      if (!$date_emission || $dt == false || array_sum($dt->getLastErrors())) {
        if ($just_check) {
          $this->csvDoc->addErreur($this->annexesNonApurementWrongDateError($num_ligne, $csvRow));
        }
        break;
      }
      if ($numero_accise && !preg_match('/^[A-Z]{2}[0-9A-Z]{11}$/', $numero_accise)) {
        if ($just_check) {
          $this->csvDoc->addErreur($this->annexesNonApurementWrongNumAcciseError($num_ligne, $csvRow));
        }
        break;
      }
      if (!$just_check) {
        $nonAppurementNode = $this->drm->getOrAdd('releve_non_apurement')->getOrAdd($numero_document);
        $nonAppurementNode->numero_document = $numero_document;
        $nonAppurementNode->date_emission = $dt->format('Y-m-d');
        $nonAppurementNode->numero_accise = $numero_accise;
      }
      break;

      case DRMClient::DRM_DOCUMENTACCOMPAGNEMENT_DAADAC:
      case DRMClient::DRM_DOCUMENTACCOMPAGNEMENT_DSADSAC:
      case DRMClient::DRM_DOCUMENTACCOMPAGNEMENT_EMPREINTE:
      $docTypeAnnexe = $this->drm->getOrAdd('documents_annexes')->getOrAdd(KeyInflector::slugify($csvRow[self::CSV_ANNEXE_TYPEANNEXE]));
      $annexeTypeMvt = KeyInflector::slugify($csvRow[self::CSV_ANNEXE_TYPEMVT]);
      $numDocument = KeyInflector::slugify(($csvRow[self::CSV_ANNEXE_QUANTITE]) ? $csvRow[self::CSV_ANNEXE_QUANTITE] :  $csvRow[self::CSV_ANNEXE_NUMERODOCUMENT]);
      if (!in_array($annexeTypeMvt, self::$permitted_annexes_type_mouvements)) {
        if ($just_check) {
          $this->csvDoc->addErreur($this->annexesTypeMvtWrongFormatError($num_ligne, $csvRow));
      }
        break;
      }
      if (!$numDocument) {
        if ($just_check) {
          $this->csvDoc->addErreur($this->annexesNumeroDocumentError($num_ligne, $csvRow));
        }
        break;
      }
      if (!$just_check) {
        $docTypeAnnexe->add(strtolower($annexeTypeMvt), $numDocument);
      }
      break;
      case self::TYPE_ANNEXE_STATS_EUROPEENES :
      $this->drm->getOrAdd('declaratif')->getOrAdd('statistiques')->add(strtolower($csvRow[self::CSV_ANNEXE_TYPEMVT]),round(floatval($csvRow[self::CSV_ANNEXE_QUANTITE]), 2));
      break;
      default:
      if ($just_check) {
        $this->csvDoc->addErreur($this->typeDocumentWrongFormatError($num_ligne, $csvRow));
      }
      break;
    }
  }
}

private function convertNumber($number){
  $numberPointed = trim(str_replace(",",".",$number));
  return round(floatval($numberPointed), FloatHelper::getInstance()->getMaxDecimalAuthorized());
}

private function getDetailsKeyFromDRMType($drmType ) {
  if(KeyInflector::slugify($drmType) == "SUSPENDU") {

    return DRM::DETAILS_KEY_SUSPENDU;
  }

  if(KeyInflector::slugify($drmType) == "ACQUITTE") {

    return DRM::DETAILS_KEY_ACQUITTE;
  }

  return null;
}

private function findContratDocId($csvRow) {
  $csvRow[self::CSV_CAVE_CONTRATID] = str_replace("-", "", $csvRow[self::CSV_CAVE_CONTRATID]);
  if($csvRow[self::CSV_CAVE_CONTRATID] == "" && DRMConfiguration::getInstance()->hasSansContratOption()) {

    return DRMESDetailVrac::CONTRAT_SANS_NUMERO;
  }

  if($vrac = VracClient::getInstance()->findByNumContrat($csvRow[self::CSV_CAVE_CONTRATID], acCouchdbClient::HYDRATE_JSON)) {

    return $vrac->_id;
  }

  if($vrac_id = VracClient::getInstance()->findDocIdByNumArchive("UNIQUE", $csvRow[self::CSV_CAVE_CONTRATID])) {

    return $vrac_id;
  }

  return VracClient::getInstance()->findDocIdByNumArchive($this->drm->campagne, $csvRow[self::CSV_CAVE_CONTRATID], 2);
}

/**
* Functions de création d'erreurs
*/
private function otherNumeroCompteError($num_ligne, $csvRow) {
  return $this->createError($num_ligne,
  KeyInflector::slugify($csvRow[self::CSV_IDENTIFIANT]),
  "Le numéro de compte n'est pas celui du ressortissant attendu",
  CSVDRMClient::LEVEL_ERROR);
}

private function createWrongFormatTypeError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, KeyInflector::slugify($csvRow[self::CSV_TYPE]), "Choix possible type : " . implode(', ', self::$permitted_types));
}

private function createWrongFormatFieldCountError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, KeyInflector::slugify($csvRow[self::CSV_TYPE]), "La ligne possède trop peu de colonnes.");
}

private function createWrongFormatNumAcciseError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, KeyInflector::slugify($csvRow[self::CSV_NUMACCISE]), "Format numéro d'accise : FR0XXXXXXXXXX");
}

private function createWrongFormatPeriodeError($num_ligne, $csvRow) {
  return $this->createError($num_ligne,
  KeyInflector::slugify($csvRow[self::CSV_PERIODE]),
  "Format période : AAAAMM",
  CSVDRMClient::LEVEL_ERROR);
}
private function otherPeriodeError($num_ligne, $csvRow) {
  return $this->createError($num_ligne,
  KeyInflector::slugify($csvRow[self::CSV_PERIODE]),
  "La période spécifiée ne correspond pas à celle transmise",
  CSVDRMClient::LEVEL_ERROR);
}

private function productNotFoundError($num_ligne, $csvRow) {
  $libellesArray = $this->buildLibellesArrayWithRow($csvRow);
  return $this->createError($num_ligne, implode(' ', $libellesArray), "Le produit n'a pas été trouvé");
}

private function DRMTypeNotFoundError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_CAVE_TYPE_DRM], "Le type de la DRM n'est pas connu doit être suspendu ou acquitte");
}

private function categorieMouvementNotFoundError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_CAVE_CATEGORIE_MOUVEMENT], "La catégorie de mouvement n'a pas été trouvée");
}

private function typeMouvementNotFoundError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_CAVE_TYPE_MOUVEMENT], "Le type de mouvement n'a pas été trouvé");
}

private function mouvementVolumeNegatifError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_CAVE_TYPE_MOUVEMENT], "Le volume est négatif");
}

private function stockVolumeIncoherentError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_CAVE_TYPE_MOUVEMENT], "Le stock n'est pas cohérent par rapport à la DRM précédente", CSVDRMClient::LEVEL_WARNING);
}
private function centiCRDNotFoundError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_CRD_CENTILITRAGE], "La centilisation de CRD n'a pas été trouvée");
}
private function couleurCRDNotFoundError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_CRD_COULEUR], "La couleur de CRD n'a pas été trouvée");
}
private function genreCRDNotFoundError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_CRD_GENRE], "Le genre de CRD n'a pas été trouvé");
}
private function categorieCRDNotFoundError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_CRD_CATEGORIE_KEY], "La categorie de mouvement de CRD (entrees, sorties, ...) n'a pas été trouvée");
}
private function typeCRDNotFoundError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_CRD_CATEGORIE_KEY]."/".$csvRow[self::CSV_CRD_TYPE_KEY], "Le type de mouvement de CRD (achats, utilisations, ...) n'a pas été trouvé");
}
private function previousCRDProductError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_CRD_REGIME], "Il n'existe pas de stock pour cette crd dans la DRM précédente");
}
private function previousCRDStockError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_CRD_REGIME], "Le stock initial pour cette crd n'est pas conforme à la DRM précédente");
}
private function exportPaysNotFoundError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_CAVE_EXPORTPAYS], "Le pays d'export n'a pas été trouvé");
}
private function contratIDEmptyError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_CAVE_CONTRATID], "L'id du contrat ne peut pas être vide");
}
private function contratIDNotFoundError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_CAVE_CONTRATID], "Le contrat n'a pas été trouvé");
}
private function observationsEmptyError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, "Observations", "Les observations sont vides.");
}

private function sucreWrongFormatError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_ANNEXE_QUANTITE], "La quantité de sucre est nulle ou possède un mauvais format.");
}

private function typeDocumentWrongFormatError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_ANNEXE_TYPEANNEXE], "Le type de document d'annexe n'est pas connu.");
}

private function annexesTypeMvtWrongFormatError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_ANNEXE_TYPEMVT], "Le type d'enregistrement des " . $csvRow[self::CSV_ANNEXE_TYPEANNEXE] . " doit être 'début' ou 'fin' .");
}

private function annexesNumeroDocumentError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_ANNEXE_TYPEANNEXE], "Le numéro de document ne peut pas être vide.");
}

private function importHorsRegionError() {
  return $this->createError(0, "Etablissemment", "Import DRM non permis pour les établissements hors région.");
}

private function annexesNonApurementWrongDateError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_ANNEXE_NONAPUREMENTDATEEMISSION], "La date est vide ou mal formattée (".$csvRow[self::CSV_ANNEXE_NONAPUREMENTDATEEMISSION].").");
}

private function annexesNonApurementWrongNumAcciseError($num_ligne, $csvRow) {
  return $this->createError($num_ligne, $csvRow[self::CSV_ANNEXE_NONAPUREMENTACCISEDEST], "La numéro d'accise du destinataire est mal formatté (".$csvRow[self::CSV_ANNEXE_NONAPUREMENTACCISEDEST].").");
}

private function typeComplementNotFoundError($num_ligne, $csvRow) {
  return $this->createError($num_ligne,
  $csvRow[self::CSV_CAVE_TYPE_COMPLEMENT_PRODUIT],
  "Le type de complément doit être observations, tav ou premix",
  CSVDRMClient::LEVEL_WARNING);
}

private function valueComplementVide($num_ligne, $csvRow) {
  return $this->createError($num_ligne,
  $csvRow[self::CSV_CAVE_VALEUR_COMPLEMENT_PRODUIT],
  "La valeur du complément doit être renseignée",
  CSVDRMClient::LEVEL_WARNING);
}

private function createError($num_ligne, $erreur_csv, $raison, $level = CSVDRMClient::LEVEL_WARNING) {
  $error = new stdClass();
  $error->num_ligne = $num_ligne;
  $error->erreur_csv = $erreur_csv;
  $error->raison = $raison;
  $error->level = $level;
  return $error;
}

/**
* Fin des functions de création d'erreurs
*/
private function getIdDouane($datas)
{
	$certification = trim(str_replace(array('(', ')'), '', $datas[self::CSV_CAVE_CERTIFICATION]));
	if (
    	$certification &&
    	!trim($datas[self::CSV_CAVE_GENRE]) &&
    	!trim($datas[self::CSV_CAVE_APPELLATION]) &&
    	!trim($datas[self::CSV_CAVE_MENTION]) &&
    	!trim($datas[self::CSV_CAVE_LIEU]) &&
    	!trim($datas[self::CSV_CAVE_COULEUR]) &&
    	!trim($datas[self::CSV_CAVE_CEPAGE])
	) {
        if(preg_match("/VT/", $datas[self::CSV_CAVE_LIBELLE_PRODUIT])) {

            $certification = preg_replace('/D2([0-9]{1})$/', 'D1\1', $certification);
        }
        if(preg_match("/SGN/", $datas[self::CSV_CAVE_LIBELLE_PRODUIT])) {

            $certification = preg_replace('/D1([0-9]{1})$/', 'D6\1', $certification);
        }

		return $certification;
	}

    if (preg_match('/(.*[^ ]) *\(([^\)]+)\)/', $datas[self::CSV_CAVE_LIBELLE_PRODUIT], $m) && trim($m[2])) {
        return $m[2];
    }

	return null;
}

private function buildLibellesArrayWithRow($csvRow, $with_slugify = false) {
  $certification = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_CAVE_CERTIFICATION]) : $csvRow[self::CSV_CAVE_CERTIFICATION];
  $genre = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_CAVE_GENRE]) : $csvRow[self::CSV_CAVE_GENRE];
  $appellation = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_CAVE_APPELLATION]) : $csvRow[self::CSV_CAVE_APPELLATION];
  $mention = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_CAVE_MENTION]) : $csvRow[self::CSV_CAVE_MENTION];
  $lieu = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_CAVE_LIEU]) : $csvRow[self::CSV_CAVE_LIEU];
  $couleur = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_CAVE_COULEUR]) : $csvRow[self::CSV_CAVE_COULEUR];
  $cepage = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_CAVE_CEPAGE]) : $csvRow[self::CSV_CAVE_CEPAGE];

  $libelles = array(strtoupper($certification),
  strtoupper($genre),
  strtoupper($appellation),
  strtoupper($mention),
  strtoupper($lieu),
  strtoupper($couleur),
  strtoupper($cepage));
  foreach ($libelles as $key => $libelle) {
    if (!$libelle) {
      $libelles[$key] = null;
    }
  }
  return $libelles;
}
private function slugifyProduitArrayOrString($produitLibelles) {
  $produitLibellesStr = is_array($produitLibelles)? implode(" ",$produitLibelles) : $produitLibelles;
  return strtoupper(KeyInflector::slugify(trim(preg_replace("/[\ ]+/"," ",$produitLibellesStr))));
}

private function slugifyProduitConf($produit, $withAOP = false, $withGenre = true) {
  $libellesSlugified = array();
  foreach ($produit->getLibelles() as $key => $libelle) {
    $libellesSlugified[] = strtoupper(KeyInflector::slugify($libelle));
  }
  if($withGenre) {
      $genreKey = $produit->getGenre()->getKey();
      if(isset(self::$genres[$genreKey])) {
        $genreLibelle = self::$genres[$genreKey];
      } else {
        $genreLibelle = null;
      }
      $libellesSlugified[1] = strtoupper(KeyInflector::slugify($genreLibelle));
  }
  if(($libellesSlugified[0] == "AOC") && $withAOP){
    $libellesSlugified[0]="AOP";
  }
  $libellesSlugified[2] = str_replace("AOC-", "", $libellesSlugified[2]);
  foreach ($libellesSlugified as $key => $libelle) {
    if (!$libelle) {
      $libellesSlugified[$key] = null;
    }
  }
  return $libellesSlugified;
}

private function buildAllMouvements() {
  $all_conf_details_slugified = array("details" => array(), "detailsACQUITTE" => array());
  foreach($this->configuration->declaration->filter('details') as $keyType => $all_conf_details) {
    foreach ($all_conf_details as $all_conf_detail_cat_Key => $all_conf_detail_cat) {
      foreach ($all_conf_detail_cat as $key_type => $type_detail) {
        if (!array_key_exists(KeyInflector::slugify($all_conf_detail_cat_Key), $all_conf_details_slugified[$keyType])) {
          $all_conf_details_slugified[$keyType][KeyInflector::slugify($all_conf_detail_cat_Key)] = array();
        }
        if (KeyInflector::slugify($key_type) == 'VRAC') {
          $all_conf_details_slugified[$keyType][KeyInflector::slugify($all_conf_detail_cat_Key)]['CONTRAT'] = $type_detail;
        }
        $all_conf_details_slugified[$keyType][KeyInflector::slugify($all_conf_detail_cat_Key)][KeyInflector::slugify($key_type)] = $type_detail;
      }
    }
  }

  return $all_conf_details_slugified;
}

public function setNoSave($noSave){
  $this->noSave = $noSave;
}


private function isEmptyArray($array){
  foreach ($array as $csvLibelle) {
    if($csvLibelle){
      return false;
    }
  }
  return true;
}
}
