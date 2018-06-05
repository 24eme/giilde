<?php

class DRMCsvEdi extends CsvFile {

    public $erreurs = array();
    public $statut = null;
    public $countryList = array();

    const STATUT_ERREUR = 'ERREUR';
    const STATUT_VALIDE = 'VALIDE';
    const STATUT_WARNING = 'WARNING';
    const TYPE_CAVE = 'CAVE';
    const TYPE_CRD = 'CRD';
    const TYPE_ANNEXE = 'ANNEXE';
    const TYPE_ANNEXE_NONAPUREMENT = 'NONAPUREMENT';
    const TYPE_ANNEXE_STATS_EUROPEENES = "STATS-EUROPEENNES";

    const CSV_TYPE = 0;
    const CSV_PERIODE = 1;
    const CSV_IDENTIFIANT = 2;
    const CSV_NUMACCISE = 3;
    const CSV_CAVE_CERTIFICATION = 4;
    const CSV_CAVE_GENRE = 5;
    const CSV_CAVE_APPELLATION = 6;
    const CSV_CAVE_MENTION = 7;
    const CSV_CAVE_LIEU = 8;
    const CSV_CAVE_COULEUR = 9;
    const CSV_CAVE_CEPAGE = 10;
    const CSV_CAVE_LIBELLE_COMPLEMENTAIRE = 11;
    const CSV_CAVE_LIBELLE_PRODUIT = 12;
    const CSV_CAVE_TYPE_DRM = 13;
    const CSV_CAVE_CATEGORIE_MOUVEMENT = 14;
    const CSV_CAVE_TYPE_MOUVEMENT = 15;
    const CSV_CAVE_VOLUME = 16;
    const CSV_CAVE_EXPORTPAYS = 17;
    const CSV_CAVE_CONTRATID = 18;
    const CSV_CAVE_COMMENTAIRE = 19;

    const CSV_CAVE_CONTRAT_PRIXHL = 20;
    const CSV_CAVE_CONTRAT_ACHETEUR_ACCISES = 21;
    const CSV_CAVE_CONTRAT_ACHETEUR_NOM = 22;


    const CSV_CRD_COULEUR = 4;
    const CSV_CRD_GENRE = 5;
    const CSV_CRD_CENTILITRAGE = 6;

    const CSV_CRD_REGIME = 13;
    const CSV_CRD_CATEGORIE_KEY = 14;
    const CSV_CRD_TYPE_KEY = 15;
    const CSV_CRD_QUANTITE = 16;

    const CSV_ANNEXE_TYPE_DRM = 11;
    const CSV_ANNEXE_TYPEANNEXE = 14;
    const CSV_ANNEXE_TYPEMVT = 15;
    const CSV_ANNEXE_QUANTITE = 16;
    const CSV_ANNEXE_NONAPUREMENTDATEEMISSION = 17;
    const CSV_ANNEXE_NONAPUREMENTACCISEDEST = 18;
    const CSV_ANNEXE_NUMERODOCUMENT = 19;
    const CSV_ANNEXE_OBSERVATION = 20;

    const COMPLEMENT = "COMPLEMENT";
    const COMPLEMENT_OBSERVATIONS = "OBSERVATIONS";
    const COMPLEMENT_TAV = "TAV";
    const COMPLEMENT_PREMIX = "PREMIX";

    protected static $permitted_types = array(self::TYPE_CAVE,
        self::TYPE_CRD,
        self::TYPE_ANNEXE);
    protected static $permitted_annexes_type_mouvements = array('DEBUT', 'FIN');
    protected $drm = null;
    protected $csv = null;
    protected static $genres = array('MOU' => 'Mousseux', 'EFF' => 'Effervescent', 'TRANQ' => 'Tranquille');
    protected $type_annexes = array(self::TYPE_ANNEXE_NONAPUREMENT => 'Non Apurement',  self::TYPE_ANNEXE_STATS_EUROPEENES => 'Statistiques Européenes');

    public function __construct($file, DRM $drm = null) {
        $this->drm = $drm;
        $this->type_annexes_docs = array_merge($this->type_annexes, DRMClient::$drm_documents_daccompagnement);
        $this->buildCountryList();
        parent::__construct($file);
    }

    public function buildCountryList() {
        $countryList = ConfigurationClient::getInstance()->getCountryList();
        $match_array = array();
        foreach ($countryList as $keyUpper => $countryString) {
            $match_array[$keyUpper . '_' . strtolower($keyUpper)] = $countryString;
            $match_array[$countryString] = $countryString;
        }
        $this->countryList = array_merge($countryList, $match_array);
    }

}
