<?php

class fichierActions extends sfActions
{
	public function executeIndex(sfWebRequest $request) {
		$this->redirect403IfIsTeledeclaration();
	}

     protected function redirect403IfIsTeledeclaration() {
        if (!$this->getUser()->hasCredential(myUser::CREDENTIAL_ADMIN)) {
            $this->redirect403();
        }
    }

    public function executeEtablissementSelection(sfWebRequest $request) {
		$this->redirect403IfIsTeledeclaration();
        $form = new FichierEtablissementChoiceForm('INTERPRO-declaration');
        $form->bind($request->getParameter($form->getName()));
        if (!$form->isValid()) {
            return $this->redirect('fichiers');
        }
        return $this->redirect('fichiers_etablissement', $form->getEtablissement());
    }

    public function executeMonEspace(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
    }

    public function executeSociete(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
    	if (!$this->getUser()->hasCredential(Roles::TELEDECLARATION) || $this->getUser()->getCompte()->identifiant != $request['identifiant']) {
    		$this->redirect403();
    	}
    	$this->redirect('pieces_historique', $this->etablissement);
    }

    protected function redirect403IfIsNotTeledeclarationAndNotMe() {
    	$this->redirect403IfIsNotTeledeclaration();
    	if ($this->getUser()->getCompte()->identifiant != $this->identifiant) {
    		$this->redirect403();
    	}
    }

    public function executeConnexion(sfWebRequest $request) {
    	$this->etablissement = $this->getRoute()->getEtablissement();
    	$societe = $this->etablissement->getSociete();
    
    	$this->getUser()->usurpationOn($societe->identifiant, $request->getReferer());
    	$this->redirect('fichiers_societe', array('identifiant' => $societe->getEtablissementPrincipal()->identifiant));
    }
	
	public function executeGet(sfWebRequest $request) {
    	$fichier = $this->getRoute()->getFichier();
    	$fileParam = $request->getParameter('file', null);
		if(!$fichier->visibilite && !$this->getUser()->hasCredential(myUser::CREDENTIAL_ADMIN)) {
			return $this->forwardSecure();
		}
    	if (!$fichier->hasFichiers()) {
    		return $this->forward404("Aucun fichier pour ".$fichier->_id);
    	}
    	$filename = null;
    	foreach ($fichier->_attachments as $key => $attachment) {
    		if (!$fileParam || $fileParam == $key) {
    			$filename = $key;
    		}
    	}
    	$file = file_get_contents($fichier->getAttachmentUri($filename));
        if(!$file) {
            return $this->forward404($filename." n'existe pas pour ".$fichier->_id);
        }
        $this->getResponse()->setHttpHeader('Content-Type', $fichier->getMime($fileParam));
        $this->getResponse()->setHttpHeader('Content-disposition', sprintf('attachment; filename="%s-%s-%s"', strtoupper($fichier->type), $fichier->getIdentifiant(), $filename));
        $this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
        $this->getResponse()->setHttpHeader('Pragma', '');
        $this->getResponse()->setHttpHeader('Cache-Control', 'public');
        $this->getResponse()->setHttpHeader('Expires', '0');

        return $this->renderText($file);
    }
    
    public function executeGetPiece(sfWebRequest $request) {
    	$docId = $request->getParameter('doc_id');
    	$pieceId = str_replace('-', '/', $request->getParameter('piece_id'));
    	$fileParam = $request->getParameter('file', null);
    	$client = acCouchdbManager::getClient();
    	if ($doc = $client->find($docId)) {
    		if ($doc->exist('pieces') && $doc->pieces->exist($pieceId)) {
    			$piece = $doc->pieces->get($pieceId);
    			return ($fileParam)? $this->redirect($piece->getUrl().'?file='.$fileParam) : $this->redirect($piece->getUrl());
    		} else {
    			return $this->forward404("Pièce $pieceId not found");
    		}
    	} else {
    		return $this->forward404("Document $docId not found");
    	}
    }
    
    public function executeDelete(sfWebRequest $request) {
		$this->redirect403IfIsTeledeclaration();
    	$fichier = $this->getRoute()->getFichier();
        $etablissement = $fichier->getEtablissementObject();
    	$fichier->deleteFichier($request->getParameter('file', null));
    	$fichier->save();
    	if (!$fichier->getNbFichier()) {
    		$fichier->delete();
    		return $this->redirect('fichiers_etablissement', array('identifiant' => $etablissement->identifiant));
    	}
    	return $this->redirect('upload_fichier', array('fichier_id' => $fichier->_id, 'sf_subject' => $fichier->getEtablissementObject()));
    }
    
    public function executeCsvgenerate(sfWebRequest $request) {
		$this->redirect403IfIsTeledeclaration();
    	$fichier = $this->getRoute()->getFichier();
    	$csv = "";
    	if (preg_match('/^([a-zA-Z0-9]+)-.*$/', $fichier->_id, $m)) {
    		$className = DeclarationClient::getInstance()->getExportCsvClassName($m[1]);
    		$csvOrigine = new $className($fichier);
    		$csv .= $csvOrigine->export();
    	}
    	$this->getResponse()->setHttpHeader('Content-Type', 'text/csv');
    	$this->getResponse()->setHttpHeader('Content-disposition', sprintf('attachment; filename="%s.csv"', $fichier->_id));
    	$this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
    	$this->getResponse()->setHttpHeader('Pragma', '');
    	$this->getResponse()->setHttpHeader('Cache-Control', 'public');
    	$this->getResponse()->setHttpHeader('Expires', '0');
    	
    	return $this->renderText($csv);
    }

