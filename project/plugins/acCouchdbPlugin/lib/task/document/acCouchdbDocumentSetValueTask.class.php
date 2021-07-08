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
      new sfCommandOption('delete', null, sfCommandOption::PARAMETER_OPTIONAL, 'Option for deleting one field', false),
      // add your own options here
    ));

    $this->namespace        = 'document';
    $this->name             = 'setvalue';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [acCouchdbDocumentSetValue|INFO] task does things.
Call it with:

  [php symfony document:setvalue|INFO]
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
        error_log("doc $doc non trouvé");
        return;
    }

    $output = array();
    foreach($values as $hash => $value) {
        try {
            $docadd = $doc;
            $hashadd = $hash;
            if (preg_match('/(.*)\/([^\/]*)$/', $hash, $match)) {
                $parenthash = $match[1];
                $docadd = $doc->get($parenthash);
                $hashadd = $match[2];
            }
            $docadd->add($hashadd);
        }catch(sfException $e) {
            error_log("$hash ne peut exister pour ".$doc->_id);
            return;
        }

        if(!$doc->exist($hash)) {
            error_log("$hash n'existe pas");
            return;
        }
        if($doc->get($hash) instanceof acCouchdbJson) {
            error_log("$hash n'est pas un couchedbJson pour ".$doc->_id);
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

    error_log("Le document ".$doc->_id."@".$oldrev." a été sauvé @".$doc->_rev.", les valeurs suivantes ont été changés : ".implode(",", $output));
  }
}
