<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of maintenanceDRMMouvementsUpdateTask
 *
 * @author mathurin
 */
class DRMMouvementsRebuildTask extends sfBaseTask {

    protected function configure() {
        // // add your own arguments here
        $this->addArguments(array(
            new sfCommandArgument('drm', sfCommandArgument::REQUIRED, 'DRM'),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
                // add your own options here
        ));

        $this->namespace = 'drm';
        $this->name = 'mouvements-rebuild';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [maintenanceDRMMouvementsUpdate|INFO] task does things.
Call it with:

  [php symfony maintenanceDRMMouvementsUpdate|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $drmId = $arguments['drm'];
        if(!$drmId){
            throw new sfException("L'identifiant d'une drm est necessaire");
        }
        $this->rebuildMouvements($drmId);
    }

    protected function rebuildMouvements($drmId) {
        $drm = DRMClient::getInstance()->find($drmId);
        if(!$drm->isValidee()) {
            return;
        }
        foreach ($drm->mouvements as $id => $list) {
           foreach ($list as $mid => $mvt) {
		if ($mvt->facture) {
			throw new sfException("$drmId : Impossible de rebuild des mouvements facturés");
                }
           }
        }
        $drm->clearMouvements();
        $isTeleclare = $drm->isTeledeclare();

        foreach ($drm->getProduits() as $hash => $produit){
            foreach ($produit->getProduitsDetails($isTeleclare) as $detail){
             $detail->storeDroits();

            }

            }

        $drm->generateMouvements();
        if ($drm->teledeclare) {
            $drm->generateDroitsDouanes();
        }
        $drm->save();
        echo $drm->_id."\n";
    }

}
