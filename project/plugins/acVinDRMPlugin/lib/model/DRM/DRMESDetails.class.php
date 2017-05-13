<?php

/**
 * Model for DRMESDetails
 *
 */
class DRMESDetails extends BaseDRMESDetails {

    public function update($params = array()) {
        parent::update($params);
    }

    public function getNoeud() {

        return $this->getParent();
    }

    public function getTotalHash() {
        return str_replace('_details', '', $this->getKey());
    }

    public function getProduitDetail() {

        return $this->getParent()->getParent();
    }

    public function init($params = array()) {
        parent::init($params);

        $this->getParent()->remove($this->getKey());
        $this->getParent()->add($this->getKey());
    }

    public function addDetail($detail) {
         return $this->add($detail->getKey(),$detail);
    }

    public function createMouvements($template_mouvement) {
        $mouvements = array();

        // Check les éventuels suppressions
        if ($this->getDocument()->hasVersion() && $this->getDocument()->motherExist($this->getHash())) {
            $mother_this = $this->getDocument()->motherGet($this->getHash());
            foreach ($mother_this as $key => $mother_detail) {
                if (!$this->exist($key)) {
                    $detail = $this->add($key, $mother_detail);
                    $detail->volume = 0;
                    $this->pushMouvement($mouvements, $template_mouvement, $detail);
                    $this->remove($key);
                }
            }
        }

        foreach ($this as $detail) {
            $this->pushMouvement($mouvements, $template_mouvement, $detail);
        }

        return $mouvements;
    }

    public function pushMouvement(&$mouvements, $template_mouvement, $detail) {
        $mouvement = $this->createMouvement(clone $template_mouvement, $detail);
        if (!$mouvement) {
            return;
        }
        $md5 = $mouvement->getMD5Key();
        $mouvements[$this->getDocument()->getIdentifiant()][$md5] = $mouvement;

        if ($mouvement_vrac_destinataire = $this->createMouvementVracDestinataire(clone $mouvement, $detail)) {
            $mouvements[$detail->getVrac()->acheteur_identifiant][$mouvement->getMD5Key()] = $mouvement_vrac_destinataire;
            $mouvements[$this->getDocument()->getIdentifiant()][$md5]['region_destinataire'] = $mouvement_vrac_destinataire->region;
        }

        if ($mouvement_vrac_intermediaire = $this->createMouvementVracIntermediaire(clone $mouvement, $detail)) {
            $mouvements[$detail->getVrac()->representant_identifiant][$mouvement->getMD5Key()] = $mouvement_vrac_intermediaire;
        }


    }

    public function createMouvement($mouvement, $detail) {
        $volume = $detail->volume;

        if ($this->getDocument()->hasVersion() && $this->getDocument()->motherExist($detail->getHash())) {
            $volume = $volume - $this->getDocument()->motherGet($detail->getHash())->volume;
        }

        $config = $this->getProduitDetail()->getConfig()->get($this->getNoeud()->getKey() . '/' . $this->getTotalHash());
        $volume = $config->mouvement_coefficient * $volume;

        if ($volume == 0) {

            return null;
        }

        $mouvement->detail_identifiant = $detail->identifiant;
        $mouvement->detail_libelle = $detail->getIdentifiantLibelle();
        $mouvement->type_libelle = $config->getLibelle();
        $mouvement->type_hash .= $this->getKey();
        $mouvement->volume = $volume;

        if ($config->isVrac()) {
            $mouvement->categorie = FactureClient::FACTURE_LIGNE_PRODUIT_TYPE_VINS;
            $mouvement->cvo = $this->getProduitDetail()->getCVOTaux();
            if(!$detail->isSansContrat()) {
                $mouvement->vrac_numero = $detail->getVrac()->numero_contrat;
                $mouvement->vrac_destinataire = (isset($detail->getVrac()->acheteur->nom)) ? $detail->getVrac()->acheteur->nom : $detail->getVrac()->acheteur->raison_sociale;
                if($detail->getVrac() instanceof Vrac) {
                    $mouvement->cvo = $this->getProduitDetail()->getCVOTaux() * $detail->getVrac()->getRepartitionCVOCoef($detail->getVrac()->vendeur_identifiant, $detail->getDocument()->getDate());
                }
            }
        }

        $mouvement->date = $detail->date_enlevement;

        return $mouvement;
    }

    public function createMouvementVracDestinataire($mouvement, $detail) {
        $config = $this->getProduitDetail()->getConfig()->get($this->getNoeud()->getKey() . '/' . $this->getTotalHash());

        if (!$config->isVrac() || $detail->isSansContrat() || !$detail->getVrac() instanceof Vrac) {

            return null;
        }

        $mouvement->vrac_destinataire = $detail->getVrac()->vendeur->nom;
        $mouvement->region = $detail->getVrac()->getAcheteurObject()->region;
        $mouvement->cvo = $this->getProduitDetail()->getCVOTaux() * $detail->getVrac()->getRepartitionCVOCoef($detail->getVrac()->acheteur_identifiant, $detail->getDocument()->getDate());
        if ($mouvement->cvo > 0 && $mouvement->volume) {
            $mouvement->facturable = 1;
        }
        if($this->getDocument()->isDrmNegoce()){
            $mouvement->facturable = 0;
        }
        return $mouvement;
    }

    public function createMouvementVracIntermediaire($mouvement, $detail) {
        $config = $this->getProduitDetail()->getConfig()->get($this->getNoeud()->getKey() . '/' . $this->getTotalHash());

        if (!$config->isVrac() || $detail->isSansContrat() || !$detail->getVrac() instanceof Vrac) {
            return null;
        }

        if ($detail->getVrac()->representant_identifiant == $detail->getVrac()->vendeur_identifiant) {
            return null;
        }

        $mouvement->vrac_destinataire = $detail->getVrac()->acheteur->nom;
        $mouvement->region = $detail->getVrac()->representant->region;
        $mouvement->cvo = $this->getProduitDetail()->getCVOTaux() * $detail->getVrac()->getRepartitionCVOCoef($detail->getVrac()->representant_identifiant, $detail->getDocument()->getDate());
        if ($mouvement->cvo > 0 && $mouvement->volume) {
            $mouvement->facturable = 1;
        }
        if($this->getDocument()->isDrmNegoce()){
            $mouvement->facturable = 0;
        }
        return $mouvement;
    }


}
