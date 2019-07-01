<?php

class DRMClient extends acCouchdbClient {

    const CONTRATSPRODUITS_NUMERO_CONTRAT = 0;
    const CONTRATSPRODUITS_ETS_NOM = 1;
    const CONTRATSPRODUITS_VOL_TOTAL = 2;
    const CONTRATSPRODUITS_VOL_ENLEVE = 3;
    const ETAPE_CHOIX_PRODUITS = 'CHOIX_PRODUITS';
    const ETAPE_MATIERE_PREMIERE = 'MATIERE_PREMIERE';
    const ETAPE_SAISIE = 'SAISIE';
    const ETAPE_SAISIE_SUSPENDU = 'SAISIE_details';
    const ETAPE_SAISIE_ACQUITTE = 'SAISIE_detailsACQUITTE';
    const ETAPE_CRD = 'CRD';
    const ETAPE_ADMINISTRATION = 'ADMINISTRATION';
    const ETAPE_VALIDATION = 'VALIDATION';
    const ETAPE_VALIDATION_EDI = 'VALIDATION_EDI';
    const VALIDE_STATUS_EN_COURS = '';
    const VALIDE_STATUS_VALIDEE = 'VALIDEE';
    const VALIDE_STATUS_VALIDEE_ENVOYEE = 'ENVOYEE';
    const VALIDE_STATUS_VALIDEE_RECUE = 'RECUE';
    const DRM_CRD_DEFAUT = 'DEFAUT';
    const DRM_CRD_VERT = 'VERT';
    const DRM_CRD_BLEU = 'BLEU';
    const DRM_CRD_LIEDEVIN = 'LIEDEVIN';
    const DRM_CRD_CATEGORIE_TRANQ = 'TRANQ';
    const DRM_CRD_CATEGORIE_MOUSSEUX = 'MOUSSEUX';
    const DRM_DOCUMENTACCOMPAGNEMENT_DAADAC = 'DAADAC';
    const DRM_DOCUMENTACCOMPAGNEMENT_DAE = 'DAE';
    const DRM_DOCUMENTACCOMPAGNEMENT_DSADSAC = 'DSADSAC';
    const DRM_DOCUMENTACCOMPAGNEMENT_EMPREINTE = 'EMPREINTE';
    const DRM_TYPE_MVT_ENTREES = 'entrees';
    const DRM_TYPE_MVT_SORTIES = 'sorties';
    const DRM_CREATION_EDI = 'CREATION_EDI';
    const DRM_CREATION_VIERGE = 'CREATION_VIERGE';
    const DRM_CREATION_NEANT = 'CREATION_NEANT';
    const DRM_CREATION_AUTO = 'CREATION_AUTO';

    const DRM_CREATION_DOCUMENTS = 'CREATION_DOCUMENTS';

    const DETAIL_EXPORT_PAYS_DEFAULT = 'inconnu';
    const TYPE_DRM_SUSPENDU = 'SUSPENDU';
    const TYPE_DRM_ACQUITTE = 'ACQUITTE';

    const CRD_TYPE_SUSPENDU = 'CRD_SUSPENDU';
    const CRD_TYPE_ACQUITTE = 'CRD_ACQUITTE';
    const CRD_TYPE_MIXTE = 'CRD_MIXTE';


