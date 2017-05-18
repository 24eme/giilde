<?php

class SV12UpdateForm  extends acCouchdbForm {


    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        $defaults = array();
        foreach ($doc->getContrats() as $value) {
                $defaults[$value->getKey()] = $value->volume;
    	}

        parent::__construct($doc,$defaults, $options, $CSRFSecret);
   }


    public function configure() {
    	foreach ($this->getDocument()->getContrats() as $value) {
                $this->setWidget($value->getKey(), new bsWidgetFormInputFloat(array()));
                $this->setValidator($value->getKey(), new sfValidatorNumber(array('required' => false, 'min' => 0), array('min' => "La saisie d'un nombre négatif est interdite")));
    	}

        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
        $this->widgetSchema->setNameFormat('sv12[%s]');
    }

    public function doUpdateObject() {
        $values = $this->values;
        foreach ($values as $num_contrat => $volume) {
            if($this->getDocument()->contrats->exist($num_contrat))
            {
                $this->getDocument()->contrats[$num_contrat]->volume = $volume;
            }
        }
        $this->getDocument()->updateTotaux();
    }
}
