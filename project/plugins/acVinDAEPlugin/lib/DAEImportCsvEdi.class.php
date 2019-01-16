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
    protected $configuration = null;
    protected $labels = null;
    protected $mentions = null;
    protected $types = null;
    protected $contenances = null;
    protected $forceEtablissement = false;
    protected $dates = array();
    protected $cache = array();
    protected $etablissement = null;
    protected $client = null;

    public function __construct($file = null, $identifiant ,$periode) {
            $this->identifiant = $identifiant;
            $this->etablissement = EtablissementClient::getInstance()->find($identifiant);
            $this->file = $file;
            $this->csvDoc = CSVDAEClient::getInstance()->find(CSVDAEClient::getInstance()->buildId($identifiant ,$periode));
            if(is_null($this->csvDoc)) {
            	$this->csvDoc = CSVDAEClient::getInstance()->createOrFindDocFromDAES($file, $identifiant ,$periode);
            }
            $this->dae = new DAE();
            $this->configuration = $this->dae->getConfig();
            $this->labels = $this->dae->getLabels();
            $this->mentions = $this->dae->getMentions();
            $this->types = $this->dae->getTypes();
            $this->contenances = $this->dae->getContenances();
            $this->client = acCouchdbManager::getClient();
            parent::__construct($file, array());
    }
    
    public function setForceEtablissement($force = true) {
    	$this->forceEtablissement = $force;
    }

    public function getCsvDoc() {
	    return $this->csvDoc;
    }

    public function getDates() {
	    return $this->dates;
    }

    public function getCampagnes() {
    	$campagnes = array();
	    foreach ($this->dates as $d) {
	    	$cm = new CampagneManager('08-01');
	    	$c = $cm->getCampagneByDate($d);
	    	$campagnes[$c] = $c;
	    }
	    return $campagnes;
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
		return $this->getCsv($this->csvDoc->getFileContent());
	}

    public function checkCSV() {
    	$this->csvDoc->clearErreurs();
    	$this->checkCSVIntegrity();
    	if ($this->csvDoc->hasErreurs()) {
    		$this->csvDoc->setStatut(self::STATUT_ERREUR);
    		$this->csvDoc->save();
    		return;
    	}
    	$this->csvDoc->setStatut(self::STATUT_VALIDE);
    	$this->csvDoc->save();
        return true;
    }

    public function importCSV() {
        return $this->importDaesFromCSV();
    }
	
    private function identifyProduct($csvRow) {
    	$hash = $this->getHashProduit($csvRow);
    	$libelle = trim($csvRow[self::CSV_PRODUIT_LIBELLE_PERSONNALISE]);
		if ($hash && isset($this->cache['produit_'.$hash])) {
			return $this->cache['produit_'.$hash];
		}
		if ($libelle && isset($this->cache['produit_'.$libelle])) {
			return $this->cache['produit_'.$libelle];
		}
		$produit = $this->configuration->identifyProduct($hash, $libelle);
		if ($hash) {
			$this->cache['produit_'.$hash] = $produit;
		}
		if ($libelle) {
			$this->cache['produit_'.$libelle] = $produit;
		}
		return $produit;
    }
    
    private function identifyItemKey($csvRows, $type) {
    	$value = trim($csvRows[$type]);
    	if ($type == self::CSV_PRODUIT_LABEL) {
			if (isset($this->cache['label_'.$value])) {
				return $this->cache['label_'.$value];
			} else {
				$this->cache['label_'.$value] = $this->getItemKey($this->labels, $csvRows[self::CSV_PRODUIT_LABEL]);
				return $this->cache['label_'.$value];
			}
    	}

    	if ($type == self::CSV_PRODUIT_DOMAINE) {
    		if (isset($this->cache['domaine_'.$value])) {
    			return $this->cache['domaine_'.$value];
    		} else {
    			$this->cache['domaine_'.$value] = $this->getItemKey($this->mentions, $csvRows[self::CSV_PRODUIT_DOMAINE]);
    			return $this->cache['domaine_'.$value];
    		}
    	}

    	if ($type == self::CSV_ACHETEUR_TYPE) {
    		if (isset($this->cache['type_'.$value])) {
    			return $this->cache['type_'.$value];
    		} else {
    			$this->cache['type_'.$value] = $this->getItemKey($this->types, $csvRows[self::CSV_ACHETEUR_TYPE]);
    			return $this->cache['type_'.$value];
    		}
    	}

    	if ($type == self::CSV_LIBELLE_CONDITIONNEMENT) {
    		if (isset($this->cache['cond_'.$value])) {
    			return $this->cache['cond_'.$value];
    		} else {
    			$this->cache['cond_'.$value] = $this->getItemKey($this->contenances, $csvRows[self::CSV_LIBELLE_CONDITIONNEMENT]);
    			return $this->cache['cond_'.$value];
    		}
    	}

    	if ($type == self::CSV_PAYS_NOM) {
    		if (isset($this->cache['pays_'.$value])) {
    			return $this->cache['pays_'.$value];
    		} else {
    			$this->cache['pays_'.$value] = $this->getItemKey($this->countryList, $csvRows[self::CSV_PAYS_NOM]);
    			return $this->cache['pays_'.$value];
    		}
    	}
    	
    	return null;
    }
    
    private function importDaesFromCSV($just_check = false) {
        $num_ligne = 1;
        $daes = array();
        $hasErrors = false;
        $nbDaes = 0;
        foreach ($this->getDocRows() as $csvRow) {
        	$founded_produit = $this->identifyProduct($csvRow);
        	if (!$founded_produit) {
        		$this->csvDoc->addErreur($this->productNotFoundError($num_ligne, $csvRow));
        		return;
        	}
            $label = $this->identifyItemKey($csvRow, self::CSV_PRODUIT_LABEL);
            $mention = $this->identifyItemKey($csvRow, self::CSV_PRODUIT_DOMAINE);
            $type = $this->identifyItemKey($csvRow, self::CSV_ACHETEUR_TYPE);
            $contenance = $this->identifyItemKey($csvRow, self::CSV_LIBELLE_CONDITIONNEMENT);
            $destination = $this->identifyItemKey($csvRow, self::CSV_PAYS_NOM);
            
            $csvRow[self::CSV_PRODUIT_LABEL] = $label;
			$csvRow[self::CSV_PRODUIT_DOMAINE] = $mention;

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
            if (!$hasErrors) {
            	$nbDaes++;
            }
            $num_ligne++;
        }
        if (!$just_check && !$hasErrors) {
        	$numeros = array();
        	$daeClient = DAEClient::getInstance();
        	foreach ($daes as $dae) {
        		if (!isset($numeros[$dae->identifiant.'_'.$dae->date])) {
        			$numeros[$dae->identifiant.'_'.$dae->date] = $daeClient->getNextIdentifiantForEtablissementAndDay($dae->identifiant, $dae->date);
        		}
        		$dae->_id = 'DAE-' . $dae->identifiant . '-' . str_replace('-','',$dae->date)."-".$numeros[$dae->identifiant.'_'.$dae->date];
        		$this->client->storeDoc($dae);
        		$numeros[$dae->identifiant.'_'.$dae->date] = sprintf("%05d", $numeros[$dae->identifiant.'_'.$dae->date] + 1);
        	}
        	return count($daes);
        }
        return (!$just_check)? 0 : $nbDaes;
    }
    
    private function getItemKey($items, $value) {
    	$value = trim($value);
    	$length = strlen($value);
    	foreach ($items as $k => $v) {
    		if ($value == $k || $value == $v) {
    			return $k;
    		}
    		if ($value && preg_match('/'.$value.'/i', $k)) {
    			return $k;
    		}
    		if ($value && preg_match('/'.KeyInflector::slugify($value).'/i', KeyInflector::slugify($k))) {
    			return $k;
    		}
    		if ($length > 3) {
	    		if ($value && preg_match('/'.$value.'/i', $v)) {
	    			return $k;
	    		}
	    		if ($value && preg_match('/'.KeyInflector::slugify($value).'/i', KeyInflector::slugify($v))) {
	    			return $k;
	    		}
    		}
    	}
    	return null;
    }

    private function checkCSVIntegrity() {
        $ligne_num = 1;
        foreach ($this->getDocRows() as $csvRow) {
            if(count($csvRow) != 31){
              $this->csvDoc->addErreur($this->createWrongFormatFieldCountError($ligne_num, $csvRow));
              $ligne_num++;
              continue;
            }
            $date = trim($csvRow[self::CSV_DATE_COMMERCIALISATION]);
            $millesime = trim($csvRow[self::CSV_PRODUIT_MILLESIME]);
            $accises = trim($csvRow[self::CSV_NUMACCISE]);
            $acheteur = trim($csvRow[self::CSV_ACHETEUR_NUMACCISE]);
            $etablissement = trim($csvRow[self::CSV_IDENTIFIANT]);
            
            if (!isset($this->cache['date_'.$date])) {
	            if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date)) {
	                $this->csvDoc->addErreur($this->createWrongFormatDateCommercialisationError($ligne_num, $csvRow));
	            } else {
	            	$this->cache['date_'.$date] = $date;
	            }
            }
            if (!isset($this->cache['millesime_'.$millesime])) {
            	if ($millesime && !preg_match('/^[0-9]{4}$/', $millesime)) {
                	$this->csvDoc->addErreur($this->createWrongFormatMillesimeError($ligne_num, $csvRow));
            	}else {
	            	$this->cache['millesime_'.$millesime] = $millesime;
	            }
            }
            if (!isset($this->cache['accises_'.$accises])) {
	            if ($accises && !preg_match('/^FR[a-zA-Z0-9]{11}$/', $accises)) {
	            	$this->csvDoc->addErreur($this->createWrongFormatNumAcciseError($ligne_num, $csvRow));
	            } else {
	            	$this->cache['accises_'.$accises] = $accises;
	            }
            }
            if (!isset($this->cache['acheteur_'.$acheteur])) {
	            if ($acheteur && !preg_match('/^FR[a-zA-Z0-9]{11}$/', $acheteur)) {
	            	$this->csvDoc->addErreur($this->createWrongFormatNumAcciseClientError($ligne_num, $csvRow));
	            } else {
	            	$this->cache['acheteur_'.$acheteur] = $acheteur;
	            }
            }
            
            if (!$this->forceEtablissement && !isset($this->cache['etablissement_'.$etablissement])) {
	            $e = EtablissementClient::getInstance()->findByIdentifiant($etablissement, acCouchdbClient::HYDRATE_JSON);
	            if(!$e || $e->identifiant != $this->identifiant) {
	                $this->csvDoc->addErreur($this->createDifferentEtbError($ligne_num, $csvRow));
	            }
            }
            
            $ligne_num++;
        }
    }

    public function createDae($csvRow, $produit) {
		$date = trim($csvRow[self::CSV_DATE_COMMERCIALISATION]);
		
        $dae = new stdClass();
        $dae->identifiant = $this->identifiant;
        $dae->date = $date;
        $dae->date_saisie = date('Y-m-d');
        $dae->type = 'DAE';
        
        if ($this->etablissement) {
	        $dae->declarant->nom = null;
	        if ($this->etablissement->exist("intitule") && $this->etablissement->get("intitule")) {
	        	$dae->declarant->nom = $this->etablissement->intitule . " ";
	        }
	        $dae->declarant->nom .= $this->etablissement->nom;
	        $dae->declarant->raison_sociale = $this->etablissement->getRaisonSociale();
	        $dae->declarant->cvi = $this->etablissement->cvi;
	        $dae->declarant->no_accises = $this->etablissement->getNoAccises();
	        $dae->declarant->adresse = $this->etablissement->siege->adresse;
	        if ($this->etablissement->siege->exist("adresse_complementaire")) {
	        	$dae->declarant->adresse .= ' ; '.$this->etablissement->siege->adresse_complementaire;
	        }
	        $dae->declarant->commune = $this->etablissement->siege->commune;
	        $dae->declarant->code_postal = $this->etablissement->siege->code_postal;
	        $dae->declarant->region = $this->etablissement->getRegion();
	        $dae->declarant->famille = $this->etablissement->famille;
	        $dae->declarant->sous_famille = $this->etablissement->sous_famille;
        }
        
        $this->dates[$date] = $date;
        
		$dae->produit_key = $produit->getHash();
        $dae->produit_libelle = $produit->getLibelleFormat();
        
        $dae->no_accises_acheteur = trim($csvRow[self::CSV_ACHETEUR_NUMACCISE]);
        $dae->nom_acheteur = trim($csvRow[self::CSV_ACHETEUR_NOM]);
        
        $dae->type_acheteur_key = $csvRow[self::CSV_ACHETEUR_TYPE];
        $dae->type_acheteur_libelle = $this->types[$dae->type_acheteur_key];
        
        $dae->destination_key = $csvRow[self::CSV_PAYS_NOM];
        $dae->destination_libelle = $this->countryList[$dae->destination_key];
        
        $dae->millesime = trim($csvRow[self::CSV_PRODUIT_MILLESIME]);
        
        $dae->contenance_key = $csvRow[self::CSV_LIBELLE_CONDITIONNEMENT];
        $dae->contenance_libelle = $this->contenances[$dae->contenance_key];
        
        $dae->label_key = trim($csvRow[self::CSV_PRODUIT_LABEL]);
        $dae->label_libelle = $this->labels[$dae->label_key];
        
        $dae->mention_key = trim($csvRow[self::CSV_PRODUIT_DOMAINE]);
        $dae->mention_libelle = $this->mentions[$dae->mention_key];
        
        $primeur = trim($csvRow[self::CSV_PRODUIT_PRIMEUR]);
        $dae->primeur = (!$primeur)? 0 : 1;
        
        $dae->quantite = $this->convertNumber($csvRow[self::CSV_QUANTITE_CONDITIONNEMENT]);
        $dae->prix_unitaire = $this->convertNumber($csvRow[self::CSV_PRIX_UNITAIRE]);
        
		$isHl = false;
        if (preg_match('/CL_/', $dae->contenance_key)) {
        	$dae->conditionnement_key = 'BOUTEILLE';
        	$dae->conditionnement_libelle = 'Bouteille';
        } elseif (preg_match('/BIB_/', $dae->contenance_key)) {
        	$dae->conditionnement_key = 'BIB';
        	$dae->conditionnement_libelle = 'Bib';
        } else {
        	$isHl = true;
        	$dae->conditionnement_key = 'HL';
        	$dae->conditionnement_libelle = 'Hectolitre';
        }

        if (!$dae->contenance_key || $isHl) {
        	$dae->contenance_hl = 1;
        } else {
        	$dae->contenance_hl = (str_replace('_', '.', str_replace(array('CL_','BIB_'), '', $dae->contenance_key)) * 1) / 10000;
        }
        
        $dae->volume_hl = round($dae->contenance_hl * $dae->quantite, 2);
        $dae->prix_hl = round($dae->prix_unitaire / $dae->contenance_hl, 2);
        
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
    	if (
    			!$this->getKey($datas[self::CSV_PRODUIT_CERTIFICATION]) &&
    			!$this->getKey($datas[self::CSV_PRODUIT_GENRE]) &&
    			!$this->getKey($datas[self::CSV_PRODUIT_APPELLATION]) &&
    			!$this->getKey($datas[self::CSV_PRODUIT_MENTION]) &&
    			!$this->getKey($datas[self::CSV_PRODUIT_LIEU]) &&
    			!$this->couleurKeyToCode($datas[self::CSV_PRODUIT_COULEUR], false) && 
    			!$this->getKey($datas[self::CSV_PRODUIT_CEPAGE])
    		) {
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
 		$$key = trim($key);
 		if ($key == " " || !$key) {
 			$key = null;
 		}
 		if ($withDefault) {
 			return ($key)? $key : ConfigurationProduit::DEFAULT_KEY;
 		} else {
 			return $key;
 		}
 	}
    
    private function couleurKeyToCode($key, $withDefault = true)
    {
    	$key = strtolower($key);
    	if (preg_match('/^ros.+$/', $key)) {
    		$key = 'rose';
    	}
    	if (!$withDefault && ($key == " " || !$key)) {
    		return null;
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
