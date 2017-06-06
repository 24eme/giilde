<?php

class DRMEtablissementCampagneForm extends BaseForm {

    private $isTeledeclarationMode = false;

    public function configure() {
        $list = $this->getChoiceCampagnes();
        $this->setWidgets(array(
            'campagne' => new bsWidgetFormChoice(array('choices' => $list, 'default' => $this->default_campagne), array('class' => 'select2 select2SubmitOnChange', 'style' => 'width: auto;'))
        ));
        $this->setValidators(array(
            'campagne' => new sfValidatorChoice(array('required' => true, 'choices' => $list))
        ));
        $this->widgetSchema->setLabels(array(
            'campagne' => "Consulter l'historique pour : "
        ));
        $this->widgetSchema->setNameFormat('etablissementcampagne[%s]');
    }

    public function __construct($identifiantEtablissement, $defaultCampagne, $isTeledeclarationMode = false) {
        $this->etablissement_id = $identifiantEtablissement;
        $this->default_campagne = $defaultCampagne;
        $this->isTeledeclarationMode = $isTeledeclarationMode;
        return parent::__construct();
    }

    private function getChoiceCampagnes() {

        $campagnes = ($this->isTeledeclarationMode) ? array('2016-2017' => '2016-2017','2015-2016' => '2015-2016', '2014-2015' => '2014-2015') : DRMClient::getInstance()->listCampagneByEtablissementId($this->etablissement_id);

        return array_merge(array('-1' => 'les derniers mois'), $campagnes);
    }

}
