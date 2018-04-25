<?php

class drm_editionActions extends drmGeneriqueActions {

    public function executeSaisieMouvements(sfWebRequest $request) {
         set_time_limit(-1);
        if(!($drmdetailtype = $this->getRequest()->getParameter('details'))) {
            $this->drm = $this->getRoute()->getDRM();

            if(!$this->drm->isDouaneType(DRMClient::TYPE_DRM_SUSPENDU) && $this->drm->isDouaneType(DRMClient::TYPE_DRM_ACQUITTE)) {
                return $this->redirect('drm_edition_details', array('sf_subject' => $this->drm, 'details' =>  DRM::DETAILS_KEY_ACQUITTE));
            }

            return $this->redirect('drm_edition_details', array('sf_subject' => $this->drm, 'details' =>  DRM::DETAILS_KEY_SUSPENDU));
        }
        $this->isTeledeclarationMode = $this->isTeledeclarationDrm();
        $this->init();
        $this->initSocieteAndEtablissementPrincipal();
        $this->loadFavoris();
        $this->initDeleteForm();
        $this->formFavoris = new DRMFavorisForm($this->drm,array('details' => $this->getRequest()->getParameter('details')));
        $this->formValidation = new DRMMouvementsValidationForm($this->drm, array('isTeledeclarationMode' => $this->isTeledeclarationMode));
        $this->detailsNodes = $this->config->get($drmdetailtype);
        $this->saisieSuspendu = ($drmdetailtype == str_replace('SAISIE_','',DRMClient::ETAPE_SAISIE_SUSPENDU));
        if ($request->isMethod(sfRequest::POST)) {
            $this->formValidation->bind($request->getParameter($this->formValidation->getName()));
            if ($this->formValidation->isValid()) {
                $this->formValidation->save();
                if($this->detailsKey == DRM::DETAILS_KEY_SUSPENDU && $this->drm->isDouaneType(DRMClient::TYPE_DRM_ACQUITTE)) {
                    $this->redirect('drm_edition_details', array('sf_subject' => $this->drm, 'details' =>  DRM::DETAILS_KEY_ACQUITTE));
                }
                if ($this->isTeledeclarationMode) {
                    $this->redirect('drm_crd', $this->formValidation->getObject());
                } else {
                    $this->redirect('drm_validation', $this->formValidation->getObject());
                }
            }
        }
    }
    


    public function executeLibelles(sfWebRequest $request) {
    	$this->isTeledeclarationMode = $this->isTeledeclarationDrm();
    	$this->init();
    	if ($this->isTeledeclarationMode || !$this->drm->exist('transmission_douane') || !$this->drm->transmission_douane) {
    		$this->redirect404();
    	}
    	
    	$this->form = new DRMLibellesForm($this->drm);
    	
    	if ($request->isMethod(sfRequest::POST)) {
        	$this->form->bind($request->getParameter($this->form->getName()));
        	if ($this->form->isValid()) {
                $drm = $this->form->save();
                $this->redirect('drm_visualisation', $drm);
        	}
    	}
    }

    public function executeDetail(sfWebRequest $request) {
        $this->init();
        $this->initSocieteAndEtablissementPrincipal();
        $this->isTeledeclarationMode = $this->isTeledeclarationDrm();
        $this->loadFavoris();
        $this->initDeleteForm();
        $this->formFavoris = new DRMFavorisForm($this->drm, array('details' => $this->getRequest()->getParameter('details')));
        $this->formValidation = new DRMMouvementsValidationForm($this->drm, array('isTeledeclarationMode' => $this->isTeledeclarationMode));
        $this->detail = $this->getRoute()->getDRMDetail();
        $this->detailsNodes = $this->detail->getConfig();
        $this->setTemplate('saisieMouvements');
    }

