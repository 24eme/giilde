<?php

class ValidatorImportSpreadsheet extends sfValidatorFile
{
    public static $csvMimes = array ('text/plain', 'text/csv','text/comma-separated-values');
    public static $excelMimes = array ('application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    
  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('file_path');
    $this->addMessage('invalid_file', "Le fichier fourni ne peut être lu");
    $options['required'] = true;
    parent::configure($options, $messages);
    $this->setMessage('mime_types', "Le fichier fourni doit être au format CSV ou XLS/XLSX");
    $this->options['mime_types'] = array_merge(self::$csvMimes, self::$excelMimes, array('application/octet-stream'));
  }

  protected function doClean($value)
  { 
	parent::doClean($value);
	
	if (in_array($value['type'], self::$excelMimes)) {
	    $csvFilename = tempnam(sys_get_temp_dir(), uniqid());
	    exec('xlsx2csv -d ";" '.$value['tmp_name'].' > '.$csvFilename);
	} else {
	    $csvFilename = $value['tmp_name'];
	}
	
    $csvValidated = new CsvValidatedFile($value['name'], 'text/csv', $csvFilename, $value['size'], $this->getOption('file_path'));
    
    $errorSchema = new sfValidatorErrorSchema($this);
    
    $csvValidated->save();

    try {
      $csv = new CsvFile($csvValidated->getSavedName());
    }catch(Exception $e) {
      $csv = null;
      $errorSchema->addError(new sfValidatorError($this, $e->getMessage()));
      throw new sfValidatorErrorSchema($this, $errorSchema);
    }

    $csvValidated->setCsv($csv);
    return $csvValidated;
  }

}