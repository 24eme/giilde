<?php

class AnnuaireAjoutForm extends acCouchdbObjectForm {

    protected $type;
    protected $etablissements;
    protected $societeChoice = false;

    public function __construct(acCouchdbJson $object, $type = null, $etablissements = null, $options = array(), $CSRFSecret = null) {
        $this->type = $type;
        $this->etablissements = $etablissements;
        if ($this->etablissements && (count($this->etablissements) > 1 ) && ($this->type != AnnuaireClient::ANNUAIRE_COMMERICAUX_KEY)) {
            $this->societeChoice = true;
        }
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
        $this->setWidget('type', new sfWidgetFormChoice(array('choices' => $this->getTypes())));
        $this->setValidator('type', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getTypes()))));
        $this->getWidget('type')->setLabel("Type*:");

        $this->setWidget('tiers', new sfWidgetFormInputText());
        $this->setValidator('tiers', new sfValidatorString(array('required' => true)));
        $this->getWidget('tiers')->setLabel("Identifiant*:");


        if ($this->societeChoice) {
//            $etablissementsList = array('0' => 'Choisir un établissement');
//            $etablissementsList = array_merge($etablissementsList,$this->getEtablissements());
            $etablissementsList = $this->getEtablissements();
            $this->setWidget('etablissementChoice', new sfWidgetFormChoice(array('expanded' => true, 'choices' => $etablissementsList)));
            $this->setValidator('etablissementChoice', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getEtablissements()))));
            $this->widgetSchema->setLabel('etablissementChoice', 'Etablissement*:');

        }
        $this->validatorSchema->setPostValidator(new AnnuaireAjoutValidator($this->societeChoice));
        $this->widgetSchema->setNameFormat('annuaire_ajout[%s]');
    }

    protected function getTypes() {
        return AnnuaireClient::getAnnuaireTypes(true);
    }

    public function doUpdateObject($values) {
        if ($this->societeChoice) {
            $tiers = EtablissementClient::getInstance()->retrieveById($values['etablissementChoice']);
        } else {
            $societe = $values['societe'];
            $tiers = array_pop($this->etablissements)->etablissement;
        }
        $libelle = ($tiers->nom) ? $tiers->nom : $tiers->raison_sociale;

        $type = AnnuaireClient::ANNUAIRE_RECOLTANTS_KEY;
        if (array_key_exists('type', $values)) {
            $type = $values['type'];
        }
        $entree = $this->getObject()->getOrAdd($type)->add($tiers->_id, $libelle);
        $this->values['etablissementObject'] = $tiers;
    }

    public function getSociete() {
        $values = $this->getValues();
        if (isset($values['societe'])) {
            return $values['societe'];
        }
        return null;
    }

    public function getEtablissements() {
        $etablissements = array();
        foreach ($this->etablissements as $key => $etablissementObj) {
            $etablissements[$etablissementObj->etablissement->identifiant] = $etablissementObj->etablissement->nom;
        }
        return $etablissements;
    }

    public function hasSocieteChoice() {
        return $this->societeChoice;
    }

}
