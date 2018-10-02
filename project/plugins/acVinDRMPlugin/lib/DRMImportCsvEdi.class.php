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

      public function __construct($file, DRM $drm = null) {
            $this->initConf($drm);

            if(is_null($this->csvDoc)) {
                $this->csvDoc = CSVDRMClient::getInstance()->createOrFindDocFromDRM($file, $drm);
            }
            parent::__construct($file, $drm);
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
            return;
        }
        // Check annexes
        $this->checkImportAnnexesFromCSV();
        // Check mouvements
        $this->checkImportMouvementsFromCSV();
        // Check Crds
        $this->checkImportCrdsFromCSV();
        // Check Crds
        $this->checkHorsRegionFromCSV();

        if ($this->csvDoc->hasErreurs()) {
            $this->csvDoc->setStatut(self::STATUT_WARNING);
            $this->csvDoc->save();
            return false;
        }
        $this->csvDoc->setStatut(self::STATUT_VALIDE);
        $this->csvDoc->save();
        return true;
    }

    /**
     * IMPORT DEPUIS LE CSV
     */
    public function importCSV($withSave = true) {
        if($this->drm->isNew()) {
            $this->drm->constructId();
        }
        $this->importAnnexesFromCSV();

        $this->importMouvementsFromCSV();
        $this->importCrdsFromCSV();
        $this->drm->etape = DRMClient::ETAPE_VALIDATION;
        $this->drm->type_creation = "IMPORT";
        $this->drm->etape = ($this->fromEdi)? DRMClient::ETAPE_VALIDATION_EDI : DRMClient::ETAPE_VALIDATION;
        $this->drm->type_creation = DRMClient::DRM_CREATION_EDI;
        $this->drm->buildFavoris();
        $this->drm->storeDeclarant();
        $this->drm->initSociete();
        $this->updateAndControlCoheranceStocks();

        if($withSave) {
            $this->drm->save();
        }
        return true;
    }

    public function updateAndControlCoheranceStocks() {
        $this->drm->update();
        $this->drm->updateStockFinDeMoisAllCrds();
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
            $aggregatedEdiList = null;
            if(DRMConfiguration::getInstance()->hasAggregatedEdi()){
              $aggregatedEdiList = DRMConfiguration::getInstance()->getAggregatedEdi();
            }
            $all_produits = $this->configuration->declaration->getProduitsAll();

        $num_ligne = 1;
        foreach ($this->getDocRows() as $csvRow) {
            if (KeyInflector::slugify($csvRow[self::CSV_TYPE] != self::TYPE_CAVE)) {
                $num_ligne++;
                continue;
            }
            $csvLibelleProductArray = $this->buildLibellesArrayWithRow($csvRow, true);
 	    $csvLibelleProductComplet = $this->slugifyProduitArrayOrString($csvLibelleProductArray);
            $founded_produit = false;
            $keys_libelle = preg_replace("/[ ]+/", " ", sprintf("%s %s %s %s %s %s %s", $csvRow[self::CSV_CAVE_CERTIFICATION], $csvRow[self::CSV_CAVE_GENRE], $csvRow[self::CSV_CAVE_APPELLATION], $csvRow[self::CSV_CAVE_MENTION], $csvRow[self::CSV_CAVE_LIEU], $csvRow[self::CSV_CAVE_COULEUR], $csvRow[self::CSV_CAVE_CEPAGE]));

            $keys_libelle_mention_fin = preg_replace("/[ ]+/", " ", sprintf("%s %s %s %s %s %s %s", $csvRow[self::CSV_CAVE_CERTIFICATION], $csvRow[self::CSV_CAVE_GENRE], $csvRow[self::CSV_CAVE_APPELLATION], $csvRow[self::CSV_CAVE_LIEU], $csvRow[self::CSV_CAVE_COULEUR], $csvRow[self::CSV_CAVE_CEPAGE],$csvRow[self::CSV_CAVE_MENTION]));

            if(!$founded_produit && ($keys_libelle != '      ')) {
                $founded_produit = $this->configuration->identifyProductByLibelle(KeyInflector::slugify(str_replace("AOC AOC","AOC",$keys_libelle)));
            }
            if(!$founded_produit && ($keys_libelle != '      ')) {
                $founded_produit = $this->configuration->identifyProductByLibelle(KeyInflector::slugify(str_replace("AOC AOC","AOC",$keys_libelle_mention_fin)));
            }

            if(!$founded_produit && preg_match('/(.*) *\(([^\)]+)\)/', $csvRow[self::CSV_CAVE_LIBELLE_PRODUIT], $m)) {
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
            }
            if(!$founded_produit) {
                $founded_produit = $this->configuration->identifyProductByLibelle(trim(preg_replace('/ *\(.*/', '', preg_replace("/[ ]+/", " ", $csvRow[self::CSV_CAVE_LIBELLE_PRODUIT]))));
            }


            if (!$founded_produit) {
                foreach ($all_produits as $produit) {
                    if ($founded_produit) {
                        break;
                    }
                    $produitConfLibelleAOC = $this->slugifyProduitConf($produit);
                    $produitConfLibelleAOP = $this->slugifyProduitConf($produit,true);
                    $libelleCompletConfAOC = $this->slugifyProduitArrayOrString($produitConfLibelleAOC);
                    $libelleCompletConfAOP = $this->slugifyProduitArrayOrString($produitConfLibelleAOP);
                    $libelleCompletEnCsv = $this->slugifyProduitArrayOrString($csvRow[self::CSV_CAVE_LIBELLE_PRODUIT]);

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
                        && ($this->slugifyProduitArrayOrString($produit->getLibelleFormat()) != $libelleCompletEnCsv)) {
                        continue;
                    }
                    $founded_produit = $produit;
                    $date = new DateTime($this->drm->getDate());
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

            if($founded_produit && $aggregatedEdiList && count($aggregatedEdiList) && count($aggregatedEdiList[0])
            && isset($aggregatedEdiList[0][$founded_produit->getHash()])){
              $founded_produit = $all_produits[$aggregatedEdiList[0][$founded_produit->getHash()]];
            }

            if($founded_produit && !$founded_produit->isDouaneActif($this->drm->getDate()) && !$founded_produit->isCVOActif($this->drm->getDate())) {
                $founded_produit = null;
            }

 	        if (!$founded_produit) {
              $this->csvDoc->addErreur($this->productNotFoundError($num_ligne, $csvRow));
              $num_ligne++;
              continue;
            }

            $cat_mouvement = KeyInflector::slugify($csvRow[self::CSV_CAVE_CATEGORIE_MOUVEMENT]);
            if(strtoupper(KeyInflector::slugify($cat_mouvement)) == self::COMPLEMENT){
                    $this->importComplementMvt($csvRow,$founded_produit,$just_check);
                    $num_ligne++;
                    continue;
           }

            $type_douane_drm = KeyInflector::slugify($csvRow[self::CSV_CAVE_TYPE_DRM]);
            $type_douane_drm_key = $this->getDetailsKeyFromDRMType($type_douane_drm);
            $type_drm = KeyInflector::slugify($csvRow[self::CSV_CAVE_TYPE_MOUVEMENT]);
            $type_mouvement = KeyInflector::slugify($csvRow[self::CSV_CAVE_TYPE_MOUVEMENT]);

            if (!array_key_exists($cat_mouvement, $this->mouvements[$type_douane_drm_key])) {
                $this->csvDoc->addErreur($this->categorieMouvementNotFoundError($num_ligne, $csvRow));
                $num_ligne++;
                continue;
            }
            if (!array_key_exists($type_mouvement, $this->mouvements[$type_douane_drm_key][$cat_mouvement])) {
                $this->csvDoc->addErreur($this->typeMouvementNotFoundError($num_ligne, $csvRow));
                $num_ligne++;
                continue;
            }
            $confDetailMvt = $this->mouvements[$type_douane_drm_key][$cat_mouvement][$type_mouvement];

            if (!$just_check) {
                $denomination_complementaire = (trim($csvRow[self::CSV_CAVE_LIBELLE_COMPLEMENTAIRE]))? trim($csvRow[self::CSV_CAVE_LIBELLE_COMPLEMENTAIRE]) : false;
                $drmDetails = $this->drm->addProduit($founded_produit->getHash(), $type_douane_drm_key, $denomination_complementaire);

                $detailTotalVol = $this->convertNumber($csvRow[self::CSV_CAVE_VOLUME]);
                $volume = $this->convertNumber($csvRow[self::CSV_CAVE_VOLUME]);

                $cat_key = $confDetailMvt->getParent()->getKey();
                $type_key = $confDetailMvt->getKey();
                if($cat_key == "stocks_debut" && !$drmDetails->canSetStockDebutMois()) {
                    $num_ligne++;
                    continue;
                }
                if($csvRow[self::CSV_CAVE_VOLUME] == "") {
                    $num_ligne++;
                    continue;
                }

                if (preg_match('/^2\d\d\d-\d\d-\d\d$/', $csvRow[self::CSV_CAVE_EXPORTPAYS])) {
                  $drmDetails->add("replacement_date", $csvRow[self::CSV_CAVE_EXPORTPAYS]);
                  $drmDetails->add('observations', $type_key);
                }

                if ($confDetailMvt->hasDetails()) {
                   $detailTotalVol += $this->convertNumber($drmDetails->getOrAdd($cat_key)->getOrAdd($type_key));

                    if (preg_match("/^export/", $type_key)) {
                        $pays = ConfigurationClient::getInstance()->findCountry($csvRow[self::CSV_CAVE_EXPORTPAYS]);
                        $export = DRMESDetailExport::freeInstance($this->drm);
                        $export->volume = $volume;
                        $export->identifiant = $pays;
                        $drmDetails->getOrAdd($cat_key)->getOrAdd($type_key . '_details')->addDetail($export);
                    }

                    if ($type_key == 'vrac' || $type_key == 'contrat') {
                        $vrac_id = $this->findContratDocId($csvRow);

                        $detailNode = $drmDetails->getOrAdd($cat_key)->getOrAdd($type_key . '_details')->add($vrac_id);
                        if ($detailNode->volume) {
                            $volume+=$detailNode->volume;
                        }
                        $date = new DateTime($this->drm->getDate());
                        $detailNode->volume = $volume;
                        $detailNode->identifiant = $vrac_id;
                        $detailNode->date_enlevement = $date->format('Y-m-d');
                    }
                    if($type_key == 'creationvrac' || $type_key == 'creationvractirebouche'){
                        $creationvrac = DRMESDetailCreationVrac::freeInstance($this->drm);
                        $creationvrac->volume = $volume;
                        $creationvrac->prixhl = floatval($csvRow[self::CSV_CAVE_CONTRAT_PRIXHL]);
                        $nego = EtablissementClient::getInstance()->findByNoAccise($csvRow[self::CSV_CAVE_CONTRAT_ACHETEUR_ACCISES]);
                        if (!$nego) {
                          $nego = EtablissementClient::getInstance()->retrieveByName($csvRow[self::CSV_CAVE_CONTRAT_ACHETEUR_NOM]);
                        }
                        $creationvrac->acheteur = $nego->identifiant;
                        $creationvrac->type_contrat = ($type_key == 'creationvrac')? VracClient::TYPE_TRANSACTION_VIN_VRAC : VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE;
                        $drmDetails->getOrAdd($cat_key)->getOrAdd($type_key . '_details')->addDetail($creationvrac);
                    }
                } else {
                    $oldVolume = $drmDetails->getOrAdd($cat_key)->getOrAdd($type_key);
                    if($cat_key == "stocks_debut" && !is_null($oldVolume) && $oldVolume != "") {
                        $this->drm->commentaire .= sprintf("IMPORT de %s le stock_debut %s de %s hl n'a pas été pris en compte\n", $drmDetails->getLibelle(), $type_key, $detailTotalVol);
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

            } else {
                if ($confDetailMvt->hasDetails()) {
                    if ($confDetailMvt->getKey() == 'export') {
                        $pays = ConfigurationClient::getInstance()->findCountry($csvRow[self::CSV_CAVE_EXPORTPAYS]);
                        if (!$pays) {
                            $this->csvDoc->addErreur($this->exportPaysNotFoundError($num_ligne, $csvRow));
                            $num_ligne++;
                            continue;
                        }
                    }
                    if ($confDetailMvt->getKey() == 'vrac' || $confDetailMvt->getKey() == 'contrat') {
                        if (!$csvRow[self::CSV_CAVE_CONTRATID]) {
                            $this->csvDoc->addErreur($this->contratIDEmptyError($num_ligne, $csvRow));
                            $num_ligne++;
                            continue;
                        }

                        $vrac_id = $this->findContratDocId($csvRow);

                        if(!$vrac_id) {
                            $this->csvDoc->addErreur($this->contratIDNotFoundError($num_ligne, $csvRow));
                            $num_ligne++;
                            continue;
                        }
                    }
                }
            }

            $num_ligne++;
        }
    }
    private function importComplementMvt($csvRow, $founded_produit, $just_check  = false){
              $type_complement = strtoupper(KeyInflector::slugify($csvRow[self::CSV_CAVE_TYPE_COMPLEMENT_PRODUIT]));
                if(!in_array($type_complement, self::$types_complement)){
                  $this->csvDoc->addErreur($this->typeComplementNotFoundError($num_ligne, $csvRow));
                  $num_ligne++;
                  return;
                }
                $valeur_complement = $csvRow[self::CSV_CAVE_VALEUR_COMPLEMENT_PRODUIT];
                if(!$valeur_complement){
                  $this->csvDoc->addErreur($this->valueComplementVide($num_ligne, $csvRow));
                  $num_ligne++;
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
		  $drmDetails = $this->drm->addProduit($founded_produit->getHash(),DRMClient::$types_node_from_libelles[strtoupper($csvRow[self::CSV_CAVE_TYPE_DRM])]);
                  $field = strtolower($type_complement);
                  $drmDetails->add($field, $value);
                }
    }

    private function importCrdsFromCSV($just_check = false) {
        $num_ligne = 1;
        $etablissementObj = $this->drm->getEtablissementObject();

        $crd_regime = ($etablissementObj->exist('crd_regime'))? $etablissementObj->get('crd_regime') : EtablissementClient::REGIME_CRD_COLLECTIF_SUSPENDU;
        $all_contenances = VracConfiguration::getInstance()->getContenancesSlugified();
        foreach ($this->getDocRows() as $csvRow) {
            if (KeyInflector::slugify($csvRow[self::CSV_TYPE] != self::TYPE_CRD)) {
                $num_ligne++;
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
              $num_ligne++;
              continue;
            }
            if ($csvRow[self::CSV_CRD_COULEUR] && !$couleur) {
              $this->csvDoc->addErreur($this->couleurCRDNotFoundError($num_ligne, $csvRow));
              $num_ligne++;
              continue;
            }
            if (!$genre) {
              $this->csvDoc->addErreur($this->genreCRDNotFoundError($num_ligne, $csvRow));
              $num_ligne++;
              continue;
            }
            if (!$categorie_key) {
              $this->csvDoc->addErreur($this->categorieCRDNotFoundError($num_ligne, $csvRow));
              $num_ligne++;
              continue;
            }
            if (!$type_key) {
              $this->csvDoc->addErreur($this->typeCRDNotFoundError($num_ligne, $csvRow));
              $num_ligne++;
              continue;
            }
            if (!$type_key) {
              $this->csvDoc->addErreur($this->typeCRDNotFoundError($num_ligne, $csvRow));
              $num_ligne++;
              continue;
            }
            if (!$just_check) {
                $centilitrage = $all_contenances[$litrageKey];
                $litrageLibelle = DRMClient::getInstance()->getLibelleCRD($litrageKey);
                $regimeNode = $this->drm->getOrAdd('crds')->getOrAdd($crd_regime);
                $keyNode = $regimeNode->constructKey($genre, $couleur, $centilitrage, $litrageLibelle);
                if (!$regimeNode->exist($keyNode)) {
                    $regimeNode->getOrAddCrdNode($genre, $couleur, $centilitrage, $litrageLibelle);
                }
                if (!preg_match('/^stock/', $fieldNameCrd) || $regimeNode->getOrAdd($keyNode)->{$fieldNameCrd} == null) {
                    $regimeNode->getOrAdd($keyNode)->{$fieldNameCrd} += intval($quantite);
                }
                $num_ligne++;
            }
        }
        return $this->csvDoc->hasErreurs();
    }

    private function importAnnexesFromCSV($just_check = false) {
        $num_ligne = 1;
        $typesAnnexes = array_keys($this->type_annexes);
        foreach ($this->getDocRows() as $csvRow) {
            if (KeyInflector::slugify($csvRow[self::CSV_TYPE] != self::TYPE_ANNEXE)) {
                $num_ligne++;
                continue;
            }
            switch (KeyInflector::slugify($csvRow[self::CSV_ANNEXE_TYPEANNEXE])) {
                case self::TYPE_ANNEXE_NONAPUREMENT:
                    $numero_document = KeyInflector::slugify($csvRow[self::CSV_ANNEXE_NUMERODOCUMENT]);
                    $date_emission = KeyInflector::slugify($csvRow[self::CSV_ANNEXE_NONAPUREMENTDATEEMISSION]);
                    $dt = DateTime::createFromFormat("d-m-Y", $date_emission);

                    $numero_accise = KeyInflector::slugify($csvRow[self::CSV_ANNEXE_NONAPUREMENTACCISEDEST]);
                    if (!$numero_document) {
                        if ($just_check) {
                            $this->csvDoc->addErreur($this->annexesNumeroDocumentError($num_ligne, $csvRow));
                        }
                        $num_ligne++;
                        break;
                    }
                    if (!$date_emission || $dt == false || array_sum($dt->getLastErrors())) {
                        if ($just_check) {
                            $this->csvDoc->addErreur($this->annexesNonApurementWrongDateError($num_ligne, $csvRow));
                        }
                        $num_ligne++;
                        break;
                    }
                    if (!preg_match('/^[A-Z]{2}[0-9A-Z]{11}$/', $numero_accise)) {
                        if ($just_check) {
                            $this->csvDoc->addErreur($this->annexesNonApurementWrongNumAcciseError($num_ligne, $csvRow));
                        }
                        $num_ligne++;
                        break;
                    }
                    if (!$just_check) {
                        $nonAppurementNode = $this->drm->getOrAdd('releve_non_apurement')->getOrAdd($numero_document);
                        $nonAppurementNode->numero_document = $numero_document;
                        $nonAppurementNode->date_emission = $dt->format('Y-m-d');
                        $nonAppurementNode->numero_accise = $numero_accise;
                    }
                    $num_ligne++;
                    break;

                case DRMClient::DRM_DOCUMENTACCOMPAGNEMENT_DAADAC:
                case DRMClient::DRM_DOCUMENTACCOMPAGNEMENT_DSADSAC:
                case DRMClient::DRM_DOCUMENTACCOMPAGNEMENT_EMPREINTE:
                    $docTypeAnnexe = $this->drm->getOrAdd('documents_annexes')->getOrAdd(KeyInflector::slugify($csvRow[self::CSV_ANNEXE_TYPEANNEXE]));
                    $annexeTypeMvt = KeyInflector::slugify($csvRow[self::CSV_ANNEXE_TYPEMVT]);
                    $numDocument = KeyInflector::slugify($csvRow[self::CSV_ANNEXE_QUANTITE]);
                    if (!in_array($annexeTypeMvt, self::$permitted_annexes_type_mouvements)) {
                        if ($just_check) {
                            $this->csvDoc->addErreur($this->annexesTypeMvtWrongFormatError($num_ligne, $csvRow));
                        } $num_ligne++;
                        break;
                    }
                    if (!$numDocument) {
                        if ($just_check) {
                            $this->csvDoc->addErreur($this->annexesNumeroDocumentError($num_ligne, $csvRow));
                        } $num_ligne++;
                        break;
                    }
                    if (!$just_check) {
                        $docTypeAnnexe->add(strtolower($annexeTypeMvt), $numDocument);
                    }
                    $num_ligne++;
                    break;
                case self::TYPE_ANNEXE_STATS_EUROPEENES :
                    $this->drm->getOrAdd('declaratif')->getOrAdd('statistiques')->add(strtolower($csvRow[self::CSV_ANNEXE_TYPEMVT]),round(floatval($csvRow[self::CSV_ANNEXE_QUANTITE]), 2));
                    $num_ligne++;
                    break;
                default:
                    if ($just_check) {
                        $this->csvDoc->addErreur($this->typeDocumentWrongFormatError($num_ligne, $csvRow));
                    }
                    $num_ligne++;
                    break;
            }
        }
    }

    private function convertNumber($number){
          $numberPointed = trim(str_replace(",",".",$number));
          return round(floatval($numberPointed), 4);
    }

    private function getDetailsKeyFromDRMType($drmType ) {
        if(KeyInflector::slugify($drmType) == "SUSPENDU") {

            return DRM::DETAILS_KEY_SUSPENDU;
        }

        if(KeyInflector::slugify($drmType) == "ACQUITTE") {

            return DRM::DETAILS_KEY_ACQUITTE;
        }

        throw new sfException(sprintf("Le type de DRM \"%s\" n'est pas connu", $drmType));
    }

    private function findContratDocId($csvRow) {
        if($vrac = VracClient::getInstance()->findByNumContrat($csvRow[self::CSV_CAVE_CONTRATID], acCouchdbClient::HYDRATE_JSON)) {

            return $vrac->_id;
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

    private function categorieMouvementNotFoundError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, $csvRow[self::CSV_CAVE_CATEGORIE_MOUVEMENT], "La catégorie de mouvement n'a pas été trouvée");
    }

    private function typeMouvementNotFoundError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, $csvRow[self::CSV_CAVE_TYPE_MOUVEMENT], "Le type de mouvement n'a pas été trouvé");
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
        return $this->createError($num_ligne, $csvRow[self::CSV_CRD_CATEGORIE_KEY], "La categorie de CRD n'a pas été trouvée");
    }
    private function typeCRDNotFoundError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, $csvRow[self::CSV_CRD_TYPE_KEY], "Le type de CRD n'a pas été trouvé");
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
        return $this->createError($num_ligne, $csvRow[self::CSV_ANNEXE_NONAPUREMENTDATEEMISSION], "La date est vide ou mal formattée.");
    }

    private function annexesNonApurementWrongNumAcciseError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, $csvRow[self::CSV_ANNEXE_NONAPUREMENTACCISEDEST], "La numéro d'accise du destinataire est vide ou mal formatté.");
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
        $genreKey = $produit->getGenre()->getKey();
        if(isset(self::$genres[$genreKey])) {
            $genreLibelle = self::$genres[$genreKey];
        } else {
            $genreLibelle = null;
        }
        $libellesSlugified[1] = strtoupper(KeyInflector::slugify($genreLibelle));
        if(($libellesSlugified[0] == "AOC") && $withAOP){
            $libellesSlugified[0]="AOP";
        }
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

    private function isEmptyArray($array){
      foreach ($array as $csvLibelle) {
        if($csvLibelle){
          return false;
        }
      }
        return true;
    }
}
