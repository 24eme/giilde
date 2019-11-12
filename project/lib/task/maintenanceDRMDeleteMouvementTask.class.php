<?php
/**
 * Description of maintenanceDRMMouvementsUpdateTask
 *
 */
class maintenanceDRMDeleteMouvementTask  extends sfBaseTask {

  protected function configure() {

      $this->addArguments(array(
        new sfCommandArgument('drm_id', sfCommandArgument::REQUIRED, "DRM document id"),
        new sfCommandArgument('hash', sfCommandArgument::REQUIRED, "DRM document id"),
      ));

      $this->addOptions(array(
          new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
          new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
          new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
              // add your own options here
      ));

      $this->namespace = 'maintenance';
      $this->name = 'drm-delete-mouvement';
      $this->briefDescription = '';
      $this->detailedDescription = <<<EOF
The [maintenanceEtablissementUpdateTask|INFO] task does things.
Call it with:

[php symfony maintenant:drm-delete-mouvement|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array()) {
      // initialize the database connection

      $databaseManager = new sfDatabaseManager($this->configuration);
      $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

      $drm = DRMClient::getInstance()->find($arguments['drm_id']);
      foreach ($drm->getProduitsDetails(true) as $produit) {
          $produit->remove($arguments['hash']);
      }
      if($drm->isModified()) {
          echo "La DRM ".$drm->_id." a été sauvées\n";
      }

      $drm->save();
  }

}
