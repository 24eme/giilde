<?php

class GenerationConfiguration
{
    private static $_instance = null;
    protected $configuration;

    const ALL_KEY = "_ALL";

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new GenerationConfiguration();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        if(!sfConfig::has('generation_configuration_generation')) {
            throw new sfException("La configuration pour les génération n'a pas été défini pour cette application");
        }

        $this->configuration = sfConfig::get('generation_configuration_generation', array());
    }

    public function getAll()
    {
        return $this->configuration;
    }

    public function getConfig($name)
    {
        if(!isset($this->configuration[$name])) {
            return null;
        }

        return $this->configuration[$name];
    }

    public function hasSousGeneration($type)
    {
        if (empty($this->configuration['sousgeneration'])) {
            return false;
        }

        if ($type && empty($this->configuration['sousgeneration'][$type])) {
            return false;
        }

        return true;
    }

    public function getSousGeneration($type)
    {
        return $this->hasSousGeneration($type) && ($type) ? $this->configuration['sousgeneration'][$type] : array();
    }
}
