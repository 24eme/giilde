<?php

class DAEClient extends acCouchdbClient {

    const ACHETEUR_TYPE_IMPORTATEUR = "IMPORTATEUR";

    public static function getInstance() {
        return acCouchdbManager::getClient("DAE");
    }

    public function createDAE($identifiant, $date,$produit,$type_acheteur,$destination,$millesime,$volume,$contenant,$prix_ht,$label) {
        $dae = new DAE();
        $dae->setIdentifiant($identifiant);
        $dae->setDate($date);
        $dae->setProduitHash($produit);
        $dae->setTypeAcheteur($type_acheteur);
        $dae->setDestination($destination);
        $dae->setMillesime($millesime);
        $dae->setVolume($volume);
        $dae->setContenance($contenant);
        $dae->setPrixHl($prix_ht);
        $dae->setLabel($label);
        $dae->storeDeclarant();
        return $dae;
    }

    public function createSimpleDAE($identifiant, $date = null) {
    	if (!$date) {
    		$date = date('Y-m-d');
    	}
        $dae = new DAE();
        $dae->setIdentifiant($identifiant);
        $dae->setDate($date);
        $dae->storeDeclarant();
        return $dae;
    }

    public function buildId($identifiant, $date, $num) {
        return 'DAE-' . $identifiant . '-' . str_replace('-','',$date)."-".$num;
    }


    public function getNextIdentifiantForEtablissementAndDay($identifiant, $date) {
        $daes = self::getForEtablissementAtDay($identifiant, $date, acCouchdbClient::HYDRATE_ON_DEMAND)->getIds();
        $last_num = 0;
        foreach ($daes as $id) {
			$exploded = explode('-', $id);
            $num = ($exploded[count($exploded) - 1] * 1);
            if ($num > $last_num) {
                $last_num = $num;
            }
        }
        return sprintf("%03d", $last_num + 1);
    }

    public function findByIdentifiant($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
        return $this->startkey('DAE-' . $identifiant . '-00000000-000')->endkey('DAE-' . $identifiant . '-99999999-999')->execute($hydrate);
    }

    public function findByIdentifiantPeriode($identifiant, $periode, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
    	if (!preg_match('/^[0-9]{6}$/', $periode)) {
    		throw new sfException('periode not valid');
    	}
        return $this->startkey('DAE-' . $identifiant . '-'. $periode .'01-000')->endkey('DAE-' . $identifiant . '-'. $periode . '99-999')->execute($hydrate);
    }

    public function getForEtablissementAtDay($identifiant,$date, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $date = str_ireplace('-','',$date);
        return $this->startkey('DAE-' . $identifiant . '-'.$date.'-000')->endkey('DAE-' . $identifiant . '-'.$date.'-999')->execute($hydrate);
    }

    public function findByIdentifiantAndDate($identifiant, $date) {

        return $this->getForEtablissementAtDay($identifiant, $date);
    }
    
    public function listCampagneByEtablissementId($identifiant) {
    	$rows = $this->findByIdentifiant($identifiant)->getDatas();
    	$list = array();
    	foreach ($rows as $r) {
    		if ($d = $r->getDateObject())
    			$list[$d->format('Y-m')] = $r->getLiteralPeriode();
    	}
    	krsort($list);
    	return $list;
    }
}
