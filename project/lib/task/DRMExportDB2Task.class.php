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
        new sfCommandOption('periode_max', null, sfCommandOption::PARAMETER_REQUIRED, 'limit on a max periode', null),
        new sfCommandOption('file_origine', null, sfCommandOption::PARAMETER_REQUIRED, "fichier origine tel que généré précédemment", null),
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

        if (isset($options['file_origine'])) {
          $csv = file($options['file_origine']);
          return $export->setFacture($csv);
        }

        $csv = $export->export(MouvementfactureFacturationView::getInstance()->getMouvementsAll(0), $options['periode_max']);

        $noLignes = true;
        foreach($csv as $file => $lignes) {
            if(count($lignes)) {
                $noLignes = false;
                break;
            }
        }

        if($noLignes) {
            return;
        }

        $origineFilename = null;
        foreach($csv as $file => $lignes) {
            $fileName = $arguments['path']."/".$date."_".preg_replace("/.+\./", "", $file);
            file_put_contents($fileName, implode("\r\n", $lignes));
            if($file == "09.ORIGINES") {
                $origineFilename = $fileName;
            }
        }
        $export->setFacture(explode("\r\n", file_get_contents($origineFilename)));
    }
}
