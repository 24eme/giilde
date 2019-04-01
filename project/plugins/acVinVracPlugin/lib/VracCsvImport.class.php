<?php

class VracCsvImport extends CsvFile
{
    const CSV_TYPE_TRANSACTION = 0;
    const CSV_DATE_SAISIE = 1;
    const CSV_CREATEUR_ID = 2;
    const CSV_CREATEUR_NUMERO = 3;
    const CSV_ACHETEUR_ID = 4;
    const CSV_ACHETEUR_NUMERO = 5;
    const CSV_ACHETEUR_NOM = 6;
    const CSV_VENDEUR_ID = 7;
    const CSV_VENDEUR_NUMERO = 8;
    const CSV_VENDEUR_NOM = 9;
    const CSV_REPRESENTANT_ID = 10;
    const CSV_REPRESENTANT_NUMERO = 11;
    const CSV_REPRESENTANT_NOM = 12;
    const CSV_COURTIER_MANDATAIRE_ID = 13;
    const CSV_COURTIER_MANDATAIRE_NUMERO = 14;
    const CSV_COURTIER_MANDATAIRE_NOM = 15;

    const CSV_VIN_LIBELLE = 16;
    const CSV_VIN_CERTIF = 17;
    const CSV_VIN_GENRE = 18;
    const CSV_VIN_APPELLATION = 19;
    const CSV_VIN_MENTION = 20;
    const CSV_VIN_LIEU = 21;
    const CSV_VIN_COULEUR = 22;
    const CSV_VIN_CEPAGE = 23;
    const CSV_VIN_COMPLEMENT = 24;
    const CSV_VIN_PERSO = 25;

    const CSV_MILLESIME = 26;
    const CSV_MILLESIME_85_15 = 27;
    const CSV_CEPAGE = 28;
    const CSV_CEPAGE_85_15 = 29;
    const CSV_AB = 30;
    const CSV_LOT = 31;
    const CSV_DEGRE = 32;
    const CSV_MENTION = 33;
    const CSV_VOLUME = 34;
    const CSV_PRIX = 35;
    const CSV_CONTENANCE = 36;

    const CSV_LOGEMENT = 37;
    const CSV_VENDEUR_TVA = 38;
    const CSV_DELAI_PAIEMENT = 39;
    const CSV_MOYEN_PAIEMENT = 40;
    const CSV_ACOMPTE = 41;
    const CSV_DATE_RETIRAISON_DEBUT = 42;
    const CSV_DATE_RETIRAISON_LIMITE = 43;
    const CSV_RESERVE_PROPRIETE = 44;
    const CSV_CAHIER_CHARGES = 45;
    const CSV_AUTH_NOM_VIN = 46;
    const CSV_AUTH_NOM_PRODUCTEUR = 47;
    const CSV_OBSERVATION = 48;

    const LABEL_BIO = 'agriculture_biologique';

    public static $labels_array = [self::LABEL_BIO => "Agriculture Biologique"];

    /** @var int $imported Nombre de vrac importé */
    protected static $imported = 0;

    /** @var int $line Numero de ligne du CSV */
    protected static $line = 1;

    /** @var array $errors Tableau des erreurs de vérification */
    private $errors = [];

    /** @var array $warnings Tableau des warnings de la vérification */
    private $warnings = [];

    /**
     * Crée une instance depuis un tableau CSV
     *
     * @param array $array Le CSV transformé en tableau
     * @return self
     */
    public static function createFromArray(array $lines, $headers = true) {
        if ($headers) {
            array_shift($lines);
            self::$line++;
        }

        $class = new self();
        $class->csvdata = $lines;

        return $class;
    }

    /**
     * Générateur qui renvoie les lignes du CSV une à une
     *
     * @yield array $line Un vrac
     */
    public function getLines() {
        foreach ($this->csvdata as $line) {
            yield $line;
        }
    }

