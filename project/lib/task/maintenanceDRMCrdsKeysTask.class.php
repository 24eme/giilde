<?php
/**
 * Description of maintenanceDRMMouvementsUpdateTask
 *
 */
class maintenanceDRMCrdsKeysTask extends sfBaseTask {

  protected function configure() {

      $this->addArguments(array(
        new sfCommandArgument('drm_id', sfCommandArgument::REQUIRED, "DRM document id"),
      ));

      $this->addOptions(array(
          new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
          new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
          new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
              // add your own options here
      ));

      $this->namespace = 'maintenance';
      $this->name = 'drm-crds-keys';
      $this->briefDescription = '';
      $this->detailedDescription = <<<EOF
The [maintenanceEtablissementUpdateTask|INFO] task does things.
Call it with:

[php symfony maintenanceEtablissementUpdateTask|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array()) {
      // initialize the database connection

      $databaseManager = new sfDatabaseManager($this->configuration);
      $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

      $drm = DRMClient::getInstance()->find($arguments['drm_id']);
      $hashesToRemove = array();
      foreach ($drm->getCrds() as $firstKey => $subNode) {
        foreach ($subNode as $key => $nodeToMove) {
            $newNode = $drm->crds->get($firstKey)->getOrAddCrdNode($nodeToMove->genre,$nodeToMove->couleur,$nodeToMove->centilitrage * DRMCrds::FACTLITRAGE,$nodeToMove->detail_libelle,$nodeToMove->stock_debut);
            $newNode->genre = $nodeToMove->genre;
            $newNode->stock_debut = $nodeToMove->stock_debut;
            $newNode->stock_fin = $nodeToMove->stock_fin;
            $newNode->couleur = $nodeToMove->couleur;
            $newNode->centilitrage = $nodeToMove->centilitrage;
            $newNode->detail_libelle = $nodeToMove->detail_libelle;
            $newNode->entrees_achats = $nodeToMove->entrees_achats;
            $newNode->entrees_retours = $nodeToMove->entrees_retours;
            $newNode->entrees_excedents = $nodeToMove->entrees_excedents;
            $newNode->sorties_utilisations = $nodeToMove->sorties_utilisations;
            $newNode->sorties_destructions = $nodeToMove->sorties_destructions;
            $newNode->sorties_manquants = $nodeToMove->sorties_manquants;
            if($nodeToMove->getHash() != $newNode->getHash()){
              $hashesToRemove[] = $nodeToMove->getHash();
            }
          }
      }
      foreach ($hashesToRemove as $hashToRemove) {
      $drm->remove($hashToRemove);
      }
      $drm->save();
      echo "La DRM ".$drm->_id." a été sauvée avec des bonnes CRDs\n";
  }

}
