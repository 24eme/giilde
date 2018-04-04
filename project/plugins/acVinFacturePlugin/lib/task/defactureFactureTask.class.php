<?php

class defactureFactureTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      new sfCommandOption('factureid', null, sfCommandOption::PARAMETER_REQUIRED, 'Facture id'),
      // add your own options here
    ));

    $this->namespace        = 'facture';
    $this->name             = 'defacturer';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [testFacture|INFO] task does things.
Call it with:

    [php symfony test:Facture|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    if(!$options['factureid']) {
	throw new sfException('factureid neeeded');
    }

    $facture = FactureClient::getInstance()->find($options['factureid']);
    $avoir = FactureClient::getInstance()->defactureCreateAvoirAndSaveThem($facture);
    if($avoir){
        echo $avoir->_id."\n";
    }else{
        echo "déjà décloturé\n";
    }
  }
}
