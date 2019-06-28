<?php

class importDRMNegoceGeneriqueProduitsTask extends sfBaseTask
{
  protected function configure()
  {
      $this->addArguments(array(
          new sfCommandArgument('csv_file', sfCommandArgument::REQUIRED, "Fichier CSV contenant les produits génériques"),
      ));
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      // add your own options here
    ));

    $this->namespace        = 'configuration';
    $this->name             = 'import-drm-negoce-generique-produits';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [exportCSVConfiguration|INFO] task does things.
Call it with:

  [php symfony importDRMNegoceGeneriqueProduits|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
    
    $csvFile = $arguments['csv_file'];

    $configuration = ConfigurationClient::getCurrent();
    if(!$configuration){
        echo "/!\ La configuration courante n'existe pas\n";
        return;
    }
    
    try {
        $csv = new ProduitCsvFile($configuration, $csvFile);
        $csv->importProduits(true);
    } catch (Exception $e) {
        echo "/!\ Une erreur est survenue : ".$e->getMessage()."\n";
        return;
    }
    
    $configuration->save();
    
    echo "Import terminé avec succès\n";
    
  }
}
