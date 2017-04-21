<?php
/**
 * Description of maintenanceDRMMouvementsUpdateTask
 *
 */
class maintenanceDRMMouvementsUpdateTask extends sfBaseTask {

    protected function configure() {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
                // add your own options here
        ));

        $this->namespace = 'maintenance';
        $this->name = 'drm-mouvements-update';
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
		$items = DRMAllView::getInstance()->findAll();
		foreach ($items as $item) {
			if ($drm = DRMClient::getInstance()->find($item->_id)) {
				$save = false;
				foreach ($drm->mouvements as $id => $mouvements) {
					foreach ($mouvements as $mouvement) {
						if ($mouvement->type_hash == 'creationvrac_details') {
							if ($vrac = VracClient::getInstance()->find('VRAC-'.$mouvement->vrac_numero)) {
								if ($drm->identifiant == $id) {
									$mouvement->add('region_destinataire', $vrac->acheteur->region);
								} else {
									$mouvement->add('region_destinataire', $vrac->vendeur->region);
								}
								$save = true;
							} else {
								echo sprintf("ERROR;Le contrat vrac n'existe pas;VRAC-%s\n", $mouvement->vrac_numero);
								continue;
							}
						}
					}
				}
				if ($save) {
					$drm->save();
					echo sprintf("SUCCESS;mvts update pour DRM;%s\n", $item->_id);
				}
			} else {
				echo sprintf("ERROR;La DRM n'existe pas;%s\n", $item->_id);
			}
		}
    }

}
