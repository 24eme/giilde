<?php

class daeComponents extends sfComponents {

  public function executeFormEtablissementChoice() {
    if (!$this->form) {
      $this->form = new DAEEtablissementChoiceForm('INTERPRO-declaration', array('identifiant' => $this->identifiant));
    }
  }

}
