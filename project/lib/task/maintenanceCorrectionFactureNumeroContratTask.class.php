<?php
/**
 * Description of maintenanceDRMMouvementsUpdateTask
 *
 */
class maintenanceCorrectionFactureNumeroContratTask extends sfBaseTask {

    protected function configure() {

        $this->addArguments(array(
        new sfCommandArgument('facture_id', sfCommandArgument::REQUIRED, "Facture document id"),
        ));

        $this->addOptions(array(
          new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
          new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
          new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
              // add your own options here
        ));

        $this->namespace = 'maintenance';
        $this->name = 'correction-facture-numero-contrat';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
        The [maintenanceCorrectionFactureNumeroContratTask|INFO] task does things.
        Call it with:

        [php symfony maintenanceCorrectionFactureNumeroContratTask|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection

        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $facture = FactureClient::getInstance()->find($arguments['facture_id']);
        $i = 0;
        foreach($facture->lignes as $ligne) {
            foreach($ligne->details as $detail) {
                if(!preg_match("/Contrat n° DRM/", $detail->origine_type)) {
                    continue;
                }
                $finded = false;
                foreach($ligne->getMouvements() as $mouvement) {
                    if($mouvement->volume != $detail->quantite*-1 || !$mouvement->detail_libelle) {
                        continue;
                    }
                    if (FactureConfiguration::getInstance()->getIdContrat() == 'ID' ) {
                        $idContrat = intval(substr($mouvement->vrac_numero, -6));
                    }else{
                        $idContrat = $mouvement->detail_libelle;
                    }
		    $finded=true;
                    $detail->origine_type
 = preg_replace("/Contrat n° DRM-[0-9]+-[0-9]+/", "Contrat n° ".$idContrat, $detail->origine_type);
                    $i++;
                    echo $detail->origine_type."\n";
;
                }
		if(!$finded) {
			echo $detail->origine_type." ".$facture->_id."\n";
		}
            }
        }

        if($i > 0) {
            echo "La ".$facture->_id." a été réécrite\n";
        }

        $facture->save();
    }

}
