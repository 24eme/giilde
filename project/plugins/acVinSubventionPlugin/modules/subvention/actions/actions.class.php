<?php

class subventionActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
      $this->operation_en_cours = SubventionConfiguration::getInstance()->getOperationEnCours();
      $this->subventions = SubventionClient::getInstance()->findByAllSortedByDate();
    }

    public function executeEtablissement(sfWebRequest $request) {
        $this->isTeledeclarationMode = $this->isTeledeclarationSubvention();
        $this->operation_en_cours = SubventionConfiguration::getInstance()->getOperationEnCours();
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->subvention = SubventionClient::getInstance()->findByEtablissementAndOperation($this->etablissement->identifiant, $this->operation_en_cours);

    }

    public function executeEtablissementSelection(sfWebRequest $request) {

            $form = new SubventionEtablissementChoiceForm('INTERPRO-declaration');
            $form->bind($request->getParameter($form->getName()));
            if (!$form->isValid()) {

                return $this->redirect('subvention');
            }

            return $this->redirect('subvention_etablissement', $form->getEtablissement());
    }

    public function executeCreation(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();

        $subvention = SubventionClient::getInstance()->createDoc($etablissement->identifiant, $request->getParameter('operation'));
        $subvention->save();

        return $this->redirect('subvention_infos', $subvention);
    }

    public function executeInfos(sfWebRequest $request) {
        $this->subvention = $this->getRoute()->getSubvention();

        $this->form = new SubventionsGenericForm($this->subvention,'infos');

        if (!$request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }
        $this->form->bind($request->getParameter($this->form->getName()));

      	if (!$this->form->isValid()) {
      		return sfView::SUCCESS;
      	}
        $this->form->save();

        $this->redirect('subvention_dossier', $this->subvention);

    }

    public function executeEngagements(sfWebRequest $request) {
        $this->subvention = $this->getRoute()->getSubvention();
        $this->form = new SubventionEngagementsForm($this->subvention);

        if (!$request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect($this->generateUrl('subvention_validation', $this->subvention));
    }

    public function executeValidation(sfWebRequest $request) {
        $this->subvention = $this->getRoute()->getSubvention();
        $this->form = new SubventionValidationForm($this->subvention);

        if (!$request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect($this->generateUrl('subvention_confirmation', $this->subvention));
    }

    public function executeConfirmation(sfWebRequest $request) {
        $this->subvention = $this->getRoute()->getSubvention();
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->subvention = $this->getRoute()->getSubvention();
        $this->isTeledeclarationMode = $this->isTeledeclarationSubvention();
        $this->formValidationInterpro = ($this->getUser()->hasCredential(myUser::CREDENTIAL_ADMIN))? new SubventionsGenericForm($this->subvention, 'approbations') : null;
        if (!$request->isMethod(sfWebRequest::POST) || !$this->formValidationInterpro) {

            return sfView::SUCCESS;
        }

        $this->formValidationInterpro->bind($request->getParameter($this->formValidationInterpro->getName()));

        if (!$this->formValidationInterpro->isValid()) {
            return sfView::SUCCESS;
        }

        $this->formValidationInterpro->save();
        $statut = $request->getParameter('statut',null);
        if($statut){
          $this->subvention->validateInterpro($statut);
          $this->subvention->save();
        }

        return $this->redirect($this->generateUrl('subvention_visualisation', $this->subvention));
    }

    public function executeDossier(sfWebRequest $request) {

        $this->subvention = $this->getRoute()->getSubvention();
        $this->form = new SubventionDossierForm($this->subvention);

        if(!$this->subvention->hasXls() && !$this->subvention->hasDefaultXlsPath()){
          throw new sfException("Il n'existe pas de document pour cette opération : ".$this->subvention->operation);
        }

        if (!$request->isMethod(sfWebRequest::POST)) {
      		return sfView::SUCCESS;
      	}
      	$this->form->bind($request->getParameter($this->form->getName()), $request->getFiles($this->form->getName()));

      	if (!$this->form->isValid()) {
      		return sfView::SUCCESS;
      	}
        $this->form->save();

        $this->redirect('subvention_engagements', array('identifiant' => $this->subvention->identifiant,'operation' => $this->subvention->operation));

    }

    public function executeReouvrir(sfWebRequest $request) {

        $this->subvention = $this->getRoute()->getSubvention();
        $this->subvention->reouvrir();
        $this->subvention->save();
        $this->redirect('subvention_dossier', array('identifiant' => $this->subvention->identifiant,'operation' => $this->subvention->operation));

    }

    public function executeXls(sfWebRequest $request) {

      $this->subvention = $this->getRoute()->getSubvention();
      $this->setLayout(false);
      $path = $this->subvention->getXlsPath();
      $this->getResponse()->setHttpHeader('Content-disposition', 'attachment; filename="' . $this->subvention->getXlsPublicName() . '"');
      $this->getResponse()->setHttpHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //  $this->getResponse()->setHttpHeader('Content-Length', filesize($path));
      $this->getResponse()->setHttpHeader('Pragma', '');
      $this->getResponse()->setHttpHeader('Cache-Control', 'public');
      $this->getResponse()->setHttpHeader('Expires', '0');
      return $this->renderText(file_get_contents($path));
    }

    public function executeLatex(sfWebRequest $request) {
        $this->setLayout(false);
        $this->subvention = $this->getRoute()->getSubvention();
        $this->forward404Unless($this->subvention);
        $latex = new SubventionLatex($this->subvention);
        $latex->echoWithHTTPHeader($request->getParameter('type'));
        exit;
    }

    public function executeZip(sfWebRequest $request) {
        $this->setLayout(false);
        $this->subvention = $this->getRoute()->getSubvention();
        $this->forward404Unless($this->subvention);
        $name = $this->subvention->_id.'_'.$this->subvention->_rev;
        $target = '/tmp/'.$name;
        $zipname = $name.'.zip';
        exec('mkdir -p '.$target.'/');
        $latex = new SubventionLatex($this->subvention);
        $pdf = $latex->generatePDF();
        rename($pdf, $target.'/'.$name.'.pdf');
        file_put_contents($target.'/'.$this->subvention->getXlsPublicName(), file_get_contents($this->subvention->getXlsPath()));
        exec('zip -j -r '.$target.$zipname.' '.$target.'/');
        $this->getResponse()->clearHttpHeaders();
        $this->getResponse()->setContentType('application/force-download');
        $this->getResponse()->setHttpHeader('Content-Disposition', 'attachment; filename="' . $zipname .'"');
        $this->getResponse()->setHttpHeader('Content-Type', 'application/zip');
        $this->getResponse()->setHttpHeader('Pragma', '');
        $this->getResponse()->setHttpHeader('Cache-Control', 'public');
        $this->getResponse()->setHttpHeader('Expires', '0');
        $this->getResponse()->setContent(file_get_contents($target.$zipname));
        $this->getResponse()->send();
    }

    // debrayage
    public function executeConnexion(sfWebRequest $request) {

        //  $this->redirect403IfIsTeledeclaration();
        $this->etablissement = $this->getRoute()->getEtablissement();
        $societe = $this->etablissement->getSociete();

        $this->getUser()->usurpationOn($societe->identifiant, $request->getReferer());
        $this->redirect('subvention_etablissement', array('identifiant' => $this->etablissement->identifiant));
    }

    protected function isTeledeclarationSubvention() {
      return $this->getUser()->hasTeledeclaration();
    }
}
