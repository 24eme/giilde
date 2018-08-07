<?php

class DAEEtablissementChoiceForm extends EtablissementChoiceForm {
        
    public function configure()
    {
        parent::configure();
        $this->configureFamilles(array(EtablissementFamilles::FAMILLE_NEGOCIANT));
    }
  
}