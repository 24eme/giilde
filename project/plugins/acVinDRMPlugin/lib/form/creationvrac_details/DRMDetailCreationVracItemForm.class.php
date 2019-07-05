<?php

class DRMDetailCreationVracItemForm extends acCouchdbObjectForm {

    protected $isTeledeclarationMode = null;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        $this->isTeledeclarationMode = $options['isTeledeclarationMode'];
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {

        $this->setWidget('numero_archive', new sfWidgetFormInputText());
        $this->setWidget('acheteur', new WidgetEtablissement(array('interpro_id' => 'INTERPRO-declaration', 'familles' => array(EtablissementFamilles::FAMILLE_NEGOCIANT, EtablissementFamilles::FAMILLE_NEGOCIANT_PUR))));
        $this->setWidget('prixhl', new bsWidgetFormInputFloat(array(), array('autocomplete' => 'off')));
        $this->setWidget('volume', new bsWidgetFormInputFloat(array(), array('autocomplete' => 'off')));

        $this->setValidator('numero_archive', new sfValidatorString(array('required' => false)));
        $this->setValidator('acheteur', new ValidatorEtablissement(array('required' => true)));
        $this->setValidator('prixhl', new sfValidatorNumber(array('required' => false, 'min' => 0), array('min' => "La saisie d'un nombre négatif est interdite")));
        $this->setValidator('volume', new sfValidatorNumber(array('required' => true, 'min' => 0), array('min' => "La saisie d'un nombre négatif est interdite")));


        $this->widgetSchema->setNameFormat(sprintf("%s[%%s]", $this->getFormName()));
    }

    public function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

    public function getProduitDetail() {

        return $this->getObject()->getProduitDetail();
    }

    public function getFormName() {

        return "drm_detail_creationvrac_item";
    }

}
