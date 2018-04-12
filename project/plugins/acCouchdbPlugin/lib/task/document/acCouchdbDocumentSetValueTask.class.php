<?php

class acCouchdbDocumentSetValueTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    $this->addArguments(array(
       new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, 'Document ID'),
       new sfCommandArgument('hash_values', sfCommandArgument::IS_ARRAY, 'Hash or values'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      // add your own options here
    ));

    $this->namespace        = 'document';
    $this->name             = 'setvalue';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [maintenanceCompteStatut|INFO] task does things.
Call it with:

  [php symfony maintenanceCompteStatut|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    $values = array();
    $hash = null;
    $value = null;
    foreach($arguments['hash_values'] as $i => $arg) {
        if($i % 2 == 0) {
            $hash = $arg;
        } else {
            $value = $arg;
        }


        if($hash && $value) {
            $values[$hash] = $value;
            $hash = null;
            $value = null;
        }
    }


    $doc = acCouchdbManager::getClient()->find($arguments['doc_id']);

    if(!$doc) {

        return;
    }

    $output = array();
    foreach($values as $hash => $value) {
        if(!$doc->exist($hash)) {

            return;
        }
        if($doc->get($hash) instanceof acCouchdbJson) {

            return;
        }
        if($doc->get($hash) === $value) {

            continue;
        }

        $output[] = $hash.":\"".$value."\" (".$doc->get($hash).")";
        $doc->set($hash, $value);
    }

    $oldrev = $doc->_rev;

    $doc->save();

    if($oldrev == $doc->_rev) {
        return;
    }

    echo "Le document ".$doc->_id."@".$oldrev." a été sauvé @".$doc->_rev.", les valeurs suivantes ont été changés : ".implode(",", $output)."\n";
  }
}
