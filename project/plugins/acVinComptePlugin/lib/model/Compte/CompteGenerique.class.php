<?php

abstract class CompteGenerique extends acCouchdbDocument {

    protected $telephone_mobile = null;
    protected $telephone_perso = null;
    protected $site_internet = null;
    protected $insee = null;

    public function getAdresse() {

        return $this->siege->adresse;
    }

    public function setAdresse($s) {

        return ($this->siege->adresse = $s);
    }

    public function getCommune() {

        return $this->siege->commune;
    }

    public function setCommune($s) {

        return ($this->siege->commune = $s);
    }

    public function getCodePostal() {

        return $this->siege->code_postal;
    }

    public function setCodePostal($s) {

        return ($this->siege->code_postal = $s);
    }

    public function getPays() {

        return $this->siege->pays;
    }

    public function setPays($s) {

        return ($this->siege->pays = $s);
    }

    public function getAdresseComplementaire() {

        return $this->siege->adresse_complementaire;
    }

    public function getInsee() {
        if (is_null($this->insee)) {
            $this->insee = $this->getMasterCompte()->insee;
        }

        return $this->insee;
    }

    public function setInsee($s) {

        return ($this->insee = $s);
    }

    public function setAdresseComplementaire($s) {

        return ($this->siege->adresse_complementaire = $s);
    }

    public function setEmail($email) {

        return $this->_set('email', $email);
    }

    public function setTelephonePerso($s) {

        return ($this->telephone_perso = $s);
    }

    public function setTelephoneMobile($s) {

        return ($this->telephone_mobile = $s);
    }

    public function setTelephoneBureau($tel) {

        return $this->setTelephone($tel);
    }

    public function setSiteInternet($s) {

        return ($this->site_internet = $s);
    }

    public function setFax($fax) {

        return $this->_set('fax', $fax);
    }

    public function getEmail() {

        return $this->_get('email');
    }

    public function getTelephone() {

        return $this->_get('telephone');
    }

    public function setTelephone($phone) {

        return $this->_set('telephone', $phone);
    }

    public function getTelephoneBureau() {

        return $this->getTelephone();
    }

    public function getTelephonePerso() {
        if (is_null($this->telephone_perso)) {
            $this->telephone_perso = $this->getMasterCompte()->telephone_perso;
        }
        return $this->telephone_perso;
    }

    public function getTelephoneMobile() {
        if (is_null($this->telephone_mobile)) {
            $this->telephone_mobile = $this->getMasterCompte()->telephone_mobile;
        }
        return $this->telephone_mobile;
    }

    public function getSiteInternet() {
        if (is_null($this->site_internet)) {
            $this->site_internet = $this->getMasterCompte()->site_internet;
        }
        return $this->site_internet;
    }

    public function getFax() {

        return $this->_get('fax');
    }

    public static function isSameAdresseComptes(InterfaceCompteGenerique $compte1, InterfaceCompteGenerique $compte2) {
        if
        (
            ($compte1->getAdresse() == $compte2->getAdresse() || !$compte1->getAdresse()) &&
            ($compte1->getCommune() == $compte2->getCommune() || !$compte1->getCommune()) &&
            ($compte1->getCodePostal() == $compte2->getCodePostal() || !$compte1->getCodePostal()) &&
            ($compte1->getInsee() == $compte2->getInsee() || !$compte1->getInsee()) &&
            ($compte1->getAdresseComplementaire() == $compte2->getAdresseComplementaire() || !$compte1->getAdresseComplementaire()) &&
            ($compte1->getPays() == $compte2->getPays() || !$compte1->getPays())
        )
        {
            return true;
        }
        return false;
    }

    public static function isSameContactComptes(InterfaceCompteGenerique $compte1, InterfaceCompteGenerique $compte2) {
        if
        (
            ($compte1->getTelephoneBureau() == $compte2->getTelephoneBureau() || !$compte1->getTelephoneBureau()) &&
            ($compte1->getTelephoneMobile() == $compte2->getTelephoneMobile() || !$compte1->getTelephoneMobile()) &&
            ($compte1->getTelephonePerso() == $compte2->getTelephonePerso() || !$compte1->getTelephonePerso()) &&
            ($compte1->getEmail() == $compte2->getEmail() || !$compte1->getEmail()) &&
            ($compte1->getFax() == $compte2->getFax() || !$compte1->getFax()) &&
            ($compte1->getSiteInternet() == $compte2->getSiteInternet() || !$compte1->getSiteInternet())
        )
        {
            return true;
        }
        return false;
    }

