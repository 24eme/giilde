<?php

class subventionComponents extends sfComponents {


    public function executeFormEtablissementChoice() {
        if (!$this->identifiant) {
            $this->identifiant = null;
        }
        $autofocus = array();
        if ($this->autofocus) {
            $autofocus = array('autofocus' => 'autofocus');
        }

        if (!$this->form) {
            $this->form = new SubventionEtablissementChoiceForm('INTERPRO-declaration', array('identifiant' => $this->identifiant), $autofocus);
        }
    }


}
