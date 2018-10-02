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
class DAEImportCsvEdi extends DAECsvEdi 
{
    protected $all_produits = null;
    protected $csvDoc = null;
    protected $file = null;
    protected $identifiant = null;
    protected $dae = null;

    public function __construct($file = null, $identifiant ,$periode) {
            $this->identifiant = $identifiant;
            $this->file = $file;
            if(is_null($this->csvDoc)) {
                $this->csvDoc = CSVDAEClient::getInstance()->createOrFindDocFromDAES($file, $identifiant ,$periode);
            }
            $this->dae = new DAE();
            parent::__construct($file, array());
    }

    public function getCsvDoc() {
	    return $this->csvDoc;
    }

    protected function getConfigProduit($date) {
    	if(!$this->all_produits){
            $this->all_produits = $this->dae->getProduitsConfig($date);
        }
        return $this->all_produits;
  	}

	public function getCsvArrayErreurs(){
    	$csvErreurs = array();
        $csvErreurs[] = array("#Niveau erreur","Numéro ligne de l'erreur","Parametre en erreur ","Diagnostic");
        if($this->getCsvDoc()->hasErreurs()){
        	$erreursRows = $this->getCsvDoc()->getErreurs();
            foreach ($erreursRows as $erreur) {
            	$erreurLevel = ($erreur->exist('level'))? CSVDAEClient::$levelErrorsLibelle[$erreur->level] : "";
              	$csvErreurs[] = array($erreurLevel,"".$erreur->num_ligne,"".$erreur->csv_erreur,$erreur->diagnostic);
            }
        }
        return $csvErreurs;
	}

	public function getDocRows() {
		if($this->file){
        	return $this->getCsv();
        }
        if($this->csvDoc->hasCsvAttachement()){
        	$csvFile = new CsvFile($this->csvDoc->getAttachmentUri($this->csvDoc->getFileName()));
            return $csvFile->getCsv();
        }
        return array();
	}

    public function checkCSV() {
        $this->csvDoc->clearErreurs();
        $this->checkCSVIntegrity();
        if ($this->csvDoc->hasErreurs()) {
            $this->csvDoc->setStatut(self::STATUT_ERREUR);
            $this->csvDoc->save();
            return;
        }
        $this->checkRowsFromCSV();
        if ($this->csvDoc->hasErreurs()) {
            $this->csvDoc->setStatut(self::STATUT_ERREUR);
            $this->csvDoc->save();
            return false;
        }
        $this->csvDoc->setStatut(self::STATUT_VALIDE);
        $this->csvDoc->save();
        return true;
    }

    public function importCSV() {
        return $this->importDaesFromCSV();
    }

    private function checkRowsFromCSV() {
        return $this->importDaesFromCSV(true);
    }

    private function importDaesFromCSV($just_check = false) {
        $num_ligne = 1;
        $daes = array();
        $hasErrors = false;
        foreach ($this->getDocRows() as $csvRow) {
        	$founded_produit = $this->dae->getConfig()->identifyProduct($this->getHashProduit($csvRow), trim($csvRow[self::CSV_PRODUIT_LIBELLE_PERSONNALISE]));
        	if (!$founded_produit) {
        		$this->csvDoc->addErreur($this->productNotFoundError($num_ligne, $csvRow));
        		return;
        	}
            $label = $this->getItemKey($this->dae->getLabels(), $csvRow[self::CSV_PRODUIT_LABEL]);
            $mention = $this->getItemKey($this->dae->getMentions(), $csvRow[self::CSV_PRODUIT_DOMAINE]);
            $type = $this->getItemKey($this->dae->getTypes(), $csvRow[self::CSV_ACHETEUR_TYPE]);
            $contenance = $this->getItemKey($this->dae->getContenances(), $csvRow[self::CSV_LIBELLE_CONDITIONNEMENT]);
            $destination = $this->getItemKey($this->countryList, $csvRow[self::CSV_PAYS_NOM]);
            
            if (!$label) {
            	$this->csvDoc->addErreur($this->labelNotFoundError($num_ligne, $csvRow));
            	$hasErrors = true;
            } else {
            	$csvRow[self::CSV_PRODUIT_LABEL] = $label;
            }
            if (!$mention) {
            	$this->csvDoc->addErreur($this->mentionNotFoundError($num_ligne, $csvRow));
            	$hasErrors = true;
            } else {
            	$csvRow[self::CSV_PRODUIT_DOMAINE] = $mention;
            }
            if (!$type) {
            	$this->csvDoc->addErreur($this->typeNotFoundError($num_ligne, $csvRow));
            	$hasErrors = true;
            } else {
            	$csvRow[self::CSV_ACHETEUR_TYPE] = $type;
            }
            if (!$contenance) {
            	$this->csvDoc->addErreur($this->contenanceNotFoundError($num_ligne, $csvRow));
            	$hasErrors = true;
            } else {
            	$csvRow[self::CSV_LIBELLE_CONDITIONNEMENT] = $contenance;
            }
            if (!$destination) {
            	$this->csvDoc->addErreur($this->destinationNotFoundError($num_ligne, $csvRow));
            	$hasErrors = true;
            } else {
            	$csvRow[self::CSV_PAYS_NOM] = $destination;
            }
            if (!$just_check && !$hasErrors) {
                $daes[] = $this->createDae($csvRow, $founded_produit);
            }
            $num_ligne++;
        }
        if (!$hasErrors) {
        	foreach ($daes as $dae) {
        		$dae->save();
        	}
        }
    }
    
