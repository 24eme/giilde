<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class VracUpdateEnlevementsTask
 * @author mathurin
 */
class VracUpdateEnlevementsTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    $this->addArguments(array(
        new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "id_doc"),
    ));
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      // add your own options here
    ));

    $this->namespace        = 'vrac';
    $this->name             = 'update-enlevements';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [VracUpdateEnlevements|INFO] task does things.
Call it with:

  [php symfony update-enlevements|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    $vrac = VracClient::getInstance()->find($arguments['doc_id']);

    if(!$vrac) {

        throw new sfException(sprintf("ERREUR;Contrat introuvable %s", $arguments['doc_id']));
    }

    $oldVolEnleve = $vrac->volume_enleve;

    $vrac->updateVolumesEnleves();

    $newVolEnleve = $vrac->volume_enleve;

    $vrac->save();
    if($oldVolEnleve != $newVolEnleve) {
        echo sprintf("Contrat %s : Volume enlevé %s => %s \n", $vrac->_id, $oldVolEnleve, $newVolEnleve);
    }else{
      echo sprintf("Contrat %s : Pas de changements\n", $vrac->_id);
    }
  }
}
