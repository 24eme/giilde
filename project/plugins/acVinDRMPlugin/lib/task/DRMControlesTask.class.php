<?php

class DRMControlesTask extends sfBaseTask
{

  protected function configure()
  {
  	$this->addArguments(array(
      new sfCommandArgument('drmid', sfCommandArgument::REQUIRED, 'Identifiant de la DRM')
  	));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
    ));

    $this->namespace        = 'drm';
    $this->name             = 'controles';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
EOF;
  }

  protected function execute($arguments = array(), $options = array()){
    if($this->isValidEnter($arguments["drmid"])){
      $databaseManager = new sfDatabaseManager($this->configuration);
      $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
      $drm = DRMClient::getInstance()->find($arguments["drmid"]);
      if(!is_null($drm)){
        $drm->updateControles();
        $drm->save();
        $vigilance = $this->getControle($drm, DRM::CONTROLE_POINT_VIGILANCE);
        $engagement = $this->getControle($drm, DRM::CONTROLE_POINT_ENGAGEMENT);
        $erreurs = $this->getControle($drm, DRM::CONTROLE_POINT_BLOCANT);
        $transmission = $this->getControle($drm, DRM::CONTROLE_TRANSMISSION);
        $coherence = $this->getControle($drm, DRM::CONTROLE_COHERENCE);
        echo sprintf("%s;erreur:%s;engagement:%s;vigilance:%s;transmission:%s;coherence:%s\n", $arguments["drmid"], $erreurs,$engagement, $vigilance, $transmission, $coherence);
      }else{
        echo sprintf("%s Not found ! Please check config databases.yml for %s!\n", $arguments["drmid"], $options["application"]);
      }
    }else{
      echo sprintf("bad drm id:%s. Please check it !\n", $arguments["drmid"]);
    }
    

  }
  protected function isValidEnter($drmid){
    if(preg_match("|^DRM-[A-Z]?[0-9]+-[0-9]{6}$|", $drmid)){
      return true;
    }
    return false;
  }
  protected function getControle($drm, $type){
    if($drm->exist("controles") && $drm->controles->exist($type))
      return $drm->controles->get($type)->nb;
    return 0;
  }

}
