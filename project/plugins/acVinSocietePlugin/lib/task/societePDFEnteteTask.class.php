<?php

class societePDFEntete extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      new sfCommandOption('all', null, sfCommandOption::PARAMETER_OPTIONAL, 'Display all societé (suspendu included)', ''),
    ));
    // add your own options here
    $this->addArguments(array(
       new sfCommandArgument('societe_id', sfCommandArgument::REQUIRED, 'ID du societe')
    ));

    $this->namespace        = 'societe';
    $this->name             = 'pdfentete';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [testFacture|INFO] task does things.
Call it with:

    [php symfony societe:pdfentete SOCIETE-ID|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
    $context = sfContext::createInstance($this->configuration);

    $soc = SocieteClient::getInstance()->find($arguments['societe_id']);
    if (!$soc) {
      throw new sfException("Societe non trouvée : ".$arguments['societe_id']);
    }
    $latex = new SocieteEnteteLatex($soc);
    echo $latex->getPDFFile()."\n";
  }
}
