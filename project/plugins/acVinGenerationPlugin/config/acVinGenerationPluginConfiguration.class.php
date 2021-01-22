<?php

class acVinGenerationPluginConfiguration extends sfPluginConfiguration
{
    public function setup()
    {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            $configCache->registerConfigHandler('config/generation.yml', 'sfDefineEnvironmentConfigHandler', ['prefix' => 'generation_']);
            $configCache->checkConfig('config/generation.yml');
        }
    }

  public function initialize()
  {
      if ($this->configuration instanceof sfApplicationConfiguration) {
        $configCache = $this->configuration->getConfigCache();
        include($configCache->checkConfig('config/generation.yml'));
      }

    $this->dispatcher->connect('routing.load_configuration', array('GenerationRouting', 'listenToRoutingLoadConfigurationEvent'));
  }
}