    public static $types_libelles = array(DRM::DETAILS_KEY_SUSPENDU => 'Suspendu', DRM::DETAILS_KEY_ACQUITTE => 'Acquitté');
    public static $types_node_from_libelles = array(self::TYPE_DRM_SUSPENDU => DRM::DETAILS_KEY_SUSPENDU, self::TYPE_DRM_ACQUITTE => DRM::DETAILS_KEY_ACQUITTE);
    public static $drm_etapes = array(self::ETAPE_CHOIX_PRODUITS, self::ETAPE_SAISIE_SUSPENDU, self::ETAPE_SAISIE_ACQUITTE, self::ETAPE_CRD, self::ETAPE_ADMINISTRATION, self::ETAPE_VALIDATION, self::ETAPE_VALIDATION_EDI);
    public static $drm_crds_couleurs = array(
        self::DRM_CRD_DEFAUT => 'Autre couleur',
        self::DRM_CRD_VERT => 'Vert',
        self::DRM_CRD_BLEU => 'Bleu',
        self::DRM_CRD_LIEDEVIN => 'Lie de vin'
    );
    public static $drm_crds_genre = array(DRMClient::DRM_CRD_CATEGORIE_TRANQ => 'Vins tranquilles', DRMClient::DRM_CRD_CATEGORIE_MOUSSEUX => 'Vins mousseux');
    public static $drm_max_favoris_by_types_mvt = array(self::DRM_TYPE_MVT_ENTREES => 3, self::DRM_TYPE_MVT_SORTIES => 6);
    public static $drm_documents_daccompagnement = array(
        self::DRM_DOCUMENTACCOMPAGNEMENT_DAADAC => 'DAA/DAC',
        self::DRM_DOCUMENTACCOMPAGNEMENT_DSADSAC => 'DSA/DSAC',
        self::DRM_DOCUMENTACCOMPAGNEMENT_DAE => 'DAE',
        self::DRM_DOCUMENTACCOMPAGNEMENT_EMPREINTE => 'Empreinte');
    public static $typesCreationLibelles = array(self::DRM_CREATION_VIERGE => "Création d'une drm vierge", self::DRM_CREATION_NEANT => "Création d'une drm à néant", self::DRM_CREATION_EDI => 'Création depuis un logiciel tiers');
    protected $drm_historiques = array();

    /**
     *
     * @return DRMClient
     */
    public static function getInstance() {

        return acCouchdbManager::getClient("DRM");
    }

    public function buildId($identifiant, $periode, $version = null) {

        return 'DRM-' . $identifiant . '-' . $this->buildPeriodeAndVersion($periode, $version);
    }

    public function buildVersion($rectificative, $modificative) {

        return DRM::buildVersion($rectificative, $modificative);
    }

    public function getRectificative($version) {

        return DRM::buildRectificative($version);
    }

    public function getModificative($version) {

        return DRM::buildModificative($version);
    }

    public function getPeriodes($campagne) {
        $periodes = array();
        $periode = $this->getPeriodeFin($campagne);
        while ($periode != $this->getPeriodeDebut($campagne)) {
            $periodes[] = $periode;
            $periode = $this->getPeriodePrecedente($periode);
        }

        $periodes[] = $periode;

        return $periodes;
    }

    public function getLastMonthPeriodes($nbMonth) {
        $periodes = array();
        $periode = $this->buildPeriode(date('Y'), date('m'));
        for ($cpt = 0; $cpt < $nbMonth; $cpt++) {

            $periodes[] = $periode;
            $periode = $this->getPeriodePrecedente($periode);
        }

        return $periodes;
    }

    public function buildDate($periode) {

        return ConfigurationClient::getInstance()->buildDate($periode);
    }

    public function getPeriodeDebut($campagne) {

        return ConfigurationClient::getInstance()->getPeriodeDebut($campagne);
    }

    public function getPeriodeFin($campagne) {

        return ConfigurationClient::getInstance()->getPeriodeFin($campagne);
    }

    public function buildCampagne($periode) {

        return ConfigurationClient::getInstance()->buildCampagneByPeriode($periode);
    }

    public function buildPeriode($annee, $mois) {

        return ConfigurationClient::getInstance()->buildPeriode($annee, $mois);
    }

    public function getCurrentPeriode() {

        return ConfigurationClient::getInstance()->getCurrentPeriode();
    }

    public function buildPeriodeAndVersion($periode, $version) {
        if ($version) {
            return sprintf('%s-%s', $periode, $version);
        }

        return $periode;
    }

    public function getAnnee($periode) {

        return ConfigurationClient::getInstance()->getAnnee($periode);
    }

    public function getMois($periode) {

        return ConfigurationClient::getInstance()->getMois($periode);
    }

    public function getPeriodeSuivante($periode) {

        return ConfigurationClient::getInstance()->getPeriodeSuivante($periode);
    }

    public function getPeriodePrecedente($periode) {

        return ConfigurationClient::getInstance()->getPeriodePrecedente($periode);
    }

