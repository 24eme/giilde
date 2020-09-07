<?php

class DRMReecritureJuilletAoutTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Id du document"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace        = 'drm';
        $this->name             = 'reecriture-juillet-aout';
        $this->briefDescription = 'compare les libellés et réécrits ceux d\'aout';
        $this->detailedDescription = '';

    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $drm_aout = DRMClient::getInstance()->find($arguments['doc_id']);

        if ($drm_aout === null) {
            exit("La DRM n'existe pas\n");
        }

        $periode_precedente = DRMClient::getInstance()->getPeriodePrecedente($drm_aout->periode);
        $drm_precedente = DRMClient::getInstance()->findMasterByIdentifiantAndPeriode($drm_aout->getIdentifiant(), $periode_precedente);

        foreach ($drm_precedente->getProduitsDetails() as $detail) {
            $hash_prec = $detail->getHash();

            try {
                $detail_aout = $drm_aout->getDetailsByHash($hash_prec);
            } catch (sfException $e) {
                echo $e->getMessage().PHP_EOL;
                continue;
            }

            if ($detail->getProduitLibelle() !== $detail_aout->getProduitLibelle()) {
                echo 'update : '. $detail_aout->getProduitLibelle() . ' -> '.$detail->getProduitLibelle().PHP_EOL;
                $detail_aout->produit_libelle = $detail->getProduitLibelle();
                $detail_aout->code_inao = $detail->getCodeInao();
            }

            $drm_aout->update();
        }

        $drm_aout->save();
    }

}
