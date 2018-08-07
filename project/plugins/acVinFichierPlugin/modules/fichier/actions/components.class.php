<?php

class fichierComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        if(class_exists("DRClient")) {
    	    $this->dr = DRClient::getInstance()->findByArgs($this->etablissement->identifiant, $this->campagne);   
        }
    }

    public function executeFormEtablissementChoice() {
        if (!$this->identifiant) {
            $this->identifiant = null;
        }
        $autofocus = array();
        if ($this->autofocus) {
            $autofocus = array('autofocus' => 'autofocus');
        }

        if (!$this->form) {
            $this->form = new FichierEtablissementChoiceForm('INTERPRO-declaration', array('identifiant' => $this->identifiant), $autofocus);
        }
    }

}
