<?php

class commonComponents extends sfComponents {

    public function executeNav(sfWebRequest $request) {
        $this->etablissement = null;
        $this->societe = null;

        if ($this->getRoute() instanceof InterfaceSocieteRoute) {
            $this->societe = $this->getRoute()->getSociete();
        }

        if ($this->getRoute() instanceof InterfaceEtablissementRoute){
            $this->etablissement = $this->getRoute()->getEtablissement();
        }

        if($request->getParameter('etablissement')) {
            $this->etablissement = EtablissementClient::getInstance()->find($request->getParameter('etablissement'));
        }

        $this->module = $this->getRequest()->getParameter('module');
    }

    public function executeNavItem(sfWebRequest $request) {
        $this->actif = preg_match('/^' . $this->prefix . '/', $this->getRequest()->getParameter('module'));
    }

    public function executeBlocks(sfWebRequest $request) {
        $this->etablissement = null;
        if ($this->getRoute() instanceof InterfaceEtablissementRoute) {
            $this->etablissement = $this->getRoute()->getEtablissement();
        }
        if($request->getParameter('identifiant')){
            $this->etablissement = EtablissementClient::getInstance()->findByIdentifiant($request->getParameter('identifiant'));
        }
    }

    public function executeBlocksTeledeclaration(sfWebRequest $request) {
      $this->teledeclaration = false;
      $this->teledeclaration_vrac = false;
      $this->teledeclaration_drm = false;
      $this->etablissementPrincipal = null;
      $this->contratsSocietesWithInfos = null;
      if($this->getUser()->hasCredential('teledeclaration')){
        $this->teledeclaration = true;
        $this->etablissementPrincipal = $this->getUser()->getCompte()->getSociete()->getEtablissementPrincipal();
          $this->compte = $this->getUser()->getCompte();
          if (!$this->compte) {
              throw new sfException("Le compte $compte n'existe pas");
          }
          $this->societe = $this->compte->getSociete();
          $this->etablissement = $this->societe->getEtablissementPrincipal();
      }


      if($this->getUser()->hasCredential('teledeclaration_vrac')){
        $this->teledeclaration_vrac = true;
        $this->contratsSocietesWithInfos = VracClient::getInstance()->retrieveBySocieteWithInfosLimit($this->societe, $this->etablissement, 10);
      }
      if($this->getUser()->hasCredential('teledeclaration_drm')){
        $this->teledeclaration_drm = true;
        $this->campagne = -1;
      }
        $this->etablissementPrincipal = $this->getUser()->getCompte()->getSociete()->getEtablissementPrincipal();

    }

    public function executeBlockItem(sfWebRequest $request) {
        $this->actif = preg_match('/^' . $this->prefix . '/', $this->getRequest()->getParameter('module'));
    }


    public function getRoute() {
        return $this->getRequest()->getAttribute('sf_route');
    }

}
