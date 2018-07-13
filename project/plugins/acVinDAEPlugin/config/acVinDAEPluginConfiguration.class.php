<?php

class acVinDAEPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('routing.load_configuration', array('DAERouting', 'listenToRoutingLoadConfigurationEvent'));
  }
}
