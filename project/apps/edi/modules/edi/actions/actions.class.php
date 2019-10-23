<?php

class ediActions extends sfActions {

    protected $save = true;

    public function executeDrmCreationEdi(sfWebRequest $request) {

        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            $this->response->setStatusCode('401');

            return sfView::NONE;
        }

      $only_edi = true;
      if($request->getContentType()){
        $only_edi = $request->getContentType();
      }
      $this->creationEdiDrmForm = new DRMEDIChoixCreationForm(array(), array('identifiant' => null, 'periode' => null,
                                                                          'only-edi' => $only_edi));
      if ($request->isMethod(sfWebRequest::POST) && ($request->getContentType() == 'multipart/form-data')) {
          $this->creationEdiDrmForm->bind(array(),array('edi-file' => $request->getFiles('edi-file')));
          if ($this->creationEdiDrmForm->isValid()) {
             $md5 = $this->creationEdiDrmForm->getValue('edi-file')->getMd5();
             $csvFilePath = sfConfig::get('sf_data_dir') . '/upload/' . $md5;
             return $this->importEdiFile($csvFilePath);
           }
      }

      if ($request->isMethod(sfWebRequest::POST) && ($request->getContentType() == 'application/x-www-form-urlencoded')){
          $file_data = array();
          $this->parse_raw_http_request($file_data);
          $file_content = $file_data['edi-file'];
          $uniqId = uniqId();
          $csvFileTmpPath = sfConfig::get('sf_data_dir') . '/upload/' . $uniqId;
          file_put_contents($csvFileTmpPath,$file_content);
          $csvFile = new CsvFile($csvFileTmpPath);
          $result = $this->importEdiFile($csvFileTmpPath);
          unlink($csvFileTmpPath);
          return $result;
      }

      if ($request->isMethod(sfWebRequest::POST) && ($request->getContentType() == 'text/csv')){
          $csvFileTmpPath = sfConfig::get('sf_data_dir') . '/upload/' . uniqId();
          $this->save = false;
          file_put_contents($csvFileTmpPath, $request->getContent());
            try {
                $csvFile = new CsvFile($csvFileTmpPath);
            } catch (Exception $e) {
                echo "Error;;;".$e->getMessage()."\n";
            }
          $result = $this->importEdiFile($csvFileTmpPath);
          unlink($csvFileTmpPath);
          return $result;
      }

      if($request->getContentType() == 'application/x-www-form-urlencoded'){
        $this->enctype = "application/x-www-form-urlencoded";
      }
    }

    public function importEdiFile($csvFilePath){

            $this->drmCsvEdi = new DRMImportCsvEdi($csvFilePath, null, true);
            if(!$this->save){ $this->drmCsvEdi->setNoSave(true); }
            $drm = $this->drmCsvEdi->getDrm();
            $this->identifiant = $drm->getIdentifiant();
            $this->periode = $drm->getPeriode();
            $this->drmCsvEdi->checkCSV();
            $csvArrayErreurs = $this->drmCsvEdi->getCsvArrayErreurs();

            //CSV RESPONSE

            $filename = sprintf("DRM_EDI_%s_%s.csv",$this->identifiant,$this->periode);
            $handle = fopen('php://memory', 'r+');

            foreach ($csvArrayErreurs as $csvRow) {
                fputcsv($handle, $csvRow,';');
            }


            if(!$this->drmCsvEdi->getCsvDoc()->hasErreurs(CSVDRMClient::LEVEL_ERROR)){
              if(($drm->etape == DRMClient::ETAPE_VALIDATION_EDI) && !$drm->isNew()){
                $drm->delete();
                $drm = DRMClient::getInstance()->findOrCreateFromEdiByIdentifiantAndPeriode($this->identifiant,$this->periode, true);
                $this->drmCsvEdi = new DRMImportCsvEdi($csvFilePath, $drm, true);
              }
              if($this->save){
                $this->drmCsvEdi->importCSV(true);
              }
              $url = sfConfig::get('app_routing_context_production_host').sfContext::getInstance()->getRouting()->generate('drm_redirect_etape', array('identifiant' => $this->identifiant , 'periode_version' => $this->periode));
              fputcsv($handle, array('OK',$url,'',''),";");
            }

            rewind($handle);
            $content = stream_get_contents($handle);
            fclose($handle);

            unlink($csvFilePath);

            $this->setLayout(false);
            $attachement = "attachment; filename=" . $filename ;
            $this->response->setContent($content);
            $this->response->setContentType('text/csv');
            $this->response->setHttpHeader('Content-Disposition', $attachement);
            $this->response->setContent($content);
            return sfView::NONE;
  }

  public function parse_raw_http_request(array &$a_data)
 {

  $input = file_get_contents('php://input');

  preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
  $boundary = $matches[1];

  $a_blocks = preg_split("/-+$boundary/", $input);
  array_pop($a_blocks);

  foreach ($a_blocks as $id => $block)
  {
    if (empty($block))
      continue;
    if (strpos($block, 'application/octet-stream') !== FALSE)
    {
      preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
    }
    else
    {
      preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
    }
    $a_data[$matches[1]] = $matches[2];
  }
}

}
