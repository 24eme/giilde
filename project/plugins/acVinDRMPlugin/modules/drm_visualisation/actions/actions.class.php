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
class drm_visualisationActions extends drmGeneriqueActions {
    public function executeReopen(sfWebRequest $request) {
      $this->redirect403IfIsTeledeclaration();
      $drm = $this->getRoute()->getDRM();
      $this->redirect403Unless($drm->isTeledeclare());
      $this->redirect403Unless($drm->isNonFactures());
      $drm->valide->date_saisie = null;
      $drm->valide->date_signee = null;
      $drm->save();
      return $this->redirect('drm_etablissement', $drm->getEtablissement());
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->drm = $this->getRoute()->getDRM();
        $this->isTeledeclarationMode = $this->isTeledeclarationDrm();
        $this->isUsurpationMode = $this->isUsurpationMode();
        $this->no_link = false;
        if ($this->getUser()->hasOnlyCredentialDRM()) {
            $this->no_link = true;
        }
        $this->hide_rectificative = $request->getParameter('hide_rectificative');
        $this->drm_suivante = $this->drm->getSuivante();
        $this->mouvements = DRMMouvementsConsultationView::getInstance()->getMouvementsByEtablissementAndPeriode($this->drm->identifiant, $this->drm->periode);
        $this->mouvementsByProduit = DRMClient::getInstance()->sortMouvementsForDRM($this->mouvements);
        $this->recapCvos = DRMClient::getInstance()->getRecapCvosByMouvements($this->mouvements);
    }
}
