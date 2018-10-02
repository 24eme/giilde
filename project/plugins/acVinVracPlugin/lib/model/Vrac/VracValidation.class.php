<?php

class VracValidation extends DocumentValidation {

    protected $teledeclaration = false;

    public function __construct($document, $isTeledeclarationMode = false, $options = null) {
        $this->teledeclaration = $isTeledeclarationMode;
        parent::__construct($document, $options);
    }

    public function configure() {
        if ($this->teledeclaration) {
            $this->addControle('vigilance', 'soussigne_vendeur_nonactif', "Le compte du vendeur n'est pas actif");
            $this->addControle('vigilance', 'soussigne_acheteur_nonactif', "Le compte de l'acheteur n'est pas actif");
            $this->addControle('vigilance', 'soussigne_courtier_nonactif', "Le compte du courtier n'est pas actif");
            $this->addControle('erreur', 'date_enlevement_inf_minimale', "La date d'enlèvement est inférieure à la date minimale d'enlèvement du produit");

            $this->addControle('erreur', 'soussigne_vendeur_absence_mail', "Aucun email renseigné pour");
            $this->addControle('erreur', 'soussigne_acheteur_absence_mail', "Aucun email renseigné pour");
            $this->addControle('erreur', 'soussigne_courtier_absence_mail', "Aucun email renseigné pour");

            $this->addControle('erreur', 'date_enlevement_sup_maximale', "La date d'enlèvement est supérieure à la date maximale d'enlèvement du produit");
        } else {
            $this->addControle('vigilance', 'stock_commercialisable_negatif', 'Le stock commercialisable est inférieur au stock proposé');
            $this->addControle('vigilance', 'contrats_similaires', null);
            $this->addControle('vigilance', 'prix_definitif_expected', "Le prix définitif de contrat n'a pas été saisi");
        }
        $this->addControle('vigilance', 'dates_retiraison_visa', 'La date de retiraison ne peut pas être inférieure à la date du contrat');

        $this->addControle('erreur', 'volume_expected', 'Le volume du contrat est manquant');
        $this->addControle('erreur', 'prix_initial_expected', 'Le prix du contrat est manquant');
        $this->addControle('erreur', 'dates_retiraison', 'La date limite de retiraison ne peut pas être inférieure à la date de début de retiraison');
        $this->addControle('erreur', 'viti_raisins_mouts_type_vins', "Le viticulteur ne peut pas faire de contrats de vins (il possède une exclusivité de raisins/moûts)");
        $this->addControle('erreur', 'quantite_raisin_surface_expected', "La quantité et/ou la surface sont requises");
        $this->addControle('erreur', 'quantite_raisin_surface_expected', "La quantité et/ou la surface sont requises");
        $this->addControle('erreur', 'cepage_autorise', "Cépage non autorisé pour le produit");
    }

    public function controle() {

        if ($this->teledeclaration) {
            $this->checkSoussigneAbsenceMail();
            $this->checkSoussigneCompteNonActive();
            $this->checkDateEnlevement();
        } else {
            $contrats_similaires = VracClient::getInstance()->retrieveSimilaryContracts($this->document);
            if ($nbsimilaires = count(array_keys($contrats_similaires))) {
                $contrat_similaires_str = $nbsimilaires . ' contrat(s) similaire(s) possèdant les même soussignés, produit et volume (ou quantité) ';
                foreach ($contrats_similaires as $contrat_similaire) {
                    $vrac_sim = VracClient::getInstance()->find($contrat_similaire->id);
                    $contrat_similaires_str.= "&nbsp;&nbsp;&nbsp;  <a href='" . $this->generateUrl('vrac_visualisation', $vrac_sim) . "'>" . substr($vrac_sim->getNumeroContrat(), 0, 4) . ' ' . substr($vrac_sim->getNumeroContrat(), 4) . "</a>";
                }
                $this->addPoint('vigilance', 'contrats_similaires', $contrat_similaires_str);
            }

            if ($this->document->hasPrixVariable() && !$this->document->hasPrixDefinitif()) {
                $this->addPoint('vigilance', 'prix_definitif_expected', 'Saisir le prix définitif', $this->generateUrl('vrac_marche', $this->document));
            }
        }

        if (!$this->document->volume_propose && $this->document->type_transaction != VracClient::TYPE_TRANSACTION_RAISINS) {
            $this->addPoint('erreur', 'volume_expected', 'Saisir un volume', $this->generateUrl('vrac_marche', $this->document));
        } elseif ($this->document->type_transaction == VracClient::TYPE_TRANSACTION_RAISINS && !$this->document->surface && !$this->document->raisin_quantite) {
            $this->addPoint('erreur', 'quantite_raisin_surface_expected', "Saisir au moins l'une de ces informations", $this->generateUrl('vrac_marche', $this->document));
        } elseif (!$this->document->volume_propose && $this->document->type_transaction == VracClient::TYPE_TRANSACTION_RAISINS) {
            $this->addPoint('vigilance', 'quantite_raisin_expected', 'Saisir la quantité', $this->generateUrl('vrac_marche', $this->document));
        }

        if ($this->document->isVitiRaisinsMoutsTypeVins()) {
            $this->addPoint('erreur', 'viti_raisins_mouts_type_vins', 'Sodifier vendeur', $this->generateUrl('etablissement_visualisation', EtablissementClient::getInstance()->find($this->document->vendeur_identifiant)));
        }
        if (is_null($this->document->prix_initial_unitaire)) {
            $this->addPoint('erreur', 'prix_initial_expected', 'Saisir un prix', $this->generateUrl('vrac_marche', $this->document));
        }
        if ($this->document->date_debut_retiraison && $this->document->date_limite_retiraison) {
            if ($this->document->date_limite_retiraison < $this->document->date_debut_retiraison) {
                $this->addPoint('erreur', 'dates_retiraison', 'Modifier la date', $this->generateUrl('vrac_condition', $this->document));
            }
        }

        if ($this->document->exist('versement_fa') && ($this->document->versement_fa == VracClient::VERSEMENT_FA_NOUVEAU) && $this->document->date_debut_retiraison && $this->document->date_debut_retiraison < $this->document->date_campagne) {
            $this->addPoint('vigilance', 'dates_retiraison_visa', 'Modifier la date', $this->generateUrl('vrac_condition', $this->document));
        }

        if (!$this->document->isCepageAutorise()) {
            $this->addPoint('erreur', 'cepage_autorise', 'Modifier le cépage', $this->generateUrl('vrac_marche', $this->document));
        }
    }

