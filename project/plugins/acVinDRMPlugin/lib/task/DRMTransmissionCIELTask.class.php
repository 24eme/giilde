<?php

class DRMTransmissionXMLTask extends sfBaseTask
{
  protected function configure()
  {

  	$this->addArguments(array(
      new sfCommandArgument('drmid', sfCommandArgument::REQUIRED, 'Cible contenant les DRM en retour de CIEL'),
  	));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'vinsdeloire'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      new sfCommandOption('checking', null, sfCommandOption::PARAMETER_REQUIRED, 'Cheking mode', 0),
    ));

    $this->namespace        = 'drm';
    $this->name             = 'transmissionCIEL';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF

EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    sfProjectConfiguration::getActive()->loadHelpers("Partial");

    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    $contextInstance = sfContext::createInstance($this->configuration);

    try {
      $drm = DRMClient::getInstance()->find($arguments['drmid']);
      $drm->transferToCiel();
      if ($drm->transmission_douane->success)  {
        echo "DRM ".$drm->_id." transmise avec succès\n";
      }else{
        echo "DRM ".$drm->_id." : Erreur de transmission\n";
        echo $drm->transmission_douane->xml;
        echo "\n";
      }
    }catch(sfException $e) {
      echo "DRM ".$drm->_id." : Erreur de transmission (".$e->getMessage().")\n";
    }

  }

}
