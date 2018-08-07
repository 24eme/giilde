<?php

class FichierDonneesForm extends acCouchdbObjectForm
{
	public function configure()
    {
		$this->embedForm('donnees', new FichierDonneeCollectionForm($this->getProduits(), $this->getObject()->donnees, array(), $this->getOptions()));
        $this->widgetSchema->setNameFormat('fichier[%s]');
    }

    public function getProduits()
    {
    	$produits = ConfigurationClient::getCurrent()->getProduits();
    	$result = array();
    	foreach ($produits as $hash => $produit) {
    		$result[str_replace('/declaration/', '', $hash)] = $produit->getLibelleFormat();
    	}
    	return $result;
    }

    public function bind(array $taintedValues = null, array $taintedFiles = null)
    {
        foreach ($this->embeddedForms as $key => $form) {
            if($form instanceof FormBindableInterface) {
                $form->bind($taintedValues[$key], $taintedFiles[$key]);
                $this->updateEmbedForm($key, $form);
            }
        }
        parent::bind($taintedValues, $taintedFiles);
    }

    public function updateEmbedForm($name, $form) {
        $this->widgetSchema[$name] = $form->getWidgetSchema();
        $this->validatorSchema[$name] = $form->getValidatorSchema();
    }

    public function getFormTemplateDrLigne()
    {
    	$object = $this->getObject()->donnees->add();
    	$form_embed = new FichierDonneeForm($this->getProduits(), $object);
    	$form = new FichierDonneeCollectionTemplateForm($this, 'donnees', $form_embed, 'var---nbItem---');
    
    	return $form->getFormTemplate();
    }
}
