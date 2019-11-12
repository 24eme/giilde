<?php

class DRMDetailReintegrationForm extends DRMESDetailsForm {

    public function __construct(acCouchdbJson $details, $defaults = array(), $options = array(), $CSRFSecret = null) {
        parent::__construct($details, $defaults, $options, $CSRFSecret);
    }

    public function configure() {
        parent::configure();
    }

    public function getFormName() {

        return 'drm_detail_reintegration';
    }

    public function getFormItemClass() {

        return 'DRMDetailReintegrationItemForm';
    }

    public function getFormTemplateClass() {

        return 'DRMDetailReintegrationTemplateForm';
    }

    public function getModelNode(){

        return 'DRMESDetailReintegration';
    }


    public function bind(array $taintedValues = null, array $taintedFiles = null) {
        parent::bind($taintedValues,$taintedFiles);
    }

    public function update(){
        parent::update();
    }
}
