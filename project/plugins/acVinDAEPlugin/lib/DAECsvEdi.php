<?php

class DAECsvEdi extends CsvFile {

    public $erreurs = array();
    public $statut = null;
    public $countryList = array();
    public $deviseList = array();

    const STATUT_ERREUR = 'ERREUR';
    const STATUT_VALIDE = 'VALIDE';
    const STATUT_WARNING = 'WARNING';

    const CSV_DATE_COMMERCIALISATION = 0; // A (colonne n°1) : date de la commercialisation (format AAAA-MM-JJ)
    const CSV_VENDEUR_ACCISES = 1; // B (colonne n°2) : identifiant declarvins du déclarant (le vendeur)
    const CSV_VENDEUR_NOM = 2; // C (colonne n°3) : numéro d'accises du déclaration (le vendeur)
    const CSV_PRODUIT_INAO = 3; // H (colonne n°8) : code ou nom de la certification du vin (champ obligatoire si la colonne P (n° 16) n'est pas renseigné)
    const CSV_PRODUIT_APPELLATION = 4; // J (colonne n°10) : nom ou code du appellation du vin (champ facultatif)
    const CSV_PRODUIT_MILLESIME = 5; // S (colonne n°19) : millésime (au format AAAA) (champ facultatif)
    const CSV_PRODUIT_LIBELLE_PERSONNALISE = 6; // P (colonne n°16) : Le libellé personnalisé du vin (champ facultatif sauf si les colonnes H à N ne sont pas renseignées) pouvant contenir entre parenthèses le code INAO ou le libellé fiscal du produit
    const CSV_PRODUIT_LABEL = 7; // Q (colonne n°17) : label du produit : "conventionnel", "biologique", ... (champ facultatif)
    const CSV_PRODUIT_DOMAINE = 8; // R (colonne n°18) : mention de domaine ou château revendiqué ("domaine", "château" ou vide)
    const CSV_PRODUIT_PRIMEUR = 9; // S (colonne n°19) : millésime (au format AAAA) (champ facultatif)
    const CSV_ACHETEUR_ACCISES = 10; // T (colonne n°20) : n°accise de l'acheteur (champ facultatif)
    const CSV_ACHETEUR_NOM = 11; // U (colonne n°20) : nom acheteur (champ facultatif)
    const CSV_PAYS_NOM = 12; // W (colonne n°20) : nom du pays de destination ou son code ISO 3166
    const CSV_ACHETEUR_TYPE = 13; // V (colonne n°20) : type acheteur ("Importateur", "Négociant région" ou "Négociant/Union Vallée du Rhône", négociant hors région, "GD" ou "Grande Distribution", "Discount", "Grossiste", "Caviste", "VD" ou "Vente directe", "Autre", ...)
    const CSV_CONDITIONNEMENT_TYPE = 14; // X (colonne n°20) : type de conditionnement (VRAC ou HL, Bouteille, BIB)
    const CSV_CONDITIONNEMENT_VOLUME = 15; // Z (colonne n°20) : contenance conditionnement en litres
    const CSV_CONDITIONNEMENT_QUANTITE = 16; // AA (colonne n°20) : quantité de conditionnement (en nombre de bib, de bouteille ou, pour le vrac, en hl)
    const CSV_PRIX_UNITAIRE = 17; // AB (colonne n°20) : prix unitaire (prix en € par bouteille, bib ou hl)
    const CSV_DEVISE = 18;
    
    protected $daes = null;
    protected $csv = null;

    public function __construct($file) {
        $this->buildCountryList();
        $this->buildDeviseList();
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

    public function buildDeviseList() {
        $deviseList = ConfigurationClient::getInstance()->getDeviseList();
        $match_array = array();
        foreach ($deviseList as $keyUpper => $countryString) {
            $match_array[$keyUpper . '_' . strtolower($keyUpper)] = $countryString;
            $match_array[$countryString] = $countryString;
        }
        $this->deviseList = array_merge($deviseList, $match_array);
    }

}
