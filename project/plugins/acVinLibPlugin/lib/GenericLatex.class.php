<?php

class GenericLatex {

  const OUTPUT_TYPE_PDF = 'pdf';
  const OUTPUT_TYPE_LATEX = 'latex';

  public function getLatexFile() {
    $fn = $this->getLatexFileName();
    $leFichier = fopen($fn, "w");
    if (!$leFichier) {
      throw new sfException("Cannot write on ".$fn);
    }
    fwrite($leFichier, $this->getLatexFileContents());
    fclose($leFichier);
    $retour = chmod($fn,intval('0660',8));
    return $fn;
  }

  private function getLatexDestinationDir() {
    return sfConfig::get('sf_root_dir')."/data/latex/";
  }

  protected function getTEXWorkingDir() {
    return "/tmp/";
  }

  public function generatePDF() {
    $cmdCompileLatex = '/usr/bin/latexmk -pdf -output-directory="'.$this->getTEXWorkingDir().'" -interaction=nonstopmode "'.$this->getLatexFile().'" 2>&1';
    exec($cmdCompileLatex, $output);
    $pdfpath = $this->getLatexFileNameWithoutExtention().'.pdf';
    if (!file_exists($pdfpath)) {
      throw new sfException("pdf not created ($pdfpath): ".implode(',', $output));
    }
    return $pdfpath;
  }

  private function cleanPDF() {
    $file = $this->getLatexFileNameWithoutExtention();
    @unlink($file.'.aux');
    @unlink($file.'.log');
    @unlink($file.'.pdf');
    @unlink($file.'.tex');
    @unlink($file.'.synctex.gz');
    @unlink($file.'.fls');
    @unlink($file.'.fdb_latexmk');
  }

  public function getPDFFile() {
    $filename = $this->getLatexDestinationDir().$this->getPublicFileName();
    if(file_exists($filename))
      return $filename;
    $tmpfile = $this->generatePDF();
    if (!file_exists($tmpfile)) {
      throw new sfException("pdf not created :(");
    }
    if (!rename($tmpfile, $filename)) {
      throw new sfException("not possible to rename $tmpfile to $filename");
    }
    $this->cleanPDF();
    return $filename;
  }

  public function getPDFFileContents() {
    return file_get_contents($this->getPDFFile());
  }

  public function echoPDFWithHTTPHeader() {
    $attachement = 'attachment; filename='.$this->getPublicFileName();
    header("Content-Type: application/pdf\n");
    header("content-length: ".filesize($this->getPDFFile())."\n");
    header("Content-Transfer-Encoding: binary\n");
    header("Cache-Control: public\n");
    header("Expires: 0\n");
    header("Content-disposition: $attachement\n\n");
    echo $this->getPDFFileContents();
  }

  public function echoLatexWithHTTPHeader() {
    $attachement = 'attachment; filename='.$this->getPublicFileName('.tex');
    header("content-type: application/latex\n");
    header("content-length: ".filesize($this->getLatexFile())."\n");
    header("content-disposition: $attachement\n\n");
    echo $this->getLatexFileContents();
  }

  public function echoWithHTTPHeader($type = 'pdf') {
    if ($type == self::OUTPUT_TYPE_LATEX)
      return $this->echoLatexWithHTTPHeader();
    return $this->echoPDFWithHTTPHeader();
  }

  public function getLatexFileName() {
    return $this->getLatexFileNameWithoutExtention().'.tex';
  }

  public function getNbPages() {
    throw new sfException("need to be implemented upstream");
  }

  public function getLatexFileNameWithoutExtention() {
    throw new sfException("need to be implemented upstream");
  }

  public function getLatexFileContents() {
    throw new sfException("need to be implemented upstream");
  }

  public function getPublicFileName($extention = '.pdf') {
    throw new sfException("need to be implemented upstream");
  }

}
