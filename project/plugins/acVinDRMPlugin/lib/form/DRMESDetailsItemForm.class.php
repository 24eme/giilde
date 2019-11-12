<?php

abstract class DRMESDetailsItemForm extends acCouchdbObjectForm {

    protected $isTeledeclarationMode = null;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        $this->isTeledeclarationMode = $options['isTeledeclarationMode'];
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {

      $this->setWidget('identifiant', new bsWidgetFormChoice(array('choices' => $this->getIdentifiantChoices()), array('class' => 'autocomplete')));
      $this->setWidget('volume', new bsWidgetFormInputFloat(array(), array('autocomplete' => 'off')));


      $this->setWidget('numero_document', new bsWidgetFormInput());
      $this->setWidget('type_document', new bsWidgetFormChoice(array('choices' => array_merge(array("" => ""), $this->getTypeDocumentsChoices()))));


      $this->setValidator('identifiant', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getIdentifiantChoices()))));
      $this->setValidator('volume', new sfValidatorNumber(array('required' => true, 'min' => 0), array('min' => "La saisie d'un nombre négatif est interdite")));

      $this->setValidator('numero_document', new sfValidatorString(array('required' => false)));
      $this->setValidator('type_document', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getTypeDocumentsChoices()))));

      $post_validator_class = $this->getPostValidatorClass();
      $this->validatorSchema->setPostValidator(new $post_validator_class());
      $this->widgetSchema->setNameFormat(sprintf("%s[%%s]", $this->getFormName()));
      $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
    }

    public function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
        if($this->getObject()->exist('date_enlevement')) {
            $date = new DateTime($this->getObject()->getDocument()->getDate());
            $this->getObject()->date_enlevement = $date->format('Y-m-d');
        }
    }

    public function getProduitDetail() {

        return $this->getObject()->getProduitDetail();
    }

    public function getTypeDocumentsChoices() {
        return DRMClient::$drm_documents_daccompagnement;
    }

    public abstract function getFormName();

    public function getIdentifiantChoices() {

        return array();
    }

    public function getPostValidatorClass() {

        return 'DRMDetailItemValidator';
    }

}
