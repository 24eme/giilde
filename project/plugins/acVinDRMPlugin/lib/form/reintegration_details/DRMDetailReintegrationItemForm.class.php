<?php

class DRMDetailReintegrationItemForm extends DRMESDetailsItemForm {

    public function __construct(\acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();

        if($this->getObject()->getDate()) {
            $this->setDefault('identifiant', $this->getObject()->getDateFr());
        }
    }

    public function configure() {
        $this->setWidget('identifiant', new bsWidgetFormInputDate());
        $this->setWidget('volume', new bsWidgetFormInputFloat(array(), array('autocomplete' => 'off')));

        $this->setValidator('identifiant', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));
        $this->setValidator('volume', new sfValidatorNumber(array('required' => true, 'min' => 0), array('min' => "La saisie d'un nombre négatif est interdite")));


        $this->widgetSchema->setNameFormat(sprintf("%s[%%s]", $this->getFormName()));
    }

    public function getFormName() {

        return "drm_detail_reintegration_item";
    }

    public function getPostValidatorClass() {

        return 'DRMDetailReintegrationItemValidator';
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

}
