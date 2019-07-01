<?php

/**
 * Model for Societe
 *
 */
class Societe extends BaseSociete implements InterfaceCompteGenerique {

    private $comptes = null;

    public function constructId() {
        $this->set('_id', 'SOCIETE-' . $this->identifiant);
    }

    public function removeContact($idContact) {
        if ($this->contacts->exist($idContact)) {
            $contact = $this->getCompte($idContact);
            $this->contacts->remove($idContact);
            $this->removeFromComptes($contact);
            $contact->delete();
            $contact = NULL;
        }
    }

    public function addNewEnseigne() {
        $this->enseignes->add(count($this->enseignes), "");
    }

    public function getInterlocuteursWithOrdre() {
        foreach ($this->contacts as $key => $interlocuteur) {
            if (is_null($interlocuteur->ordre))
                $interlocuteur->ordre = 2;
        }
        return $this->contacts;
    }

    public function getMaxOrdreContacts() {
        $max = 0;
        foreach ($this->contacts as $contact) {
            if ($max < $contact->ordre)
                $max = $contact->ordre;
        }
        return $max;
    }

    public function hasChais() {
        return count($this->etablissements);
    }

    public function setIdentifiant($identifiant) {
        $r = $this->_set('identifiant', $identifiant);

        $this->code_comptable_client = $this->getCodeComtableClient();

        return $r;
    }

    public function getCodeComtableClient() {
        if(!$this->_get('code_comptable_client')) {
            return FactureConfiguration::getInstance()->getPrefixCodeComptable().((int)$this->identifiant)."";
        }

        return $this->_get('code_comptable_client');
    }

    public function canHaveChais() {
        return in_array($this->type_societe, SocieteClient::getSocieteTypesWithChais());
    }

    public function getFamille() {
        if (!$this->canHaveChais())
            throw new sfException('La societe ' . $this->identifiant . " ne peut pas avoir famille n'ayant pas d'établissement");
        return $this->getTypeSociete();
    }

    public function getRegionViticole($throwexception = true) {
        if (!$this->isTransaction()) {
            return '';
        }
        $regions = $this->getRegionsViticoles($throwexception);
        if (count($regions) > 1) {
            if ($throwexception) {
                throw new sfException("La societe " . $this->identifiant . " est reliée des établissements de plusieurs régions viticoles, ce qui n'est pas permis");
            }
            return array_shift($regions);
        }
        if (!count($regions)) {
            if ($throwexception) {
                throw new sfException("La societe " . $this->identifiant . " n'a pas de région viti ceci peut être dû au faite que ses établissements sont suspendus");
            }
            return '';
        }
        return array_shift($regions);
    }

    private function getRegionsViticoles($excludeSuspendus = true) {
        $regions = array();
        foreach ($this->getEtablissementsObj() as $id => $e) {
            if (!$e->etablissement->isActif() || $e->etablissement->region == EtablissementClient::REGION_HORS_CVO) {
                continue;
            }
            $regions[$e->etablissement->region] = $e->etablissement->region;
        }
        //Si tous suspendus que !excludeSuspendus, on va tout de même chercher des régions
        if (!count($regions) && !$excludeSuspendus) {
            foreach ($this->getEtablissementsObj() as $id => $e) {
                if ($e->etablissement->region == EtablissementClient::REGION_HORS_CVO) {
                    continue;
                }
                $regions[$e->etablissement->region] = $e->etablissement->region;
            }
        }
        return $regions;
    }

    public function getEtablissementsObj($withSuspendu = true) {
        $etablissements = array();
        foreach ($this->etablissements as $id => $obj) {
            $etb = EtablissementClient::getInstance()->find($id);
            if (!$withSuspendu) {
                if (!$etb->isActif()) {
                    continue;
                }
            }
            $etablissements[$id] = new stdClass();
            $etablissements[$id]->etablissement = $etb;
            $etablissements[$id]->ordre = $obj->ordre;
        }
        return $etablissements;
    }

