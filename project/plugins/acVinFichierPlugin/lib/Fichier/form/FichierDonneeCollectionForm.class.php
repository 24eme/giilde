<?php

class FichierDonneeCollectionForm extends sfForm implements FormBindableInterface
{
    protected $donnees;
    protected $produits;
	public $virgin_object = null;

	public function __construct($produits, $donnees, $defaults = array(), $options = array(), $CSRFSecret = null)
  	{
        $this->donnees = $donnees;
		$this->produits = $produits;
        parent::__construct($defaults, $options, $CSRFSecret);
    }

   	public function configure()
    {
		if (count($this->donnees) == 0) {
			$this->virgin_object = $this->donnees->add();
		}
    	$produits = ConfigurationClient::getCurrent()->getProduits();
        foreach ($this->donnees as $k => $donnee) {
            $form = new FichierDonneeForm($this->produits, $donnee, $this->getOptions());
            $this->embedForm($k, $form);
        }
    }

    public function bind(array $taintedValues = null, array $taintedFiles = null)
    {
    	foreach ($this->embeddedForms as $key => $form) {
    		if(!array_key_exists($key, $taintedValues)) {
    			$this->unEmbedForm($key);
    		}
    	}
    	foreach($taintedValues as $key => $values) {
    		if(!is_array($values) || array_key_exists($key, $this->embeddedForms)) {
    			continue;
    		}
    		$this->embedForm($key, new FichierDonneeForm($this->produits, $this->donnees->add()));
    	}
    }
    
    public function unEmbedForm($key)
    {
    	unset($this->widgetSchema[$key]);
    	unset($this->validatorSchema[$key]);
    	unset($this->embeddedForms[$key]);
    	$this->donnees->remove($key);
    }
    
    public function update($values)
    {
    	foreach ($this->embeddedForms as $key => $form) {
    		$form->update($values[$key]);
    	}
    }

	public function offsetUnset($offset)
	{
		parent::offsetUnset($offset);
		if (!is_null($this->virgin_object)) {
			$this->virgin_object->delete();
		}
    }
}
