<?php

class maintenanceMouvementsFactureQuantiteTask extends sfBaseTask
{

    protected function configure()
    {
        // // add your own arguments here
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "DRM document id"),
        ));
        $this->addOptions(array(
          new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
          new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
          new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
          // add your own options here
        ));

        $this->namespace        = 'maintenance';
        $this->name             = 'mouvements-facture-quantite';
        $this->briefDescription = '';
        $this->detailedDescription = '';
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $doc = MouvementsFactureClient::getInstance()->find($arguments['doc_id']);

        if(!$doc) {
            return;
        }

        foreach($doc->mouvements as $mouvs) {
            foreach($mouvs as $mouv) {
                $mouv->quantite = $mouv->quantite * -1;
            }
        }

        $doc->save(true);
        echo "La doc ".$doc->_id." a été modifiée\n";
    }

}
