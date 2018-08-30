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
        $campagnes = DRMClient::getInstance()->listCampagneByEtablissementId($this->etablissement_id);
        if($this->isTeledeclarationMode && !DRMConfiguration::getInstance()->isCampagneListeMinimale()){
            $campagnes = array();
            $currentCampagne = ConfigurationClient::getInstance()->getCurrentCampagne();
            $campages[$currentCampagne] = $currentCampagne;
            for($i = date('Y'); $i > date('Y') - 4; $i--) {
                $campagne = ($i - 1)."-".$i;
                $campagnes[$campagne] = $campagne;
            }
        }

        return array_merge(array('-1' => 'les derniers mois'), $campagnes);
    }

}
