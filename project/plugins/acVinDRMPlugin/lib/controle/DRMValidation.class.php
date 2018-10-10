<?php

class DRMValidation extends DocumentValidation {

    protected $isTeledeclarationDrm = false;

    public function __construct($document, $isTeledeclarationDrm = false, $options = null) {
        $this->isTeledeclarationDrm = $isTeledeclarationDrm;
        parent::__construct($document, $options);
    }

    public function configure($isTeledeclarationDrm = false) {
        $this->addControle('erreur', 'repli', "La somme des replis en entrée et en sortie n'est pas la même");
        $this->addControle('erreur', 'declassement', "La somme des déclassements en entrée et en sortie n'est pas la même");
        $this->addControle('erreur', 'regime_crd', "Le régime CRD n'a pas été rempli");
        $this->addControle('erreur', 'transfert_appellation', "La somme des transferts d'appellation en entrée et en sortie n'est pas la même");
        $this->addControle('vigilance', 'revendique_sup_initial', "Le stock revendiqué ne peut pas être supérieur au stock récolté");
        if (!$this->isTeledeclarationDrm) {
            $this->addControle('vigilance', 'vrac_detail_nonsolde', "Le contrat est soldé (ou annulé)");
            $this->addControle('erreur', 'vrac_detail_exist', "Le contrat n'existe plus");
        }
        $this->addControle('erreur', 'total_negatif', "Le stock revendiqué théorique fin de mois est négatif");
        $this->addControle('vigilance', 'vrac_detail_negatif', "Le volume qui sera enlevé sur le contrat est supérieur au volume restant");
        $this->addControle('vigilance', 'crd_negatif', "Le nombre de CRD ne dois pas être négatif");
        $this->addControle('vigilance', 'documents_annexes_erreur', "Les numéros de document d'accompagnement saisis en annexe sont mal renseignés.");
        $this->addControle('vigilance', 'siret_absent', "Le numéro de siret n'a pas été renseigné");
        $this->addControle('erreur', 'no_accises_absent', "Le numéro d'accise n'a pas été renseigné");
        $this->addControle('vigilance', 'frequence_paiement_absent', "La fréquence de paiement aux douanes n'a pas été renseigné");

        $this->addControle('erreur', 'observations', "Les observations n'ont pas été renseignées");
        $this->addControle('erreur', 'replacement_date', "Pour tout replacement, la date de sortie du produit est nécessaire. Vous ne l'avez pas saisi");

    }