    /**
     * Retourne le tableau contenant les erreurs
     *
     * @return array Le tableau d'erreur
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Retourne le tableau contenant les avertissements
     *
     * @return array Le tableau des avertissements
     */
    public function getWarnings() {
        return $this->warnings;
    }

    /**
     * Importe des vracs dans la base
     *
     * @param bool $verified Le csv a été vérifier
     * @return int Nombre de vracs importés
     */
    public function import($verified = false) {
        $configuration = ConfigurationClient::getInstance()->getCurrent();

        foreach ($this->getLines() as $line) {
            $v = new Vrac();

            $v->teledeclare = true;

            $v->type_transaction = $line[self::CSV_TYPE_TRANSACTION];

            $v->createur_identifiant = $line[self::CSV_CREATEUR_ID];
            if (! $v->createur_identifiant) {
                $v->createur_identifiant = $this->guessId($line[self::CSV_CREATEUR_NUMERO]);
            }

            $v->acheteur_identifiant = $line[self::CSV_ACHETEUR_ID];
            if (! $v->acheteur_identifiant) {
                $v->acheteur_identifiant = $this->guessId($line[self::CSV_ACHETEUR_NUMERO]);
            }

            $v->vendeur_identifiant = $line[self::CSV_VENDEUR_ID];
            if (! $v->vendeur_identifiant) {
                $v->vendeur_identifiant = $this->guessId($line[self::CSV_VENDEUR_NUMERO]);
            }

            $v->representant_identifiant = $line[self::CSV_REPRESENTANT_ID];
            if (! $v->representant_identifiant) {
                $v->representant_identifiant = $v->vendeur_identifiant;
            }

            $v->mandataire_identifiant = $line[self::CSV_COURTIER_MANDATAIRE_ID];
            if (! $v->mandataire_identifiant) {
                $v->mandataire_identifiant = $this->guessId($line[self::CSV_COURTIER_MANDATAIRE_NUMERO]);
            }

            if ($v->mandataire_identifiant) {
                $v->mandataire_exist = true;
            }

            // vin
            /*
            $produit = "/declaration/certifications/";
            $produit .= ($line[self::CSV_VIN_CERTIF]) ?: "DEFAUT";
            $produit .= "/genres/";
            $produit .= ($line[self::CSV_VIN_GENRE]) ?: "DEFAUT";
            $produit .= "/appellations/";
            $produit .= ($line[self::CSV_VIN_APPELLATION]) ?: "DEFAUT";
            $produit .= "/mentions/";
            $produit .= ($line[self::CSV_VIN_MENTION]) ?: "DEFAUT";
            $produit .= "/lieux/";
            $produit .= ($line[self::CSV_VIN_LIEU]) ?: "DEFAUT";
            $produit .= "/couleurs/";
            $produit .= ($line[self::CSV_VIN_COULEUR]) ?: "DEFAUT";
            $produit .= "/cepages/";
            $produit .= ($line[self::CSV_VIN_CEPAGE]) ?: "DEFAUT";

            $v->produit = $produit;
            */

            $produit = $configuration->identifyProductByLibelle($line[self::CSV_VIN_LIBELLE]);
            if ($produit) {
                $v->setProduit($produit->getHash());
            }

            $v->millesime = $line[self::CSV_MILLESIME];
            $v->millesime_85_15 = (bool) $line[self::CSV_MILLESIME_85_15];

            $v->cepage = $line[self::CSV_CEPAGE];
            $v->cepage_85_15 = (bool) $line[self::CSV_CEPAGE_85_15];

            if ($line[self::CSV_AB]) {
                $v->getOrAdd('label')->add(self::LABEL_BIO, self::$labels_array[self::LABEL_BIO]);
            }

            $v->lot = $line[self::CSV_LOT];
            $v->degre = $line[self::CSV_DEGRE];

            $v->categorie_vin = VracClient::CATEGORIE_VIN_GENERIQUE;
            if ($line[self::CSV_MENTION]) {
                $v->domaine = $line[self::CSV_MENTION];
                $v->categorie_vin = "MENTION";
            }

            if ($v->type_transaction === VracClient::TYPE_TRANSACTION_RAISINS) {
                $v->raisin_quantite = $line[self::CSV_VOLUME];
            } else {
                $v->jus_quantite = $line[self::CSV_VOLUME];
            }
            $v->prix_initial_unitaire = $line[self::CSV_PRIX];

            if ($v->type_transaction === VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE) {
                $contenances = VracConfiguration::getInstance()->getContenances();
                if (array_key_exists($line[self::CSV_CONTENANCE], $contenances)) {
                    $v->bouteilles_contenance_libelle = $line[self::CSV_CONTENANCE];
                }
            }

            $v->logement = $line[self::CSV_LOGEMENT];
            $v->vendeur_tva = $line[self::CSV_VENDEUR_TVA];
            $v->delai_paiement = $line[self::CSV_DELAI_PAIEMENT];
            $v->moyen_paiement = $line[self::CSV_MOYEN_PAIEMENT];
            $v->acompte = $line[self::CSV_ACOMPTE];
            $v->date_debut_retiraison = $line[self::CSV_DATE_RETIRAISON_DEBUT];
            $v->date_limite_retiraison = $line[self::CSV_DATE_RETIRAISON_LIMITE];
            $v->clause_reserve_propriete = $line[self::CSV_RESERVE_PROPRIETE];
            $v->cahier_charge = $line[self::CSV_CAHIER_CHARGES];
            $v->autorisation_nom_producteur = $line[self::CSV_AUTH_NOM_PRODUCTEUR];
            $v->autorisation_nom_vin = $line[self::CSV_AUTH_NOM_VIN];
            $v->conditions_particulieres = $line[self::CSV_OBSERVATION];

            $v->valide->date_saisie = $line[self::CSV_DATE_SAISIE];

            if ($verified) {
                $v->acompte = (float) $v->acompte;
                $v->degre = (float) $v->degre;
                $v->millesime = (int) $v->millesime;
                $v->jus_quantite = (float) $v->jus_quantite;
                $v->prix_initial_unitaire = (float) $v->prix_initial_unitaire;

                $v->valide->date_signature_acheteur = date('c');

                $v->update();
                $v->save();

                self::$imported++;
            } else {
                $validator = new VracValidation($v);

                if ($validator->hasErreurs()) {
                    foreach ($validator->getErreurs() as $err) {
                        $this->errors[self::$line][] = $err->getMessage() . ': ' . $err->getInfo();
                    }
                }

                if ($validator->hasVigilances()) {
                    foreach ($validator->getVigilances() as $warn) {
                        $this->warnings[self::$line][] = $warn->getMessage() . ': ' .  $warn->getInfo();
                    }
                }
            }

            self::$line++;
        }

        return self::$imported;
    }

    /**
     * Trouve le numero d'identifiant en fonction d'un autre
     *
     * @param string $numero Le numéro d'accise, de siret, ou de cvi
     * @return bool|string L'identifiant à trouver ou false
     */
    private function guessId($numero) {
        $res = false;
        $instance = EtablissementClient::getInstance();

        switch ($numero) {
            // SIRET
            case (preg_match('#^\d{14}$#', $numero) ? true : false):
                $res = false;
                break;

            // CVI
            case (preg_match('#^\d{10}$#', $numero) ? true : false):
                $res = $instance->findByCvi($numero);
                break;

            // Accise
            case (preg_match('#^FR0[a-zA-Z0-9]{10}$#', $numero) ? true : false):
                $res = $instance->findByNoAccise($numero);
                break;

            // Mauvais code
            default:
                $res = false;
                break;
        }

        if ($res === null) {
            $res = false;
        }

        if ($res instanceof Etablissement) {
            $res = $res->identifiant;
        }

        return $res;
    }
}