    private function getItemKey($items, $value) {
    	$length = strlen($value);
    	foreach ($items as $k => $v) {
    		if (preg_match('/'.trim($value).'/i', $k)) {
    			return $k;
    		}
    		if (preg_match('/'.KeyInflector::slugify($value).'/i', KeyInflector::slugify($k))) {
    			return $k;
    		}
	    	if (preg_match('/'.str_replace(' ', '', $value).'/i', str_replace(' ', '', $k))) {
	    		return $k;
	    	}
    		if ($length > 3) {
	    		if (preg_match('/'.trim($value).'/i', $v)) {
	    			return $k;
	    		}
	    		if (preg_match('/'.KeyInflector::slugify($value).'/i', KeyInflector::slugify($v))) {
	    			return $k;
	    		}
	    		if (preg_match('/'.str_replace(' ', '', $value).'/i', str_replace(' ', '', $v))) {
	    			return $k;
	    		}
    		}
    	}
    	return null;
    }

    private function checkCSVIntegrity() {
        $ligne_num = 1;
        foreach ($this->getDocRows() as $csvRow) {
            if(count($csvRow) != 30){
              $this->csvDoc->addErreur($this->createWrongFormatFieldCountError($ligne_num, $csvRow));
              $ligne_num++;
              continue;
            }
            if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', KeyInflector::slugify($csvRow[self::CSV_DATE_COMMERCIALISATION]))) {
                $this->csvDoc->addErreur($this->createWrongFormatDateCommercialisationError($ligne_num, $csvRow));
            }
            if ($csvRow[self::CSV_PRODUIT_MILLESIME] && !preg_match('/^[0-9]{4}$/', trim($csvRow[self::CSV_PRODUIT_MILLESIME]))) {
                $this->csvDoc->addErreur($this->createWrongFormatMillesimeError($ligne_num, $csvRow));
            }
            if ($csvRow[self::CSV_NUMACCISE] && !preg_match('/^FR[a-zA-Z0-9]{11}$/', trim($csvRow[self::CSV_NUMACCISE]))) {
            	$this->csvDoc->addErreur($this->createWrongFormatNumAcciseError($ligne_num, $csvRow));
            }
            if ($csvRow[self::CSV_ACHETEUR_NUMACCISE] && !preg_match('/^FR[a-zA-Z0-9]{11}$/', trim($csvRow[self::CSV_ACHETEUR_NUMACCISE]))) {
            	$this->csvDoc->addErreur($this->createWrongFormatNumAcciseClientError($ligne_num, $csvRow));
            }
            $etablissement = EtablissementClient::getInstance()->findByIdentifiant(trim($csvRow[self::CSV_IDENTIFIANT]));
            if(!$etablissement || $etablissement->identifiant != $this->identifiant || ($etablissement->no_accises && $csvRow[self::CSV_NUMACCISE] && trim($csvRow[self::CSV_NUMACCISE]) != $etablissement->no_accises)) {
                $this->csvDoc->addErreur($this->createDifferentEtbError($ligne_num, $csvRow));
            }
            $ligne_num++;
        }
    }

    public function createDae($csvRow, $produit) {
        $dae = DAEClient::getInstance()->createSimpleDAE($this->identifiant, KeyInflector::slugify($csvRow[self::CSV_DATE_COMMERCIALISATION]));

        $dae->produit_key = $produit->getHash();
        $dae->produit_libelle = $produit->getLibelleFormat();
        
        $dae->no_accises_acheteur = trim($csvRow[self::CSV_ACHETEUR_NUMACCISE]);
        $dae->nom_acheteur = trim($csvRow[self::CSV_ACHETEUR_NOM]);
        
        $dae->type_acheteur_key = trim($csvRow[self::CSV_ACHETEUR_TYPE]);
        $types = $this->dae->getTypes();
        $dae->type_acheteur_libelle = $types[$dae->type_acheteur_key];
        
        $dae->destination_key = trim($csvRow[self::CSV_PAYS_NOM]);
        $dae->destination_libelle = $this->countryList[$dae->destination_key];
        
        $dae->millesime = trim($csvRow[self::CSV_PRODUIT_MILLESIME]);
        
        $dae->contenance_key = trim($csvRow[self::CSV_LIBELLE_CONDITIONNEMENT]);
        $contenances = $this->dae->getContenances();
        $dae->contenance_libelle = $contenances[$dae->contenance_key];
        
        $dae->label_key = trim($csvRow[self::CSV_PRODUIT_LABEL]);
        $labels = $this->dae->getLabels();
        $dae->label_libelle = $labels[$dae->label_key];
        
        $dae->mention_key = trim($csvRow[self::CSV_PRODUIT_DOMAINE]);
        $mentions = $this->dae->getMentions();
        $dae->mention_libelle = $mentions[$dae->mention_key];
        
        $dae->quantite = $this->convertNumber($csvRow[self::CSV_QUANTITE_CONDITIONNEMENT]);
        $dae->prix_unitaire = $this->convertNumber($csvRow[self::CSV_PRIX_UNITAIRE]);
        
        $dae->calculateDatas();
        
        return $dae;
    }

    private function convertNumber($number){
          $numberPointed = trim(str_replace(",",".",$number));
          return round(floatval($numberPointed), 4);
    }

	private function createError($num_ligne, $erreur_csv, $raison, $level = CSVDAEClient::LEVEL_WARNING) {
    	$error = new stdClass();
        $error->num_ligne = $num_ligne;
        $error->erreur_csv = $erreur_csv;
        $error->raison = $raison;
        $error->level = $level;
        return $error;
 	}
 	
 	private function getHashProduit($datas)
 	{
 		if (!$this->getKey($datas[self::CSV_PRODUIT_CERTIFICATION]) && !$this->getKey($datas[self::CSV_PRODUIT_APPELLATION])) {
 			return null;
 		}
 		$hash = 'declaration/certifications/'.$this->getKey($datas[self::CSV_PRODUIT_CERTIFICATION]).
 		'/genres/'.$this->getKey($datas[self::CSV_PRODUIT_GENRE], true).
 		'/appellations/'.$this->getKey($datas[self::CSV_PRODUIT_APPELLATION], true).
 		'/mentions/'.$this->getKey($datas[self::CSV_PRODUIT_MENTION], true).
 		'/lieux/'.$this->getKey($datas[self::CSV_PRODUIT_LIEU], true).
 		'/couleurs/'.strtolower($this->couleurKeyToCode($datas[self::CSV_PRODUIT_COULEUR])).
 		'/cepages/'.$this->getKey($datas[self::CSV_PRODUIT_CEPAGE], true);
 		return $hash;
 	}
 	 
 	private function getKey($key, $withDefault = false)
 	{
 		if ($key == " " || !$key) {
 			$key = null;
 		}
 		$key = strtoupper($key);
 		if ($withDefault) {
 			return ($key)? $key : ConfigurationProduit::DEFAULT_KEY;
 		} else {
 			return $key;
 		}
 	}
    
    private function couleurKeyToCode($key)
    {
    	$key = strtolower($key);
    	if (preg_match('/^ros.+$/', $key)) {
    		$key = 'rose';
    	}
    	$correspondances = array(1 => "rouge",
    			2 => "rose",
    			3 => "blanc");
    	if (!in_array($key, array_keys($correspondances))) {
    		return $this->getKey($key, true);
    	}
    	return $correspondances[$key];
    }	

	private function buildLibellesArrayWithRow($csvRow, $with_slugify = false) {
	    $certification = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_PRODUIT_CERTIFICATION]) : $csvRow[self::CSV_PRODUIT_CERTIFICATION];
	    $genre = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_PRODUIT_GENRE]) : $csvRow[self::CSV_PRODUIT_GENRE];
	    $appellation = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_PRODUIT_APPELLATION]) : $csvRow[self::CSV_PRODUIT_APPELLATION];
	    $mention = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_PRODUIT_MENTION]) : $csvRow[self::CSV_PRODUIT_MENTION];
	    $lieu = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_PRODUIT_LIEU]) : $csvRow[self::CSV_PRODUIT_LIEU];
	    $couleur = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_PRODUIT_COULEUR]) : $csvRow[self::CSV_PRODUIT_COULEUR];
	    $cepage = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_PRODUIT_CEPAGE]) : $csvRow[self::CSV_PRODUIT_CEPAGE];
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
    
    private function createWrongCsvFileError($num_ligne, $csvRow) {
    	return $this->createError($num_ligne,"", "Ce fichier n'est pas un fichier CSV valide.", CSVDAEClient::LEVEL_ERROR);
    }
    
    private function createWrongFormatFieldCountError($num_ligne, $csvRow) {
    	return $this->createError($num_ligne, KeyInflector::slugify($csvRow[self::CSV_DATE_COMMERCIALISATION]), "La ligne ne possède pas le bon nombre de champs.");
    }
    
    private function createWrongFormatDateCommercialisationError($num_ligne, $csvRow) {
    	return $this->createError($num_ligne, KeyInflector::slugify($csvRow[self::CSV_DATE_COMMERCIALISATION]), "Le format de la date de commercialisation n'est pas bon (format attendu AAAA-MM-JJ).");
    }
    
    private function createDifferentEtbError($num_ligne, $csvRow) {
    	return $this->createError($num_ligne, KeyInflector::slugify($csvRow[self::CSV_IDENTIFIANT]), "L'établissement n'existe pas ou n'est pas le bon.");
    }
    
    private function productNotFoundError($num_ligne, $csvRow) {
    	$libellesArray = $this->buildLibellesArrayWithRow($csvRow);
    	return $this->createError($num_ligne, implode(' ', $libellesArray), "Le produit n'a pas été trouvé");
    }
    
    private function labelNotFoundError($num_ligne, $csvRow) {
    	return $this->createError($num_ligne, KeyInflector::slugify($csvRow[self::CSV_PRODUIT_LABEL]), "Le label n'a pas été trouvé");
    }
    
    private function mentionNotFoundError($num_ligne, $csvRow) {
    	return $this->createError($num_ligne, KeyInflector::slugify($csvRow[self::CSV_PRODUIT_DOMAINE]), "La mention n'a pas été trouvé");
    }
    
    private function typeNotFoundError($num_ligne, $csvRow) {
    	return $this->createError($num_ligne, KeyInflector::slugify($csvRow[self::CSV_ACHETEUR_TYPE]), "Le type de client n'a pas été trouvé");
    }
    
    private function contenanceNotFoundError($num_ligne, $csvRow) {
    	return $this->createError($num_ligne, KeyInflector::slugify($csvRow[self::CSV_LIBELLE_CONDITIONNEMENT]), "Le conditionnement n'a pas été trouvé");
    }
    
    private function destinationNotFoundError($num_ligne, $csvRow) {
    	return $this->createError($num_ligne, KeyInflector::slugify($csvRow[self::CSV_PAYS_NOM]), "Le pays n'a pas été trouvé");
    }

    private function createWrongFormatNumAcciseError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, KeyInflector::slugify($csvRow[self::CSV_NUMACCISE]), "Format numéro d'accises non valide");
    }

    private function createWrongFormatNumAcciseClientError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, KeyInflector::slugify($csvRow[self::CSV_ACHETEUR_NUMACCISE]), "Format numéro d'accises non valide");
    }

    private function createWrongFormatMillesimeError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, KeyInflector::slugify($csvRow[self::CSV_PRODUIT_MILLESIME]), "Format millésime non valide");
    }
    
}
