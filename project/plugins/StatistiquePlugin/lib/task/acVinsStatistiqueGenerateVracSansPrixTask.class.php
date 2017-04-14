<?php
class acVinsStatistiqueGenerateVracSansPrixTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    $this->addArguments(array(
       new sfCommandArgument('date_debut', sfCommandArgument::REQUIRED, 'Date de fin'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      // add your own options here
    ));

    $this->namespace        = 'generate';
    $this->name             = 'vracSansPrix';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
    $context = sfContext::createInstance($this->configuration);

    $vsp = new VracSansPrixData($arguments['date_debut']);
    $pdfs = $vsp->getPdfObjects();
    foreach ($pdfs as $k => $pdf) {
      echo "$k: ";
      $pdf->getPDFFile();
      echo "done\n";
    }
  }
}
