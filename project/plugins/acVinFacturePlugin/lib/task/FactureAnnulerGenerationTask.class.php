<?php

class FactureAnnulerGenerationTask extends sfBaseTask
{
    protected function configure()
    {
        // add your own arguments here
        $this->addArguments(array(
		    new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, 'Generation doc id'),
        ));

        // add your own arguments here
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            // add your own options here
        ));

        $this->namespace        = 'facture';
        $this->name             = 'annuler-generation';
        $this->briefDescription = '';
        $this->detailedDescription = '';
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $generation = GenerationClient::getInstance()->find($arguments['doc_id']);
        if(!$generation || $generation->type_document != "FACTURE") {
            return;
        }
        foreach($generation->documents as $id) {
            $facture = FactureClient::getInstance()->find($id);
            if(!$facture) {
                echo "$id;;Facture non trouvé\n";
                continue;
            }

            foreach ($facture->getLignes() as $ligne) {
                $ligne->defacturerMouvements();
            }

            foreach ($facture->origines as $docid) {
                $doc = FactureClient::getInstance()->getDocumentOrigine($docid);
                if ($doc) {
                    $doc->save();
                    echo $id.":".$doc->_id."@".$doc->_rev.";saved\n";
                }
            }

            $facture->delete();
            echo $id.";;deleted\n";
        }
    }
}
