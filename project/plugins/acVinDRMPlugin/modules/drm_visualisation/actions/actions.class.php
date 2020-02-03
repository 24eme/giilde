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
        if ($this->drm->isMaster()) {
            $this->mouvements = DRMMouvementsConsultationView::getInstance()->getMouvementsByEtablissementAndPeriode($this->drm->identifiant, $this->drm->periode);
        }else{
            $this->mouvements = array();
            foreach($this->drm->mouvements as $operateur => $mouvements) {
                foreach($mouvements as $key => $mouvement) {
                    if(!$mouvement->produit_hash) {
                        continue;
                    }
                    $this->mouvements[] = $mouvement;
                }
            }
        }
        $this->mouvementsByProduit = DRMClient::getInstance()->sortMouvementsForDRM($this->mouvements);
        $this->recapCvos = DRMClient::getInstance()->getRecapCvosByMouvements($this->mouvements);
    }
}
