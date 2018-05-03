<?php

class drm_crdsActions extends drmGeneriqueActions {

    public function executeCrd(sfWebRequest $request) {
        $this->initSocieteAndEtablissementPrincipal();
        $this->drm = $this->getRoute()->getDRM();
        $this->drm->crdsInitDefault();
        $this->isUsurpationMode = $this->isUsurpationMode();
        $this->crdsForms = new DRMCrdsForm($this->drm,array("isUsurpationMode" => $this->isUsurpationMode));
        $this->isTeledeclarationMode = $this->isTeledeclarationDrm();
        $this->initDeleteForm();
        $this->showPopupRegimeCrd = $request->getParameter('popupCRD') || !$this->drm->getEtablissement()->hasRegimeCrd();
        if ($request->getParameter('add_crd')) {
            $this->addCrdRegime = $request->getParameter('add_crd');
            $this->addCrdGenre = $request->getParameter('genre');
            $this->addCrdForm = new DRMAddCrdTypeForm($this->drm,array('genre' => $this->addCrdGenre));
        }

        if ($request->isMethod(sfRequest::POST)) {
            $this->crdsForms->bind($request->getParameter($this->crdsForms->getName()));
            if ($this->crdsForms->isValid()) {
                $this->crdsForms->save();

                if ($this->addCrdRegime) {
                    $this->redirect('drm_crd', array('sf_subject' => $this->crdsForms->getObject(), 'add_crd' => $this->addCrdRegime, 'genre' => $this->addCrdGenre));
                }

                $this->redirect('drm_redirect_etape', $this->crdsForms->getObject());
            }
        }
        if ($this->showPopupRegimeCrd) {
                  $this->crdRegimeForm = new DRMCrdRegimeChoiceForm($this->drm);
        }
    }

    public function executeAjoutTypeCrd(sfWebRequest $request) {
        $this->initSocieteAndEtablissementPrincipal();
        $this->drm = $this->getRoute()->getDRM();
        $this->form = new DRMAddCrdTypeForm($this->drm);
        if ($request->isMethod(sfRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->form->save();
                $this->redirect('drm_crd', $this->form->getObject());
            }
            $regimes = $this->form->getRegimeCrds();
            $this->regime = $regimes[0];
        }
    }

    public function executeChoixRegimeCrd(sfWebRequest $request) {
        $this->initSocieteAndEtablissementPrincipal();
        $drm = $this->getRoute()->getDRM();
        $etablissement = $drm->getEtablissement();
        if (!$this->isTeledeclarationDrm()) {
            $this->redirect403IfIsNotTeledeclaration();
        }
        $retour = $request->getParameter('retour', null);
        $this->form = new DRMCrdRegimeChoiceForm($drm, array('regime' => $request->getParameter('drmAddTypeForm[regime]')));
        if ($request->isMethod(sfRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->form->save();
              if ($retour == 'crds') {
                  $this->redirect('drm_crd', $this->form->getObject());
              } else {
                  $this->redirect('drm_choix_produit', $this->form->getObject());
              }
            }
        }
    }

}
