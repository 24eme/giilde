<?php

class ProduitCsvFile extends CsvFile {

    const CSV_PRODUIT_INTERPRO = 0;
    const CSV_PRODUIT_CATEGORIE_LIBELLE = 1;    //CATEGORIE == CERTIFICATION
    const CSV_PRODUIT_CATEGORIE_CODE = 2;       //CATEGORIE == CERTIFICATION
    const CSV_PRODUIT_CATEGORIE_CODE_APPLICATIF_DROIT = 'C';
    const CSV_PRODUIT_GENRE_LIBELLE = 3;
    const CSV_PRODUIT_GENRE_CODE = 4;
    const CSV_PRODUIT_GENRE_CODE_APPLICATIF_DROIT = 'G';
    const CSV_PRODUIT_DENOMINATION_LIBELLE = 5; //DENOMINATION == APPELLATION
    const CSV_PRODUIT_DENOMINATION_CODE = 6;    //DENOMINATION == APPELLATION
    const CSV_PRODUIT_DENOMINATION_CODE_APPLICATIF_DROIT = 'A';
    const CSV_PRODUIT_MENTION_LIBELLE = 7;
    const CSV_PRODUIT_MENTION_CODE = 8;
    const CSV_PRODUIT_MENTION_CODE_APPLICATIF_DROIT = 'M';
    const CSV_PRODUIT_LIEU_LIBELLE = 9;
    const CSV_PRODUIT_LIEU_CODE = 10;
    const CSV_PRODUIT_LIEU_CODE_APPLICATIF_DROIT = 'L';
    const CSV_PRODUIT_COULEUR_LIBELLE = 11;
    const CSV_PRODUIT_COULEUR_CODE = 12;
    const CSV_PRODUIT_COULEUR_CODE_APPLICATIF_DROIT = 'CO';
    const CSV_PRODUIT_CEPAGE_LIBELLE = 13;
    const CSV_PRODUIT_CEPAGE_CODE = 14;
    const CSV_PRODUIT_CEPAGE_CODE_APPLICATIF_DROIT = 'CE';
    const CSV_PRODUIT_DEPARTEMENTS = 15;
    const CSV_PRODUIT_DOUANE_CODE = 16;
    const CSV_PRODUIT_DOUANE_LIBELLE = 17;
    const CSV_PRODUIT_DOUANE_TAXE = 18;
    const CSV_PRODUIT_DOUANE_DATE = 19;
    const CSV_PRODUIT_DOUANE_NOEUD = 20; //a *_DROIT value previously defined
    const CSV_PRODUIT_CVO_TAXE = 21;
    const CSV_PRODUIT_CVO_DATE = 22;
    const CSV_PRODUIT_CVO_NOEUD = 23; //a _DROIT value previously defined
    const CSV_PRODUIT_REPLI_ENTREE = 24;
    const CSV_PRODUIT_REPLI_SORTI = 25;
    const CSV_PRODUIT_DECLASSEMENT_ENTREE = 26;
    const CSV_PRODUIT_DECLASSEMENT_SORTI = 27;
    const CSV_PRODUIT_DENSITE = 28;
    const CSV_PRODUIT_LABELS = 29;
    const CSV_PRODUIT_CODE_PRODUIT = 30;
    const CSV_PRODUIT_CODE_PRODUIT_NOEUD = 31;
    const CSV_PRODUIT_CODE_COMPTABLE = 32;
    const CSV_PRODUIT_CODE_COMPTABLE_NOEUD = 33;
    const CSV_PRODUIT_CODE_DOUANE = 34;
    const CSV_PRODUIT_CODE_DOUANE_NOEUD = 35;
    const CSV_PRODUIT_ALIAS_PRODUIT = 36;
    const CSV_PRODUIT_FORMAT_LIBELLE = 37;
    const CSV_PRODUIT_FORMAT_LIBELLE_NOEUD = 38;
    const CSV_PRODUIT_CEPAGES_AUTORISES = 39;

    protected $config;
    protected $errors;

    public function __construct($config, $file) {
        parent::__construct($file);
        $this->config = $config;
    }

    private function getProduit($hash) {
        return $this->config->declaration->getOrAdd($hash);
    }

    private function getNewHash($line) {
        return 'certifications/' . $this->getKeyProduit($line[self::CSV_PRODUIT_CATEGORIE_CODE], true) .
                '/genres/' . $this->getKeyProduit($line[self::CSV_PRODUIT_GENRE_CODE], true, true) .
                '/appellations/' . $this->getKeyProduit($line[self::CSV_PRODUIT_DENOMINATION_CODE], true, true) .
                '/mentions/' . $this->getKeyProduit($line[self::CSV_PRODUIT_MENTION_CODE], true, true) .
                '/lieux/' . $this->getKeyProduit($line[self::CSV_PRODUIT_LIEU_CODE], true, true) .
                '/couleurs/' . strtolower($this->couleurKeyToCode($line[self::CSV_PRODUIT_COULEUR_CODE])) .
                '/cepages/' . $this->getKeyProduit($line[self::CSV_PRODUIT_CEPAGE_CODE], true, true);
    }

