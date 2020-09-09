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
                echo 'HASH TROUVÉE: '.$hash_prec.PHP_EOL;
            } catch (sfException $e) {
                echo $e->getMessage().PHP_EOL;
                continue;
            }

            if ($detail->getProduitLibelle() !== $detail_aout->getProduitLibelle()
                || $detail->getCodeInao() !== $detail_aout->getCodeInao()) {
                echo 'UPDATE : '. $hash_prec . ' :'.PHP_EOL;
                echo 'update : '. $detail_aout->getProduitLibelle() . ' -> '.$detail->getProduitLibelle().PHP_EOL;
                $detail_aout->produit_libelle = $detail->getProduitLibelle();
                $detail_aout->code_inao = $detail->getCodeInao();
            }

            $drm_aout->update();
            echo 'Fin UPDATE : '. $hash_prec . ' :'.PHP_EOL.PHP_EOL;
        }

        echo PHP_EOL.'Parcours des non trouvés :'.PHP_EOL;

        foreach ($unknowns as $detail) {
            echo 'On cherche : '.$detail->getProduitLibelle().PHP_EOL;
            foreach ($drm_aout->getProduitsDetails() as &$aout_detail) {
                if ($aout_detail->denomination_complementaire == $detail->denomination_complementaire
                    && $aout_detail->getCepage()->getHash() == $detail->getCepage()->getHash()
                    && $aout_detail->tav == $detail->tav) {

                    echo 'CANDIDAT: '.PHP_EOL;
                    echo $aout_detail->denomination_complementaire." == \n".$detail->denomination_complementaire."\n";
                    echo $aout_detail->getCepage()->getHash().' == '."\n".$detail->getCepage()->getHash().PHP_EOL;
                    echo $aout_detail->tav.' == '.$detail->tav.PHP_EOL;

                    echo 'update : '. $aout_detail->getProduitLibelle() . ' -> ' . $detail->getProduitLibelle().PHP_EOL;
                    echo 'hash : '. $aout_detail->getHash() . ' -> ' . $detail->getHash().PHP_EOL;
                    echo PHP_EOL;

                    $aout_detail->produit_libelle = $detail->getProduitLibelle();
                    $aout_detail->code_inao = $detail->getCodeInao();
                    $drm_aout->update();

                    continue;
                }
            }
            echo PHP_EOL.'On passe au suivant'.PHP_EOL;
        }

        $drm_aout->save();
    }

}
