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
        }else{
          $this->addControle('vigilance', 'alcool_hlap', "Pour cet alcool");
        }
        $this->addControle('erreur', 'vrac_achateur_exists', "Le contrat n'a pas d'acheteur connu");
        $this->addControle('erreur', 'total_negatif', "Le stock revendiqué théorique fin de mois est négatif");
        $this->addControle('vigilance', 'vrac_detail_negatif', "Le volume qui sera enlevé sur le contrat est supérieur au volume restant");
        $this->addControle('vigilance', 'crd_negatif', "Le nombre de CRD ne dois pas être négatif");
        $this->addControle('vigilance', 'documents_annexes_vigilance', "Les numéros de document d'accompagnement saisis en annexe sont mal renseignés.");
        $this->addControle('erreur', 'documents_annexes_erreur', "La saisie de document d'accompagnement n'est pas complètement renseignée");
        $this->addControle('vigilance', 'siret_absent', "Le numéro de siret n'a pas été renseigné");
        $this->addControle('erreur', 'no_accises_absent', "Le numéro d'accise n'a pas été renseigné");

        $this->addControle('erreur', 'observations', "Les observations n'ont pas été renseignées");
        $this->addControle('erreur', 'replacement_date', "Pour tout type de replacement, la date de sortie du produit est nécessaire. Vous ne l'avez pas saisi");

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
            $entrees_cooperative = ($detail->entrees->exist('cooperative'))? $detail->entrees->cooperative : 0.0;
            $entrees_retourmarchandisenontaxees = ($detail->entrees->exist('retourmarchandisenontaxees'))? $detail->entrees->retourmarchandisenontaxees : 0.0;
            
            // entrees drm negoce
            $entrees_declassement = ($detail->entrees->exist('declassement'))? $detail->entrees->declassement : 0.0;
            $entrees_retourembouteillage = ($detail->entrees->exist('retourembouteillage'))? $detail->entrees->retourembouteillage : 0.0;
            $entrees_transfertcomptamatierecession = ($detail->entrees->exist('transfertcomptamatierecession'))? $detail->entrees->transfertcomptamatierecession : 0.0;
            $entrees_regularisation = ($detail->entrees->exist('regularisation'))? $detail->entrees->regularisation : 0.0;

            $sorties_manquant = ($detail->sorties->exist('manquant'))? $detail->sorties->manquant : 0.0;
            $sorties_autre = ($detail->sorties->exist('autre'))? $detail->sorties->autre : 0.0;
            
            // sorties drm negoce
            $sorties_apportgroupement = ($detail->sorties->exist('apportgroupement'))? $detail->sorties->apportgroupement : 0.0;
            $sorties_declassement = ($detail->sorties->exist('declassement'))? $detail->sorties->declassement : 0.0;
            $sorties_transfertcomptamatiere = ($detail->sorties->exist('transfertcomptamatiere'))? $detail->sorties->transfertcomptamatiere : 0.0;
            $sorties_cession = ($detail->sorties->exist('cession'))? $detail->sorties->cession : 0.0;

            $total_observations_obligatoires = $entrees_excedents + $entrees_retourmarchandisetaxees + $entrees_retourmarchandisesanscvo + $entrees_cooperative + $sorties_manquant + $entrees_autre + $sorties_autre;
            
            if ($this->document->isNegoce()) {
                $total_observations_obligatoires += $entrees_declassement + $entrees_retourembouteillage + $entrees_transfertcomptamatierecession + $entrees_regularisation + $sorties_apportgroupement + $sorties_declassement + $sorties_transfertcomptamatiere + $sorties_cession;
            }
            
            $produitLibelle = " pour le produit ".$detail->getLibelle();

            if ($this->isTeledeclarationDrm) {

              if(DRMConfiguration::getInstance()->hasWarningForProduit()){
                $msgs = DRMConfiguration::getInstance()->getWarningsMessagesForProduits(array($detail->getHash() => ""));
                if(count($msgs)){
                  $this->addPoint('vigilance', 'alcool_hlap', '<a href="'.$this->generateUrl('drm_edition_detail', $detail)."\">".$detail->getLibelle()."</a> , les mouvements d'entrées et de sorties doivent être renseignés en HL (et non en HLAP). Un taux d'alcool volumique \"TAV\" doit être renseigné dans les <a href=\"".$this->generateUrl('drm_annexes', $this->document)."\">annexes</a>");
                }
              }

              if($detail->getParent()->getKey() == 'details' && $total_observations_obligatoires && (!$detail->exist('observations') || !trim($detail->observations)))
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
                if($entrees_cooperative){
                  $this->addPoint('erreur', 'observations', "Entrée coopérative (".sprintf("%.2f",$entrees_cooperative)." hl)".$produitLibelle, $this->generateUrl('drm_annexes', $this->document));
                }
                if($sorties_manquant){
                  $this->addPoint('erreur', 'observations', "Sortie manquant (".sprintf("%.2f",$sorties_manquant)." hl)".$produitLibelle, $this->generateUrl('drm_annexes', $this->document));
                }
                if($entrees_autre){
                  $this->addPoint('erreur', 'observations', "Entrée autre (".sprintf("%.2f",$entrees_autre)." hl)".$produitLibelle, $this->generateUrl('drm_annexes', $this->document));
                }
                if($sorties_autre){
                  $this->addPoint('erreur', 'observations', "Sortie autre (".sprintf("%.2f",$sorties_autre)." hl)".$produitLibelle, $this->generateUrl('drm_annexes', $this->document));
                }
                if($entrees_retourmarchandisenontaxees){
                  $this->addPoint('erreur', 'observations', "Entrée retour de marchandises non taxées (".sprintf("%.2f",$entrees_retourmarchandisenontaxees)." hl)".$produitLibelle, $this->generateUrl('drm_annexes', $this->document));
                }
                
                if ($this->document->isNegoce()) {
                    if($entrees_declassement){
                        $this->addPoint('erreur', 'observations', "Entrée declassement (".sprintf("%.2f",$entrees_declassement)." hl)".$produitLibelle, $this->generateUrl('drm_annexes', $this->document));
                    }
                    if($entrees_retourembouteillage){
                        $this->addPoint('erreur', 'observations', "Entrée retour embouteillage (".sprintf("%.2f",$entrees_retourembouteillage)." hl)".$produitLibelle, $this->generateUrl('drm_annexes', $this->document));
                    }
                    if($entrees_transfertcomptamatierecession){
                        $this->addPoint('erreur', 'observations', "Entrée transfert compta matiere cession (".sprintf("%.2f",$entrees_transfertcomptamatierecession)." hl)".$produitLibelle, $this->generateUrl('drm_annexes', $this->document));
                    }
                    if($entrees_regularisation){
                        $this->addPoint('erreur', 'observations', "Entrée regularisation (".sprintf("%.2f",$entrees_regularisation)." hl)".$produitLibelle, $this->generateUrl('drm_annexes', $this->document));
                    }
                    if($sorties_apportgroupement){
                        $this->addPoint('erreur', 'observations', "Sortie apport groupement (".sprintf("%.2f",$sorties_apportgroupement)." hl)".$produitLibelle, $this->generateUrl('drm_annexes', $this->document));
                    }
                    if($sorties_declassement){
                        $this->addPoint('erreur', 'observations', "Sortie declassement (".sprintf("%.2f",$sorties_declassement)." hl)".$produitLibelle, $this->generateUrl('drm_annexes', $this->document));
                    }
                    if($sorties_transfertcomptamatiere){
                        $this->addPoint('erreur', 'observations', "Sortie transfert compta matiere (".sprintf("%.2f",$sorties_transfertcomptamatiere)." hl)".$produitLibelle, $this->generateUrl('drm_annexes', $this->document));
                    }
                    if($sorties_cession){
                        $this->addPoint('erreur', 'observations', "Sortie cession (".sprintf("%.2f",$sorties_cession)." hl)".$produitLibelle, $this->generateUrl('drm_annexes', $this->document));
                    }
                }
              }
              if($detail->getParent()->getKey() == 'details' && ($entrees_retourmarchandisetaxees + $entrees_retourmarchandiseacquitte + $entrees_retourmarchandisesanscvo + $entrees_cooperative) && (!$detail->exist('replacement_date') || !$detail->replacement_date)) {
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

            if ($detail->getConfig()->entrees->exist('transfertsrecolte') && $detail->entrees->exist('transfertsrecolte') && !$detail->isAlcoolPurOrMatierePremiere()) {
              $total_entrees_transfert_appellation += $detail->entrees->transfertsrecolte;
            }

            if ($detail->getConfig()->sorties->exist('transfertsrecolte') && $detail->sorties->exist('transfertsrecolte') && !$detail->isAlcoolPurOrMatierePremiere()) {
              $total_sorties_transfert_appellation += $detail->sorties->transfertsrecolte;
            }

            if($detail->total_revendique  > $detail->total){
                $this->addPoint('vigilance', 'revendique_sup_initial', $detail->getLibelle(), $this->generateUrl('drm_edition_detail', $detail));
            }
            if ($detail->getConfig()->sorties->exist('creationvrac') && $detail->sorties->exist('creationvrac_details')) {
                foreach ($detail->sorties->creationvrac_details as $k => $dvrac) {
                    if (!$dvrac->acheteur){
                        $this->addPoint('erreur', 'vrac_achateur_exists', sprintf("Contrat vrac de %d à %d €/hl", $dvrac->volume, $dvrac->prixhl), $this->generateUrl('drm_edition_detail', $detail));
                    }
                }
            }
            if ($detail->getConfig()->sorties->exist('creationvractirebouche') && $detail->sorties->exist('creationvractirebouche_details')) {
                foreach ($detail->sorties->creationvractirebouche_details as $k => $dvrac) {
                    if (!$dvrac->acheteur){
                        $this->addPoint('erreur', 'vrac_achateur_exists', sprintf("Contrat bouteilles de %d à %d €/hl", $dvrac->volume, $dvrac->prixhl), $this->generateUrl('drm_edition_detail', $detail));
                    }
                }
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
                    $this->addPoint('vigilance', 'documents_annexes_vigilance', 'retour aux annexes', $this->generateUrl('drm_annexes', $this->document));
                }
            }
        }
        foreach ($this->document->documents_annexes as $documentAnnexe) {
            if ((!$documentAnnexe->debut || !$documentAnnexe->fin || !$documentAnnexe->nb) && ($documentAnnexe->debut || $documentAnnexe->fin || $documentAnnexe->nb)) {
                $this->addPoint('erreur', 'documents_annexes_erreur', 'retour aux annexes', $this->generateUrl('drm_annexes', $this->document));
            }
        }
    }

}
