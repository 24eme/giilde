<?php

class configurationExportEdiCatalogueMouvementTask extends sfBaseTask
{

  public static $escaped_mvts_keys = array("dont_revendique");

  protected function configure()
  {

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      // add your own options here
    ));

    $this->namespace        = 'configuration';
    $this->name             = 'export-edi-catalogue-produit';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [configurationExportEdiCatalogueMouvement|INFO] task does things.
Call it with:

  [php symfony configurationExportEdiCatalogueMouvementTask|INFO] exporte les mouvements de la configuration courrante
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    $configuration = ConfigurationClient::getCurrent();
    if(!$configuration){
      throw new sfException("La configuration courante n'existe pas");
    }
    echo sprintf("TYPE_DRM,CATEGORIE MOUVEMENT,TYPE MOUVEMENT,EXPLICATION MOUVEMENT\n");

    $configurationDeclaration = $configuration->getDeclaration();
    $typesMvt = DRMClient::$types_libelles;

    $libelles = $configuration->libelle_detail_ligne;

    foreach ($typesMvt as $typesMvtKey => $typesMvtValue) {
      foreach ($configurationDeclaration->$typesMvtKey as $categorieKey => $categorieValue) {
        $categorieArray = array();
        foreach ($categorieValue as $mvtKey => $mvtValue) {
          if(!in_array($mvtKey,self::$escaped_mvts_keys)){
            $libelleMvt = strtolower($libelles->$typesMvtKey->$categorieKey->$mvtKey->libelle);
            $categorieArray[$libelleMvt] = sprintf("%s,%s,%s,%s\n",strtolower(KeyInflector::slugify($typesMvtValue)),$categorieKey,$mvtKey,$libelleMvt);
          }
        }
        ksort($categorieArray);
        foreach ($categorieArray as $row) {
          echo $row;
        }
      }
    }

  }
}