    public function getEtablissementPrincipal() {
        $etablissements = $this->getEtablissementsObj();
        if (!count($etablissements)) {
            return null;
        }
        foreach ($etablissements as $id => $etbObj) {
            $etablissement = $etbObj->etablissement;
            $compte = $this->getCompte($etablissement->compte);
            if ($compte->compte_type == CompteClient::TYPE_COMPTE_SOCIETE) {
                return $etablissement;
            }
        }
        $etbObj = array_shift($etablissements);
        return $etbObj->etablissement;
    }

    public function getContactsObj() {
        if (!$this->comptes || !count($this->comptes)) {
            foreach ($this->contacts as $id => $obj) {
                $compte = CompteClient::getInstance()->find($id);
                if ($compte) {
                    $this->addToComptes($compte);
                }
            }
        }
        return $this->comptes;
    }

    private function addToComptes($compte) {
        if (!$this->comptes) {
            $this->comptes = array();
        }
        if ($compte === null) {
            throw new sfException("Could not add NULL compte");
        }
        $this->comptes[$compte->_id] = $compte;
    }
    private function removeFromComptes($compte) {
        $this->getContactsObj();
        unset($this->comptes[$compte->_id]);
    }

    public function getComptesAndEtablissements() {
        $contacts = array();
        foreach ($this->getEtablissementsObj() as $id => $obj) {
            $contacts[$id] = EtablissementClient::getInstance()->find($id);
        }
        foreach ($this->getContactsObj() as $id => $obj) {
            $contacts[$id] = $obj;
        }

        return $contacts;
    }

    public function getCompte($id) {
        $this->getContactsObj();
        if (!isset($this->comptes[$id]) || !$this->comptes[$id]) {
            throw new sfException($this->_id." :  pas de compte ".$id);
        }
        return $this->comptes[$id];
    }

    public function addEtablissement($e, $ordre = null) {
        if (!$this->etablissements->exist($e->_id)) {
            $this->etablissements->add($e->_id, array('nom' => $e->nom, 'ordre' => $ordre));
        } else {
            $this->etablissements->add($e->_id)->nom = $e->nom;
            if ($ordre !== null) {
                $ordre = 0;
            }
            $this->etablissements->add($e->_id)->ordre = $ordre;
        }
        if ($e->compte) {
            $this->addCompte($e->getMasterCompte(), $ordre);
        }
    }

    public function removeEtablissement($e) {
        $this->etablissements->remove($e->_id);
    }

    public function addCompte($c, $ordre = null) {
        if (!$this->compte_societe) {
            $this->compte_societe = $c->_id;
        }
        if (!$c->_id) {
            return;
        }

        if (!$ordre) {
            $ordre = 0;
        }

        $cid = 'COMPTE-' . $c->identifiant;
        if (!$this->contacts->exist($c->_id)) {
            $this->addToComptes($c);
            $this->contacts->add($cid, array('nom' => $c->nom_a_afficher, 'ordre' => $ordre));
        } else {
            $this->contacts->add($cid)->nom = $c->nom_a_afficher;
            $this->contacts->add($cid)->ordre = $ordre;
        }
    }

    public static function cmpOrdreContacts($a, $b) {
        if ($a->ordre == $b->ordre) {
            return 0;
        }
        return (intval($a->ordre) < intval($b->ordre)) ? -1 : 1;
    }

    public function getMasterCompte() {
        if (!$this->compte_societe) {

            return null;
        }

        return $this->getCompte($this->compte_societe);
    }

    public function getContact() {

        return $this->getMasterCompte();
    }

    public function isManyEtbPrincipalActif() {
        $cptActif = 0;
        foreach ($this->getEtablissementsObj() as $etb) {
            if ($etb->etablissement->isSameCompteThanSociete() && $etb->etablissement->isActif()) {
                $cptActif++;
            }
            if ($cptActif > 1)
                return true;
        }
        return false;
    }

    public function isOperateur() {
        return SocieteClient::TYPE_OPERATEUR == $this->type_societe;
    }

    public function isTransaction() {
        return $this->isNegoOrViti() || $this->isCourtier();
    }

    public function isNegoOrViti() {
        return ($this->type_societe == SocieteClient::TYPE_OPERATEUR);
    }

    public function isCourtier() {
        return $this->type_societe == SocieteClient::TYPE_COURTIER;
    }

