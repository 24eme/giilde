<?php
/**
 * Model for DRMESDetailExport
 *
 */

class DRMESDetailReintegration extends BaseDRMESDetailExport {
    public function getProduitDetail() {

        return $this->getParent()->getProduitDetail();
    }

    public function getIdentifiantLibelle() {

        return $this->getDateFr();
    }

    public function setKey($k) {
        $this->key = $k;
    }

    public function getKey() {
        if (!isset($this->key) || !$this->key) {
            if (!($this->key = parent::getKey())) {
                $this->key = str_replace('-', '', $this->identifiant).'-'.uniqid();
            }
        }

        return $this->key;
    }

    public function setIdentifiant($date) {
        $date = preg_replace('|([0-9]{2})/([0-9]{2})/([0-9]{4})|', '\3-\2-\1', $date);

        $this->_set('identifiant', $date);
    }

    public function setDate($date) {
        $this->identifiant = $date;
    }

    public function getDate() {

        return $this->identifiant;
    }

    public function getDateFr() {
        if(!$this->getDate()) {
            return null;
        }

        $date = new DateTime($this->getDate());

        return $date->format('d/m/Y');
    }

    public function getReplacementMonth() {

      return sprintf('%02d', preg_replace('/.*(-|\/)(\d{2})(-|\/).*/', '\2', $this->getDate()));
    }
    public function getReplacementYear() {

      return preg_replace('/.*(\d{4}).*/', '\1', $this->getDate());
    }

}
