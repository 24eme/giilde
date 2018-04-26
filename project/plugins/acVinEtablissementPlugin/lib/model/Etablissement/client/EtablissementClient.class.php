<?php

class EtablissementClient extends acCouchdbClient {

    /**
     *
     * @return EtablissementClient
     */
    const REGION_HORS_CVO = 'REGION_HORS_CVO';
    const REGION_CVO = 'REGION_CVO';
    const RECETTE_LOCALE = 'RECETTE_LOCALE';
    const TYPE_DR_DRM = 'DRM';
    const TYPE_DR_DRA = 'DRA';
    const TYPE_LIAISON_BAILLEUR = 'BAILLEUR';
    const TYPE_LIAISON_METAYER = 'METAYER';
    const TYPE_LIAISON_ADHERENT = 'ADHERENT'; //pour les cooperateurs
    const TYPE_LIAISON_CONTRAT_INTERNE = 'CONTRAT_INTERNE';
    const STATUT_ACTIF = 'ACTIF'; #'actif';
    const STATUT_SUSPENDU = 'SUSPENDU'; #'suspendu';
    const OUI = 'OUI';
    const NON = 'NON';
    const RELANCE_DS_OUI = self::OUI;
    const RELANCE_DS_NON = self::NON;
    const RAISINS_MOUTS_OUI = self::OUI;
    const RAISINS_MOUTS_NON = self::NON;
    const EXCLUSION_DRM_OUI = self::OUI;
    const EXCLUSION_DRM_NON = self::NON;
    const REGIME_CRD_PERSONNALISE = 'PERSONNALISE';
    const REGIME_CRD_COLLECTIF_ACQUITTE = 'COLLECTIFACQUITTE';
    const REGIME_CRD_COLLECTIF_SUSPENDU = 'COLLECTIFSUSPENDU';
    const CAUTION_DISPENSE = 'DISPENSE';
    const CAUTION_CAUTION = 'CAUTION';
    const NATURE_INAO_PRODUCTEUR_INDIVIDUEL = 'Producteur individuel';
    const NATURE_INAO_COOPERATIVE = 'Coopérative';
    const NATURE_INAO_UNION_DE_COOPERATIVES = 'Union de coopératives';
    const NATURE_SOCIETE_CIVILE = 'Société civile (GFA, GAEC, ...)';
    const NATURE_INAO_SICA = 'SICA';
    const NATURE_INAO_SOCIETE_COMMERCIALE = 'Société commerciale (négociant)';
    const NATURE_INAO_AUTRE = 'Autre';

    public static $statuts = array(self::STATUT_ACTIF => 'ACTIF',
        self::STATUT_SUSPENDU => 'SUSPENDU');
    public static $regimes_crds_libelles_longs = array(self::REGIME_CRD_PERSONNALISE => 'personnalisé (P)',
        self::REGIME_CRD_COLLECTIF_ACQUITTE => 'banalisées acquittées (DA)',
        self::REGIME_CRD_COLLECTIF_SUSPENDU => 'banalisées suspendues (DS)');
    public static $regimes_crds_libelles = array(self::REGIME_CRD_PERSONNALISE => 'Personnalisé',
        self::REGIME_CRD_COLLECTIF_ACQUITTE => 'Banalisées acquittées',
        self::REGIME_CRD_COLLECTIF_SUSPENDU => 'Banalisées suspendues');
    public static $natures_inao_libelles = array(
        "01" => self::NATURE_INAO_PRODUCTEUR_INDIVIDUEL,
        "04" => self::NATURE_INAO_COOPERATIVE,
        "05" => self::NATURE_INAO_UNION_DE_COOPERATIVES,
        "06" => self::NATURE_SOCIETE_CIVILE,
        "07" => self::NATURE_INAO_SICA,
        "08" => self::NATURE_INAO_SOCIETE_COMMERCIALE,
        "09" => self::NATURE_INAO_AUTRE);

    public static $caution_libelles = array(self::CAUTION_DISPENSE => 'Dispensé',
        self::CAUTION_CAUTION => 'Caution');

    public static function getInstance() {
        return acCouchdbManager::getClient("Etablissement");
    }

