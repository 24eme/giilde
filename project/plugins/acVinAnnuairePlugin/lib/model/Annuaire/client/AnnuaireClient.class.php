<?php

class AnnuaireClient extends acCouchdbClient {

    const ANNUAIRE_PREFIXE_ID = 'ANNUAIRE-';
    const ANNUAIRE_RECOLTANTS_KEY = 'recoltants';
    const ANNUAIRE_NEGOCIANTS_KEY = 'negociants';
    const ANNUAIRE_COMMERICAUX_KEY = 'commerciaux';
    const ANNUAIRE_REPRESENTANT_KEY = 'representants';
    const ANNUAIRE_CAVES_COOPERATIVES_KEY = 'caves_cooperatives';

    static $annuaire_types = array(
        self::ANNUAIRE_RECOLTANTS_KEY => 'Viticulteur',
        self::ANNUAIRE_NEGOCIANTS_KEY => 'Négociant',
        self::ANNUAIRE_REPRESENTANT_KEY => 'Représentant'
    );
    static $tiers_qualites = array(
        self::ANNUAIRE_RECOLTANTS_KEY => '',
        self::ANNUAIRE_NEGOCIANTS_KEY => '',
        self::ANNUAIRE_REPRESENTANT_KEY => ''
    );

    public static function getAnnuaireTypes() {
        return self::$annuaire_types;
    }

    public static function getTiersQualites() {
        return self::$tiers_qualites;
    }

    public static function getInstance() {
        return acCouchdbManager::getClient("Annuaire");
    }

    public static function getTiersCorrespondanceType($tiersType) {
        $types = self::getTiersCorrespondanceTypes();
        return $types[$tiersType];
    }

    public function createAnnuaire($identifiant) {
        $annuaire = new Annuaire();
        $annuaire->identifiant = $identifiant;
        $annuaire->save();
        return $annuaire;
    }

    public function findOrCreateAnnuaire($identifiant) {
        if (preg_match("/^(C?[0-9]{10})[0-9]{2}$/", $identifiant, $matches)) {
            $identifiant = $matches[1];
        }

        if ($annuaire = $this->find(self::ANNUAIRE_PREFIXE_ID . $identifiant)) {
            return $annuaire;
        }
        return $this->createAnnuaire($identifiant);
    }

    public function buildId($identifiant) {
        return self::ANNUAIRE_PREFIXE_ID . $identifiant;
    }

    public function findOrCreateAnnuaireWithSuspendu($identifiant) {
        $annuaire = $this->findOrCreateAnnuaire($identifiant);
        $annuaireWithSuspendu = new stdClass();
        $annuaireWithSuspendu->recoltants = array();
        foreach ($annuaire->recoltants as $key => $item) {
            $isActif = (EtablissementClient::getInstance()->find($key, acCouchdbClient::HYDRATE_JSON) && EtablissementClient::getInstance()->find($key, acCouchdbClient::HYDRATE_JSON)->statut == EtablissementClient::STATUT_ACTIF);
            $localEtb = new stdClass();
            $localEtb->isActif = $isActif;
            $localEtb->name = $item;
            $annuaireWithSuspendu->recoltants[$key] = $localEtb;
        }
        $annuaireWithSuspendu->negociants = array();
        foreach ($annuaire->negociants as $key => $item) {
            $isActif = (EtablissementClient::getInstance()->find($key, acCouchdbClient::HYDRATE_JSON)->statut == EtablissementClient::STATUT_ACTIF);
            $localEtb = new stdClass();
            $localEtb->isActif = $isActif;
            $localEtb->name = $item;
            $annuaireWithSuspendu->negociants[$key] = $localEtb;
        }
        $annuaireWithSuspendu->representants = array();
        foreach ($annuaire->representants as $key => $item) {
            $isActif = (EtablissementClient::getInstance()->find($key, acCouchdbClient::HYDRATE_JSON)->statut == EtablissementClient::STATUT_ACTIF);
            $localEtb = new stdClass();
            $localEtb->isActif = $isActif;
            $localEtb->name = $item;
            $annuaireWithSuspendu->representants[$key] = $localEtb;
        }
        $annuaireWithSuspendu->commerciaux = $annuaire->commerciaux;
        return $annuaireWithSuspendu;
    }

    public function findSocieteByTypeAndTiers($type, $identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $societe = SocieteClient::getInstance()->find($identifiant);
        if (!$societe) {
            return null;
        }
        if (!$societe->isActif()) {
            return null;
        }
        if ($type == self::ANNUAIRE_RECOLTANTS_KEY && $societe->type_societe != SocieteClient::TYPE_OPERATEUR) {
            return null;
        }
        return $societe;
    }

    public function findTiersByTypeAndTiers($type, $identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $etablissement = EtablissementClient::getInstance()->find($identifiant);

        if (!$etablissement) {

            return null;
        }

        if (!$etablissement->isActif()) {

            return null;
        }

        if ($type == self::ANNUAIRE_RECOLTANTS_KEY && $etablissement->famille != EtablissementFamilles::FAMILLE_PRODUCTEUR) {
            return null;
        }

        if ($type == self::ANNUAIRE_NEGOCIANTS_KEY && $etablissement->famille != EtablissementFamilles::FAMILLE_NEGOCIANT) {
            return null;
        }

        return $etablissement;
    }

}
