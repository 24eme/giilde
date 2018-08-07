<?php

class FichierForm extends BaseForm
{
	protected $fichier;

	public function __construct($fichier, $defaults = array(), $options = array(), $CSRFSecret = null)
	{
		$this->fichier = $fichier;
		if (!$this->fichier->isNew()) {
			$defaults['libelle'] = $this->fichier->getLibelle();
			$defaults['date_depot'] = $this->fichier->getDateDepotFormat();
			$defaults['visibilite'] = ($this->fichier->getVisibilite())? 1 : null;
		} else {
			$defaults['date_depot'] = date('d/m/Y');
			$defaults['visibilite'] = 1;
		}
		$this->options = $options;
		parent::__construct($defaults, $options, $CSRFSecret);
	}

     public function configure() {

     	$this->setWidgets(array(
     		'file' => new sfWidgetFormInputFile(array('label' => 'Document')),
     		'libelle' => new sfWidgetFormInputText(),
     		'date_depot' => new sfWidgetFormInput(array(), array("data-date-defaultDate" => date('Y-m-d'))),
     		'visibilite' => new sfWidgetFormInputCheckbox()
     	));
     	$fileRequired = ($this->fichier->isNew())? true : false;
     	$this->setValidators(array(
     		'file' => new sfValidatorFile(array('required' => $fileRequired, 'path' => sfConfig::get('sf_cache_dir'))),
     		'libelle' => new sfValidatorString(array('required' => true)),
     		'date_depot' => new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)),
     		'visibilite' => new ValidatorBoolean()
     	));

     	$this->widgetSchema->setLabels(array(
     		'file' => ($fileRequired)? 'Fichier*' : 'Fichier',
     		'libelle' => 'Libellé du document*',
     		'date_depot' => 'Date dépôt*',
     		'visibilite' => 'Visible par le déclarant'
     	));

        $this->widgetSchema->setNameFormat('fichier[%s]');
    }

    public function save() {
    	$file = $this->getValue('file');
    	if (!$file && $this->fichier->isNew()) {
    		throw new sfException("Une erreur lors de l'upload est survenue");
    	}
    	if ($file && !$file->isSaved()) {
    		$file->save();
    	}
		if ($this->getValue('libelle') !== null) {
    		$this->fichier->setLibelle($this->getValue('libelle'));
		}
		if ($this->getValue('date_depot') !== null) {
    		$this->fichier->setDateDepot($this->getValue('date_depot'));
		}
		if ($this->getValue('visibilite') !== null) {
    		$this->fichier->setVisibilite(($this->getValue('visibilite'))? 1 : 0);
		}
    	$isNew = false;
    	if ($this->fichier->isNew()) {
    		$this->fichier->save();
    		$isNew = true;
    	}
    	if ($file) {
	    	try {
	    		$this->fichier->storeFichier($file->getSavedName());
	    	} catch (sfException $e) {
	    		if ($isNew) {
	    			$this->fichier->remove();
	    		}
	    		throw new sfException($e);
	    	}
    		unlink($file->getSavedName());
    	}

    	$this->fichier->save();
    	return $this->fichier;
    }


}
