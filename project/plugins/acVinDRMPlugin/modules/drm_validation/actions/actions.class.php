<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of actions
 *
 * @author mathurin
 */
class drm_validationActions extends drmGeneriqueActions {

    public function executeValidation(sfWebRequest $request) {

        set_time_limit(180);
        ini_set('memory_limit',"1024M");
        $this->drm = $this->getRoute()->getDRM();
        $this->isTeledeclarationMode = $this->isTeledeclarationDrm();
        if($this->drm->isValidee()){
          $this->redirect('drm_visualisation', array('identifiant' => $this->drm->identifiant,
      	            'periode_version' => $this->drm->getPeriodeAndVersion()));
        }
        $this->initSocieteAndEtablissementPrincipal();
        $this->mouvements = array();
        try {
            $this->mouvements = $this->drm->getMouvementsCalculeByIdentifiant($this->drm->identifiant);
        }catch(sfException $e) {}
        foreach($this->mouvements as $key => $mouvement) {
            if($mouvement->produit_hash) {
                continue;
            }

            unset($this->mouvements[$key]);
        }
        $this->mouvementsByProduit = DRMClient::getInstance()->sortMouvementsForDRM($this->mouvements);

        $this->drm->cleanDeclaration();
        if ($this->isTeledeclarationMode) {
            $this->validationCoordonneesSocieteForm = new DRMValidationCoordonneesSocieteForm($this->drm);
            $this->validationCoordonneesEtablissementForm = new DRMValidationCoordonneesEtablissementForm($this->drm);
        } else {
            $this->formCampagne = new DRMEtablissementCampagneForm($this->drm->identifiant, $this->drm->campagne,$this->isTeledeclarationMode);
        }
        $this->no_link = false;
        if ($this->getUser()->hasOnlyCredentialDRM()) {
            $this->no_link = true;
        }

        $this->validation = new DRMValidation($this->drm, $this->isTeledeclarationMode);
        if($this->validation->hasPoints()){
            $this->drm->addPoints($this->validation);
        }else if($this->drm->exist("controles")){
            $this->drm->cleanControles();
        }
        $this->produits = array();
        foreach ($this->drm->getProduits() as $produit) {
            $d = new stdClass();
            $d->version = $this->drm->version;
            $d->periode = $this->drm->periode;
            $d->produit_hash = $produit->getHash();
            $d->produit_libelle = $produit->getLibelle();
            $d->total_debut_mois = $produit->total_debut_mois;
            $d->total_entrees = $produit->total_entrees;
            $d->total_sorties = $produit->total_sorties;
            $d->total = $produit->total;
            $d->total_facturable = $produit->total_facturable;
            $this->produits[] = $d;
        }

        $this->isUsurpationMode = $this->isUsurpationMode();

        $this->form = new DRMValidationCommentaireForm($this->drm);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->mouvements = $this->drm->getMouvementsCalculeByIdentifiant($this->drm->identifiant);
        $this->form->bind($request->getParameter($this->form->getName()));
        if ($request->getParameter('brouillon')) {
            $this->form->save();
            return $this->redirect('drm_etablissement', $this->drm->getEtablissement());
        }

        if (!$this->validation->isValide()) {
            return sfView::SUCCESS;
        }
        $this->form->save();
        $this->drm->validate(array('isTeledeclarationMode' => $this->isTeledeclarationMode));
        $this->drm->save();

        $this->drm->updateVracs();

        if(!$this->isUsurpationMode() && $this->isTeledeclarationMode){
            $mailManager = new DRMEmailManager($this->getMailer());
            $mailManager->setDRM($this->drm);
            $mailManager->sendMailValidation();
        }

        DRMClient::getInstance()->generateVersionCascade($this->drm);
        if ($this->form->getValue('transmission_ciel') == "true") {
		      $this->redirect('drm_transmission', array('identifiant' => $this->drm->identifiant,'periode_version' => $this->drm->getPeriodeAndVersion()));
      	}else{
      	        $this->redirect('drm_visualisation', array('identifiant' => $this->drm->identifiant,
      	            'periode_version' => $this->drm->getPeriodeAndVersion(),
      	            'hide_rectificative' => 1));
      	}
    }


    public function executeUpdateEtablissement(sfWebRequest $request) {
        $this->drm = $this->getRoute()->getDRM();
        $this->isTeledeclarationMode = $this->isTeledeclarationDrm();
        $this->initSocieteAndEtablissementPrincipal();
        $this->form = new DRMValidationCoordonneesEtablissementForm($this->drm);
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $diff = $this->form->getDiff();
                $this->form->save();
                if(!count($diff)) {

                    return $this->redirect('drm_validation', $this->drm);
                }
                $mailManager = new DRMEmailManager($this->getMailer());
                $mailManager->setDRM($this->drm);
                $mailManager->sendMailCoordonneesOperateurChanged(CompteClient::TYPE_COMPTE_ETABLISSEMENT, $diff);

                return $this->redirect('drm_validation', $this->drm);
            }
        }
    }

    public function executeUpdateSociete(sfWebRequest $request) {
        $this->drm = $this->getRoute()->getDRM();
        $this->isTeledeclarationMode = $this->isTeledeclarationDrm();
        $this->initSocieteAndEtablissementPrincipal();
        $this->form = new DRMValidationCoordonneesSocieteForm($this->drm);
        if($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $diff = $this->form->getDiff();
                $this->form->save();
                if(!count($diff)) {

                    return $this->redirect('drm_validation', $this->drm);
                }

                $mailManager = new DRMEmailManager($this->getMailer());
                $mailManager->setDRM($this->drm);
                $mailManager->sendMailCoordonneesOperateurChanged(CompteClient::TYPE_COMPTE_SOCIETE, $diff);

                return $this->redirect('drm_validation', $this->drm);
            }
        }
    }

}
