<?php

class acCouchdbDocumentDuplicateTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    $this->addArguments(array(
       new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, 'ID du document'),
       new sfCommandArgument('new_doc_id', sfCommandArgument::OPTIONAL, 'Nouvelle id du document'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      // add your own options here
    ));

    $this->namespace        = 'document';
    $this->name             = 'duplicate';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [acCouchdbDocumentDuplicate|INFO] task does things.
Call it with:

  [php symfony document:changeid|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();



    $ret = acCouchdbManager::getClient()->copyDoc($arguments['doc_id'], $arguments['new_doc_id']);

    echo "INFO;Document dupliqué;".$ret->id."@".$ret->rev."\n";
  }
}
