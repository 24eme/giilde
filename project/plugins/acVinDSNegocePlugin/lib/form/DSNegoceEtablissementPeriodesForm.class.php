<?php

class DSNegoceEtablissementPeriodesForm extends BaseForm {

    private $isTeledeclarationMode = false;

    public function configure() {
        $list = $this->getChoicePeriodes();
        $this->setWidgets(array(
            'date' => new bsWidgetFormChoice(array('choices' => $list, 'default' => $this->default_date), array('class' => 'select2 select2SubmitOnChange form-control', 'style' => 'width: 18rem;'))
        ));
        $this->setValidators(array(
            'date' => new sfValidatorChoice(array('required' => true, 'choices' => $list))
        ));
        $this->widgetSchema->setLabels(array(
            'date' => "Historique"
        ));
        $this->widgetSchema->setNameFormat('%s');
    }

    public function __construct($identifiantEtablissement, $defaultDate, $isTeledeclarationMode = false) {
        $this->etablissement_id = $identifiantEtablissement;
        $this->default_date = $defaultDate;
        $this->isTeledeclarationMode = $isTeledeclarationMode;
        return parent::__construct();
    }

    private function getChoicePeriodes() {
        $dates = DSNegoceClient::getInstance()->listPeriodesByEtablissementId($this->etablissement_id);
        return $dates;
    }

}