    public function findLastByIdentifiant($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $drms = $this->viewByIdentifiant($identifiant);

        foreach ($drms as $id => $drm) {

            return $this->find($id, $hydrate);
        }

        return null;
    }

    public function findLastByIdentifiantAndCampagne($identifiant, $campagne, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $drms = $this->viewByIdentifiantAndCampagne($identifiant, $campagne);

        foreach ($drms as $id => $drm) {

            return $this->find($id, $hydrate);
        }

        return null;
    }

    public function findMasterByIdentifiantAndPeriode($identifiant, $periode, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $drms = $this->viewByIdentifiantPeriode($identifiant, $periode);

        foreach ($drms as $id => $drm) {

            return $this->find($id, $hydrate);
        }

        return null;
    }

    public function getMaster($id) {
        $matches = array();
        $drm_master = null;
        if (preg_match('/^DRM-([0-9]{8})-([0-9]{6})*/', $id, $matches)) {
            $identifiant = $matches[1];
            $periode = $matches[2];
            $drm_master = $this->findMasterByIdentifiantAndPeriode($identifiant, $periode);
        }
        if (!$drm_master) {
            throw new sfException("La DRM master avec l'id $id n'a pas été trouvée.");
        }
        return $drm_master;
    }

    public function getMasterVersionOfRectificative($identifiant, $periode, $version_rectificative) {
        $drms = $this->viewByIdentifiantPeriodeAndVersion($identifiant, $periode, $version_rectificative);

        foreach ($drms as $id => $drm) {

            return $drm[3];
        }

        return null;
    }

    public function findOrCreateByIdentifiantAndPeriode($identifiant, $periode, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        if ($obj = $this->findMasterByIdentifiantAndPeriode($identifiant, $periode, $hydrate)) {

            return $obj;
        }

        $this->getHistorique($identifiant, $periode)->reload();

        return $this->createDocByPeriode($identifiant, $periode);
    }

    public function findOrCreateFromEdiByIdentifiantAndPeriode($identifiant, $periode, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        if ($obj = $this->findMasterByIdentifiantAndPeriode($identifiant, $periode, $hydrate)) {

            return $obj;
        }

        $this->getHistorique($identifiant, $periode)->reload();

        $drm = $this->createDocByPeriode($identifiant, $periode);
        $drm->type_creation = DRMClient::DRM_CREATION_EDI;
        $drm->etape = self::ETAPE_VALIDATION_EDI;
        $drm->teledeclare = true;
        return $drm;
    }

    public function listCampagneByEtablissementId($identifiant) {
        $rows = acCouchdbManager::getClient()
                        ->group_level(2)
                        ->startkey(array($identifiant))
                        ->endkey(array($identifiant, array()))
                        ->getView("drm", "all")
                ->rows;
        $current = ConfigurationClient::getInstance()->getCurrentCampagne();
        $list = array();
        foreach ($rows as $r) {
            $c = $r->key[1];
            $list[$c] = $c;
        }
        krsort($list);
        if(DRMConfiguration::getInstance()->isCampagneListeMinimale()){
            return $list;
        }
        return ConfigurationClient::getInstance()->getCampagneVinicole()->consoliderCampagnesList($list);
    }

    public function viewByIdentifiant($identifiant) {
        $rows = acCouchdbManager::getClient()
                        ->startkey(array($identifiant))
                        ->endkey(array($identifiant, array()))
                        ->reduce(false)
                        ->getView("drm", "all")
                ->rows;

        $drms = array();

        foreach ($rows as $row) {
            $drms[$row->id] = $row->key;
        }

        krsort($drms);

        return $drms;
    }

    public function viewByIdentifiantAndCampagne($identifiant, $campagne) {
        $rows = acCouchdbManager::getClient()
                        ->startkey(array($identifiant, $campagne))
                        ->endkey(array($identifiant, $campagne, array()))
                        ->reduce(false)
                        ->getView("drm", "all")
                ->rows;

        $drms = array();
        foreach ($rows as $row) {
            $drms[$row->id] = $row->key;
        }
        krsort($drms);

        return $drms;
    }

