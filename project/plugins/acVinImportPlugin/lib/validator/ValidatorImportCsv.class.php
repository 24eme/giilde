<?php

class ValidatorImportCsv extends sfValidatorFile
{
  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('file_path');
    $this->addMessage('invalid_file', "Le fichier fourni ne peut être lu");
    $options['required'] = true;
    parent::configure($options, $messages);
    $this->setMessage('mime_types', "Le fichier fourni doit être un CSV");
    $this->options['mime_types'] = array('text/plain', 'text/csv','text/comma-separated-values','application/csv', 'application/octet-stream');

  }

  protected function doClean($value)
  { 
	parent::doClean($value);
    $csvValidated = new CsvValidatedFile($value['name'], 'text/csv', $value['tmp_name'], $value['size'], $this->getOption('file_path'));
    
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