    public function executeUpload(sfWebRequest $request) {
		$this->redirect403IfIsTeledeclaration();
    	$this->etablissement = $this->getRoute()->getEtablissement();
    	$this->fichier_id = $request->getParameter('fichier_id');
    	$this->fichier = ($this->fichier_id) ? FichierClient::getInstance()->find($this->fichier_id) : FichierClient::getInstance()->createDoc($this->etablissement->identifiant, true);
    	$this->form = new FichierForm($this->fichier);

    	if (!$request->isMethod(sfWebRequest::POST)) {
    		return sfView::SUCCESS;
    	}

    	$this->form->bind($request->getParameter($this->form->getName()), $request->getFiles($this->form->getName()));

    	if (!$this->form->isValid()) {
    		return sfView::SUCCESS;
    	}

    	$this->form->save();
    	return ($request->hasParameter('keep_page'))? $this->redirect('upload_fichier', array('fichier_id' => $this->fichier->_id, 'sf_subject' => $this->etablissement)) : $this->redirect('fichiers_etablissement', $this->etablissement);
    }

	public function executePiecesHistorique(sfWebRequest $request) {
		$this->etablissement = $this->getRoute()->getEtablissement();

		$this->year = $request->getParameter('annee', 0);
		$this->category = $request->getParameter('categorie');

		$allHistory = PieceAllView::getInstance()->getPiecesByEtablissement($this->etablissement->identifiant, $this->getUser()->hasCredential(myUser::CREDENTIAL_ADMIN));
		$this->history = ($this->year)? PieceAllView::getInstance()->getPiecesByEtablissement($this->etablissement->identifiant, $this->getUser()->hasCredential(myUser::CREDENTIAL_ADMIN), $this->year.'-01-01', $this->year.'-12-31') : $allHistory;
		$this->years = array();
		$this->categories = array();
		$this->decreases = 0;
		foreach ($allHistory as $doc) {
			if (preg_match('/^([0-9]{4})-[0-9]{2}-[0-9]{2}$/', $doc->key[PieceAllView::KEYS_DATE_DEPOT], $m)) {
				$this->years[$m[1]] = $m[1];
			}
			if ($this->year && (!isset($m[1]) || $m[1] != $this->year)) { continue; }
			if (preg_match('/^([a-zA-Z]*)\-./', $doc->id, $m)) {
				if (!isset($this->categories[$m[1]])) {
					$this->categories[$m[1]] = 0;
				}
				$this->categories[$m[1]]++;
			}
		}
		ksort($this->categories);
	}
	
	public function executeEdit(sfWebRequest $request) {
		$this->redirect403IfIsTeledeclaration();
    	$this->fichier = $this->getRoute()->getFichier();
        $this->etablissement = $this->fichier->getEtablissementObject();
		
        $this->fichier->generateDonnees();
        
        $this->form = new FichierDonneesForm($this->fichier);
        
        if (!$request->isMethod(sfWebRequest::POST)) {
        	return sfView::SUCCESS;
        }
        
        $this->form->bind($request->getParameter($this->form->getName()));
        
        if (!$this->form->isValid()) {
        	return sfView::SUCCESS;
        }
        
        $this->form->save();

        $this->getUser()->setFlash("notice", "Modifications prises en compte avec succès.");

        return $this->redirect($this->generateUrl('edit_fichier', $this->fichier));
	}
	
	public function executeNew(sfWebRequest $request) {
		$this->redirect403IfIsTeledeclaration();
    	$this->etablissement = $this->getRoute()->getEtablissement();
    	$this->campagne = $request->getParameter('campagne');
    	$this->type = $request->getParameter('type');

    	if (!$this->campagne) {
    		return $this->forward404("La création d'un fichier nécessite la campagne");
    	}

    	if (!$this->type) {
    		return $this->forward404("La création d'un fichier nécessite le type");
    	}
    	
    	$client = $this->type.'Client';
    	if ($doc = $client::getInstance()->findByArgs($this->etablissement->identifiant, $this->campagne)) {
    		return $this->redirect($this->generateUrl('edit_fichier', $doc));
    	}
		
        $doc = $client::getInstance()->createDoc($this->etablissement->identifiant, $this->campagne, true);
        if ($doc->exist('libelle')) $doc->libelle = $this->type.' '.$this->campagne.' saisie interne';
        if ($doc->exist('visibilite')) $doc->visibilite = 0;
        if ($doc->exist('date_depot')) $doc->date_depot = date('Y-m-d');
        if ($doc->exist('date_import')) $doc->date_import = date('Y-m-d');
        $doc->save();
        
        return $this->redirect($this->generateUrl('edit_fichier', $doc));
	}
	
	public function executeScrape(sfWebRequest $request) {
		$this->redirect403IfIsTeledeclaration();
		$this->etablissement = $this->getRoute()->getEtablissement();
		$this->campagne = $request->getParameter('campagne');
		$this->type = $request->getParameter('type');
	
		try {
			FichierClient::getInstance()->scrapeAndSaveFiles($this->etablissement, $this->type, $this->campagne);
		} catch(Exception $e) {
		}
	
		return $this->redirect('fichiers_etablissement', array('identifiant' => $this->etablissement->identifiant));
	}

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }

}
