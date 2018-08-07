<?php

class DAEEtablissementCampagneForm extends BaseForm {

    private $isTeledeclarationMode = false;

    public function configure() {
        $list = $this->getChoiceCampagnes();
        $this->setWidgets(array(
            'periode' => new bsWidgetFormChoice(array('choices' => $list, 'default' => $this->default_campagne), array('class' => 'select2 select2SubmitOnChange form-control', 'style' => 'width: 18rem;'))
        ));
        $this->setValidators(array(
            'periode' => new sfValidatorChoice(array('required' => true, 'choices' => $list))
        ));
        $this->widgetSchema->setLabels(array(
            'periode' => "Historique"
        ));
        $this->widgetSchema->setNameFormat('%s');
    }

    public function __construct($identifiantEtablissement, $defaultCampagne, $isTeledeclarationMode = false) {
        $this->etablissement_id = $identifiantEtablissement;
        $this->default_campagne = $defaultCampagne;
        $this->isTeledeclarationMode = $isTeledeclarationMode;
        return parent::__construct();
    }

    private function getChoiceCampagnes() {
        $campagnes = DAEClient::getInstance()->listCampagneByEtablissementId($this->etablissement_id);
        return $campagnes;
    }

}