    private function checkSoussigneAbsenceMail() {
        $vendeurEtb = EtablissementClient::getInstance()->findByIdentifiant($this->document->vendeur_identifiant);

        if (!$vendeurEtb->getEmailTeledeclaration()) {
            $this->addPoint('erreur', 'soussigne_vendeur_absence_mail', $vendeurEtb->nom);
        }

        $acheteurEtb = EtablissementClient::getInstance()->findByIdentifiant($this->document->acheteur_identifiant);
        if (!$acheteurEtb->getEmailTeledeclaration()) {
            $this->addPoint('erreur', 'soussigne_acheteur_absence_mail', $acheteurEtb->nom);
        }
        if ($this->document->mandataire_exist) {
            $courtierEtb = EtablissementClient::getInstance()->findByIdentifiant($this->document->mandataire_identifiant);
            if (!$courtierEtb->getEmailTeledeclaration()) {
                $this->addPoint('erreur', 'soussigne_courtier_absence_mail', $courtierEtb->nom);
            }
        }
    }

    private function checkSoussigneCompteNonActive() {
        $vendeurEtb = EtablissementClient::getInstance()->findByIdentifiant($this->document->vendeur_identifiant);
        $vendeurCompte = CompteClient::getInstance()->find($vendeurEtb->getSociete()->getCompteSociete());
        if (!$vendeurCompte->isTeledeclarationActive()) {
            $this->addPoint('vigilance', 'soussigne_vendeur_nonactif', '');
        }

        $acheteurEtb = EtablissementClient::getInstance()->findByIdentifiant($this->document->acheteur_identifiant);
        $acheteurCompte = CompteClient::getInstance()->find($acheteurEtb->getSociete()->getCompteSociete());
        if (!$acheteurCompte->isTeledeclarationActive()) {
            $this->addPoint('vigilance', 'soussigne_acheteur_nonactif', '');
        }
        if ($this->document->mandataire_exist) {
            $courtierEtb = EtablissementClient::getInstance()->findByIdentifiant($this->document->mandataire_identifiant);
            $courtierCompte = CompteClient::getInstance()->find($courtierEtb->getSociete()->getCompteSociete());

            if (!$courtierCompte->isTeledeclarationActive()) {
                $this->addPoint('vigilance', 'soussigne_courtier_nonactif', '');
            }
        }
    }

    private function checkDateEnlevement() {
        $produits = ConfigurationClient::getCurrent()->getProduits();
        $produit = null;
        foreach ($produits as $hash => $produitConf) {
                if ($this->document->produit == $hash) {
                    $produit = $produitConf;
                    break;
                }
        }
        $millesime = null;
        if (!($millesime = $this->document->millesime)) {
            return;
        }
        if (!$produit || !$produit->getDateCirulation($millesime)) {
            return;
        }
        $date_debut = $produit->getDateCirulation($millesime)->date_debut;
        $date_fin = $produit->getDateCirulation($millesime)->date_fin;
        if ($this->document->enlevement_date && $date_debut) {
            if (str_replace('-', '', $this->document->enlevement_date) < str_replace('-', '', $date_debut)) {
                $date_debutArr = explode('-', $date_debut);
                $date_debutFR = $date_debutArr[2] . '/' . $date_debutArr[1] . '/' . $date_debutArr[0];
                $this->addPoint('erreur', 'date_enlevement_inf_minimale', ' (' . $date_debutFR . ')');
            }
        }
        if ($this->document->enlevement_date && $date_fin) {
            if (str_replace('-', '', $this->document->enlevement_date) > str_replace('-', '', $date_fin)) {
                $date_finArr = explode('-', $date_fin);
                $date_finFR = $date_finArr[2] . '/' . $date_finArr[1] . '/' . $date_finArr[0];
                $this->addPoint('erreur', 'date_enlevement_sup_maximale', ' (' . $date_finFR . ')');
            }
        }
    }

}