    public function viewMasterByIdentifiantPeriode($identifiant, $periode) {
        $campagne = $this->buildCampagne($periode);

        $rows = acCouchdbManager::getClient()
                        ->startkey(array($identifiant, $campagne, $periode))
                        ->endkey(array($identifiant, $campagne, $periode, array()))
                        ->reduce(false)
                        ->getView("drm", "all")
                ->rows;

        $drms = array();

        foreach ($rows as $row) {
            $drms[$row->id] = $row->key;
        }

        krsort($drms);

        return array_shift($drms);
    }

    protected function viewByIdentifiantPeriode($identifiant, $periode) {
        $campagne = $this->buildCampagne($periode);

        $rows = acCouchdbManager::getClient()
                        ->startkey(array($identifiant, $campagne, $periode))
                        ->endkey(array($identifiant, $campagne, $periode, array()))
                        ->reduce(false)
                        ->getView("drm", "all")
                ->rows;

        $drms = array();

        foreach ($rows as $row) {
            $drms[$row->id] = $row->key;
        }

        krsort($drms);

        return $drms;
    }

    protected function viewByIdentifiantPeriodeAndVersion($identifiant, $periode, $version_rectificative) {
        $campagne = $this->buildCampagne($periode);

        $rows = acCouchdbManager::getClient()
                        ->startkey(array($identifiant, $campagne, $periode, $version_rectificative))
                        ->endkey(array($identifiant, $campagne, $periode, $this->buildVersion($version_rectificative, 99)))
                        ->reduce(false)
                        ->getView("drm", "all")
                ->rows;

        $drms = array();

        foreach ($rows as $row) {
            $drms[$row->id] = $row->key;
        }

        krsort($drms);

        return $drms;
    }

    public function getContratsFromProduit($vendeur_identifiant, $produit, $transaction_types = null, $date = null) {
        if ($transaction_types && !is_array($transaction_types))
            throw new sfException("transaction_types (param 3) must be an array");
        if (!$transaction_types)
            return $this->getContratsFromProduitAndATransaction($vendeur_identifiant, $produit, $date);

        $vracs = array();
        foreach ($transaction_types as $t) {
            $vracs = array_merge($vracs, $this->getContratsFromProduitAndATransaction($vendeur_identifiant, $produit, $t, $date));
        }
        return $vracs;
    }

    public function getContratsFromProduitAndATransaction($vendeur_identifiant, $produit, $type_transaction = null, $date = null) {
        $withAgregat = DRMConfiguration::getInstance()->isDRMVracMultiProduit();
        $statutsContrats = VracClient::STATUS_CONTRAT_NONSOLDE;
        $results = array();
        if($withAgregat){
          $results = $this->getContratsFromMultiProduitsAndATransactionRow($statutsContrats,$vendeur_identifiant, $produit,$type_transaction);
        }else{
          $results = $this->getContratsFromProduitAndATransactionRow($statutsContrats,$vendeur_identifiant, $produit,$type_transaction);
        }
        return $this->postTraitementVracResult($results);
    }

    private function getContratsFromProduitAndATransactionRow($contratStatut, $vendeur_identifiant, $produit,$type_transaction = null){
      $startkey = array($contratStatut, $vendeur_identifiant, $produit);
      if ($type_transaction) {
          array_push($startkey, $type_transaction);
      }
      $endkey = $startkey;
      array_push($endkey, array());
      return acCouchdbManager::getClient()
                      ->startkey($startkey)
                      ->endkey($endkey)
                      ->getView("vrac", "contratsFromProduit")
              ->rows;
    }

