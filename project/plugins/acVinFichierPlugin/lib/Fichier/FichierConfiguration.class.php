<?php

class FichierConfiguration {

    private static $_instance = null;
    protected $configuration;

    const ALL_KEY = "_ALL";

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new FichierConfiguration();
        }
        return self::$_instance;
    }

    public function __construct() {
        if(!sfConfig::has('fichier_configuration_fichier')) {
			throw new sfException("La configuration pour les fichiers n'a pas été défini pour cette application");
		}

        $this->configuration = sfConfig::get('fichier_configuration_fichier', array());
    }

    public function getAll() {

        return $this->configuration;
    }

    public function isActif() {

        return $this->configuration['actif'];
    }
}