    public function createEtablissementFromSociete($societe, $famille = null) {
        return $societe->createEtablissement($famille);
    }

    public function getNextIdentifiantForSociete($societe) {
        $societe_id = $societe->identifiant;
        $etbs = self::getAtSociete($societe_id, acCouchdbClient::HYDRATE_ON_DEMAND)->getIds();
        $last_num = 0;
        foreach ($etbs as $id) {
            if (!preg_match('/ETABLISSEMENT-[0-9]{6}([0-9]{2})/', $id, $matches)) {
                continue;
            }

            $num = $matches[1];
            if ($num > $last_num) {
                $last_num = $num;
            }
        }

        return sprintf("%s%02d", $societe_id, $last_num + 1);
    }

    public function getAtSociete($societe_id, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        return $this->startkey('ETABLISSEMENT-' . $societe_id . '00')->endkey('ETABLISSEMENT-' . $societe_id . '99')->execute($hydrate);
    }

    public function getViewClient($view) {
        return acCouchdbManager::getView("etablissement", $view, 'Etablissement');
    }

    public function findAll() {
        return EtablissementRegionView::getInstance()->findAll();
    }

    public function findByFamille($famille) {
        return EtablissementRegionView::getInstance()->findByFamilleAndRegionNonSuspendu($famille);
    }

    public function findByFamillesAndRegions($familles, $regions) {
        return EtablissementRegionView::getInstance()->findByFamillesAndRegionsNonSuspendus($familles, $regions, null);
    }

