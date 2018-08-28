<?php
class DAENouveauForm extends acCouchdbObjectForm 
{
    protected $_choices_produits;
    protected $_choices_labels;
    protected $_choices_mentions;
    protected $_choices_millesimes;
    protected $_choices_types;
    protected $_choices_destinations;
    protected $_choices_contenances;

    public function configure() {
    	
    	$this->setWidget('produit_key', new bsWidgetFormChoice(array('choices' => $this->getProduits()), array('class' => 'select2 form-control')));
    	$this->setWidget('label_key', new bsWidgetFormChoice(array('choices' => $this->getLabels()), array('class' => 'select2 form-control')));
    	$this->setWidget('mention_key', new bsWidgetFormChoice(array('choices' => $this->getMentions()), array('class' => 'select2 form-control')));
    	$this->setWidget('millesime', new bsWidgetFormInput());
    	$this->setWidget('type_acheteur_key', new bsWidgetFormChoice(array('choices' => $this->getTypes()), array('class' => 'select2 form-control')));
    	$this->setWidget('destination_key', new bsWidgetFormChoice(array('choices' => $this->getDestinations()), array('class' => 'select2 form-control')));
    	$this->setWidget('contenance_key', new bsWidgetFormChoice(array('choices' => $this->getContenances()), array('class' => 'select2 form-control')));
    	$this->setWidget('quantite', new bsWidgetFormInputFloat());
    	$this->setWidget('prix_unitaire', new bsWidgetFormInputFloat());
    	$this->setWidget('no_accises_acheteur', new bsWidgetFormInput());
    	$this->setWidget('nom_acheteur', new bsWidgetFormInput());
    	$this->setWidget('label_libelle', new bsWidgetFormInput());
    	$this->widgetSchema->setLabels(array(
    			'produit_key' => 'Produit',
    			'label_key' => 'Label',
    			'label_libelle' => 'Préciser',
    			'mention_key' => 'Mention',
    			'millesime' => 'Millesime',
    			'type_acheteur_key' => 'Type',
    			'destination_key' => 'Pays',
    			'quantite' => 'Quantité',
    			'contenance_key' => 'Condi.',
    			'prix_unitaire' => 'Prix unitaire',
    			'no_accises_acheteur' => 'Accises',
    			'nom_acheteur' => 'Nom'
    	));
        $this->setValidator('produit_key', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getProduits()))));
        $this->setValidator('label_key', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getLabels()))));
        $this->setValidator('mention_key', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getMentions()))));
        $this->setValidator('millesime', new sfValidatorRegex(array('required' => false, 'pattern' => '/^[0-9]{4}$/')));
        $this->setValidator('type_acheteur_key', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getTypes()))));
        $this->setValidator('destination_key', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getDestinations()))));
        $this->setValidator('quantite', new sfValidatorNumber(array('required' => true, 'min' => 0.1), array('min' => 'La quantité ne peut pas être nul')));
        $this->setValidator('contenance_key', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getContenances()))));
        $this->setValidator('prix_unitaire', new sfValidatorNumber(array('required' => true, 'min' => 0.1), array('min' => 'Le prix ne peut pas être nul')));
        $this->setValidator('no_accises_acheteur', new sfValidatorRegex(array('required' => false, 'pattern' => '/^[0-9A-Za-z]{13}$/')));
        $this->setValidator('nom_acheteur', new sfValidatorString(array('required' => false)));
        $this->setValidator('label_libelle', new sfValidatorString(array('required' => false)));
        
        $this->widgetSchema->setNameFormat('dae[%s]');
        
        $this->validatorSchema->setPostValidator(new DAENouveauFormValidator($this->getObject()->getEtablissementObject()));
    }

    public function getProduits() {
        if (is_null($this->_choices_produits)) {
            $this->_choices_produits = array_merge(array(null => null), $this->getObject()->getProduitsConfig());
        }
        return $this->_choices_produits;
    }

    public function getLabels() {
        if (is_null($this->_choices_labels)) {
            $this->_choices_labels = array_merge(array(null => null), $this->getObject()->getLabels());
        }
        return $this->_choices_labels;
    }

    public function getMentions() {
        if (is_null($this->_choices_mentions)) {
            $this->_choices_mentions = array_merge(array(null => null), $this->getObject()->getMentions());
        }
        return $this->_choices_mentions;
    }

    public function getMillesimes() {
        if (is_null($this->_choices_millesimes)) {
        	$this->_choices_millesimes = array();
        	$date = new DateTime();
        	$annee = $date->format('Y');
        	if ($date->format('m') < 8) {
        		$annee--;
        	}
        	$stop = $annee - 15;
        	while ($annee >= $stop) {
        		$this->_choices_millesimes[$annee] = '' . $annee;
        		$annee--;
        	}
        }
        return $this->_choices_millesimes;
    }

    public function getTypes() {
        if (is_null($this->_choices_types)) {
            $this->_choices_types = array_merge(array(null => null), $this->getObject()->getTypes()); 
        }
        return $this->_choices_types;
    }

    public function getDestinations() {
        if (is_null($this->_choices_destinations)) {
        	$destinationChoicesWidget = new bsWidgetFormI18nChoiceCountry(array('culture' => 'fr', 'add_empty' => true));
        	$destinationChoices = $destinationChoicesWidget->getChoices();
        	unset($destinationChoices['FR']);
        	unset($destinationChoices['']);
            $this->_choices_destinations = array_merge(array(null => null, 'FR' => 'France'), $destinationChoices);
        }
        return $this->_choices_destinations;
    }

    public function getContenances() {
        if (is_null($this->_choices_contenances)) {
            $this->_choices_contenances = $this->getObject()->getContenances();
        }
        return $this->_choices_contenances;
    }

    protected function updateDefaultsFromObject() {
    	parent::updateDefaultsFromObject();
    	if ($this->getObject()->isNew()) {
	        $defaults = $this->getDefaults();
	        $defaults['contenance_key'] = 'HL'; 
	        $defaults['destination_key'] = 'FR'; 
	        $defaults['label_key'] = 'CONV';
	        $this->setDefaults($defaults);
    	}
    }
    
    protected function doUpdateObject($values) {
    	$produits = $this->getProduits();
    	$labels = $this->getLabels();
    	$types = $this->getTypes();
    	$destinations = $this->getDestinations();
    	$contenances = $this->getContenances();
    	
    	$values['produit_libelle'] = (isset($produits[$values['produit_key']]))? $produits[$values['produit_key']] : null;
    	$values['label_libelle'] = (isset($labels[$values['label_key']]) && !$values['label_libelle'])? $labels[$values['label_key']] : $values['label_libelle'];
    	$values['mention_libelle'] = (isset($labels[$values['mention_key']]))? $labels[$values['mention_key']] : null;
    	$values['type_acheteur_libelle'] = (isset($types[$values['type_acheteur_key']]))? $types[$values['type_acheteur_key']] : null;
    	$values['destination_libelle'] = (isset($destinations[$values['destination_key']]))? $destinations[$values['destination_key']] : null;
    	$values['contenance_libelle'] = (isset($contenances[$values['contenance_key']]))? $contenances[$values['contenance_key']] : null;
    	
    	parent::doUpdateObject($values);
    	
    	$this->getObject()->calculateDatas();
    }
}