<?php

class DRMReorderProduitsTask extends sfBaseTask {

    protected function configure() {
        // // add your own arguments here
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, 'id de la DRM'),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
                // add your own options here
        ));

        $this->namespace = 'drm';
        $this->name = 'reorder-produits';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $drm = DRMClient::getInstance()->find($arguments['doc_id']);
        $drm->declaration->reorderByConf();
        $drm->save();
    }

}
