<?php

class etablissementActions extends sfCredentialActions {

    public function executeAjout(sfWebRequest $request) {
        $this->societe = $this->getRoute()->getSociete();
        $this->applyRights();
        if (!$this->modification) {
            $this->forward('acVinCompte', 'forbidden');
        }
        $this->famille = $request->getParameter('famille');
        $this->etablissement = $this->societe->createEtablissement($this->famille);
        $this->processFormEtablissement($request);
        $this->setTemplate('modification');
    }

    public function executeModification(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->societe = $this->etablissement->getSociete();
        $this->applyRights();
        if (!$this->modification) {
            $this->forward('acVinCompte', 'forbidden');
        }
        $this->processFormEtablissement($request);
    }

    protected function processFormEtablissement(sfWebRequest $request) {
        $this->etablissementModificationForm = new EtablissementModificationForm($this->etablissement);
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->etablissementModificationForm->bind($request->getParameter($this->etablissementModificationForm->getName()));
            if ($this->etablissementModificationForm->isValid()) {
                $this->etablissementModificationForm->save();
                $this->redirect('etablissement_visualisation', array('identifiant' => $this->etablissement->identifiant));
            }
        }
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->societe = $this->etablissement->getSociete();
        $this->contact = $this->etablissement->getContact();
        $this->applyRights();

        $this->redirect($this->generateUrl('societe_visualisation', array('sf_subject' => $this->etablissement->getSociete(), 'etablissement' => $this->etablissement->_id)) . '#' . $this->etablissement->_id);
    }

     public function executeSwitchStatus(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $newStatus = "";
        if($this->etablissement->isActif()){
           $newStatus = SocieteClient::STATUT_SUSPENDU;
        }
        if($this->etablissement->isSuspendu()){
           $newStatus = SocieteClient::STATUT_ACTIF;
        }
        $compte = $this->etablissement->getMasterCompte();
        if($compte && !$this->etablissement->isSameCompteThanSociete()){
            $compte->setStatut($newStatus);
            $compte->save();
        }
        $this->etablissement->setStatut($newStatus);
        $this->etablissement->save();
        return $this->redirect('etablissement_visualisation', array('identifiant' => $this->etablissement->identifiant));
    }
    


    public function executeReinitCrd(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
    	if ($this->etablissement->exist('crd_regime')) {
    		$this->etablissement->remove('crd_regime');
    		$this->etablissement->save();
    	}
        return $this->redirect('etablissement_visualisation', array('identifiant' => $this->etablissement->identifiant));
    }

}