    private function postTraitementVracResult($rowsVrac){
      $vracs = array();
      foreach ($rowsVrac as $key => $row) {
          $vol_restant = round($row->value[self::CONTRATSPRODUITS_VOL_TOTAL] - $row->value[self::CONTRATSPRODUITS_VOL_ENLEVE], 2);
          $volume = '[' . $row->value[self::CONTRATSPRODUITS_VOL_ENLEVE] . '/' . $row->value[self::CONTRATSPRODUITS_VOL_TOTAL] . ']';
          $volume = ($row->value[self::CONTRATSPRODUITS_VOL_ENLEVE] == '') ? '[0/' . $row->value[self::CONTRATSPRODUITS_VOL_TOTAL] . ']' : $volume;
          $vracs[VracClient::getInstance()->getId($row->id)] = $row->value[self::CONTRATSPRODUITS_ETS_NOM] .
                  ' - ' . str_replace('VRAC-', '', $row->id).' ('. $row->value[self::CONTRATSPRODUITS_NUMERO_CONTRAT] . ') - ' .
                  $vol_restant . ' hl ' .
                  $volume;
      }
      return $vracs;
    }

    private function getContratsFromMultiProduitsAndATransactionRow($statutsContrats, $vendeur_identifiant, $produit, $type_transaction = null, $date = null){
      $results = array();
      $produits = ConfigurationClient::getInstance()->convertHashProduitForDRM($produit, true, $date);
      $vendeur_identifiant = "ETABLISSEMENT-".$vendeur_identifiant;
      foreach ($produits as $produit) {
        $results = array_merge($results,$this->getContratsFromProduitAndATransactionRow($statutsContrats,$vendeur_identifiant, $produit,$type_transaction));
      }
      return $results;
    }

    public function findProduits() {
        return $this->startkey(array("produit"))
                        ->endkey(array("produit", array()))->getView('drm', 'produits');
    }

    public function getAllProduits() {
        $produits = $this->findProduits()->rows;
        $result = array();
        foreach ($produits as $produit) {
            $result[] = $produit->key[1];
        }

        return $result;
    }

    public function clearHistorique($identifiant, $campagne_or_periode) {
        $campagne = $campagne_or_periode;

        if (preg_match('/^[0-9]{6}$/', $campagne_or_periode)) {
            $campagne = $this->buildCampagne($campagne_or_periode);
        }

        unset($this->drm_historiques[$identifiant . $campagne]);
    }

    public function getHistorique($identifiant, $campagne_or_periode) {
        $campagne = $campagne_or_periode;

        if (preg_match('/^[0-9]{6}$/', $campagne_or_periode)) {
            $campagne = $this->buildCampagne($campagne_or_periode);
        }

        if (!array_key_exists($identifiant . $campagne, $this->drm_historiques)) {

            $this->drm_historiques[$identifiant . $campagne] = new DRMHistorique($identifiant, $campagne);
        }

        return $this->drm_historiques[$identifiant . $campagne];
    }

    public function createDoc($identifiant, $periode = null, $isTeledeclarationMode = false) {
        if (!$periode) {
            $periode = $this->getCurrentPeriode();
            $this->clearHistorique($identifiant, $periode);
            $last_drm = $this->getHistorique($identifiant, $periode)->getLastDRM();
            if ($last_drm) {
                $periode = $this->getPeriodeSuivante($last_drm->periode);
            }
        }
        $drm = $this->createDocByPeriode($identifiant, $periode, $isTeledeclarationMode);
        $drm->type_creation = DRMClient::DRM_CREATION_VIERGE;
        return $drm;
    }

    public function createDocByPeriode($identifiant, $periode, $isTeledeclarationMode = false) {
        $this->clearHistorique($identifiant, $periode);
        $prev_drm = $this->getHistorique($identifiant, $periode)->getPrevious($periode);
        $next_drm = $this->getHistorique($identifiant, $periode)->getNext($periode);

        if ($prev_drm) {

            return $prev_drm->generateSuivanteByPeriode($periode, $isTeledeclarationMode);
        } elseif ($next_drm) {

            return $next_drm->generateSuivanteByPeriode($periode, $isTeledeclarationMode);
        }

        $drm = new DRM();
        $drm->identifiant = $identifiant;
        $drm->periode = $periode;
        $drm->teledeclare = $isTeledeclarationMode;
        $drm->etape = self::ETAPE_SAISIE;
        $drm->buildFavoris();
        $drm->initSociete();
        $drm->initCrds();
        $drm->initProduitsAutres($isTeledeclarationMode);
        $drm->clearAnnexes();
        if ($isTeledeclarationMode) {
            $drm->etape = self::ETAPE_CHOIX_PRODUITS;
        }
        $drmLast = DRMClient::getInstance()->findLastByIdentifiant($identifiant);

        if ($drmLast) {
            $drm->generateByDRM($drmLast);
            return $drm;
        }
        if (!$drm->getEtablissement()->isNegociant()) {
            $dsLast = null;
            try {
                $dsLast = DSClient::getInstance()->findLastByIdentifiant($identifiant);
            } catch (Exception $e) {

            }
            if ($dsLast) {
                $drm->generateByDS($dsLast);
                return $drm;
            }
        }
        return $drm;
    }

