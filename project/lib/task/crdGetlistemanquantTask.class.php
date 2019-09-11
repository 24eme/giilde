<?php

class crdGetlistemanquantTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    $this->addArguments(array(
      new sfCommandArgument('periode', sfCommandArgument::REQUIRED, 'La periode des DRM'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      // add your own options here
    ));

    $this->namespace        = 'crd';
    $this->name             = 'get-liste-manquant';
    $this->briefDescription = 'Liste les DRMs avec des CRD manquants';
    $this->detailedDescription = <<<EOF
[crd:get-liste-manquant|INFO] extrait les DRMs avec des CRDs manquants
Call it with:

  [php symfony crd:get-liste-manquant|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
    $periode = $arguments['periode'];

    $drms = acCouchdbManager::getClient()->reduce(false)->getView("drm", "all");

    foreach ($drms->rows as $drm) {
        if ($drm->key[2] !== $periode) {
            continue;
        }

        $drm = DRMClient::getInstance()->find($drm->id);

        $accise = $drm->declarant->no_accises;
        $nom = $drm->declarant->nom;
        $commune = $drm->declarant->commune;

        $crds = $drm->getCrds();
        foreach ($crds as $mention => $crd) {
            foreach ($crd as $key => $detail) {
                if ($detail->sorties_manquants > 0) {
                    echo implode(';', [$accise, $nom, $commune, $detail->genre, $detail->couleur, $detail->centilitrage * 100, $detail->sorties_manquants]) . PHP_EOL;
                }
            }
        }
    }
  }
}
