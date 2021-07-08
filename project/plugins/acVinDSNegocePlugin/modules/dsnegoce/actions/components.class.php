<?php

class dsnegoceComponents extends sfComponents {

  public function executeFormEtablissementChoice() {
    if (!$this->form) {
      $this->form = new DSNegoceEtablissementChoiceForm('INTERPRO-declaration', array('identifiant' => $this->identifiant));
    }
  }

}
