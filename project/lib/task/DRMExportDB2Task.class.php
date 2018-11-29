<?php

class DRMExportDB2Task extends sfBaseTask
{

  protected function configure()
  {

    $this->addArguments(array(
        new sfCommandArgument('path', sfCommandArgument::REQUIRED, "Chemin ou générer les fichiers"),
    ));
    $this->addOptions(array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        // add your own options here
    ));

    $this->namespace        = 'drm';
    $this->name             = 'export-db2';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [DRMExportDB2|INFO] task does things.
Call it with:

    [php symfony DRMExportDB2|INFO]
EOF;
  }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $date = date('YmdHis');
        $export = new ExportMouvementsDRMDB2();
        $csv = $export->export(MouvementfactureFacturationView::getInstance()->getMouvementsAll(0));

        foreach($csv as $file => $lignes) {
            file_put_contents($arguments['path']."/".$date."_".preg_replace("/.+\./", "", $file), implode("\r\n", $lignes));
        }
    }
}
