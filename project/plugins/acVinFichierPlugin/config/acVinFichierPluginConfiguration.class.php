<?php

class acVinFichierPluginConfiguration extends sfPluginConfiguration
{
	public function setup() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            $configCache->registerConfigHandler('config/fichier.yml', 'sfDefineEnvironmentConfigHandler', array('prefix' => 'fichier_'));
            $configCache->checkConfig('config/fichier.yml');
        }
    }

    public function initialize() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            include($configCache->checkConfig('config/fichier.yml'));
        }
    }
}