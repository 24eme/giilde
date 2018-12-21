<?php

class DRMEDIChoixCreationForm extends sfForm {

    private $periode = null;
    private $identifiant = null;
    private $onlyEdi = false;

    public function __construct($defaults = array(), $options = array(), $CSRFSecret = null) {
        $this->periode = (isset($options['periode']))? $options['periode'] : null ;
        $this->identifiant = (isset($options['identifiant']))? $options['identifiant'] : null ;
        $this->onlyEdi = (isset($options['only-edi']))? $options['only-edi'] : false;
        parent::__construct($defaults, $options, $CSRFSecret);
    }

    public function configure() {
        if(!$this->onlyEdi){
          $this->setWidget('type_creation', new sfWidgetFormChoice(array('multiple' => false, 'expanded' => true, 'choices' => $this->getTypesCreation())));
          $this->widgetSchema->setLabel('type_creation','Type de création : ');
          $this->setValidator('type_creation', new sfValidatorChoice(array('multiple' => false,  'required' => true, 'choices' => array_keys($this->getTypesCreation()))));
          $this->widgetSchema['type_creation']->setDefault(DRMClient::DRM_CREATION_VIERGE);
        }
        $options = ($this->onlyEdi && ($this->onlyEdi == 'application/x-www-form-urlencoded'))? array('needs_multipart' => false) : array();
        $this->setWidget('edi-file', new sfWidgetFormInputFile($options));
        $this->widgetSchema->setLabel('edi-file', "Fichier d'import de votre logiciel tiers");
        $this->setValidator('edi-file' , new ValidatorImportCsv(array('file_path' => sfConfig::get('sf_data_dir') . '/upload')));

        $this->widgetSchema->setNameFormat('drmChoixCreation[%s]');
        if($this->onlyEdi){
          $this->widgetSchema->setNameFormat('%s');
        }
    }

    public function getTypesCreation() {
        return DRMClient::$typesCreationLibelles;
    }


}
