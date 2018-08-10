<?php

class maintenanceDRMDetailConfigUpdateTask extends sfBaseTask
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
        $this->name             = 'drm-detail-config-update';
        $this->briefDescription = '';
        $this->detailedDescription = '';
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $drm = DRMClient::getInstance()->find($arguments['doc_id']);

        if(!$drm) {
            return;
        }

        foreach($drm->getProduitsDetails() as $detail) {
            foreach ($detail->getParent()->getConfigDetails() as $detailConfigCat => $detailConfig) {
                foreach ($detailConfig as $detailConfigKey => $detailConfigNode) {
                    $detail->getOrAdd($detailConfigCat)->add($detailConfigKey);
                    if ($detailConfigNode->hasDetails()) {
                        $detail->getOrAdd($detailConfigCat)->add($detailConfigKey . "_details");
                    }
                }
            }
        }
	$drm->forceModified();
        $drm->save();
    }

}