    public function generateVersionCascade($drm) {
        if (!$drm->needNextVersion()) {

            return array();
        }

        $drm_version_suivante = $drm->generateNextVersion();

        if (!$drm_version_suivante) {
            return array();
        }

        $drm_version_suivante->save();

        return array_merge(array($drm_version_suivante->get('_id')), $this->generateVersionCascade($drm_version_suivante));
    }

    public function getLibelleFromId($id) {
        if (!$id) {
            return null;
        }

        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Orthographe', 'Date'));
        $origineLibelle = 'DRM de';
        $drmSplited = explode('-', $id);
        $periode = $drmSplited[2];
        $annee = substr($periode, 0, 4);
        $mois = substr($periode, 4, 2);
        $date = $annee . '-' . $mois . '-01';
        $df = format_date($date, 'MMMM yyyy', 'fr_FR');
        return elision($origineLibelle, $df);
    }

    public static function determineTypeDocument($numero_document) {
        if (preg_match('/^\d{3}$/', $numero_document)) {
            return self::DRM_DOCUMENTACCOMPAGNEMENT_EMPREINTE;
        }
        if (preg_match('/^[0-9]{2}[A-Z]{3}[0-9]{16}$/', $numero_document)) {
            return self::DRM_DOCUMENTACCOMPAGNEMENT_DAADAC;
        }
        if (preg_match('/^[0-9]{5,8}$/', $numero_document)) {
            return self::DRM_DOCUMENTACCOMPAGNEMENT_DSADSAC;
        }
        return null;
    }

    public static function recapCvo($mouvements) {
        $recapCvo = new stdClass();
        $recapCvo->totalVolumeDroitsCvo = 0;
        $recapCvo->totalVolumeReintegration = 0;
        $recapCvo->totalPrixDroitCvo = 0;
        foreach ($mouvements as $mouvement) {
            if ($mouvement->facturable) {
                $recapCvo->totalPrixDroitCvo += $mouvement->volume * -1 * $mouvement->cvo;
                $recapCvo->totalVolumeDroitsCvo += $mouvement->volume * -1;
            }
            if ($mouvement->type_hash == 'entrees/reintegration') {
                $recapCvo->totalVolumeReintegration += $mouvement->volume;
            }
        }
        return $recapCvo;
    }

