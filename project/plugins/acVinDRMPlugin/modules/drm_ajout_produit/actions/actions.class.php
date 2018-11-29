<?php

class drm_ajout_produitActions extends drmGeneriqueActions {

    public function executeChoixProduits(sfWebRequest $request) {
        $this->initSocieteAndEtablissementPrincipal();
        $this->drm = $this->getRoute()->getDRM();
        $this->certificationsProduits = $this->drm->declaration->getProduitsDetailsByCertifications(true);
        $this->form = new DRMProduitsChoiceForm($this->drm);
        $this->initDeleteForm();
        $this->hasRegimeCrd = $this->drm->getEtablissement()->hasRegimeCrd();

        $this->showPopupRegimeCrd = $request->getParameter('popupCRD') || !$this->hasRegimeCrd;
        $this->isTeledeclarationMode = $this->isTeledeclarationDrm();
        if ($this->hasRegimeCrd && $request->isMethod(sfRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->form->save();
                if($request->hasParameter('add_produit')) {
                    $this->redirect($this->redirect('drm_choix_produit', array('sf_subject' => $this->drm, 'add_produit' => $request->getParameter('add_produit'))));
                }

                return $this->redirect('drm_edition', $this->form->getObject());
            }

            return $this->redirect('drm_edition', $this->form->getObject());
        }

        if($request->hasParameter('add_produit')) {
            $this->formAddProduitsByCertification = new DRMAddProduitByCertificationForm($this->drm, array('produitFilter' => $request->getParameter('add_produit')));
        }

        if ($this->showPopupRegimeCrd){
           $this->crdRegimeForm = new DRMCrdRegimeChoiceForm($this->drm);
        }
    }

    public function executeChoixAjoutProduits(sfWebRequest $request) {
        $this->initSocieteAndEtablissementPrincipal();
        $this->drm = $this->getRoute()->getDRM();
        $this->form = new DRMAddProduitByCertificationForm($this->drm, array('produitFilter' => $request->getParameter('add_produit')));
        if ($request->isMethod(sfRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->form->save();
            }
        }
        $this->redirect('drm_choix_produit', $this->form->getDrm());
    }

}
