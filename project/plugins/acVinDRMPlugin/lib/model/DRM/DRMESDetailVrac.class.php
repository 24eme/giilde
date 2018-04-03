<?php
/**
 * Model for DRMESDetailVrac
 *
 */

class DRMESDetailVrac extends BaseDRMESDetailVrac {

    protected $vrac = null;

    public function getProduitDetail() {

        return $this->getParent()->getProduitDetail();
    }

    public function getVrac() {
        if (is_null($this->vrac)) {
            $this->vrac = VracClient::getInstance()->find($this->identifiant);
        }

        return $this->vrac;
    }

    public function getDateEnlevement(){
        if(!$this->_get('date_enlevement')){
            return $this->getDocument()->getDate();
        }

        return $this->_get('date_enlevement');
    }

    public function isContratExterne() {

        return $this->getProduitDetail()->isContratExterne();
    }

    public function getIdentifiantLibelle() {
        if($this->getProduitDetail()->isContratExterne()) {

            return "externe ".$this->identifiant;
        }

        return $this->getVrac()->getNumeroArchive();
    }

    public function setKey($k) {
      $this->key = $k;
    }

    public function getKey() {
        if (!isset($this->key) || !$this->key) {
            if (!($this->key = parent::getKey())) {
                $this->key = $this->identifiant.'-'.uniqid();
            }
        }
      return $this->key;
    }

}
