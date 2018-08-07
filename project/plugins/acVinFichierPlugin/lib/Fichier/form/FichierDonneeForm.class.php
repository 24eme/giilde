<?php

class FichierDonneeForm extends acCouchdbObjectForm
{
	protected $produits;
	
	public function __construct($produits, acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
		$this->produits = $produits;
        parent::__construct($object, $options, $CSRFSecret);
        $this->getValidatorSchema()->setOption('allow_extra_fields', true);
        $this->getDocable()->remove();
        $this->getDocable()->disabledRevisionVerification();
    }

    public function configure() {
        $this->setWidgets(array(
            'produit' => new bsWidgetFormChoice(array('choices' => $this->produits)),
            'complement' => new bsWidgetFormInput(),
            'categorie' => new bsWidgetFormChoice(array('choices' => sfConfig::get('app_dr_categories'))),
            'valeur' => new bsWidgetFormInput(),
            'tiers' => new WidgetEtablissement(array('interpro_id' => 'INTERPRO-declaration')),
            'bailleur' => new WidgetEtablissement(array('interpro_id' => 'INTERPRO-declaration'))
        ));

        $this->setValidators(array(
            'produit' => new sfValidatorChoice(array('choices' => array_keys($this->produits))),
            'complement' => new sfValidatorString(array('required' => false)),
            'categorie' => new sfValidatorChoice(array('choices' => array_keys(sfConfig::get('app_dr_categories')))),
            'valeur' => new sfValidatorString(array('required' => false)),
            'tiers' => new ValidatorEtablissement(array('required' => false)),
            'bailleur' => new ValidatorEtablissement(array('required' => false)),
        ));

        $this->widgetSchema->setNameFormat('[%s]');
    }
}
