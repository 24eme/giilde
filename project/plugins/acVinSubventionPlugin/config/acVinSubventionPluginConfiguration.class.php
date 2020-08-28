<?php

class acVinSubventionPluginConfiguration extends sfPluginConfiguration
{
  public function setup() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            $configCache->registerConfigHandler('config/subvention.yml', 'sfDefineEnvironmentConfigHandler', array('prefix' => 'subvention_'));
            $configCache->checkConfig('config/subvention.yml');
        }
    }

    public function initialize() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            include($configCache->checkConfig('config/subvention.yml'));
        }
    }
}
