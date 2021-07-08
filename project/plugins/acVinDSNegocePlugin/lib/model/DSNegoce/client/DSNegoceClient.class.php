<?php

class DSNegoceClient extends acCouchdbClient {

	const TYPE_MODEL = 'DSNEGOCE';

    public static function getInstance()
    {
      return acCouchdbManager::getClient("DSNegoce");
    }

		public static function makeId($identifiant, $date)
		{
			return self::TYPE_MODEL.'-' . $identifiant . '-' . str_replace('-', '', $date);
		}

		public function createOrFind($etablissement, $date = null, $teledeclare = false)
		{
			$dateDeclaration = self::getDateDeclaration($date);
			if ($dsnegoce = $this->find(self::makeId($etablissement, $dateDeclaration))) {
				return $dsnegoce;
			}
			$dsnegoce = new DSNegoce();
			$dsnegoce->initDoc($etablissement, $dateDeclaration, $teledeclare);
			return $dsnegoce;
		}

		public static function getDocumentRepriseProduits($identifiant, $dateDeclaration) {
				$periode = substr(str_replace('-', '', $dateDeclaration), 0, 6);
				$drm = DRMClient::getInstance()->findMasterByIdentifiantAndPeriode($identifiant, $periode);
				if (!$drm) {
					$drm = DRMClient::getInstance()->findMasterByIdentifiantAndPeriode($identifiant, substr($periode, 0, 4).'-'.substr($periode, -2));
				}
				return $drm;
		}

		public static function getDateDeclaration($date = null)
		{
			if (!$date) {
				$date = date('Y-m-d');
			} elseif (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date)) {
				throw new Exception('Date format invalide : '.$date);
			}
			$cm = new CampagneManager('08-01');
			$campagne = explode('-', $cm->getCampagneByDate($date));
			return $campagne[1].'-07-31';
		}

    public function findByArgs($identifiant, $date, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT)
    {
    	$id = self::TYPE_MODEL.'-' . $identifiant . '-' . $date;
    	return $this->find($id, $hydrate);
    }

    public function findByIdentifiant($identifiant,  $hydrate = acCouchdbClient::HYDRATE_DOCUMENT)
    {
    	$view = $this->startkey(sprintf(self::TYPE_MODEL."-%s-%s", $identifiant, "00000000"))
    	->endkey(sprintf(self::TYPE_MODEL."-%s-%s", $identifiant, "99999999"));
    	return $view->execute($hydrate)->getDatas();
    }

    public function listPeriodesByEtablissementId($identifiant) {
    	$rows = $this->findByIdentifiant($identifiant, acCouchdbClient::HYDRATE_ON_DEMAND);
    	sfApplicationConfiguration::getActive()->loadHelpers(array('Date'));
    	$periodes = array();
    	foreach ($rows as $k => $v) {
    		$ex = explode('-', $k);
				$lastInd = count($ex) - 1;
    		if ($lastInd >= 0) {
    			$date = substr($ex[$lastInd], 0, 4).'-'.substr($ex[$lastInd], 4, 2).'-'.substr($ex[$lastInd], 6, 2);
    			if (!in_array($date, $periodes)) {
    				$periodes[$date] = ucfirst(format_date($date, 'MMMM yyyy', 'fr_FR'));
    			}
    		}
    	}
			$date = self::getDateDeclaration(date('Y-m-d'));
    	if (!in_array($date, $periodes)) {
    		$periodes[$date] = ucfirst(format_date($date, 'MMMM yyyy', 'fr_FR'));
    	}
			$dateCampagneAnterieur = self::getDateDeclaration((date('Y')-1).'-'.date('m-d'));
    	if (!in_array($dateCampagneAnterieur, $periodes)) {
    		$periodes[$dateCampagneAnterieur] = ucfirst(format_date($dateCampagneAnterieur, 'MMMM yyyy', 'fr_FR'));
    	}
    	krsort($periodes);
    	return $periodes;
    }
}
