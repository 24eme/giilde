<?php

class DRMDetailReintegrationTemplateForm extends DRMDetailReintegrationForm {

    protected $isTeledeclarationMode = null;

    public function __construct(acCouchdbJson $details, $defaults = array(), $options = array(), $CSRFSecret = null) {
        $this->isTeledeclarationMode = $options['isTeledeclarationMode'];
        parent::__construct($details, $defaults, $options, $CSRFSecret);
        unset($this['_revision']);
    }

    public function configure() {
        $newReintegration = DRMESDetailReintegration::freeInstance($this->details->getDocument());
        $item_form_class = $this->getFormItemClass();
        $this->embedForm('var---nbItem---', new $item_form_class($this->details->addDetail($newReintegration),array('isTeledeclarationMode' => $this->isTeledeclarationMode)));
        $this->widgetSchema->setNameFormat(sprintf("%s[%%s]", $this->getFormName()));
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
    }

    public function getFormTemplate() {

        return $this['var---nbItem---'];
    }

}
