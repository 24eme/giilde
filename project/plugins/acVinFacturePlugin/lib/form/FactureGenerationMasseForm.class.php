<?php

class FactureGenerationMasseForm extends FactureGenerationForm {

    const TYPE_DOCUMENT_TOUS = "TOUS";

    public static $types_document = array(self::TYPE_DOCUMENT_TOUS => "Tous",
                     FactureClient::FACTURE_LIGNE_ORIGINE_TYPE_DRM => "DRM",
                     FactureClient::FACTURE_LIGNE_ORIGINE_TYPE_SV12_NEGO => "SV12NEGO",
                     FactureClient::FACTURE_LIGNE_ORIGINE_TYPE_SV12 => "SV12");

    public function __construct($defaults = array(), $options = array(), $CSRFSecret = null) {
        $defaults['date_facturation'] = date('d/m/Y');
        $defaults['type_document'] = FactureClient::FACTURE_LIGNE_ORIGINE_TYPE_DRM;
        parent::__construct($defaults, $options, $CSRFSecret);
    }

    public function configure() {

        $this->setWidget('regions', new sfWidgetFormChoice(array('choices' => $this->getRegions(), 'multiple' => true, 'expanded' => true, 'default' => array_keys($this->getRegions()))));
        $this->setWidget('type_document', new sfWidgetFormChoice(array('choices' => $this->getTypesDocument())));
        $this->setWidget('seuil', new sfWidgetFormInput(array(), array('autocomplete' => 'off')));
        $this->setWidget('date_mouvement', new sfWidgetFormInput(array('default' => date('d/m/Y'))));
        $this->setWidget('date_facturation', new sfWidgetFormInput(array('default' => date('d/m/Y'))));
        $this->setWidget('message_communication', new sfWidgetFormTextarea());

        $this->setValidator('regions', new sfValidatorChoice(array('choices' => array_keys($this->getRegions()), 'multiple' => true, 'required' => false)));
        $this->setValidator('type_document', new sfValidatorChoice(array('choices' => array_keys($this->getTypesDocument()), 'required' => true)));
	$this->setValidator('seuil', new sfValidatorNumber(array('required' => false)));
        $this->setValidator('date_mouvement', new sfValidatorString());
        $this->setValidator('date_facturation', new sfValidatorString());
        $this->setValidator('message_communication', new sfValidatorString(array('required' => false)));

        $this->widgetSchema->setLabels(array(
            'regions' => 'Sélectionner des régions à facturer :',
            'seuil_facture' => "Seuil de facturation :",
            'seuil_avoir' => 'Seuil des avoirs :',
            'date_mouvements' => 'Dernière date de prise en compte des mouvements :',
            'date_facturation' => 'Date de facturation :',
            'message_communication' => 'Cadre de communication :'
        ));
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
        $this->widgetSchema->setNameFormat('facture_generation[%s]');
    }

    public function getRegions() {
        return EtablissementClient::getRegionsWithoutHorsInterpro();
    }

    public function getTypesDocument() {

        return self::$types_document;
    }

}
