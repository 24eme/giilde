<?php

class compteActions extends sfCredentialActions {

    public function executeAjout(sfWebRequest $request) {
        $this->societe = $this->getRoute()->getSociete();
        $this->compte = CompteClient::getInstance()->createCompteFromSociete($this->societe);
        $this->applyRights();
        if(!$this->modification && !$this->reduct_rights){

            return $this->forward('acVinCompte','forbidden');
        }
        $this->processFormCompte($request);
        $this->setTemplate('modification');
    }

    public function executeModification(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
        $this->societe = $this->compte->getSociete();
        $this->applyRights();
        if(!$this->modification && !$this->reduct_rights){

            return $this->forward('acVinCompte','forbidden');
        }
        $this->processFormCompte($request);
    }

    protected function processFormCompte(sfWebRequest $request) {
        $this->compteForm = new InterlocuteurForm($this->compte);
        if (!$request->isMethod(sfWebRequest::POST)) {
            return;
        }

        $this->compteForm->bind($request->getParameter($this->compteForm->getName()));

        if (!$this->compteForm->isValid()) {
            return;
        }

        $this->compteForm->save();
        return $this->redirect('compte_visualisation', $this->compte);
    }

    public function executeModificationCoordonnee(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
        $this->societe = $this->compte->getSociete();
        $this->compteForm = new CompteCoordonneeForm($this->compte);
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->compteForm->bind($request->getParameter($this->compteForm->getName()));
            if ($this->compteForm->isValid()) {
                if($this->compte->isNew()){
                    $this->compte->setStatut(EtablissementClient::STATUT_ACTIF);
                }
                $this->compteForm->save();
                $this->redirect('compte_visualisation', $this->compte);
            }
        }
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
        $this->societe = $this->compte->getSociete();
        $this->applyRights();
        if($this->compte->isSocieteContact())
            $this->redirect('societe_visualisation',array('identifiant' => $this->societe->identifiant));
        if($this->compte->isEtablissementContact())
            $this->redirect('etablissement_visualisation',array('identifiant' => preg_replace ('/^ETABLISSEMENT-/', '', $this->compte->getEtablissementOrigine())));

