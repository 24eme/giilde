<?php

class DAEClient extends acCouchdbClient {

    const ACHETEUR_TYPE_IMPORTATEUR = "IMPORTATEUR";

    public static $types = array('IMPORTATEUR' => 'Importateur', 'NEGOCIANT_REGION' => 'Négociant/Union Vallée du Rhône', 'NEGOCIANT_HORS_REGION' => 'Négociant hors région', 'GD' => 'Grande Distribution (Leclerc, Carrefour...)', 'DISCOUNT' => 'Hard Discount  (LIDL, ALDI, Leader Price...)', 'GROSSISTE' => 'Grossiste-CHR', 'CAVISTE' => 'Caviste', 'VSITE' => 'Vente sur site', 'VSALON' => 'Vente sur salon', 'VNET' => 'Vente par correspondance / internet', 'AUTRE' => 'Autre');

    public static function getInstance() {
        return acCouchdbManager::getClient("DAE");
    }

    public function createSimpleDAE($identifiant, $date = null) {
    	if (!$date) {
    		$date = date('Y-m-d');
    	}
        $dae = new DAE();
        $dae->setIdentifiant($identifiant);
        $dae->setDate($date);
        $dae->setDateSaisie(date('Y-m-d'));
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

    public function findLastByIdentifiantDate($identifiant, $date, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
        $date = str_replace("-", "", $date);
    	if (!preg_match('/^[0-9]{8}$/', $date)) {
    		throw new sfException('date not valid');
    	}
    	$num = $this->getNextIdentifiantForEtablissementAndDay($identifiant, $date);
    	if ($num < 2) {
    		return null;
    	}
    	return $this->find('DAE-' . $identifiant . '-'. $date .'-' . sprintf("%03d", $num - 1));
    }

    public function findByIdentifiant($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
        return $this->startkey('DAE-' . $identifiant . '-00000000-000')->endkey('DAE-' . $identifiant . '-99999999-999')->execute($hydrate);
    }

    public function findByIdentifiantCampagne($identifiant, $campagne, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){

        return $this->startkey('DAE-' . $identifiant . '-'. str_replace("-", "", ConfigurationClient::getInstance()->getDateDebutCampagne($campagne)) .'-000')->endkey('DAE-' . $identifiant . '-'.str_replace("-", "", ConfigurationClient::getInstance()->getDateFinCampagne($campagne)) . '99-999')->execute($hydrate);
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
    	sfApplicationConfiguration::getActive()->loadHelpers(array('Date'));
    	$list[date('Y-m')] = ucfirst(format_date(date('Y-m-d'), 'MMMM yyyy', 'fr_FR'));
    	krsort($list);
    	return $list;
    }
}