    private function getOldHash($line) {
        return 'certifications/' . $this->getKeyProduit($line[self::CSV_PRODUIT_CATEGORIE_CODE], false) .
                '/genres/' . $this->getKeyProduit($line[self::CSV_PRODUIT_GENRE_CODE], false, true) .
                '/appellations/' . $this->getKeyProduit($line[self::CSV_PRODUIT_DENOMINATION_CODE], false, true) .
                '/mentions/' . $this->getKeyProduit($line[self::CSV_PRODUIT_MENTION_CODE], false, true) .
                '/lieux/' . $this->getKeyProduit($line[self::CSV_PRODUIT_LIEU_CODE], false, true) .
                '/couleurs/' . strtolower($this->couleurKeyToCode($line[self::CSV_PRODUIT_COULEUR_CODE])) .
                '/cepages/' . $this->getKeyProduit($line[self::CSV_PRODUIT_CEPAGE_CODE], false, true);
    }

    private function couleurKeyToCode($key) {
        $correspondances = array(1 => "rouge",
            2 => "rose",
            3 => "blanc");

        if (!isset($correspondances[$key])) {

            return $key;
        }

        return $correspondances[$key];
    }

    private function getKeyProduit($key, $new = true, $withDefault = false) {
        $keyProduit = split('/', $key);
        if (count($keyProduit) != 2) {
            return $this->getKey($keyProduit[0], $withDefault);
        } else {
            if ($new) {
                return $this->getKey($keyProduit[0], $withDefault);
            } else {
                return $this->getKey($keyProduit[1], $withDefault);
            }
        }
    }

    private function getKey($key, $withDefault = false) {
        if ($withDefault) {
            return ($key) ? $key : Configuration::DEFAULT_KEY;
        } elseif (!$key) {
            throw new sfException('La clé "' . $key . '" n\'est pas valide');
        } else {
            return $key;
        }
    }

    public function importProduits() {
        $this->errors = array();
        $csv = $this->getCsv();

        if(!$this->config->isNew()) {
            $this->oldconfig = clone $this->config;
        }

        $this->config->declaration->remove('certifications');
        $this->config->declaration->add('certifications');

        try {
            foreach ($csv as $line) {
                if(preg_match("/^#/", $line[0])) {
                    continue;
                }

                $oldHash = $this->getOldHash($line);
                $newHash = $this->getNewHash($line);

                if ($oldHash != $newHash) {
                    if(!$this->oldconfig->declaration->exist($oldHash)) {

                        echo "ERROR;La corresponde n'a pas été trouvé dans l'ancienne conf $oldHash \n";
                        continue;
                    }
                    $this->config->getOrAdd('correspondances')->add("-declaration-".str_replace('/', '-', $newHash), "/declaration/".$oldHash);
                    //echo "UPDATE;On ajoute la corresondance " . $oldHash . " => " . $newHash . "\n";
                }

                $produit = $this->getProduit($newHash);
                $produit->setDonneesCsv($line);

                if(!isset($this->oldconfig) || (!$this->oldconfig->declaration->exist($oldHash) && $oldHash == $newHash)) {
                  //echo "ADDED;".$newHash." \n";
                } else {
                  echo "UPDATED;".$newHash." \n";
                  if($this->oldconfig->declaration->get($oldHash)->getTauxCvo(date('Y-m-d')) != $produit->getTauxCvo(date('Y-m-d'))) {
                        echo "/!\ CVO;".$newHash." ".$this->oldconfig->declaration->get($oldHash)->getTauxCvo(date('Y-m-d'))." => ".$produit->getTauxCvo(date('Y-m-d'))." \n";
                  }
                }                
            }

            if(isset($this->oldconfig)) {
                foreach($this->oldconfig->getProduits() as $produit) {
                    try {
                    $correspondance = @$this->config->getProduitWithCorrespondanceInverse($produit->getHash());
                    } catch(Exception $e) {
                        $correspondance = null;
                    }
                    $hash = $produit->getHash();
                    if($correspondance) {
                        $hash = $correspondance->getHash();
                    }
                    if(!$this->config->exist($hash)) {
                        echo "DELETED;".$hash." \n";
                    }
                }
            }
        } catch (Execption $e) {
            $this->errors[] = $e->getMessage();
        }

        $this->config->updateAliasForCorrespondances();

        return $this->config;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getStringLine($line) {
        $result = "";
        foreach ($line as $field) {
            $result.=$field . ";";
        }
        return $result;
    }

}