    public static function storeXMLRetourFromURL($url, $verbose = false, $allwaysreturndrm = false) {
      $xml = file_get_contents($url);
      if (!$xml) {
          throw new sfException($url." vide");
      }
      $aggrement = array();
      $etablissement = null;
      $cvimatch = preg_match('/<numero-cvi>([^<]+)</', $xml, $m);
      $aggrementmatch = preg_match('/<numero-agrement>([^<]+)</', $xml, $aggrement);
      $aggrement = isset($aggrement[1])? $aggrement[1] : null;

      if(!$cvimatch && !$aggrementmatch){
          throw new sfException("Il n'y a ni numéro de CVI ni numéro d'agrément (Accise) dans l'xml");
      }
      if(isset($m[1])){
        $etbs = EtablissementClient::getInstance()->findAllByCvi($m[1]);
        foreach ($etbs as $id => $etb) {
            if($etb->isActif() && $aggrement){
                $etbaggrement = EtablissementClient::getInstance()->findByNoAccise($aggrement,false);
                if($etbaggrement->_id == $etb->_id){
                    $etablissement = $etb;
                    break;
                }
            }
            if($etb->isActif() && !$aggrement){
                $etablissement = $etb;
                break;
            }
        }
      }
      if(!$etablissement && $aggrement){
        $etablissement = EtablissementClient::getInstance()->findByNoAccise($aggrement,false);
      }
      if (!$etablissement) {
        $idebntifiantCVI = (isset($m[1]))? $m[1] : "VIDE";
        throw new sfException("L'établissement n'a ni été trouvé par son CVI ".$idebntifiantCVI." ni par son numéro d'agrément ".$aggrement);
      }
      if ($aggrement && ($etablissement->no_accises != $aggrement)) {
        throw new sfException("Le numéro d'accise ".$aggrement." ne correspond pas a celui de l'établissement (".$etablissement->identifiant." | ".$etablissement->no_accises.")");
      }
      if(!preg_match('/<mois>([^<]+)</', $xml, $m)){
          throw new sfException("Mois non trouvé dans l'xml");
      }
      $mois = sprintf('%02d',intval($m[1]));
      if(!preg_match('/<annee>([^<]+)</', $xml, $m)){
          throw new sfException("Année non trouvé dans l'xml");
      }
      $annee = $m[1];
      if ($verbose) echo "INFO: recherche de la DRM pour ".$etablissement->identifiant.' '.$annee.$mois."\n";
      $drm = DRMClient::getInstance()->findOrCreateByIdentifiantAndPeriode($etablissement->identifiant, $annee.$mois);
      if (!$drm->_id) {
          echo "La DRM de ".$etablissement->identifiant.' '.$annee.$mois." n'a pas été trouvée\n";
          $drm->setEtape(self::ETAPE_VALIDATION);
          $drm->type_creation = self::DRM_CREATION_AUTO;
          $drm->add('transmission_douane')->add("xml", "généré automatiquement");
          $drm->add('transmission_douane')->add('success', false);
          $drm->add('transmission_douane')->add('horodatage', null);
          $drm->add('transmission_douane')->add('id_declaration', null);
          $drm->add('transmission_douane')->add('diff', null);
          $drm->add('transmission_douane')->add('coherente', false);
          if($drm->hasPrecedente() && $drm->getPrecedente()->isTeledeclare()){
              $drm->teledeclare = true;
          }
          $drm->save();
      }
      if (!$drm->storeXMLRetour($xml) && !$allwaysreturndrm) {
        return null;
      }
      $drm->save();
      return $drm;
    }

    public function sortMouvementsForDRM($mouvements) {
        $mouvementsSorted = array();
        foreach ($mouvements as $mouvement) {
          $type_drm = ($mouvement->type_drm)? $mouvement->type_drm : "SUSPENDU";
          if (!isset($mouvementsSorted[$type_drm])) {
              $mouvementsSorted[$type_drm] = array();
          }
          if (!array_key_exists($mouvement->produit_hash, $mouvementsSorted[$type_drm])) {
              $mouvementsSorted[$type_drm][$mouvement->produit_hash] = array();
          }
            $mouvementsSorted[$type_drm][$mouvement->produit_hash][] = $mouvement;
        }
        return $mouvementsSorted;
    }

    public static function convertCRDCouleur($s) {
      switch (preg_replace('/[\s_]/', '', strtoupper($s))) {
        case self::DRM_CRD_BLEU:
          return self::DRM_CRD_BLEU;
        case self::DRM_CRD_VERT:
        case '':
          return self::DRM_CRD_VERT;
        case self::DRM_CRD_LIEDEVIN:
          return self::DRM_CRD_LIEDEVIN;
      }
      return '';
    }

    public static function convertCRDGenre($s) {
      $s = strtoupper(KeyInflector::slugify($s));
      if (preg_match('/^TRANQ/', $s)) {
        return self::DRM_CRD_CATEGORIE_TRANQ;
      }
      if (preg_match('/^MOU/', $s)) {
        return self::DRM_CRD_CATEGORIE_MOUSSEUX;
      }
      return '';
    }
    public static function convertCRDRegime($s) {
      $s = strtoupper(KeyInflector::slugify($s));
      if (preg_match('/PERSONNALISE/', $s)) {
        return EtablissementClient::REGIME_CRD_PERSONNALISE;
      }
      if (preg_match('/ACQUIT/', $s)) {
        return EtablissementClient::REGIME_CRD_COLLECTIF_ACQUITTE;
      }
      if (preg_match('/SUSPEND/', $s)) {
        return EtablissementClient::REGIME_CRD_COLLECTIF_SUSPENDU;
      }
      return '';
    }
    public static function convertCRDLitrage($s) {
      return VracConfiguration::slugifyContenances($s);
    }

