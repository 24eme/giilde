<?php

class DRMCompareXMLsTask extends sfBaseTask
{

  protected function configure()
  {
  	$this->addArguments(array(
      new sfCommandArgument('drmid', sfCommandArgument::REQUIRED, 'Cible contenant les DRM en retour de CIEL')
  	));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'vinsdeloire'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      new sfCommandOption('checking', null, sfCommandOption::PARAMETER_REQUIRED, 'Cheking mode', false),
    ));

    $this->namespace        = 'drm';
    $this->name             = 'compareXMLs';
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
    $routing = clone ProjectConfiguration::getAppRouting();
    $contextInstance = sfContext::createInstance($this->configuration);
    $contextInstance->set('routing', $routing);

    $drm = DRMClient::getInstance()->find($arguments['drmid']);
    if ($drm->areXMLIdentical()) {
      $drm->getOrAdd('transmission_douane')->add("coherente", true);
      echo $drm->_id." : XML sont identiques\n";
      $drm->save();
    }else{
      echo $drm->_id." : XML differents\n";
      $comp = $drm->getXMLComparison();
      $drm->getOrAdd('transmission_douane')->add("coherente", 0);
      $drm->save();
      $drm = DRMClient::getInstance()->find($drm->_id);
      $drm->getOrAdd('transmission_douane')->add("coherente", false);
      $drm->save();
      try {
        if($suivante = $drm->getSuivante()){
          echo "      DRM modificatrice non ouverte : il existe une DRM Suivante $suivante->_id \n";
        }elseif(!$drm->transmission_douane->success){
          echo "      DRM modificatrice non ouverte : la DRM n'a pas été transmise aux douanes\n";
        }else{
          $drm_modificatrice = $drm->generateModificative();
          $drm_modificatrice->type_creation = DRMClient::DRM_CREATION_AUTO;
          $drm_modificatrice->save();
          echo "      DRM modificatrice ouverte : ".sfConfig::get('app_vinsi_url').sfContext::getInstance()->getRouting()->generate("drm_etablissement",array("identifiant" => $drm->identifiant))."\n";
        }
      } catch (Exception $e) {
        echo "      Une DRM modificatrice est déjà ouverte : ".sfConfig::get('app_vinsi_url').sfContext::getInstance()->getRouting()->generate("drm_etablissement",array("identifiant" => $drm->identifiant),true)."\n";
      }

        $diffArrStr = $comp->getFormattedXMLComparaison();
        foreach ($diffArrStr as $key => $values) {
            echo "      ".$key . " [" . ((is_null($values[0])) ? "valeur nulle" : $values[0]) . "]\n";
        }


      if ($options['checking']) {
        echo "Différence trouvée : \n";
        foreach ($comp->getDiff() as $key => $value) {
          echo $key." : ".$value."\n";
        }
      }
    }


  }

}
