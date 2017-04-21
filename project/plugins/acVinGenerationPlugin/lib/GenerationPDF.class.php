<?php

class GenerationPDF extends GenerationAbstract {

    protected $generation = null;
  protected $config = null;

  function __construct(Generation $g, $config = null, $options = null) {
    $this->generation = $g;
    $this->config = $config;
    $this->options = $options;
  }

  private function doesPDFsExist($pdffiles) {
    foreach($pdffiles as $file) {
      if (!file_exists($file)) {
	throw new sfException("$file does not exist :(");
      }
    }
  }

  function concatenatePDFs($pdffiles) {
    if (!count($pdffiles)) {
      return null;
    }
    $fileres = rand().".pdf";
    $this->doesPDFsExist($pdffiles);
    file_put_contents("/tmp/$fileres.sh", '/usr/bin/pdftk "'.implode('" "', $pdffiles).'" cat output "'.$fileres.'"');
    $str = system('bash /tmp/'.$fileres.'.sh 2>&1');
    if ($str) {
        throw new sfException('pdftk returned an error: '.$str);
    }
    return $fileres;
  }

  function generateAPDFForAPageId($pdf, $pageid) {
    if (!count($pdf))
      return null;
    $fileres = rand().".pdf";
    file_put_contents("/tmp/$fileres.sh", '/usr/bin/pdftk "'.$pdf.'" cat '.intval($pageid).' output "'.$fileres.'"');
    $str = system('bash /tmp/'.$fileres.'.sh 2>&1');
    if ($str) {
        throw new sfException('pdftk returned an error: '.$str);
    }
    if (!file_exists($fileres) || !filesize($fileres)) {
      throw new sfException("wrong result file $fileres extracting page # $pageid from $pdf");
    }
    return $fileres;
  }

  function concatenatePDFsForAPageId($pdfs, $pageid) {
    $files = array();
    foreach($pdfs as $pdf) {
      $f = $this->generateAPDFForAPageId($pdf, $pageid);
      if ($f) $files[] = $f;
    }
    $fileres = $this->concatenatePDFs($files);
    if (!file_exists($fileres) || !filesize($fileres)) {
      throw new sfException("$fileres (for pages # $pageid) not generated");
    }
    $this->cleanFiles($files);
    return $fileres;
  }

  private function generatePDFFiles($pdfs) {
    $files = array();
    foreach($pdfs as $pdf) {
      if ($pdf) {
	$file = $pdf->getPDFFile();
	if (!file_exists($file)) {
	  throw new sfException("$file doesn't exist");
	}
	$files[] = $file;
      }
    }
    return $files;
  }


  function generatePDFAndConcatenateThem($pdfs) {
    return $this->concatenatePDFs($this->generatePDFFiles($pdfs));
  }

  function generatePDFGroupByPageNumberAndConcatenateThem($pdfs, $pagenumber) {
    $files = $this->generatePDFFiles($pdfs);
    $filesbypage = array();
    for($i = 1 ; $i <= $pagenumber ; $i++) {
      $filesbypage[] = $this->concatenatePDFsForAPageId($files, $i);
    }
    $this->cleanFiles($files);
    $res = $this->concatenatePDFs($filesbypage);
    $this->cleanFiles($filesbypage);
    if (!file_exists($res)) {
      throw new sfException("$res doesn't exist");
    }
    return $res;
  }

  protected function cleanFiles($files) {
    foreach ($files as $f) {
      unlink($f);
    }
  }

  public function generatePDF() {
    if (!$this->generation)
      throw new sfException('Object generation should not be null');

    $this->generation->setStatut(GenerationClient::GENERATION_STATUT_ENCOURS);
    $this->generation->save();

    $pdfs = array();

    if($this->generation->exist('documents_regenerate') && count($this->generation->documents_regenerate)) {
      $this->preRegeneratePDF();
      if(count($this->generation->documents_regenerate) != count($this->generation->documents)) {

          throw new sfException("La regénération ne c'est pas bien passé");
      }
      $this->generation->remove('documents_regenerate');
      $this->generation->save();
    }

    if (!count($this->generation->documents) || $this->generation->exist('pregeneration_needed')) {
      $this->generation->add('pregeneration_needed',1);
      $this->preGeneratePDF();
      $this->generation->remove('pregeneration_needed');
      $this->generation->save();
    }
    foreach ($this->generation->documents as $docid) {
      $pdf = $this->generatePDFForADocumentID($docid);
      if (!isset($pdfs[$pdf->getNbPages()]))
	      $pdfs[$pdf->getNbPages()] = array();
      array_push($pdfs[$pdf->getNbPages()], $pdf);
    }
    $pages = array();
    foreach ($pdfs as $page => $pdfspage) {
      if (!count($pdfspage))
        continue;
      if (isset($this->options['page'.$page.'perpage']) && $this->options['page'.$page.'perpage']) {
	$origin = $this->generatePDFGroupByPageNumberAndConcatenateThem($pdfspage, $page);
	if ($origin)
	  $this->generation->add('fichiers')->add($this->publishFile($origin, $this->generation->date_emission.'-'.$page),
						$this->getDocumentName().' de '.$page.' page(s) trié par numéro de page');
      }else{
        $origin = $this->generatePDFAndConcatenateThem($pdfspage);
        if ($origin)
            $this->generation->add('fichiers')->add($this->publishFile($origin, $this->generation->date_emission.'-'.$page),
						$this->getDocumentName().' de '.$page.' page(s)');
      }
    }
    $this->cleanFiles($pages);
    $this->generation->save();
    if ($this->postGeneratePDF()) {
        $this->generation->save();
    }
    $this->generation->setStatut(GenerationClient::GENERATION_STATUT_GENERE);
    $this->generation->save();
  }

  public function generate() {

      $this->generatePDF();
  }

  protected function getDocumentName() {
    throw new sfException('should be called from the parent class');
  }
  protected function generatePDFForADocumentID($docid) {
    throw new sfException('should be called from the parent class');
  }

  function preGeneratePDF() { }

  function postGeneratePDF() { return false; }

  function preRegeneratePDF() { }

}
