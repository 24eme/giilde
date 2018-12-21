<?php

class StatistiqueConfiguration {

    private static $_instance = null;
    protected $configuration;

    const ALL_KEY = "_ALL";

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new StatistiqueConfiguration();
        }
        return self::$_instance;
    }

    public function __construct() {
        if(!sfConfig::has('statistique_configuration_statistique')) {
			throw new sfException("La configuration pour les statistiques n'a pas été défini pour cette application");
		}

        $this->configuration = sfConfig::get('statistique_configuration_statistique', array());
    }

    public function getAll() {

        return $this->configuration;
    }

    public function isActif() {

        return $this->configuration['actif'];
    }

    public function isAggregatAppellation(){
        return $this->configuration['aggregat_appellation'];
    }
}
