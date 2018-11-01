<?php

class DAECsvEdi extends CsvFile {

    public $erreurs = array();
    public $statut = null;
    public $countryList = array();

    const STATUT_ERREUR = 'ERREUR';
    const STATUT_VALIDE = 'VALIDE';
    const STATUT_WARNING = 'WARNING';

    const CSV_DATE_COMMERCIALISATION = 0; // A (colonne n°1) : date de la commercialisation (format AAAA-MM-JJ)
    const CSV_IDENTIFIANT = 1; // B (colonne n°2) : identifiant declarvins du déclarant (le vendeur)
    const CSV_NUMACCISE = 2; // C (colonne n°3) : numéro d'accises du déclaration (le vendeur)
    const CSV_NOM_DECLARANT = 3; // D (colonne n°4) : nom du déclarant (le vendeur)
    const CSV_STAT_FAMILLE = 4; // E (colonne n°5) : champs réservé (stat famille)
    const CSV_STAT_SOUS_FAMILLE = 5; // F (colonne n°6) : champs réservé (stat sous famille)
    const CSV_STAT_DPT = 6; // G (colonne n°7) : champs réservé (stat département)


    const CSV_PRODUIT_CERTIFICATION = 7; // H (colonne n°8) : code ou nom de la certification du vin (champ obligatoire si la colonne P (n° 16) n'est pas renseigné)
    const CSV_PRODUIT_GENRE = 8; // I (colonne n°9) : nom ou code du genre du vin (champ obligatoire si la colonne P (n° 16) n'est pas renseigné)
    const CSV_PRODUIT_APPELLATION = 9; // J (colonne n°10) : nom ou code du appellation du vin (champ facultatif)
    const CSV_PRODUIT_MENTION = 10; // K (colonne n°11) : nom ou code du mention du vin (champ facultatif)
    const CSV_PRODUIT_LIEU = 11; // L (colonne n°12) : nom ou code du lieu du vin (champ facultatif)
    const CSV_PRODUIT_COULEUR = 12; // M (colonne n°13) : nom ou code du couleur du vin (champ obligatoire si la colonne P (n° 16) n'est pas renseigné)
    const CSV_PRODUIT_CEPAGE = 13; // N (colonne n°14) : nom ou code du cépage du vin (champ facultatif)
    const CSV_PRODUIT_COMPLEMENT = 14; // O (colonne n°15) : Le complément du vin (champ facultatif)
    const CSV_PRODUIT_LIBELLE_PERSONNALISE = 15; // P (colonne n°16) : Le libellé personnalisé du vin (champ facultatif sauf si les colonnes H à N ne sont pas renseignées) pouvant contenir entre parenthèses le code INAO ou le libellé fiscal du produit
    const CSV_PRODUIT_LABEL = 16; // Q (colonne n°17) : label du produit : "conventionnel", "biologique", ... (champ facultatif)
    const CSV_PRODUIT_DOMAINE = 17; // R (colonne n°18) : mention de domaine ou château revendiqué ("domaine", "château" ou vide)
    const CSV_PRODUIT_MILLESIME = 18; // S (colonne n°19) : millésime (au format AAAA) (champ facultatif)
    const CSV_PRODUIT_PRIMEUR = 19; // S (colonne n°19) : millésime (au format AAAA) (champ facultatif)

    const CSV_ACHETEUR_NUMACCISE = 20; // T (colonne n°20) : n°accise de l'acheteur (champ facultatif)
    const CSV_ACHETEUR_NOM = 21; // U (colonne n°20) : nom acheteur (champ facultatif)
    const CSV_ACHETEUR_TYPE = 22; // V (colonne n°20) : type acheteur ("Importateur", "Négociant région" ou "Négociant/Union Vallée du Rhône", négociant hors région, "GD" ou "Grande Distribution", "Discount", "Grossiste", "Caviste", "VD" ou "Vente directe", "Autre", ...)
    const CSV_PAYS_NOM = 23; // W (colonne n°20) : nom du pays de destination ou son code ISO 3166
    const CSV_TYPE_CONDITIONNEMENT = 24; // X (colonne n°20) : type de conditionnement (VRAC ou HL, Bouteille, BIB)
    const CSV_LIBELLE_CONDITIONNEMENT = 25; // Y (colonne n°20) : libellé conditionnement
    const CSV_CONTENANCE_CONDITIONNEMENT = 26; // Z (colonne n°20) : contenance conditionnement en litres
    const CSV_QUANTITE_CONDITIONNEMENT = 27; // AA (colonne n°20) : quantité de conditionnement (en nombre de bib, de bouteille ou, pour le vrac, en hl)
    const CSV_PRIX_UNITAIRE = 28; // AB (colonne n°20) : prix unitaire (prix en € par bouteille, bib ou hl)
    const CSV_STAT_QUANTITE = 29; // AC (colonne n°20) : champ réservé (stat qtt hl)
    const CSV_STAT_PRIX = 30; // AD (colonne n°20) : champ réservé (stat prix hl)

    protected $daes = null;
    protected $csv = null;

    public function __construct($file) {
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
