<?php

/**
 * statistique actions.
 *
 * @package    declarvin
 * @subpackage statistique
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class statistiqueActions extends sfActions {

    
	public function executeIndex(sfWebRequest $request) {
		
	}
	
	protected function getFields($key, $value)
	{
		if (!isset($value['properties'])) {
			return array($key => $key);
		}
		$result = array();
		foreach ($value['properties'] as $subkey => $subvalue) {
			$result = array_merge($result, $this->getFields($key.'.'.$subkey, $subvalue));
		}
		return $result;
	}
	
	public function executeStatsStatistiques(sfWebRequest $request) 
	{
		$this->statistiquesConfig = sfConfig::get('app_statistiques_stats');
		if (!$this->statistiquesConfig) {
			throw new sfException('No configuration set for statistiques type stats');
		}
		
		$this->form = new StatistiqueStatsFilterForm($this->statistiquesConfig);
		
		if (!$request->isMethod(sfWebRequest::POST)) {
			return sfView::SUCCESS;
		}
		$this->form->bind($request->getParameter($this->form->getName()));
		if ($this->form->isValid()) {
			$values = $this->form->getValues();
			if (!isset($this->statistiquesConfig['statistiques'][$values['statistiques']]['aggregation'])) {
				throw new sfException('No aggregation set for statistiques '.$values['statistiques']);
			}
			$result = $this->getAggsResult($this->statistiquesConfig['statistiques'][$values['statistiques']]['index'], $this->form->processFilters(), array($values['statistiques'] => $this->statistiquesConfig['statistiques'][$values['statistiques']]['aggregation']));
			$csvResult = $this->getAggsResultCsv($values['statistiques'], $result);
			if ($this->form->canPeriodeCompare()) {
				$resultLastPeriode = $this->getAggsResult($this->statistiquesConfig['statistiques'][$values['statistiques']]['index'], $this->form->processFilters($this->form->getValuesLastPeriode()), array($values['statistiques'] => $this->statistiquesConfig['statistiques'][$values['statistiques']]['aggregation']));
				$csvResultLastPeriode = $this->getAggsResultCsv($values['statistiques'], $resultLastPeriode);
				$nbKeys = $this->statistiquesConfig['statistiques'][$values['statistiques']]['hashkeysize'];
				$csvResult = $this->getAggsResultCsv($values['statistiques'], $this->getCsvToArray($csvResult, $nbKeys), $this->getCsvToArray($csvResultLastPeriode, $nbKeys));
			}
			if ($this->form->pdfFormat()) {
				return $this->renderPdf($csvResult, $values['statistiques'], array('categories' => $this->form->getCategories(), 'appellations' => $this->form->getAppellations(), 'compare' => $this->form->canPeriodeCompare(), 'periode' => $this->form->getPeriode()));
			} else {
				return $this->renderCsv($csvResult, 'statistiques_'.$values['statistiques']);
			}
		}
	}
	
	protected function renderPdf($csv, $type, $options = array()) 
	{
		$latex = new StatistiqueLatex($csv, $type, $options);
		$latex->echoWithHTTPHeader();
		exit;
	}
	
	protected function getAggsResult($index, $filters, $agg)
	{		
		$index = acElasticaManager::getType($index);
		$params = ($filters)? array('aggs' => $agg, 'query' => $filters) : array('aggs' => $agg);
		$elasticaQuery = new acElasticaQuery();
		$elasticaQuery->setSize(0);
		$elasticaQuery->setParams($params);
		//print_r(json_encode($elasticaQuery->toArray()));exit;
		return $index->search($elasticaQuery)->getFacets();
	}
	
	protected function getAggsResultCsv($type, $current, $lastPeriode = null)
	{
		return ($lastPeriode !== null)? $this->getPartial('statistique/'.$type, array('lastPeriode' => $lastPeriode, 'result' => $current)) : $this->getPartial('statistique/'.$type, array('lastPeriode' => null, 'result' => $current[$type]));
	}

	protected function getCsvToArray($csv, $nbKeys)
	{
		$lines = explode(PHP_EOL, $csv);
		$array = array();
		foreach ($lines as $line) {
			$subArray = str_getcsv($line, ';');
			$key = '';
			for ($i=0; $i<$nbKeys; $i++) {
				if (isset($subArray[$i])) {
					$key .= ($key)? '/'.$subArray[$i] : $subArray[$i];
					unset($subArray[$i]);
				} else {
					$key = null;
					break;
				}
			}
			if ($key) {
				$array[$key] = array_values($subArray);
			}
		}
		array_shift($array);
		return $array;
	}
	
	protected function getLibelles($noeud) {
        $libelles = array();
        $items = ConfigurationClient::getCurrent()->declaration->getKeys($noeud);

        foreach($items as $key => $item) {
            $libelles[$key] = $item->getLibelle();
        }

        return $libelles;
    }	
    protected function formatNumber($number) {
    	return ($number && $number != 0)? number_format($number, 2, ',', '') : null;
    }
	
    public function executeDrmStatistiques(sfWebRequest $request) {
        $this->page = $request->getParameter('p', 1);
        $this->statistiquesConfig = sfConfig::get('app_statistiques_drm');
        if (!$this->statistiquesConfig) {
            throw new sfException('No configuration set for elasticsearch type drm');
        }
        
        $index = acElasticaManager::getType($this->statistiquesConfig['elasticsearch_type']);
        
        $this->fields = array();
        $mapping = $index->getMapping()[acElasticaManager::getClient()->dbname]['mappings'][$this->statistiquesConfig['elasticsearch_type']]['properties']['doc']['properties'];

        foreach ($mapping as $propertie => $value) {
        	$key = 'doc.'.$propertie;
        	$this->fields = array_merge($this->fields, $this->getFields($key, $value));
        }

        $this->form = new StatistiqueDRMFilterForm($this->fields);
        $this->query = $this->form->getDefaultQuery();
        $this->collapseIn = false;
        $this->filters = array();
        
        if ($request->hasParameter($this->form->getName())) {
        	$this->form->bind($request->getParameter($this->form->getName()));
        	if ($this->form->isValid()) {
        		if ($q = $this->form->getQuery()) {
        			$this->query = $q;
        			$this->collapseIn = $this->form->getCollapseIn();
        			$this->filters = $this->form->getParameters();
        		}
        	}
        }
        
        $elasticaQuery = new acElasticaQuery();
        $elasticaQuery->setQuery($this->query);
        $elasticaQuery->setLimit($this->statistiquesConfig['nb_resultat']);
        $elasticaQuery->setFrom(($this->page - 1) * $this->statistiquesConfig['nb_resultat']);
        $result = $index->search($elasticaQuery);
        $this->hits = $result->getResults();
        $this->nbHits = $result->getTotalHits();
        $this->nbPage = ceil($this->nbHits / $this->statistiquesConfig['nb_resultat']);
    }

    public function executeVracStatistiques(sfWebRequest $request) {
        $this->page = $request->getParameter('p', 1);
        $this->statistiquesConfig = sfConfig::get('app_statistiques_vrac');
        if (!$this->statistiquesConfig) {
            throw new sfException('No configuration set for elasticsearch type vrac');
        }

        $index = $index = acElasticaManager::getType($this->statistiquesConfig['elasticsearch_type']);
        
        $this->fields = array();
        $mapping = $index->getMapping()[acElasticaManager::getClient()->dbname]['mappings'][$this->statistiquesConfig['elasticsearch_type']]['properties']['doc']['properties'];
        foreach ($mapping as $propertie => $value) {
        	$key = 'doc.'.$propertie;
        	$this->fields = array_merge($this->fields, $this->getFields($key, $value));
        }
        
        $this->form = new StatistiqueVracFilterForm($this->fields);
        $this->query = $this->form->getDefaultQuery();
        $this->collapseIn = false;
        $this->filters = array();
        
        if ($request->hasParameter($this->form->getName())) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                if ($q = $this->form->getQuery()) {
                    $this->query = $q;
        			$this->collapseIn = $this->form->getCollapseIn();
        			$this->filters = $this->form->getParameters();
                }
            }
        }
        
        $elasticaQuery = new acElasticaQuery();
        $elasticaQuery->setQuery($this->query);


        $elasticaQuery->setLimit($this->statistiquesConfig['nb_resultat']);
        $elasticaQuery->setFrom(($this->page - 1) * $this->statistiquesConfig['nb_resultat']);
        $result = $index->search($elasticaQuery);
        $this->hits = $result->getResults();
        $this->nbHits = $result->getTotalHits();
        $this->nbPage = ceil($this->nbHits / $this->statistiquesConfig['nb_resultat']);
    }

    public function executeDrmCsvStatistiques(sfWebRequest $request) {
    	ini_set('memory_limit', '2048M');
    	set_time_limit(0);
        $this->statistiquesConfig = sfConfig::get('app_statistiques_drm');
        if (!$this->statistiquesConfig) {
            throw new sfException('No configuration set for elasticsearch type drm');
        }

        $index = $index = acElasticaManager::getType($this->statistiquesConfig['elasticsearch_type']);
        
        $this->fields = array();
        $mapping = $index->getMapping()[acElasticaManager::getClient()->dbname]['mappings'][$this->statistiquesConfig['elasticsearch_type']]['properties']['doc']['properties'];

        foreach ($mapping as $propertie => $value) {
        	$key = 'doc.'.$propertie;
        	$this->fields = array_merge($this->fields, $this->getFields($key, $value));
        }
        
        $this->form = new StatistiqueDRMFilterForm($this->fields);
        $this->query = $this->form->getDefaultQuery();
        
        if ($request->hasParameter($this->form->getName())) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                if ($q = $this->form->getQuery()) {
                    $this->query = $q;
                }
            }
        }
        
        $elasticaQuery = new acElasticaQuery();
        $elasticaQuery->setQuery($this->query);
        $elasticaQuery->setLimit(5000);
        $result = $index->search($elasticaQuery);
        $hits = $result->getResults();
        /**
         * 
         */
        
        $csv_file = '';
        $csv_file .= "#Periode;Date saisie;Declarant id;Declarant nom;Total debut mois;Total entrees;Total recolte;Total sorties;Total facturable;Total;";
        $csv_file .= "\n";
        foreach ($hits as $hit):
	        $item = $hit->getData();
	        $csv_file .= $item['doc']['periode'];
	        $csv_file .= ";";
	        $csv_file .= $item['doc']['valide']['date_saisie'];
	        $csv_file .= ";";
	        $csv_file .= $item['doc']['identifiant'];
	        $csv_file .= ";";
	        $csv_file .= $item['doc']['declarant']['nom'];
	        $csv_file .= ";";
	        $csv_file .= $item['doc']['declaration']['total_debut_mois'];
	        $csv_file .= ";";
	        $csv_file .= $item['doc']['declaration']['total_entrees'];
	        $csv_file .= ";";
	        $csv_file .= $item['doc']['declaration']['total_recolte'];
	        $csv_file .= ";";
	        $csv_file .= $item['doc']['declaration']['total_sorties'];
	        $csv_file .= ";";
	        $csv_file .= $item['doc']['declaration']['total_facturable'];
	        $csv_file .= ";";
	        $csv_file .= $item['doc']['declaration']['total'];
	        $csv_file .= ";";
	        $csv_file .= "\n";
        endforeach;
        return $this->renderCsv($csv_file, 'drm');
    }

    public function executeVracCsvStatistiques(sfWebRequest $request) {
    	ini_set('memory_limit', '2048M');
    	set_time_limit(0);
        $this->statistiquesConfig = sfConfig::get('app_statistiques_vrac');
        if (!$this->statistiquesConfig) {
            throw new sfException('No configuration set for elasticsearch type vrac');
        }

        $index = $index = acElasticaManager::getType($this->statistiquesConfig['elasticsearch_type']);
        
        $this->fields = array();
        $mapping = $index->getMapping()[acElasticaManager::getClient()->dbname]['mappings'][$this->statistiquesConfig['elasticsearch_type']]['properties']['doc']['properties'];
        foreach ($mapping as $propertie => $value) {
        	$key = 'doc.'.$propertie;
        	$this->fields = array_merge($this->fields, $this->getFields($key, $value));
        }
        
        $this->form = new StatistiqueVracFilterForm($this->fields);
        $this->query = $this->form->getDefaultQuery();
        
        if ($request->hasParameter($this->form->getName())) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                if ($q = $this->form->getQuery()) {
                    $this->query = $q;
                }
            }
        }
        $elasticaQuery = new acElasticaQuery();
        $elasticaQuery->setQuery($this->query);
        $elasticaQuery->setLimit(5000);
        $result = $index->search($elasticaQuery);
        $hits = $result->getResults();
        $csv_file = '';
        $csv_file .= "#Statut;Type transaction;Num. archive;Num. contrat;Teledeclare;Date signature;Date saisie;Vendeur id;Vendeur nom;Acheteur id;Acheteur nom;Representant id;Representant nom;Courtier id;Courtier nom;Produit;Millesime;Volume propose;Volume enleve;Prix initial unitaire;";
        $csv_file .= "\n";
        foreach ($hits as $hit):
        	$item = $hit->getData();
        	$csv_file .= $item['doc']['valide']['statut'];
        	$csv_file .= ";";
        	$csv_file .= $item['doc']['type_transaction'];
        	$csv_file .= ";";
        	$csv_file .= $item['doc']['numero_archive'];
        	$csv_file .= ";";
        	$csv_file .= $item['doc']['numero_contrat'];
        	$csv_file .= ";";
        	$csv_file .= $item['doc']['teledeclare'];
        	$csv_file .= ";";
        	$csv_file .= $item['doc']['date_signature'];
        	$csv_file .= ";";
        	$csv_file .= $item['doc']['valide']['date_saisie'];
        	$csv_file .= ";";
        	$csv_file .= $item['doc']['vendeur_identifiant'];
        	$csv_file .= ";";
        	$csv_file .= $item['doc']['vendeur']['nom'];
        	$csv_file .= ";";
        	$csv_file .= $item['doc']['acheteur_identifiant'];
        	$csv_file .= ";";
        	$csv_file .= $item['doc']['acheteur']['nom'];
        	$csv_file .= ";";
        	if ($item['doc']['representant_identifiant'] != $item['doc']['vendeur_identifiant']) {
	        	$csv_file .= $item['doc']['representant_identifiant'];
        		$csv_file .= ";";
	        	$csv_file .= $item['doc']['representant']['nom'];
        	} else {
	        	$csv_file .= null;
        		$csv_file .= ";";
	        	$csv_file .= null;
        		
        	}
        	$csv_file .= ";";
        	$csv_file .= $item['doc']['mandataire_identifiant'];
        	$csv_file .= ";";
        	$csv_file .= $item['doc']['mandataire']['nom'];
        	$csv_file .= ";";
        	$produit = ($item['doc']['type_transaction'] == VracClient::TYPE_TRANSACTION_VIN_VRAC || $item['doc']['type_transaction'] == VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE)? $item['doc']['produit_libelle'] : $item['doc']['cepage_libelle'];
        	$millesime = $item['doc']['millesime'] ? $item['doc']['millesime'] : 'nm';
        	$csv_file .= $millesime;
        	$csv_file .= ";";
        	$csv_file .= $item['doc']['volume_propose'];
        	$csv_file .= ";";
        	$csv_file .= $item['doc']['volume_enleve'];
        	$csv_file .= ";";
        	$csv_file .= $item['doc']['prix_initial_unitaire'];
        	$csv_file .= ";";
        	$csv_file .= "\n";
        endforeach;
        return $this->renderCsv($csv_file, 'vrac');
    }


    protected function renderCsv($csv_file, $type)
    {
    	$this->setLayout(false);
    	$dateTime = new DateTime();
    	$date = $dateTime->format('c');
    	$this->response->setContentType('text/csv');
    	$this->response->setHttpHeader('md5', md5($csv_file));
    	$this->response->setHttpHeader('Content-Disposition', "attachment; filename=stats_".$type."_".$date.".csv");
    	$this->response->setHttpHeader('LastDocDate', $date);
    	$this->response->setHttpHeader('Last-Modified', date('r', strtotime($date)));
    	return $this->renderText(utf8_decode($csv_file));
    }

}