        $this->redirect($this->generateUrl('societe_visualisation', array('identifiant' => $this->societe->identifiant, 'interlocuteur' => $this->compte->_id)).'#'.$this->compte->_id);
    }

    public function executeSwitchStatus(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
        $newStatus = "";
        if($this->compte->isActif()){
            $newStatus = CompteClient::STATUT_SUSPENDU;
        }
        if($this->compte->isSuspendu()){
            $newStatus = CompteClient::STATUT_ACTIF;
        }

        $this->compte->setStatut($newStatus);
        $this->compte->save();
        return $this->redirect('compte_visualisation', array('identifiant' => $this->compte->identifiant));
    }

    private function initSearch(sfWebRequest $request, $extratag = null, $excludeextratag = false) {
        $query = $request->getParameter('q', '*');
        if($query == ""){
            $query.="*";
        }
        if (! $request->getParameter('contacts_all') ) {
            $query .= " doc.statut:ACTIF";
        }
        $this->selected_rawtags = array_unique(array_diff(explode(',', $request->getParameter('tags')), array('')));
        $this->selected_typetags = array();
        foreach ($this->selected_rawtags as $t) {
            if (preg_match('/^([^:]+):(.+)$/', $t, $m)) {
                if (!isset($this->selected_typetags[$m[1]])) {
                    $this->selected_typetags[$m[1]] = array();
                }
                $this->selected_typetags[$m[1]][] = $m[2];
            }
            $query .= ' doc.tags.'.$t;
        }
        $this->real_q = $query;
        if ($extratag) {
            $query .= ($excludeextratag) ? ' -' : ' ';
            $query .= 'doc.tags.manuel:'.$extratag;
        }
        $qs = new acElasticaQueryQueryString($query);
        $q = new acElasticaQuery();
        $q->setQuery($qs);
        $this->contacts_all = $request->getParameter('contacts_all');
        $this->q = $request->getParameter('q');
        $this->args = array('q' => $this->q, 'contacts_all' => $this->contacts_all, 'tags' => implode(',', $this->selected_rawtags));
        return $q;
    }

    public function executeSearchcsv(sfWebRequest $request) {
        $index = acElasticaManager::getType('COMPTE');

        $q = $this->initSearch($request);
        $q->setLimit(5000);
        $resset = $index->search($q);
        $this->results = $resset->getResults();
        $this->setLayout(false);
        $filename = 'export';

        //      $filename.=str_replace(',', '_', $this->q).'_';
        //      if(count($this->args['tags'])){
        //          $filename.= str_replace(',', '_', $this->args['tags']);
        //      }

        $attachement = "attachment; filename=".$filename.".csv";
        $this->response->setContentType('text/csv');
        $this->response->setHttpHeader('Content-Disposition',$attachement );
    }

    private function addremovetag(sfWebRequest $request, $remove = false) {
        $index = acElasticaManager::getType('COMPTE');
        $tag = Compte::transformTag($request->getParameter('tag'));
        $q = $this->initSearch($request, $tag, !$remove);

        //$q->setLimit(1000000);
        $resset = $index->search($q);

        if (!$tag) {
            throw new sfException("Un tag doit être fourni pour pouvoir être ajouté");
        }
        if (!$this->real_q) {
            throw new sfException("Il n'est pas possible d'ajouter un tag sur l'ensemble des contacts");
        }
        $cpt = 0;
        $nbimpactables =  $resset->getTotalHits();
        foreach ($resset->getResults() as $res) {
            $data = $res->getData();
            $doc = CompteClient::getInstance()->findByIdentifiant($data['doc']['identifiant'], acCouchdbClient::HYDRATE_JSON);
            if (!$doc) {
                continue;
            }
            if (!isset($doc->tags->manuel)) {
                $doc->tags->manuel = array();
            }else{
                $doc->tags->manuel = json_decode(json_encode($doc->tags->manuel), true);
            }
            if ($remove && $doc->tags->manuel) {
                $doc->tags->manuel = array_values(array_diff($doc->tags->manuel, array($tag)));
            }else{
                $doc->tags->manuel = array_unique(array_merge($doc->tags->manuel, array($tag)));
            }
            CompteClient::getInstance()->storeDoc($doc);
            $cpt++;
            if ($cpt > 200) {
                break;
            }
        }
        $q = $this->initSearch($request, $tag, !$remove);
        //$q->setLimit(1000000);
        $resset = $index->search($q);

        $nbimpactes = $resset->getTotalHits();

        $this->setTemplate('addremovetag');
        if ($nbimpactes) {
            $this->restants = $nbimpactables;
            return false;
        }

        if (!$remove && $nbimpactes) {
            $this->restants = $nbimpactes;
            return false;
        }
        return true;
    }

    public function executeAddtag(sfWebRequest $request) {
        if (!$this->addremovetag($request, false)) {
            return ;
        }
        return $this->redirect('compte_search', $this->args);
    }

    public function executeRemovetag(sfWebRequest $request) {
        if (!$this->addremovetag($request, true)) {
            return ;
        }
        $this->args['tags'] = implode(',', array_diff($this->selected_rawtags, array('manuel:'.$request->getParameter('tag'))));
        return $this->redirect('compte_search', $this->args);
    }

    public function executeSearch(sfWebRequest $request) {
        $res_by_page = 30;
        $page = $request->getParameter('page', 1);
        $from = $res_by_page * ($page - 1);

        $q = $this->initSearch($request);
        $q->setLimit($res_by_page);
        $q->setFrom($from);
        $facets = array('manuel' => 'doc.tags.manuel', 'export' => 'doc.tags.export', 'produit' => 'doc.tags.produit', 'automatique' => 'doc.tags.automatique');
        foreach($facets as $nom => $f) {
            $elasticaFacet 	= new acElasticaFacetTerms($nom);
            $elasticaFacet->setField($f);
            $elasticaFacet->setSize(100);
            $q->addFacet($elasticaFacet);
        }

        $index = acElasticaManager::getType('COMPTE');

        $resset = $index->search($q);

        $this->results = $resset->getResults();
        $this->nb_results = $resset->getTotalHits();
        $this->facets = $resset->getFacets();

        ksort($this->facets);

        $this->last_page = ceil($this->nb_results / $res_by_page);
        $this->current_page = $page;
    }
}