    public function executeUpdate(sfWebRequest $request) {
        $this->init();

        $this->form = new DRMDetailForm($this->getRoute()->getDRMDetail());
        $this->form->bind($request->getParameter($this->form->getName()));

        if ($this->form->isValid()) {
            $this->form->save();
            if ($request->isXmlHttpRequest()) {
                return $this->renderText(json_encode(array(
                            "success" => true,
                            "content" => "",
                            "document" => array("id" => $this->drm->get('_id'),
                                "revision" => $this->drm->get('_rev'))
                )));
            } else {
                $this->redirect('drm_edition', $this->config_lieu);
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->renderText(json_encode(array("success" => false, "content" => $this->getPartial('drm_recap/itemFormErrors', array('form' => $this->form)))));
        } else {
            $this->setTemplate('saisieMouvements');
        }
    }

    public function executeProduitAjout(sfWebRequest $request) {
        $this->init();
        $this->isTeledeclarationMode = $this->isTeledeclarationDrm();
        $this->form = new DRMProduitForm($this->drm, $this->drm->declaration->getConfig(), $this->detailsKey, $this->isTeledeclarationMode);
        $this->form->bind($request->getParameter($this->form->getName()));
        if ($this->form->isValid()) {
            $detail = $this->form->addProduit();
            $this->drm->save();
            if ($request->isXmlHttpRequest()) {
                return $this->renderText(json_encode(array(
                            "success" => true,
                            "content" => $this->getComponent('drm_edition', 'itemForm', array('config' => $this->config,
                                'detail' => $detail,
                                'active' => false,
                                'favoris' => $this->drm->getAllFavoris(),
                                'etablissement' => $this->drm->getEtablissement(),
                                'isTeledeclarationMode' => $this->isTeledeclarationMode)),
                            "produit" => array("old_hash" => $detail->getCepage()->getHash(), "hash" => $detail->getHash(), "libelle" => sprintf("%s (%s)", $detail->getLibelle("%g% %a% %m% %l% %co% %ce%"), $detail->getCepage()->getConfig()->getCodeProduit())),
                            "document" => array("id" => $this->drm->get('_id'),
                                "revision" => $this->drm->get('_rev'))
                )));
            } else {
                $this->redirect('drm_edition_details', array('sf_subject' => $this->drm, 'details' => $this->detailsKey));
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->renderText(json_encode(array(
                        "success" => false,
                        "content" => ""
            )));
        } else {
            $this->redirect('drm_edition', $this->drm);
        }
    }

    protected function init($teledeclarationMode = false) {
        $this->form = null;
        $this->detail = null;
        $this->detailsKey = $this->getRequest()->getParameter('details');
        $this->drm = $this->getRoute()->getDRM();
        $this->config = $this->drm->declaration->getConfig();
        $this->details = $this->drm->declaration->getProduitsDetailsSorted($teledeclarationMode, $this->detailsKey);
        if (!$this->drm->exist('favoris')) {
            $this->drm->buildFavoris();
        }
    }

    public function executeAddLabel(sfWebRequest $request) {
        $this->detail = $this->getRoute()->getDRMDetail();
        $this->drm = $this->getRoute()->getDRM();

        $this->form = new DRMProduitLabelForm($this->detail);
        if ($request->isMethod(sfRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->form->save();
                if ($request->isXmlHttpRequest()) {
                    $this->getUser()->setFlash("notice", 'Les labels ont étés mis à jour avec success.');
                    //return $this->renderPartial('labelsList', array('form' => $this->form));
                    return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->drm->get('_id'), "revision" => $this->drm->get('_rev')), 'content' => $this->form->getObject()->getLabelsLibelle())));
                }
            }
            if ($request->isXmlHttpRequest()) {
                $this->getUser()->setFlash("notice", 'Echec lors de la mis à jour des labels');
                return $this->renderText(json_encode(array('success' => false, 'document' => array("id" => $this->drm->get('_id'), "revision" => $this->drm->get('_rev')))));
            }
        }
    }

    public function executeChoixFavoris(sfWebRequest $request) {
        $this->drm = $this->getRoute()->getDRM();
        $details = (!$request->getParameter('details'))? 'details' : $request->getParameter('details');
        if (!$this->drm->exist('favoris')) {
            $this->drm->buildFavoris();
        }
        $form = new DRMFavorisForm($this->drm, array('details' => $details));
        if ($request->isMethod(sfRequest::POST)) {
            $form->bind($request->getParameter($form->getName()));
            if ($form->isValid()) {
                $form->save();
                $this->redirect('drm_edition', $this->drm);
            }
        }
        $this->redirect('drm_edition', $this->drm);
    }

    private function loadFavoris() {
        $detail = $this->getRequest()->getParameter('details');
        $this->favoris = $this->drm->getAllFavoris()->get($detail);
    }

}