    public static function getLibelleCRD($s) {
      return VracConfiguration::getInstance()->getContenanceLibelle($s);
    }
    public static function convertCRDCategorie($s) {
      $s = strtolower(KeyInflector::slugify($s));
      if (preg_match('/^entr/', $s)) {
        return 'entrees';
      }
      if (preg_match('/^sortie/', $s)) {
        return 'sorties';
      }
      if (preg_match('/debut$/', $s)) {
        return 'stock_debut';
      }
      if (preg_match('/fin$/', $s)) {
        return 'stock_fin';
      }
    }
    public static function convertCRDType($s) {
      $s = strtolower(KeyInflector::slugify($s));
      switch ($s) {
        case "fin":
            return "fin";
        case "debut":
            return "debut";
        case "achats":
            return "achats";
        case "retours":
            return "retours";
        case "excedents":
            return "excedents";
        case "utilisations":
            return "utilisations";
        case "destructions":
            return "destructions";
        case "manquants":
            return "manquants";
      }
      return '';
    }

    public function getRecapCvos($identifiant, $periode) {

        return $this->getRecapCvosByMouvements(DRMMouvementsConsultationView::getInstance()->getMouvementsByEtablissementAndPeriode($identifiant, $periode));
    }

    public function getRecapCvosByMouvements($mouvements) {
        $recapCvos = array();

        $recapCvos["TOTAL"] = new stdClass();
        $recapCvos["TOTAL"]->totalVolumeDroitsCvo = 0;
        $recapCvos["TOTAL"]->totalVolumeReintegration = 0;
        $recapCvos["TOTAL"]->totalPrixDroitCvo = 0;
        $recapCvos["TOTAL"]->version = null;

        foreach ($mouvements as $mouvement) {
            $version = $mouvement->version;
            if(!$version) {
                $version = "M00";
            }
            if(!array_key_exists($version, $recapCvos)) {
                $recapCvos[$version] = new stdClass();
                $recapCvos[$version]->totalVolumeDroitsCvo = 0;
                $recapCvos[$version]->totalVolumeReintegration = 0;
                $recapCvos[$version]->totalPrixDroitCvo = 0;
                $recapCvos[$version]->version = $version;
            }
            if ($mouvement->facturable) {
                $recapCvos[$version]->totalVolumeDroitsCvo += $mouvement->quantite;
                $recapCvos["TOTAL"]->totalVolumeDroitsCvo += $mouvement->quantite;
                $recapCvos[$version]->totalPrixDroitCvo += $mouvement->prix_ht;
                $recapCvos["TOTAL"]->totalPrixDroitCvo +=  $mouvement->prix_ht;
            }
            if ($mouvement->type_hash == 'entrees/reintegration' && $mouvement->facturable) {
                $recapCvos[$version]->totalVolumeReintegration += $mouvement->volume;
                $recapCvos["TOTAL"]->totalVolumeReintegration += $mouvement->volume;
            }
        }

        if(count($recapCvos) <= 2) {

            return array("TOTAL" => $recapCvos["TOTAL"]);
        }

        ksort($recapCvos);

        return $recapCvos;
    }

    public function getAllRegimesCrdsChoices($libelleLong = false){
      $crdsRegimesChoices = array();
      $crdsRegimesChoices = EtablissementClient::$regimes_crds_libelles;

      if($libelleLong){
        $crdsRegimesChoices = EtablissementClient::$regimes_crds_libelles_longs;
      }
      $onlySuspendus = DRMConfiguration::getInstance()->isCrdOnlySuspendus();
      if($onlySuspendus){
        $crdsRegimesChoices = EtablissementClient::$regimes_crds_libelles_longs_only_suspendu;
      }
      return $crdsRegimesChoices;
    }

}
