<?php

class factureActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
        $this->form = new FactureSocieteChoiceForm('INTERPRO-declaration');
        $this->generationForm = new FactureGenerationForm();
        $this->generations = GenerationClient::getInstance()->findHistoryWithType(array(GenerationClient::TYPE_DOCUMENT_EXPORT_SHELL, GenerationClient::TYPE_DOCUMENT_FACTURES), 10);
        sfContext::getInstance()->getResponse()->setTitle('FACTURE');
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                return $this->redirect('facture_societe', $this->form->getSociete());
            }
        }
    }

    public function executeNouveauMouvements(sfWebRequest $request) {

        sfContext::getInstance()->getResponse()->setTitle('FACTURES LIBRES - (nouveau)');
        $this->factureMouvements = MouvementsFactureClient::getInstance()->createMouvementsFacture();
        $this->factureMouvements->save();
        $this->redirect('facture_mouvements_edition', array('id' => $this->factureMouvements->identifiant));
    }

    public function executeMouvementsList(sfWebRequest $request) {
        sfContext::getInstance()->getResponse()->setTitle('FACTURES LIBRES');
        $this->factureMouvementsAll = MouvementsFactureClient::getInstance()->startkey('MOUVEMENTSFACTURE-0000000000')->endkey('MOUVEMENTSFACTURE-9999999999')->execute();
    }

    public function executeMouvementsedition(sfWebRequest $request) {
        $this->factureMouvements = MouvementsFactureClient::getInstance()->find('MOUVEMENTSFACTURE-' . $request->getParameter('id'));
        sfContext::getInstance()->getResponse()->setTitle('FACTURES LIBRES - ' . $this->factureMouvements->identifiant);
        $this->form = new FactureMouvementsEditionForm($this->factureMouvements, array('interpro_id' => 'INTERPRO-declaration'));
        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if ($this->form->isValid()) {
            $this->form->save();
            $this->redirect('facture_mouvements', array('id' => $this->factureMouvements->identifiant));
        }
    }

    public function executeMouvementssupprimer(sfWebRequest $request) {
        $this->factureMouvements = MouvementsFactureClient::getInstance()->find('MOUVEMENTSFACTURE-' . $request->getParameter('id'));
        if ($this->factureMouvements->getNbMvtsAFacture()) {
            $this->redirect('facture_mouvements', array('id' => $this->factureMouvements->identifiant));
        }
        $this->factureMouvements->delete();
        $this->redirect('facture_mouvements', array('id' => $this->factureMouvements->identifiant));
    }

    public function executeGeneration(sfWebRequest $request) {
        $this->form = new FactureGenerationForm();
        $filters_parameters = array();
        if (!$request->isMethod(sfWebRequest::POST)) {

            throw new sfException("Pas en mode post");
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            throw new sfException("Formulaire pas valide");
        }

        $filters_parameters = $this->constructFactureFiltersParameters();
        $generation = new Generation();

        $generation->type_document = $filters_parameters['type_document'];

        $generation->arguments->add('date_facturation', $filters_parameters['date_facturation']);
        $generation->arguments->add('date_mouvement', $filters_parameters['date_mouvement']);
        if($filters_parameters['modele']) {
            $generation->arguments->add('modele', $filters_parameters['modele']);
        }
        if ($filters_parameters['message_communication']) {
            $generation->arguments->add('message_communication', $filters_parameters['message_communication']);
        }
        if (isset($filters_parameters['seuil'])) {
            $generation->arguments->add('seuil', $filters_parameters['seuil']);
        }
        $generation->save();

        return $this->redirect('generation_view', array('type_document' => $generation->type_document, 'date_emission' => $generation->date_emission));
    }

    public function executeEtablissement(sfWebRequest $request) {
        return $this->redirect('facture_societe', $this->getRoute()->getEtablissement()->getSociete());
    }

    public function executeMonEspace(sfWebRequest $request) {
        $this->form = new FactureGenerationForm();
        $this->societe = $this->getRoute()->getSociete();
        $this->factures = FactureSocieteView::getInstance()->findBySociete($this->societe);
        $this->mouvements = MouvementfactureFacturationView::getInstance()->getMouvementsNonFacturesBySociete($this->societe);

        $this->compte = $this->societe->getMasterCompte();
    }

    public function executeCreation(sfWebRequest $request) {

        $this->societe = $this->getRoute()->getSociete();
        $this->values = array();

        $this->form = new FactureGenerationForm();
        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $parameters = $this->constructFactureFiltersParameters();

        $f = FactureClient::getInstance()->createAndSaveFacturesBySociete($this->societe, $parameters);

        if(!$f) {

            return $this->redirect('facture_societe', $this->societe);

        }

        $f->save();

        $generation = FactureClient::getInstance()->createGenerationForOneFacture($f);
        $generation->save();

        return $this->redirect('facture_societe', $this->societe);
    }

    public function executeDefacturer(sfWebRequest $resquest) {
        $this->facture = $this->getRoute()->getFacture();
        if (!$this->facture->hasAvoir()) {
            $this->avoir = FactureClient::getInstance()->defactureCreateAvoirAndSaveThem($this->facture);
        }
        $this->redirect('facture_societe', array('identifiant' => $this->facture->identifiant));
    }

    public function executeRedirect(sfWebRequest $request) {
        $iddoc = $request->getParameter('iddocument');

        if (preg_match('/^DRM/', $iddoc)) {
            $drm = DRMClient::getInstance()->find($iddoc);
            return $this->redirect('drm_visualisation', $drm);
        } else if (preg_match('/^SV12/', $iddoc)) {
            $sv12 = SV12Client::getInstance()->find($iddoc);
            return $this->redirect('sv12_visualisation', $sv12);
        } else if (preg_match('/^MOUVEMENTSFACTURE/', $iddoc)) {
            $mouvementFacture = MouvementsFactureClient::getInstance()->find($iddoc);
            return $this->redirect('facture_mouvements_edition', array('id' => $mouvementFacture->identifiant));
        }
        return $this->forward404();
    }

    public function executeLatex(sfWebRequest $request) {
        $this->setLayout(false);
        $this->facture = FactureClient::getInstance()->find($request->getParameter('id'));
        $this->forward404Unless($this->facture);
        $latex = new FactureLatex($this->facture);
        $latex->echoWithHTTPHeader($request->getParameter('type'));
        //    var_dump($latex->echoWithHTTPHeader('latex'));
        exit;
    }

    private function getLatexTmpPath() {
        return "/tmp/";
    }

    public function executeComptabiliteEdition(sfWebRequest $request) {
        $compta = ComptabiliteClient::getInstance()->findCompta();
        $this->form = new ComptabiliteEditionForm($compta);

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->form->save();
                return $this->redirect('comptabilite_edition');
            }
        }
    }

    private function constructFactureFiltersParameters() {
        $values = $this->form->getValues();
        $filters_parameters = array();
        $filters_parameters['date_mouvement'] = date('Y-m-d');
        $filters_parameters['message_communication'] = "";
        $filters_parameters['type_document'] = GenerationClient::TYPE_DOCUMENT_FACTURES;
        $filters_parameters['modele'] = $values['modele'];

        if (isset($values['date_facturation']) && $values['date_facturation']) {
            $filters_parameters['date_facturation'] = DATE::getIsoDateFromFrenchDate($values['date_facturation']);
        }
        if (isset($values['date_mouvement']) && $values['date_mouvement']) {
            $filters_parameters['date_mouvement'] = DATE::getIsoDateFromFrenchDate($values['date_mouvement']);
        }else if (isset($values['date_facturation']) && $values['date_facturation']) {
            $filters_parameters['date_mouvement'] = DATE::getIsoDateFromFrenchDate($values['date_facturation']);
        }
        if (isset($values['message_communication']) && $values['message_communication']) {
            $filters_parameters['message_communication'] = $values['message_communication'];
        }
        if (isset($values['modele']) && $values['modele']) {
            if ($values['modele'] == FactureClient::TYPE_FACTURE_MOUVEMENT_DIVERS) {
                $filters_parameters['modele'] = 'MouvementsFacture';
            }elseif($values['modele'] == FactureClient::TYPE_FACTURE_MOUVEMENT_DRM){
              $filters_parameters['modele'] = "DRM";
            }elseif ($values['modele'] == FactureGenerationForm::TYPE_GENERATION_EXPORT) {
                $filters_parameters['type_document'] = GenerationClient::TYPE_DOCUMENT_EXPORT_SHELL;
                $filters_parameters['modele'] = null;
            }elseif ($values['modele'] == GenerationClient::TYPE_DOCUMENT_VRACSSANSPRIX) {
                $filters_parameters['type_document'] = GenerationClient::TYPE_DOCUMENT_VRACSSANSPRIX;
                $filters_parameters['modele'] = null;
            }
        }
        if(isset($values['seuil'])) {
            $filters_parameters['seuil'] = $values['seuil']*1.0;
        }
        return $filters_parameters;
    }

}
