<?php

class commonActions extends sfActions {

    public function executeError500(sfWebRequest $request) {
        $this->exception = $request->getParameter('exception');
        if (sfConfig::get('app_auth_mode') != 'HTTP_AD') {
            $this->setTemplate('error500Teledeclaration');
        }
        $this->getResponse()->setStatusCode(500);
    }

    public function executeError404(sfWebRequest $request) {
        $this->exception = $request->getParameter('exception');
    }

    public function executeHome(sfWebRequest $request) {

        if ($this->getUser()->hasCredential('transactions')) {
            return $this->redirect('common_accueil');
        }

        if ($this->getUser()->hasCredential('drm')) {
            return $this->redirect('drm');
        }

        if ($this->getUser()->hasObservatoire() && !$this->getUser()->hasTeledeclarationVrac()) {
            $this->redirect(sfConfig::get('app_observatoire_url'));
        }

        if (!$this->getUser()->hasCredential('operateur')) {

            return $this->redirectWithCredentials($this->getUser()->getCompte()->identifiant);
        }

        return $this->redirect('societe');
    }

    public function executeAccueil(sfWebRequest $request) {
      $this->initTeledeclarationDroits();
      if($this->teledeclaration){
        return $this->redirect("common_accueil_etablissement" ,array('identifiant' => $this->getUser()->getCompte()->getSociete()->getEtablissementPrincipal()->identifiant));
      }
    }

    public function executeAccueilEtablissement(sfWebRequest $request) {
        $this->initTeledeclarationDroits();
        $this->etablissement = EtablissementClient::getInstance()->findByIdentifiant($request->getParameter('identifiant'));
        return $this->setTemplate('accueil');
    }

    private function initTeledeclarationDroits(){
      $this->teledeclaration = false;
      $this->teledeclaration_vrac = false;
      $this->teledeclaration_drm = false;
      $this->etablissementPrincipal = null;
      if($this->getUser()->hasCredential('teledeclaration')){
        $this->teledeclaration = true;
        $this->etablissementPrincipal = $this->getUser()->getCompte()->getSociete()->getEtablissementPrincipal();
      }
      if($this->getUser()->hasCredential('teledeclaration_vrac')){
        $this->teledeclaration_vrac = true;
      }
      if($this->getUser()->hasCredential('teledeclaration_drm')){
        $this->teledeclaration_drm = true;
      }
    }

    protected function redirectWithCredentials($idCompte){
            if($this->getUser()->hasCredential(Roles::TELEDECLARATION_DRM) && $this->getUser()->hasCredential(Roles::TELEDECLARATION_VRAC)){
            return $this->redirect("common_accueil_etablissement" ,array('identifiant' => $idCompte));
            }
            if($this->getUser()->hasCredential(Roles::TELEDECLARATION_VRAC)){
                 return $this->redirect('vrac_societe', array('identifiant' => $idCompte));
            }
            if($this->getUser()->hasCredential(Roles::TELEDECLARATION_DRM)){
                   return $this->redirect('drm_societe', array('identifiant' => $idCompte));
            }
           return $this->redirect("common_accueil_etablissement" ,array('identifiant' => $idCompte));
    }

}
