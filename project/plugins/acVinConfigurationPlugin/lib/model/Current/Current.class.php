<?php
/**
 * Model for Current
 *
 */

class Current extends BaseCurrent {

    public function __construct() {
        parent::__construct();
        $this->set('_id', 'CURRENT');
    }

    public function getPeriode()
    {
    	return date('Y-m');
    }

    public function getConfigurationId($date) {
        foreach($this->configurations as $confDate => $confId) {
            if($date >= $confDate) {

                return $confId;
            }
        }

        throw new sfException(sprintf("Pas de configuration pour cette date %s", $date));
    }

    public function reorderConfigurations() {
        $configurations = $this->configurations->toArray(true, false);

        krsort($configurations);

        $this->remove('configurations');
        $this->add('configurations', $configurations);
    }

    public function save() {
        parent::save();
        CurrentClient::getInstance()->cacheResetConfiguration();
    }

}
