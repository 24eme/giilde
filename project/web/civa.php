<?php


require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('civa', 'prod', false);
sfConfig::set("sf_no_script_name", false);
sfContext::createInstance($configuration)->dispatch();