    public function controle() {
        $total_entrees_replis = 0;
        $total_sorties_replis = 0;
        $total_entrees_declassement = 0;
        $total_sorties_declassement = 0;
        $total_entrees_transfert_appellation = 0;
        $total_sorties_transfert_appellation = 0;
        $total_entrees_excedents = 0;
        $total_entrees_manipulation = 0;
        $total_sorties_destructionperte = 0;

        $total_mouvement_absolu = 0;
        foreach ($this->document->getProduitsDetails() as $detail) {

            $total_mouvement_absolu += $detail->total_entrees + $detail->total_sorties;

            $entrees_excedents = ($detail->entrees->exist('excedents'))? $detail->entrees->excedents : 0.0;
            $entrees_retourmarchandisetaxees = ($detail->entrees->exist('retourmarchandisetaxees'))? $detail->entrees->retourmarchandisetaxees : 0.0;
            $entrees_retourmarchandiseacquitte = ($detail->entrees->exist('retourmarchandiseacquitte'))? $detail->entrees->retourmarchandiseacquitte : 0.0;
            $entrees_retourmarchandisesanscvo = ($detail->entrees->exist('retourmarchandisesanscvo'))? $detail->entrees->retourmarchandisesanscvo : 0.0;
            $entrees_autre = ($detail->entrees->exist('autre'))? $detail->entrees->autre : 0.0;

            $sorties_destructionperte = ($detail->sorties->exist('destructionperte'))? $detail->sorties->destructionperte : 0.0;
            $sorties_autre = ($detail->sorties->exist('autre'))? $detail->sorties->autre : 0.0;

            $total_observations_obligatoires = $entrees_excedents + $entrees_retourmarchandisetaxees + $entrees_retourmarchandisesanscvo + $sorties_destructionperte + $entrees_autre + $sorties_autre;

            $produitLibelle = " pour le produit ".$detail->getLibelle();

            if ($this->isTeledeclarationDrm) {
              if($total_observations_obligatoires && (!$detail->exist('observations') || !trim($detail->observations)))
              {
                if($entrees_excedents){
                  $this->addPoint('erreur', 'observations', "Entrée excédents (".sprintf("%.2f",$entrees_excedents)." hl)".$produitLibelle, $this->generateUrl('drm_annexes', $this->document));
                }
                if($entrees_retourmarchandisetaxees){
                  $this->addPoint('erreur', 'observations', "Entrée retour de marchandises taxées (".sprintf("%.2f",$entrees_retourmarchandisetaxees)." hl)".$produitLibelle, $this->generateUrl('drm_annexes', $this->document));
                }
                if ($entrees_retourmarchandiseacquitte) {
                  $this->addPoint('erreur', 'observations', "Entrée retour de marchandises acquittées (".sprintf("%.2f",$entrees_retourmarchandiseacquitte)." hl)".$produitLibelle, $this->generateUrl('drm_annexes', $this->document));
                }
                if($entrees_retourmarchandisesanscvo){
                  $this->addPoint('erreur', 'observations', "Entrée retour de marchandises sans CVO (".sprintf("%.2f",$entrees_retourmarchandisesanscvo)." hl)".$produitLibelle, $this->generateUrl('drm_annexes', $this->document));
                }
                if($sorties_destructionperte){
                  $this->addPoint('erreur', 'observations', "Sortie manquant (".sprintf("%.2f",$sorties_destructionperte)." hl)".$produitLibelle, $this->generateUrl('drm_annexes', $this->document));
                }
                if($entrees_autre){
                  $this->addPoint('erreur', 'observations', "Entrée autre (".sprintf("%.2f",$entrees_autre)." hl)".$produitLibelle, $this->generateUrl('drm_annexes', $this->document));
                }
                if($sorties_autre){
                  $this->addPoint('erreur', 'observations', "Sortie autre (".sprintf("%.2f",$sorties_autre)." hl)".$produitLibelle, $this->generateUrl('drm_annexes', $this->document));
                }
              }
              if(($entrees_retourmarchandisetaxees + $entrees_retourmarchandiseacquitte + $entrees_retourmarchandisesanscvo) && (!$detail->exist('replacement_date') || !$detail->replacement_date)) {
                $this->addPoint('erreur', 'replacement_date', $produitLibelle, $this->generateUrl('drm_annexes', $this->document));
              }
            }

            if ($detail->total < 0) {
                $this->addPoint('erreur', 'total_negatif', $detail->getLibelle(), $this->generateUrl('drm_edition_detail', $detail));
            }

            if($detail->getConfig()->entrees->exist('repli') && $detail->entrees->exist('repli') && $detail->sorties->exist('repli')){
                  $total_entrees_replis += $detail->entrees->repli;
                  $total_sorties_replis += $detail->sorties->repli;
            }
            if($detail->getConfig()->entrees->exist('declassement')){
                $total_entrees_declassement += $detail->entrees->declassement;
                $total_sorties_declassement += $detail->sorties->declassement;
            }

            $total_entrees_excedents += ($detail->entrees->exist('excedents')) ? $detail->entrees->excedents : 0;
            $total_entrees_manipulation += ($detail->entrees->exist('manipulation')) ? $detail->entrees->manipulation : 0;

            $total_sorties_destructionperte += ($detail->sorties->exist('destructionperte')) ? $detail->sorties->destructionperte : 0;

            if ($detail->getConfig()->entrees->exist('transfertsrecolte') && $detail->entrees->exist('transfertsrecolte')) {
              $total_entrees_transfert_appellation += $detail->entrees->transfertsrecolte;
            }

            if ($detail->getConfig()->sorties->exist('transfertsrecolte') && $detail->sorties->exist('transfertsrecolte')) {
              $total_sorties_transfert_appellation += $detail->sorties->transfertsrecolte;
            }
            if ($detail->getConfig()->sorties->exist('manipulationssoutirages') && $detail->sorties->exist('manipulationssoutirages')) {
              $total_sorties_transfert_appellation += $detail->sorties->manipulationssoutirages;
            }
            if($detail->total_revendique  > $detail->total){
                $this->addPoint('vigilance', 'revendique_sup_initial', $detail->getLibelle(), $this->generateUrl('drm_edition_detail', $detail));
            }
        }

        $volumes_restant = array();
        if (!$this->isTeledeclarationDrm && !DRMConfiguration::getInstance()->isVracCreation()) {
            foreach ($this->document->getMouvementsCalculeByIdentifiant($this->document->identifiant, $this->isTeledeclarationDrm) as $mouvement) {
                if ($mouvement->isVrac()) {
                    $vrac = $mouvement->getVrac();
                    if (!$vrac) {
                        $this->addPoint('erreur', 'vrac_detail_exist', sprintf("%s, Contrat n°%s avec %s", $mouvement->produit_libelle, $mouvement->detail_libelle, $mouvement->vrac_destinataire), $this->generateUrl('drm_edition_detail', $detail));
                        continue;
                    }
                    $id_volume_restant = $mouvement->produit_hash . $mouvement->vrac_numero;
                    if (!isset($volumes_restant[$id_volume_restant])) {
                        $volumes_restant[$id_volume_restant]['volume'] = $vrac->volume_propose - $vrac->volume_enleve;
                        $volumes_restant[$id_volume_restant]['vrac'] = $vrac;
                    }
                    $volumes_restant[$id_volume_restant]['volume'] += $mouvement->volume;

                    if ($vrac->valide->statut != VracClient::STATUS_CONTRAT_NONSOLDE) {
                        $this->addPoint('vigilance', 'vrac_detail_nonsolde', sprintf("Contrat %s", $mouvement->produit_libelle, $vrac->__toString()), $this->generateUrl('vrac_visualisation', $vrac));
                        continue;
                    }
                }
            }
          foreach ($volumes_restant as $is => $restant) {
            if ($restant['volume'] < 0) {
                $vrac = $restant['vrac'];
                $this->addPoint('vigilance', 'vrac_detail_negatif', sprintf("%s, Contrat %s (%01.02f hl enlevé / %01.02f hl proposé)", $vrac->produit_libelle, $vrac->__toString(), $vrac->volume_propose - $restant['volume'], $vrac->volume_propose), $this->generateUrl('drm_edition', $this->document));
            }
          }
        }
        if (round($total_entrees_replis, 2) != round($total_sorties_replis, 2)) {
            $this->addPoint('erreur', 'repli', sprintf("%s  (+%.2fhl / -%.2fhl)", 'revenir aux mouvements', round($total_entrees_replis, 2), round($total_sorties_replis, 2)), $this->generateUrl('drm_edition', $this->document));
        }
        if (round($total_entrees_transfert_appellation, 2) != round($total_sorties_transfert_appellation, 2)) {
            $this->addPoint('erreur', 'transfert_appellation', sprintf("%s  (+%.2fhl / -%.2fhl)", 'revenir aux mouvements', round($total_entrees_transfert_appellation, 2), round($total_sorties_transfert_appellation, 2)), $this->generateUrl('drm_edition', $this->document));
        }
        if ($this->isTeledeclarationDrm) {
            if (round($total_entrees_declassement, 2) != round($total_sorties_declassement, 2)) {
                $this->addPoint('erreur', 'declassement', sprintf("%s  (+%.2fhl / -%.2fhl)", 'revenir aux mouvements', round($total_entrees_declassement, 2), round($total_sorties_declassement, 2)), $this->generateUrl('drm_edition', $this->document));
            }
        }
        if ($this->isTeledeclarationDrm) {

            if ($total_mouvement_absolu && (!$this->document->getEtablissement()->exist('crd_regime') || !$this->document->getEtablissement()->get('crd_regime'))) {
                $this->addPoint('erreur', 'regime_crd', "vous pouvez l'indiquer dans l'écran CRD", $this->generateUrl('drm_crd', $this->document));
            }

            foreach ($this->document->getAllCrdsByRegimeAndByGenre() as $regime => $crdByRegime) {
                foreach ($crdByRegime as $genre => $crds) {
                    foreach ($crds as $type_crd => $crd) {
                        if (!is_null($crd->stock_fin) && $crd->stock_fin < 0) {
                            $genreLibelle = ($genre == 'TRANQ') ? 'TRANQUILLE' : $genre;
                            $this->addPoint('vigilance', 'crd_negatif', $crd->getLibelle() . ' (' . $genreLibelle . ')', $this->generateUrl('drm_crd', $this->document));
                        }
                    }
                }
            }

            if (!$this->document->societe->siret) {
                $this->addPoint('vigilance', 'siret_absent', 'Veuillez enregistrer votre siret', $this->generateUrl('drm_validation_update_societe', $this->document));
            }
            if (!$this->document->declarant->no_accises) {
                $this->addPoint('erreur', 'no_accises_absent', 'Veuillez enregistrer votre numéro d\'accise', $this->generateUrl('drm_validation_update_etablissement', $this->document));
            }

            $societe = $this->document->getEtablissement()->getSociete();


            if (!$this->document->societe->exist('paiement_douane_frequence') || !$this->document->societe->paiement_douane_frequence) {
                $this->addPoint('vigilance', 'frequence_paiement_absent', 'Veuillez enregistrer votre fréquence de paiement', $this->generateUrl('drm_validation_update_societe', $this->document));
            }


        }

        $sortiesDocAnnexes = array();
        foreach ($this->document->getProduitsDetails($this->document->teledeclare,'details') as $detail) {
            if (count($detail->sorties->export_details)) {
                foreach ($detail->sorties->export_details as $paysCode => $export) {
                    if ($export->numero_document) {
                        $sortiesDocAnnexes[$export->type_document] = $export->numero_document;
                    }
                }
            }
            if ($detail->sorties->exist('vrac_details') && count($detail->sorties->vrac_details)) {
                foreach ($detail->sorties->vrac_details as $num_vrac => $vrac) {
                    if ($vrac->numero_document) {
                        $sortiesDocAnnexes[$vrac->type_document] = $vrac->numero_document;
                    }
                }
            }
        }
        foreach ($sortiesDocAnnexes as $type_doc => $num) {
            $doc_annexe = $this->document->documents_annexes;
            foreach (array_keys(DRMClient::$drm_documents_daccompagnement) as $document_accompagnement_type) {
                if (($type_doc == $document_accompagnement_type) && ($type_doc != DRMClient::DRM_DOCUMENTACCOMPAGNEMENT_DAE) &&
                        ((!$doc_annexe->exist($document_accompagnement_type)) || (!$doc_annexe->$document_accompagnement_type->fin) || (!$doc_annexe->$document_accompagnement_type->debut)
                        )) {
                    $this->addPoint('vigilance', 'documents_annexes_erreur', 'retour aux annexes', $this->generateUrl('drm_annexes', $this->document));
                }
            }
        }
    }

}
