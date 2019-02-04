<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class sfCredentialActions
 * @author mathurin
 */
class sfCredentialActions extends sfActions {

    const CREDENTIAL_ADMIN = "admin";
    const CREDENTIAL_COMPTA = "compta";
    const CREDENTIAL_TRANSACTIONS = "transactions";
    const CREDENTIAL_PRESSE = "presse";
    const CREDENTIAL_DIRECTION = "direction";
    const CREDENTIAL_AUTRE = "autre";
    const CREDENTIAL_BUREAU = "bureau";

    protected function getUserCredential() {
        $users = $this->getUser()->getCredentials();
        if (in_array(self::CREDENTIAL_ADMIN, $users)) {
            return self::CREDENTIAL_ADMIN;
        }
        if (in_array(self::CREDENTIAL_COMPTA, $users)) {
            return self::CREDENTIAL_COMPTA;
        }
        if (in_array(self::CREDENTIAL_TRANSACTIONS, $users)) {
            return self::CREDENTIAL_TRANSACTIONS;
        }
        if (in_array(self::CREDENTIAL_PRESSE, $users)) {
            return self::CREDENTIAL_PRESSE;
        }
        if (in_array(self::CREDENTIAL_DIRECTION, $users)) {
            return self::CREDENTIAL_DIRECTION;
        }
        if (in_array(self::CREDENTIAL_BUREAU, $users)) {
            return self::CREDENTIAL_BUREAU;
        }
        return self::CREDENTIAL_AUTRE;
    }

    protected function getSocieteTypesRights() {
        $this->user = $this->getUserCredential();
        if (!$this->user) {
            return;
        }
        switch ($this->user) {
            case self::CREDENTIAL_COMPTA:
            case self::CREDENTIAL_TRANSACTIONS:
                return array(SocieteClient::SUB_TYPE_VITICULTEUR,
                             SocieteClient::SUB_TYPE_NEGOCIANT,
                             SocieteClient::SUB_TYPE_COURTIER,
                             SocieteClient::SUB_TYPE_HOTELRESTAURANT,
                             SocieteClient::SUB_TYPE_AUTRE);

            case self::CREDENTIAL_PRESSE:
                return array(SocieteClient::TYPE_PRESSE,
                             SocieteClient::SUB_TYPE_HOTELRESTAURANT,
                             SocieteClient::SUB_TYPE_AUTRE);

            case self::CREDENTIAL_DIRECTION:
                return array(SocieteClient::SUB_TYPE_INSTITUTION,
                             SocieteClient::SUB_TYPE_HOTELRESTAURANT,
                             SocieteClient::SUB_TYPE_AUTRE);

            case self::CREDENTIAL_AUTRE:
                return array(SocieteClient::SUB_TYPE_HOTELRESTAURANT,
                             SocieteClient::SUB_TYPE_AUTRE);

            case self::CREDENTIAL_BUREAU:
                return array(SocieteClient::SUB_TYPE_SYNDICAT);
            default:
                return;
        }
    }


    protected function applyRights() {

        //reduction de droits dans le module contact
        $this->reduct_rights = false;

        //reduction des droits en lecture seule pour le module contact
        $this->modification = true;

        $this->user = $this->getUserCredential();
        if (!$this->user) {
            return;
        }

        switch ($this->user) {
            case self::CREDENTIAL_PRESSE:
                if ($this->societe->isTransaction()) {
                    $this->modification = false;
                    $this->reduct_rights = true;
                }
                if ($this->societe->isInstitution() || $this->societe->isSyndicat()) {
                    $this->modification = false;
                }
            return;
            case self::CREDENTIAL_DIRECTION:
                if ($this->societe->isTransaction()) {
                    $this->modification = false;
                    $this->reduct_rights = true;
                }
                if ($this->societe->isPresse() || $this->societe->isSyndicat()) {
                    $this->modification = false;
                }
            return;
            case self::CREDENTIAL_AUTRE:
                if ($this->societe->isTransaction()) {
                    $this->modification = false;
                    $this->reduct_rights = true;
                }
                if ($this->societe->isPresse() || $this->societe->isInstitution() || $this->societe->isSyndicat()) {
                    $this->modification = false;
                }
            return;
            case self::CREDENTIAL_TRANSACTIONS:
            case self::CREDENTIAL_COMPTA:
                if ($this->societe->isPresse() ||
                        $this->societe->isInstitution()
                        || $this->societe->isSyndicat()) {
                    $this->modification = false;
                }
            return;

            case self::CREDENTIAL_BUREAU:
                 if (!$this->societe->isSyndicat()) {
                    $this->modification = false;
                }
                return;
            default:
                return;
        }
    }

}
