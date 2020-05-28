<?php

class ConfigurationListDroitsTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    $this->addArguments(array(
       new sfCommandArgument('date', null, sfCommandOption::PARAMETER_REQUIRED, 'Date', null),
       new sfCommandArgument('droit_type', null, sfCommandOption::PARAMETER_REQUIRED, 'cvo ou douane', ConfigurationDroits::DROIT_CVO),
   ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      new sfCommandOption('with_produit', null, sfCommandOption::PARAMETER_REQUIRED, 'print on line per produit hash', false),
      // add your own options here
    ));

    $this->namespace        = 'configuration';
    $this->name             = 'list-droits';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [ConfigurationListDroitsTask|INFO] task does things.
Call it with:

  [php symfony ConfigurationListDroitsTask|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    $date = $arguments['date'];
    $configuration = ConfigurationClient::getConfiguration($date);
    $droits = array();

    foreach ($configuration->getProduits() as $produit) {
        $droit_hash = null;
        $droit_taux = null;
        try {
            $droit = $produit->getDroitByType($date, $arguments['droit_type']);
            $droit_hash = $droit->getHash();
            $droit_taux = $droit->getTaux();
        }catch(Exception $e) {
            if (!$options['with_produit']) {
                continue;
            }
        }
        $droit_taux = sprintf("%.02f", $droit_taux);
        if (!isset($droits[$droit_taux])) {
            $droits[$droit_taux] = array();
        }
        if (!isset($droits[$droit_taux][$droit_hash])) {
            $droits[$droit_taux][$droit_hash] = array();
        }
        $droits[$droit_taux][$droit_hash][] = $produit->getHash();
    }

    ksort($droits);
    foreach($droits as $droit_taux => $array_droit_hashes) {
        ksort($array_droit_hashes);
        foreach ($array_droit_hashes as $droit_hash => $array_produits) {
            if ($options['with_produit']) {
                foreach($array_produits as $produit_hash) {
                    echo "$droit_taux;$droit_hash;$produit_hash\n";
                }
            }else{
                echo "$droit_taux;$droit_hash;".count($array_produits)."\n";
            }
        }
    }
  }
}
