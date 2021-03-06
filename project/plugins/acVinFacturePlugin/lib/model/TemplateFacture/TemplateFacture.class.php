<?php

/**
 * Model for TemplateFacture
 *
 */
class TemplateFacture extends BaseTemplateFacture {
/** INUTILE => PLUS DE TEMPLATE GIILDA **/
    public function generateCotisations($identifiant_or_societe, $campagne, $force = false) {
        $template = $this;
        $societe = $identifiant_or_societe;
        if (is_string($societe)) {
            $societe = SocieteClient::getInstance()->findByIdentifiant($identifiant_or_societe);
        }

        $cotisations = array();
        foreach ($this->docs as $doc) {
            $document = $this->getDocumentFacturable($doc, $societe->identifiant, $campagne);

            if (!$document) {

                throw new sfException(sprintf("Le document %s n'a pas été trouvé (%s-%s-%s)", strtoupper($doc), strtoupper($doc), $compte->identifiant, $campagne));
            }

            if (!count($document->mouvements)) {
                $document->generateMouvements();
                $document->save();
            }

            if ($document->isFactures() && !$force) {
                continue;
            }
            $this->generateCotisationsForFacture($cotisations,$societe,$document);
        }
        if(!count($this->docs)){
            $this->generateCotisationsForFacture($cotisations,$societe);
        }
        return $cotisations;
    }

    
    /*** INUTILE ICI PLUS DE GENERATION POUR LES FACTURE A PARTIR DE COTISATION... */
    public function generateCotisationsForFacture(&$cotisations,$societe,$document = null) {
        foreach ($this->cotisations as $key => $cotisation) {

            $modele = $cotisation->modele;

            $object = new $modele($societe, $cotisation->callback);
            $details = $object->getDetails($cotisation->details);
        

            if (!in_array($cotisation->libelle, array_keys($cotisations))) {
                $cotisations[$key] = array();
                $cotisations[$key]["libelle"] = $cotisation->libelle;
                $cotisations[$key]["code_comptable"] = $cotisation->code_comptable;
                $cotisations[$key]["details"] = array();
                $cotisations[$key]["origines"] = array();
            }
            foreach ($details as $type => $detail) {
                $docs = $detail->docs->toArray();
                
                if ($document && in_array($document->type, $docs)) {
                    $modele = $detail->modele;
                    $object = new $modele($template, $document, $detail);
                    if ($key == 'syndicat_viticole') {
                        $cotisations[$key]["details"][] = array("libelle" => $object->getLibelle(), "taux" => $detail->tva, "prix" => $object->getTotal(), "total" => $object->getTotal(), "tva" => $object->getTva(), "quantite" => 1);
                    } else {
                        $cotisations[$key]["details"][] = array("libelle" => $object->getLibelle(), "taux" => $detail->tva, "prix" => $object->getPrix(), "total" => $object->getTotal(), "tva" => $object->getTva(), "quantite" => $object->getQuantite());
                    }
                    $cotisations[$key]["origines"][$document->_id] = array($this->_id);
                }
                if(!$document){
                     $cotisations[$key]["details"][] = array("libelle" => $detail->libelle, "taux" => $detail->tva, "prix" => round($detail->prix,2), "total" => round($detail->prix,2), "tva" => round($detail->prix * $detail->tva,2), "quantite" => 1);
                }
            }
        }
    }

    public function getDocumentFacturable($docModele, $identifiant, $campagne) {
        $client = acCouchdbManager::getClient($docModele);
        if ($client instanceof FacturableClient) {

            return $client->findFacturable($identifiant, $campagne);
        }
        throw new sfException($docModele . 'Client must implements FacturableClient interface');
    }

    public function getCampagne() {
        $campagne = $this->_get('campagne');
        if (!$campagne) {
            $campagne = date('Y');
        }
        return $campagne;
    }

}
