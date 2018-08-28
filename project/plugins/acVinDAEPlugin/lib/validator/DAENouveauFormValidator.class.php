<?php
class DAENouveauFormValidator extends sfValidatorBase {
	
	protected $etablissement;
	
	public function __construct($etablissement, $options = array(), $messages = array())
	{
		$this->etablissement = $etablissement;
		parent::__construct($options, $messages);
	}

    public function configure($options = array(), $messages = array()) {
        $this->addMessage('doublon', "Les ventes type négociants/unions en région sont déjà connues via vos contrats interprofessionnels et ne nécessitent pas de nouvelle saisie");
    }

    protected function doClean($values) {

    	$errorSchema = new sfValidatorErrorSchema($this);
    	$hasError = false;
    	
    	
    	if ($values['type_acheteur_key'] == 'NEGOCIANT_REGION' && $this->etablissement->isViticulteur()) {
    		$errorSchema->addError(new sfValidatorError($this, 'doublon'), 'type_acheteur_key');
    		$hasError = true;
    	}
    	
    	
    	if ($hasError) {
    		throw new sfValidatorErrorSchema($this, $errorSchema);
    	}
        return $values;
    }

}