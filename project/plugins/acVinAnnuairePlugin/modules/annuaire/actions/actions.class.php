<?php

class annuaireActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
        $this->cleanSessions();
        $this->identifiant = $request->getParameter('identifiant');
        $this->etablissement = EtablissementClient::getInstance()->find($this->identifiant);
        $this->annuaire = AnnuaireClient::getInstance()->findOrCreateAnnuaireWithSuspendu($this->identifiant);

        $this->initSocieteAndEtablissementPrincipal();
    }

    public function executeSelectionner(sfWebRequest $request) {
        $this->type = $request->getParameter('type');
        $this->identifiant = $request->getParameter('identifiant');
        $this->etablissement = EtablissementClient::getInstance()->find($this->identifiant);

        $this->initSocieteAndEtablissementPrincipal();

        $this->annuaire = AnnuaireClient::getInstance()->findOrCreateAnnuaire($this->identifiant);
        $this->form = new AnnuaireAjoutForm($this->annuaire);
        $this->form->setDefault('type', $this->type);
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $values = $this->form->getValues();
                $type = AnnuaireClient::ANNUAIRE_RECOLTANTS_KEY;
                if (array_key_exists('type', $values)) {
                    $type = $values['type'];
                }
                return $this->redirect('annuaire_ajouter', array('type' => $type, 'identifiant' => $this->identifiant, 'tiers' => $values['tiers']));
            }
        }
    }

    public function executeAjouter(sfWebRequest $request) {
        $this->type = $request->getParameter('type');
        $this->identifiant = $request->getParameter('identifiant');
        $this->etablissement = EtablissementClient::getInstance()->find($this->identifiant);

        $this->initSocieteAndEtablissementPrincipal();
        $this->societeId = $request->getParameter('tiers');
        $this->societeChoice = false;

        if ($this->type && $this->societeId) {
            $this->annuaire = AnnuaireClient::getInstance()->findOrCreateAnnuaire($request->getParameter('identifiant'));

            $this->societeObject = AnnuaireClient::getInstance()->findSocieteByTypeAndTiers($this->type, $this->societeId);
            $etablissements = $this->societeObject->getEtablissementsObj(false);
            $this->etablissements = array();
            foreach($etablissements as $item) {
              $e = $item->etablissement;
              if ($this->type == AnnuaireClient::ANNUAIRE_RECOLTANTS_KEY && $e->famille != EtablissementFamilles::FAMILLE_PRODUCTEUR) {
                  continue;
              }
              if ($this->type == AnnuaireClient::ANNUAIRE_NEGOCIANTS_KEY && $e->famille != EtablissementFamilles::FAMILLE_NEGOCIANT) {
                  continue;
              }
              $this->etablissements[] = $item;
            }

            if (!count($this->etablissements)) {
                return $this->redirect('annuaire_selectionner', array('type' => $type, 'identifiant' => $this->identifiant));
            }


            $this->form = new AnnuaireAjoutForm($this->annuaire, $this->type, $this->etablissements);
            $this->form->setDefault('type', $this->type);
            $this->form->setDefault('tiers', $this->societeId);
            if (!$this->form->hasSocieteChoice() && count($this->etablissements)) {
                $this->etbObject = array_pop($this->etablissements)->etablissement;
            }
        }

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            $values = $this->form->getValues();
            if ($this->form->isValid()) {
                $type = AnnuaireClient::ANNUAIRE_RECOLTANTS_KEY;
                if (array_key_exists('type', $values)) {
                    $type = $values['type'];
                }
                if ($this->societeId != $values['tiers']) {
                    return $this->redirect('annuaire_ajouter', array('type' => $type, 'identifiant' => $this->identifiant, 'tiers' => $values['tiers']));
                } else {
                    $this->form->save();
                    if ($vrac = $this->getUser()->getAttribute('vrac_object')) {
                        $vrac = unserialize($vrac);
                        $acteur = $this->getUser()->getAttribute('vrac_acteur');
                        $this->etbObject = $this->form->getValue("etablissementObject");
                        $vrac->{$acteur . '_identifiant'} = $this->etbObject->identifiant;
                        $this->getUser()->setAttribute('vrac_object', serialize($vrac));
                        $this->getUser()->setAttribute('vrac_acteur', null);
                        if ($vrac->isNew()) {
                            return $this->redirect('vrac_nouveau', array('choix-etablissement' => $vrac->createur_identifiant));
                        } else {
                            return $this->redirect('vrac_soussigne', array('numero_contrat' => $vrac->numero_contrat));
                        }
                    }
                    return $this->redirect('annuaire', array('identifiant' => $this->identifiant));
                }
            }
        }
    }

    public function executeAjouterCommercial(sfWebRequest $request) {
        $this->identifiant = $request->getParameter('identifiant');
        $this->etablissement = EtablissementClient::getInstance()->find($this->identifiant);

        $this->initSocieteAndEtablissementPrincipal();

        $this->annuaire = AnnuaireClient::getInstance()->findOrCreateAnnuaire($request->getParameter('identifiant'));
        $this->form = new AnnuaireAjoutCommercialForm($this->annuaire);
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->form->save();
                $values = $this->form->getValues();
                if ($vrac = $this->getUser()->getAttribute('vrac_object')) {
                    $vrac = unserialize($vrac);
                    $vrac->storeInterlocuteurCommercialInformations($values['identite'], $value['contact']);
                    $this->getUser()->setAttribute('vrac_object', serialize($vrac));
                    if ($vrac->isNew()) {
                        return $this->redirect('vrac_nouveau', array('choix-etablissement' => $vrac->createur_identifiant));
                    } else {
                        return $this->redirect('vrac_soussigne', array('numero_contrat' => $vrac->numero_contrat));
                    }
                }
                return $this->redirect('annuaire', array('identifiant' => $this->identifiant));
            }
        }
    }

    public function executeRetour(sfWebRequest $request) {
        $this->identifiant = $request->getParameter('identifiant');
        $vrac = $this->getUser()->getAttribute('vrac_object');
        $vrac = unserialize($vrac);
        if ($vrac) {
            if ($vrac->isNew()) {
                return $this->redirect('vrac_nouveau', array('choix-etablissement' => $vrac->createur_identifiant));
            } else {
                return $this->redirect('vrac_soussigne', array('numero_contrat' => $vrac->numero_contrat));
            }
        }

        return $this->redirect('annuaire', array('identifiant' => $this->identifiant));
    }

    public function executeSupprimer(sfWebRequest $request) {
        $this->cleanSessions();
        $type = $request->getParameter('type');
        $id = $request->getParameter('id');
        $identifiant = $request->getParameter('identifiant');
        $annuaire = AnnuaireClient::getInstance()->findOrCreateAnnuaire($identifiant);
        $etablissement = EtablissementClient::getInstance()->find($identifiant);
        $societe = SocieteClient::getInstance()->find($etablissement->id_societe);
        if ($type !== null && $id !== null) {
            if ($annuaire && $annuaire->exist($type)) {
                if ($annuaire->get($type)->exist($id)) {
                    $annuaire->get($type)->remove($id);
                    $annuaire->save();
                    return $this->redirect('annuaire', array('identifiant' => $identifiant));
                }
            }
        }
        throw new sfError404Exception('La paire "' . $type . '"/"' . $id . '" n\'existe pas dans l\'annuaire');
    }

    public function cleanSessions() {
        $this->getUser()->setAttribute('vrac_object', null);
        $this->getUser()->setAttribute('vrac_acteur', null);
    }

    /*
     * Fonctions de service liées aux droits Users
     *
     */

    private function isTeledeclarationVrac() {
        return $this->getUser()->hasTeledeclarationVrac();
    }

    private function initSocieteAndEtablissementPrincipal() {
        $this->compte = $this->getUser()->getCompte();
        if (!$this->compte) {
            throw new sfException("Le compte $compte n'existe pas");
        }
        $this->societe = $this->compte->getSociete();
        $this->etablissementPrincipal = $this->societe->getEtablissementPrincipal();
    }

}
