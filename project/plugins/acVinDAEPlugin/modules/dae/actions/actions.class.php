<?php
class daeActions extends sfActions {

  public function executeIndex(sfWebRequest $request) {

    $this->form = new DAEEtablissementChoiceForm('INTERPRO-declaration');

       if ($request->isMethod(sfWebRequest::POST)) {
	 $this->form->bind($request->getParameter($this->form->getName()));
	 if ($this->form->isValid()) {
	   return $this->redirect('dae_etablissement', $this->form->getEtablissement());
	 }
       }
    }


     public function executeMonEspace(sfWebRequest $request) {

        $this->etablissement = $this->getRoute()->getEtablissement();
÷      }

}