    public function isViticulteur() {
        if ($this->type_societe != SocieteClient::TYPE_OPERATEUR) {
            return false;
        }
        return $this->getViticulteur();
    }
    public function getViticulteur() {
        foreach ($this->getEtablissementsObj() as $id => $e) {
            if ($e->etablissement->famille == EtablissementFamilles::FAMILLE_PRODUCTEUR) {
                return $e->etablissement;
            }
        }
        return null;
    }

    public function isNegociant() {
        if ($this->type_societe != SocieteClient::TYPE_OPERATEUR) {
            return false;
        }
        return ($this->getNegociant() != null);
    }
    public function getNegociant() {
        foreach ($this->getEtablissementsObj() as $id => $e) {
            if ($e->etablissement->famille == EtablissementFamilles::FAMILLE_NEGOCIANT) {
                return $e->etablissement;
            }
        }
        return null;
    }

    public function isActif() {
        return $this->exist('statut') && $this->statut === EtablissementClient::STATUT_ACTIF;
    }

    public function isSuspendu() {
        return $this->exist('statut') && $this->statut === EtablissementClient::STATUT_SUSPENDU;
    }

    public function hasNumeroCompte() {
        return ($this->code_comptable_client || $this->code_comptable_fournisseur);
    }

    public function getSiegeAdresses() {
        $a = $this->siege->adresse;
        if ($this->siege->exist("adresse_complementaire")) {
            $a .= ' ; ' . $this->siege->adresse_complementaire;
        }
        return $a;
    }

    protected function createCompteSociete() {
        if ($this->compte_societe) {
            return $this->getCompte($this->compte_societe);
        }

        $compte = CompteClient::getInstance()->findOrCreateCompteSociete($this);
        $this->compte_societe = $compte->_id;
        $compte->setSociete($this);
        if($this->statut) {
            $compte->statut = $this->statut;
        } else {
            $compte->statut = CompteClient::STATUT_ACTIF;
        }
        $compte->mot_de_passe = "{TEXT}" . sprintf("%04d", rand(0, 9999));
        $compte->addOrigine($this->_id);
        $this->addCompte($compte, -1);
        $compte->nom = $this->raison_sociale;
        $this->addToComptes($compte);
        $this->pushContactAndAdresseTo($compte);
        return $compte;
    }

    public function getDateCreation() {
        $this->add('date_creation');
        return $this->_get('date_creation');
    }

    public function getDateModification() {
        $this->add('date_modification');
        return $this->_get('date_modification');
    }

    public function isSynchroAutoActive() {

        return sfConfig::get('app_compte_synchro', true);
    }

    public function save() {
        $this->add('date_modification', date('Y-m-d'));
        $this->interpro = "INTERPRO-declaration";
        $compteMaster = $this->getMasterCompte();
        if ($this->isSynchroAutoActive() && !$compteMaster) {
            $compteMaster = $this->createCompteSociete();
        }

        parent::save();

        SocieteClient::getInstance()->setSingleton($this);

        if($this->isSynchroAutoActive()) {
            $compteMasterOrigin = clone $compteMaster;
            $this->pushToCompteOrEtablissementAndSave($compteMaster, $compteMaster);

            foreach ($this->etablissements as $id => $obj) {
                $this->pushToCompteOrEtablissementAndSave($compteMaster, EtablissementClient::getInstance()->find($id), $compteMasterOrigin);
            }

            foreach ($this->getContactsObj() as $id => $compte) {
                $this->pushToCompteOrEtablissementAndSave($compteMaster, $compte, $compteMasterOrigin);
            }
        }
    }

    public function pushToCompteOrEtablissementAndSave($compteMaster, $compteOrEtablissement, $compteMasterOrigin = null) {
        $needSave = false;
        if(is_null($compteMasterOrigin)) {
            $compteMasterOrigin = $compteMaster;
        }
        if (!$compteMaster) {
            throw new sfException("compteMaster should not be NULL");
        }
        if (!$compteOrEtablissement) {
            throw new sfException("compteOrEtablissement should not be NULL");
        }
        if ($compteMaster->_id == $compteOrEtablissement->_id) {
            if ($compteOrEtablissement->nom != $this->raison_sociale) {
                $compteOrEtablissement->nom = $this->raison_sociale;
            }
            $needSave = true;
        }
        if (CompteGenerique::isSameAdresseComptes($compteOrEtablissement, $compteMasterOrigin)) {
            $ret = $this->pushAdresseTo($compteOrEtablissement);
            $needSave = $needSave || $ret;
        }
        if (CompteGenerique::isSameContactComptes($compteOrEtablissement, $compteMasterOrigin)) {
            $ret = $this->pushContactTo($compteOrEtablissement);
            $needSave = $needSave || $ret;
        }
        if ($needSave) {
            $compteOrEtablissement->save();
        }
    }