    /**
     *
     * @param string $login
     * @param integer $hydrate
     * @return Etablissement
     * @deprecated find()
     */
    public function retrieveById($id, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {

        return parent::find('ETABLISSEMENT-' . $id, $hydrate);
    }

    public function find($id_or_identifiant, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {

        return parent::find($this->getId($id_or_identifiant), $hydrate, $force_return_ls);
    }

    public function findByAccises($no_accises) {
        return $this->findByCvi($no_accises);
    }

    public function findByCvi($cvi) {
        $rows = EtablissementFindByCviView::getInstance()->findByCvi(str_replace(' ', '', $cvi));
        if (!count($rows)) {
            return null;
        }

        return $this->find($rows[0]->id);
    }

    public function findAllByCvi($cvi) {
        $rows = EtablissementFindByCviView::getInstance()->findByCvi($cvi);
        if (!count($rows)) {
            return array();
        }
        $etbs = array();
        foreach ($rows as $row) {
            $etbs[$row->id] = $this->find($row->id);
        }
        return $etbs;
    }

    public function findByNoAccise($accise,$withSuspendu = true) {
        $rows = EtablissementFindByCviView::getInstance()->findByAccise($accise);

        if (!count($rows)) {
            return null;
        }

        return $this->find($rows[0]->id);
    }

    public function retrieveByName($name) {
      $e = EtablissementAllView::getInstance()->findByInterproStatutAndFamilles('INTERPRO-declaration', EtablissementClient::STATUT_ACTIF, array(EtablissementFamilles::FAMILLE_NEGOCIANT), $name, 1);
      if (count($e) == 1){
        return $this->find($e[0]->id);
      }
      return null;
    }

    public function getId($id_or_identifiant) {
        $id = $id_or_identifiant;
        if (strpos($id_or_identifiant, 'ETABLISSEMENT-') === false) {
            $id = 'ETABLISSEMENT-' . $id_or_identifiant;
        }

        return $id;
    }

    public function getIdentifiant($id_or_identifiant) {

        return $identifiant = str_replace('ETABLISSEMENT-', '', $id_or_identifiant);
    }

    /**
     *
     * @deprecated find()
     */
    public function findByIdentifiant($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {

        return parent::find('ETABLISSEMENT-' . $identifiant, $hydrate);
    }

    public function matchFamille($f) {
        if (preg_match('/producteur/i', $f)) {

            return EtablissementFamilles::FAMILLE_PRODUCTEUR;
        }
        if (preg_match('/n.*gociant/i', $f)) {

            return EtablissementFamilles::FAMILLE_NEGOCIANT;
        }
        if (preg_match('/courtier/i', $f)) {

            return EtablissementFamilles::FAMILLE_COURTIER;
        }

        throw new sfException("La famille $f doit être soit producteur soit negociant soit courtier");
    }

    public function matchSousFamille($sf) {
        $sf = KeyInflector::slugify($sf);
        $matches = array("(particuliere|cooperative)" => EtablissementFamilles::SOUS_FAMILLE_CAVE_PARTICULIERE,
            "regional" => EtablissementFamilles::SOUS_FAMILLE_REGIONAL,
            "exterieur" => EtablissementFamilles::SOUS_FAMILLE_EXTERIEUR,
            "etranger" => EtablissementFamilles::SOUS_FAMILLE_ETRANGER,
            "union" => EtablissementFamilles::SOUS_FAMILLE_UNION,
            "vinificateur" => EtablissementFamilles::SOUS_FAMILLE_VINIFICATEUR);
        foreach ($matches as $match => $s) {
            if (preg_match('/' . $match . '/i', $sf)) {
                return $s;
            }
        }

        if (!$sf) {
            return EtablissementFamilles::SOUS_FAMILLE_CAVE_PARTICULIERE;
        }

        throw new sfException('Sous Famille "' . $sf . '" inconnue');
    }

    public static function getStatuts() {
        return array(self::STATUT_ACTIF => self::STATUT_ACTIF,
            self::STATUT_SUSPENDU => self::STATUT_SUSPENDU);
    }

    public static function getRecettesLocales() {
        return array(self::RECETTE_LOCALE => 'Recette locale');
    }

    public static function getRegionsWithoutHorsInterLoire() {
        return array(self::REGION_CVO => self::REGION_CVO);
    }

    public static function getRegions() {
        return array_merge(self::getRegionsWithoutHorsInterLoire(), array(self::REGION_HORS_CVO => self::REGION_HORS_CVO));
    }

    public static function getNaturesInao() {
        return array_merge(array('' => ''), self::$natures_inao_libelles);
    }

    public function getNatureInaoLibelle($nature) {
        if (!$nature) {
            return "";
        }
        $naturesLibelles = self::getNaturesInao();
        return $naturesLibelles[$nature];
    }

    public static function getTypeDR() {
        return array(self::TYPE_DR_DRM => self::TYPE_DR_DRM,
            self::TYPE_DR_DRA => self::TYPE_DR_DRA);
    }

    public static function listTypeLiaisons() {
        return array_keys(self::getTypesLiaisons());
    }

    public static function getTypesLiaisons() {
        return array(self::TYPE_LIAISON_BAILLEUR => 'A pour bailleur',
            self::TYPE_LIAISON_METAYER => 'A pour métayer',
            self::TYPE_LIAISON_ADHERENT => 'Adhérent de (coop.)',
            self::TYPE_LIAISON_CONTRAT_INTERNE => 'Contrat interne');
    }

    public static function getPrefixForRegion($region) {
        $prefixs = array(self::REGION_CVO => '1');
        return $prefixs[$region];
    }

    public function buildInfosContact($etb) {
        $result = new stdClass();
        $region = $etb->region;
        $contacts = sfConfig::get('app_teledeclaration_contact_contrat');

        if ($etb->famille == EtablissementFamilles::FAMILLE_COURTIER) {
            $region = self::REGION_HORS_CVO;

            $result->nom = $contacts[$region]['nom'];
            $result->email = $contacts[$region]['email'];
            $result->telephone = $contacts[$region]['telephone'];
            return $result;
        }
        $result->nom = $contacts[$region]['nom'];
        $result->email = $contacts[$region]['email'];
        $result->telephone = $contacts[$region]['telephone'];
        return $result;
    }

    public function calculRegion($etablissement) {
        if($etablissement->getPays() != 'FR') {

            return self::REGION_HORS_CVO;
        }

        if(!preg_match("/".VracConfiguration::getInstance()->getRegionDepartement()."/", $etablissement->getCodePostal())) {

            return self::REGION_HORS_CVO;
        }

        return self::REGION_CVO;
    }

}
