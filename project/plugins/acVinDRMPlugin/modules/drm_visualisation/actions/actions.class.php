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
      $this->redirect403Unless(!$drm->isNonFactures());
      $drm->valide->date_saisie = null;
      $drm->valide->date_signee = null;
      $drm->save();
      return $this->redirect('drm_etablissement', $drm->getEtablissement());
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->drm = $this->getRoute()->getDRM();
        $this->isTeledeclarationMode = $this->isTeledeclarationDrm();
        $this->no_link = false;
        if ($this->getUser()->hasOnlyCredentialDRM()) {
            $this->no_link = true;
        }
        $this->hide_rectificative = $request->getParameter('hide_rectificative');
        $this->drm_suivante = $this->drm->getSuivante();
        $this->mouvements = DRMMouvementsConsultationView::getInstance()->getMouvementsByEtablissementAndPeriode($this->drm->identifiant, $this->drm->periode);
        $this->mouvementsByProduit = DRMClient::getInstance()->sortMouvementsForDRM($this->mouvements);      

        $this->recapCvos = $this->recapCvos();
    }

    public function recapCvos() {
        $recapCvos = array();

        $recapCvos["TOTAL"] = new stdClass();
        $recapCvos["TOTAL"]->totalVolumeDroitsCvo = 0;
        $recapCvos["TOTAL"]->totalVolumeReintegration = 0;
        $recapCvos["TOTAL"]->totalPrixDroitCvo = 0;
        $recapCvos["TOTAL"]->version = null;

        foreach ($this->mouvements as $mouvement) {
            $version = $mouvement->version;
            if(!$version) {
                $version = "M00";
            }
            if(!array_key_exists($version, $recapCvos)) {
                $recapCvos[$version] = new stdClass();
                $recapCvos[$version]->totalVolumeDroitsCvo = 0;
                $recapCvos[$version]->totalVolumeReintegration = 0;
                $recapCvos[$version]->totalPrixDroitCvo = 0;
                $recapCvos[$version]->version = $version;
            }
            if ($mouvement->facturable) {
                $recapCvos[$version]->totalPrixDroitCvo += $mouvement->volume * -1 * $mouvement->cvo;
                $recapCvos["TOTAL"]->totalPrixDroitCvo += $mouvement->volume * -1 * $mouvement->cvo;
                $recapCvos[$version]->totalVolumeDroitsCvo += $mouvement->volume * -1;
                $recapCvos["TOTAL"]->totalVolumeDroitsCvo += $mouvement->volume * -1;
            }
            if ($mouvement->type_hash == 'entrees/reintegration') {
                $recapCvos[$version]->totalVolumeReintegration += $mouvement->volume;
                $recapCvos["TOTAL"]->totalVolumeReintegration += $mouvement->volume;
            }
        }

        if(count($recapCvos) <= 2) {

            return array("TOTAL" => $recapCvos["TOTAL"]);
        }
        ksort($recapCvos);
        return $recapCvos;
    }

}
