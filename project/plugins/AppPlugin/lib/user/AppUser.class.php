<?php

class AppUser extends sfBasicSecurityUser {

    const SESSION_COMPTE_LOGIN = "COMPTE_LOGIN";
    const SESSION_COMPTE_DOC = "COMPTE_DOC_ID";
    const SESSION_USURPATION_URL_BACK = "USURPATION_URL_BACK";
    const NAMESPACE_COMPTE = "COMPTE";
    const NAMESPACE_COMPTE_ORIGIN = "COMPTE_ORIGIN";
    const CREDENTIAL_ADMIN = "admin";

    public function signInOrigin($login_or_compte) {

        $compte = $this->registerCompteByNamespace($login_or_compte, self::NAMESPACE_COMPTE_ORIGIN);

        $this->setAuthenticated(true);
        $this->signIn($login_or_compte);
    }

    public function signIn($login_or_compte) {
        $compte = $this->registerCompteByNamespace($login_or_compte, self::NAMESPACE_COMPTE);

        if ($compte && $compte->exist('droits')) {
            foreach ($compte->droits as $droit) {
                $roles = Roles::getRoles($droit);
                $this->addCredentials($roles);
            }
        }
    }

    protected function registerCompteByNamespace($login_or_compte, $namespace) {

        if (is_object($login_or_compte) && $login_or_compte instanceof Compte) {
            $compte = $login_or_compte;
            $login = $compte->getLogin();
        } else {

            $compte = CompteClient::getInstance()->findByLogin($login_or_compte);
            $login = $login_or_compte;
        }

        if(!$compte) {
            throw new sfException("Le compte est nul : ".$compte->_id);
        }

        $this->setAttribute(self::SESSION_COMPTE_LOGIN, $login, $namespace);

        if ($compte->isNew()) {
            $this->setAttribute(self::SESSION_COMPTE_DOC, $compte, $namespace);
        } else {
            $this->setAttribute(self::SESSION_COMPTE_DOC, $compte->_id, $namespace);
        }

        return $compte;
    }

    public function signOut() {
        $this->clearCredentials();
        $this->getAttributeHolder()->removeNamespace(self::NAMESPACE_COMPTE);
    }

    public function signOutOrigin() {
        $this->signOut();
        $this->setAuthenticated(false);
        $this->clearCredentials();
        $this->getAttributeHolder()->removeNamespace(self::NAMESPACE_COMPTE_ORIGIN);
    }

    public function getCompte() {

        return $this->getCompteByNamespace(self::NAMESPACE_COMPTE);
    }

    public function getCompteOrigin() {

        return $this->getCompteByNamespace(self::NAMESPACE_COMPTE_ORIGIN);
    }

    protected function getCompteByNamespace($namespace) {
        $id_or_doc = $this->getAttribute(self::SESSION_COMPTE_DOC, null, $namespace);

        if (!$id_or_doc) {
            return null;
        }

        if ($id_or_doc instanceof Compte) {

            return $id_or_doc;
        }

        return CompteClient::getInstance()->find($id_or_doc);
    }

    public function usurpationOn($login_or_compte, $url_back) {
        $this->signOut();
        $this->signIn($login_or_compte);
        $this->setAttribute(self::SESSION_USURPATION_URL_BACK, $url_back);
    }

    public function usurpationOff() {
        $this->signOut();
        $this->signIn($this->getCompteOrigin());

        $url_back = $this->getAttribute(self::SESSION_USURPATION_URL_BACK);
        $this->getAttributeHolder()->remove(self::SESSION_USURPATION_URL_BACK);

        return $url_back;
    }

    public function isUsurpationCompte() {

        return $this->getAttribute(self::SESSION_COMPTE_LOGIN, null, self::NAMESPACE_COMPTE) != $this->getAttribute(self::SESSION_COMPTE_LOGIN, null, self::NAMESPACE_COMPTE_ORIGIN);
    }

    public function hasObservatoire() {
        return $this->hasCredential(Roles::OBSERVATOIRE);
    }

    public function hasTeledeclaration() {
        return $this->isAuthenticated() && $this->getCompte() && $this->hasCredential(Roles::TELEDECLARATION);
    }

    public function hasTeledeclarationVrac() {
        return $this->hasTeledeclaration() && $this->hasCredential(Roles::TELEDECLARATION_VRAC);
    }

    public function hasTeledeclarationVracCreation() {
        return $this->hasTeledeclaration() && $this->hasCredential(Roles::TELEDECLARATION_VRAC_CREATION);
    }

    public function hasTeledeclarationDrm() {
        return $this->hasTeledeclaration() && $this->hasCredential(Roles::TELEDECLARATION_DRM);
    }

    public function hasOnlyCredentialDRM() {
        return $this->hasCredential(Roles::ROLEDRM) && $this->hasCredential(Roles::DRM);
    }

}