    public function isSameAdresseThan(InterfaceCompteGenerique $compte) {

        return self::isSameAdresseComptes($this, $compte);
    }

    public function isSameContactThan(InterfaceCompteGenerique $compte) {

        return self::isSameContactComptes($this, $compte);
    }

    public function pushContactAndAdresseTo(InterfaceCompteGenerique $compte) {
        $this->pushAdresseTo($compte);
        $this->pushContactTo($compte);
    }

    public function pushAdresseTo(InterfaceCompteGenerique $compte) {
        $ret = false;
        if ($compte->adresse != $this->getAdresse()) {
          $compte->adresse = $this->getAdresse();
          $ret = true;
        }
        if ($compte->adresse_complementaire != $this->getAdresseComplementaire()) {
          $compte->adresse_complementaire = $this->getAdresseComplementaire();
          $ret = true;
        }
        if ($compte->commune != $this->getCommune()) {
          $compte->commune = $this->getCommune();
          $ret = true;
        }
        if ($compte->code_postal != $this->getCodePostal()) {
          $compte->code_postal = $this->getCodePostal();
          $ret = true;
        }
        if ($compte->insee != $this->getInsee()) {
          $compte->insee = $this->getInsee();
          $ret = true;
        }
        if ($compte->pays != $this->getPays()) {
          $compte->pays = $this->getPays();
          $ret = true;
        }
        return $ret;
    }

    public function pushContactTo(InterfaceCompteGenerique $compte) {
        $ret = false;
        if ($compte->telephone_bureau != $this->getTelephoneBureau()) {
          $compte->telephone_bureau = $this->getTelephoneBureau();
          $ret = true;
        }
        if ($compte->email != $this->getEmail()) {
          $compte->email = $this->getEmail();
          $ret = true;
        }
        if ($compte->fax != $this->getFax()) {
          $compte->fax = $this->getFax();
          $ret = true;
        }
        if ($compte->telephone_perso != $this->getTelephonePerso()) {
          $compte->telephone_perso = $this->getTelephonePerso();
          $ret = true;
        }
        if ($compte->telephone_mobile != $this->getTelephoneMobile()) {
          $compte->telephone_mobile = $this->getTelephoneMobile();
          $ret = true;
        }
        if ($compte->site_internet != $this->getSiteInternet()) {
          $compte->site_internet = $this->getSiteInternet();
          $ret = true;
        }
        return $ret;
    }

    public function pullContactAndAdresseFrom(InterfaceCompteGenerique $compte) {
        $this->pullAdresseFrom($compte);
        $this->pullContactFrom($compte);
    }

    public static function pullAdresse(InterfaceCompteGenerique $compteTo, InterfaceCompteGenerique $compteFrom) {
        $compteTo->setAdresse($compteFrom->adresse);
        $compteTo->setAdresseComplementaire($compteFrom->adresse_complementaire);
        $compteTo->setCommune($compteFrom->commune);
        $compteTo->setCodePostal($compteFrom->code_postal);
        $compteTo->setInsee($compteFrom->insee);
        $compteTo->setPays($compteFrom->pays);
    }

    public function pullAdresseFrom(InterfaceCompteGenerique $compteFrom) {
        self::pullAdresse($this, $compteFrom);
    }

    public static function pullContact(InterfaceCompteGenerique $compteTo, InterfaceCompteGenerique $compteFrom) {
        $compteTo->setTelephoneBureau($compteFrom->telephone_bureau);
        $compteTo->setEmail($compteFrom->email);
        $compteTo->setFax($compteFrom->fax);
        $compteTo->setTelephonePerso($compteFrom->telephone_perso);
        $compteTo->setTelephoneMobile($compteFrom->telephone_mobile);
        $compteTo->setSiteInternet($compteFrom->site_internet);
    }

    public function pullContactFrom(InterfaceCompteGenerique $compteFrom) {
        self::pullContact($this, $compteFrom);
    }

}