    public function isPresse() {
        return $this->exist('type_societe') && ($this->type_societe == SocieteClient::TYPE_PRESSE);
    }

    public function isInstitution() {
        return $this->exist('type_societe') && ($this->type_societe == SocieteClient::SUB_TYPE_INSTITUTION);
    }

    public function isSyndicat() {
        return $this->exist('type_societe') && ($this->type_societe == SocieteClient::SUB_TYPE_SYNDICAT);
    }

    public function getEmailTeledeclaration() {
        if ($compteSociete = $this->getMasterCompte()) {
            if ($compteSociete->exist('societe_information') && $compteSociete->societe_information->exist('email') && $compteSociete->societe_information->email) {
                return $compteSociete->societe_information->email;
            }
            return $compteSociete->email;
        }
        if ($this->exist('teledeclaration_email') && $this->teledeclaration_email) {
            return $this->teledeclaration_email;
        }
        if ($this->exist('email') && $this->email) {
            return $this->email;
        }
        return null;
    }

    public function setEmailTeledeclaration($email) {
        $this->add('teledeclaration_email', $email);
    }

    public function getCommentaire() {
        $c = $this->_get('commentaire');
        $c1 = $this->getMasterCompte()->get('commentaire');
        if ($c && $c1) {
            return $c . "\n" . $c1;
        }
        if ($c) {
            return $c;
        }
        if ($c1) {
            return $c1;
        }
    }

    public function addCommentaire($s) {
        $c = $this->get('commentaire');
        if ($c) {
            return $this->_set('commentaire', $c . "\n" . $s);
        }
        return $this->_set('commentaire', $s);
    }

    public function hasLegalSignature() {
        if ($this->exist('legal_signature'))
            return ($this->add('legal_signature')->add('v1'));
        return false;
    }

    public function delete() {
        foreach($this->getComptesAndEtablissements() as $id => $obj) {
            if ($obj) {
                $obj->delete();
            }
        }
        return parent::delete();
    }

    public function createEtablissement($famille) {
        $etablissement = new Etablissement();
        $etablissement->id_societe = $this->_id;
        $societeSingleton = SocieteClient::getInstance()->findSingleton($this->_id);
        if(!$societeSingleton) {
            throw new sfException("La société doit être créée avant de créer l'établissement");
        }
        $etablissement->setSociete($societeSingleton);
        $etablissement->identifiant = EtablissementClient::getInstance()->getNextIdentifiantForSociete($societeSingleton);
        if ($famille) {
            $etablissement->famille = $famille;
        }
        $etablissement->constructId();
        $societeSingleton->pushContactAndAdresseTo($etablissement);
        return $etablissement;
    }

    public function createCompteFromEtablissement($etablissement) {
        $compte = CompteClient::getInstance()->createCompteFromEtablissement($etablissement);
        $this->addCompte($compte);
        return $compte;
    }

    public function switchStatusAndSave() {
        $newStatus = "";
        $this->save();

        if($this->isActif()){
            $newStatus = SocieteClient::STATUT_SUSPENDU;
        }
        if($this->isSuspendu()){
            $newStatus = SocieteClient::STATUT_ACTIF;
        }
        foreach ($this->contacts as $keyCompte => $compte) {
            $contact = CompteClient::getInstance()->find($keyCompte);
            $contact->setStatut($newStatus);
            $contact->save();
        }
        $this->comptes = null;
        foreach ($this->etablissements as $keyEtablissement => $etablissement) {
            $etablissement = EtablissementClient::getInstance()->find($keyEtablissement);
            $etablissement->setStatut($newStatus);
            $etablissement->save();
        }
        $this->setStatut($newStatus);
        $this->save();
    }

}
