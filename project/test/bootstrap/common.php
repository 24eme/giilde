<?php

require_once(dirname(__FILE__).'/../../config/ProjectConfiguration.class.php');
require_once dirname(__FILE__).'/../../lib/vendor/symfony/test/bootstrap/unit.php';

$configuration = ProjectConfiguration::getApplicationConfiguration( 'bivc', 'prod', true);

new sfDatabaseManager($configuration);
