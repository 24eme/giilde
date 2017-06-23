<?php

class LabelCsvFile extends CsvFile 
{
  const CSV_LABEL_INTERPRO = 0;
  const CSV_LABEL_CATEGORIE_CODE = 1;       //CATEGORIE == CERTIFICATION
  const CSV_LABEL_GENRE_CODE = 2;
  const CSV_LABEL_DENOMINATION_CODE = 3;    //DENOMINATION == APPELLATION
  const CSV_LABEL_LIBELLE = 4;
  const CSV_LABEL_CODE = 5;
  
  protected $config;
  protected $errors;
  
  public function __construct($config, $file) {
    parent::__construct($file);
    $this->config = $config;
  }

  private function getLabelNoeud($line) 
  {
  	if ($line[self::CSV_LABEL_GENRE_CODE] && $line[self::CSV_LABEL_DENOMINATION_CODE] && $line[self::CSV_LABEL_CATEGORIE_CODE]) {
  		$hash = 'certifications/'.$this->getKey($line[self::CSV_LABEL_CATEGORIE_CODE]).
  				'/genres/'.$this->getKey($line[self::CSV_LABEL_GENRE_CODE]).
                '/appellations/'.$this->getKey($line[self::CSV_LABEL_DENOMINATION_CODE]);
  	} elseif ($line[self::CSV_LABEL_CATEGORIE_CODE] && $line[self::CSV_LABEL_GENRE_CODE]) {
  		$hash = 'certifications/'.$this->getKey($line[self::CSV_LABEL_CATEGORIE_CODE]).
  				'/genres/'.$this->getKey($line[self::CSV_LABEL_GENRE_CODE]);		
  	} elseif ($line[self::CSV_LABEL_CATEGORIE_CODE]) {
  		$hash = 'certifications/'.$this->getKey($line[self::CSV_LABEL_CATEGORIE_CODE]);  		
  	} else {
  		throw new sfException('Categorie code needed');
  	}
    return $this->config->declaration->getOrAdd($hash);
  }
  
  private function getKey($key, $withDefault = false) 
  {
	if ($withDefault) {
  		return ($key)? $key : Configuration::DEFAULT_KEY;
  	} elseif (!$key) {
  		throw new sfException('La clé "'.$key.'" n\'est pas valide');
  	} else {
  		return $key;
  	}
  }

  public function importLabels() 
  {    
    $this->errors = array();
    $csv = $this->getCsv();
    try {
		foreach ($csv as $line) {
			$noeud = $this->getLabelNoeud($line);
			$noeud->setLabelCsv($line);
			$this->config->setLabelCsv($line);
      	}
    } catch(Execption $e) {
    	$this->errors[] = $e->getMessage();
    }
    return $this->config;
  }

  public function getErrors() 
  {
    return $this->errors;
  }
}