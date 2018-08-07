<?php

class PieceAllView extends acCouchdbView
{

	const KEYS_VISIBILITE = 0;
    const KEYS_IDENTIFIANT = 1;
    const KEYS_DATE_DEPOT = 2;
    const KEYS_LIBELLE = 3;
    const KEYS_MIME = 4;
    const KEYS_SOURCE = 5;

    const VALUES_KEY = 0;
    const VALUES_FICHIERS = 1;

    public static function getInstance() {
        return acCouchdbManager::getView('piece', 'all');
    }
    
 	public function getAll() {
        return $this->client->getView($this->design, $this->view)->rows;
 	}

    public function getPiecesByEtablissement($etablissement, $allVisibilite = false, $startdate = null, $enddate = null) {
    	$start = array($etablissement);
    	$end = array($etablissement);
		if($startdate) {
			$start[] = $startdate;
		}
		if($enddate) {
			$end[] = $enddate;
		}

    	$end[] = array();
    	$visibles = array_reverse($this->client
    			->startkey(array_merge(array(1), $start))
    			->endkey(array_merge(array(1), $end))
    			->getView($this->design, $this->view)->rows);
    	$nonVisibles = array();
    	if ($allVisibilite) {
    		$nonVisibles = array_reverse($this->client
    				->startkey(array_merge(array(0), $start))
    				->endkey(array_merge(array(0), $end))
    				->getView($this->design, $this->view)->rows);
    	}
        return array_merge($nonVisibles, $visibles);
    }
    
    public function getStartISODateForView() {
    	return '1900-01-01';
    }
    
    public function getEndISODateForView() {
    	return '9999-99-99';
    }
}  
