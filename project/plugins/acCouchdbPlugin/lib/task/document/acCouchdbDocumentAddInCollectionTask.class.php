<?php

class documentAddInCollectionTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    $this->addArguments(array(
       new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, 'ID du document'),
      new sfCommandArgument('hash_collection', sfCommandArgument::REQUIRED, 'hash de la collection'),
    ));

    $this->addOptions(array(
      new sfCommandOption('key', null, sfCommandOption::PARAMETER_REQUIRED, 'clé de l\'élément à inserer'),
      new sfCommandOption('value', null, sfCommandOption::PARAMETER_REQUIRED, 'Valeur de l\'élément à inserer'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      // add your own options here
    ));

    $this->namespace        = 'document';
    $this->name             = 'add-in-collection';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [documentAddInCollection|INFO] task does things.
Call it with:

  [php symfony documentAddInCollection|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    $doc_id = $arguments['doc_id'];
    $hash = $arguments['hash_collection'];

    $doc = acCouchdbManager::getClient()->find($arguments['doc_id']);

    $nodeCollection = $doc->getOrAdd($hash);

    $key = $options['key'];
    $value = $options['value'];
    $nodeCollection->add($key,$value);

    $doc->save();
    echo "Le document ".$doc->_id." a été sauvé : ajout dans ".$hash.", de $key : $value \n";
  }

}
