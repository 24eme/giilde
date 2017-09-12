<?php

class SendRappelContratTask extends sfBaseTask {

    protected $date = null;

    protected function configure() {

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
                // add your own options here
        ));

        $this->addArguments(array(
           new sfCommandArgument('vrac_id', sfCommandArgument::REQUIRED, 'ID du contrat'),
        ));

        $this->namespace = 'teledeclaration';
        $this->name = 'sendRappelContrat';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [generateAlertes|INFO] task does things.
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $routing = clone ProjectConfiguration::getAppRouting();
        $context = sfContext::createInstance($this->configuration);

        $contrat = VracClient::getInstance()->find($arguments['vrac_id']);

        if (!$contrat) {
            throw new sfException($arguments['vrac_id'].' invalid');
        }

        $vracEmailManager = new VracEmailManager($this->getMailer());
        $vracEmailManager->setVrac($contrat);
        $vracEmailManager->sendMailRappel(true);
    }
}
