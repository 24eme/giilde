<?php

/**
 * Model for ConfigurationDroits
 *
 */
class ConfigurationDroits extends BaseConfigurationDroits {
	
	const CODE_CVO = 'CVO';
	const CODE_DOUANE = 'DOUANE';
	const LIBELLE_CVO = 'Cvo';

	const DROIT_CVO = 'cvo';
	const DROIT_DOUANE = 'douane';

	protected $currentDroits = array();
	
	public function addDroit($date, $taux, $code, $libelle) {
	  $value = $this->add();
	  $value->date = $date;
	  $value->taux = $taux;
	  $value->code = $code;
	  $value->libelle = $libelle;
	}

	public function getCurrentDroit($date_str, $include_chapeau = true) {
        $cache_key = ($include_chapeau) ? '1'.$date_str : '0'.$date_str;
		if(array_key_exists($cache_key, $this->currentDroits) && $this->currentDroits[$cache_key]) {

			return $this->currentDroits[$cache_key];
		}

		if(array_key_exists($cache_key, $this->currentDroits) && $this->currentDroits[$cache_key] === false) {

			throw new sfException('Aucun droit spécifié pour '.$this->getHash());
		}

	  	$currentDroit = null;
        foreach ($this as $configurationDroit) {
		    $date = new DateTime($configurationDroit->date);
		    if ($date_str >= $date->format('Y-m-d')) {
		      	if ($currentDroit) {
					if ($date->format('Y-m-d') > $currentDroit->date) {
			  			$currentDroit = $configurationDroit;
	                }
	      		} else {
					$currentDroit = $configurationDroit;
		      	}
		    }
	  	}

	  	if ($currentDroit) {
	  		$this->currentDroits[$cache_key] = $currentDroit;
	    	return $this->currentDroits[$cache_key];
	  	}

		try {
			$parent = $this->getNoeud()->getParentNode();
            $droit = $parent->interpro->getOrAdd($this->getInterpro()->getKey())->droits->getOrAdd($this->getKey())->getCurrentDroit($date_str);
			$this->currentDroits[$cache_key] = $droit;
			return $this->currentDroits[$cache_key];
		} catch (sfException $e) {
			$this->currentDroits[$cache_key] = false;
			throw new sfException('Aucun droit spécifié pour '.$this->getHash());
		}
	}

	public function compressDroits() {
		$droits_to_remove = array();
		$moreRecent = null;
		foreach($this as $droit) {
			if(!$moreRecent || $droit->date > $moreRecent->date) {
				$moreRecent = $droit;
			}
		}

		if($moreRecent) {
			$this->clear();
			$this->add(null, $droit);
		}
	}
	
	public function getInterpro() {
		return $this->getParent()->getParent();
	}

	public function getNoeud() {

		return $this->getInterpro()->getParent()->getParent();
	}

}