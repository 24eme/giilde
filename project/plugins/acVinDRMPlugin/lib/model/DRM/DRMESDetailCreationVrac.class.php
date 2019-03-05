<?php
/**
 * Model for DRMESDetailCreationVrac
 *
 */

class DRMESDetailCreationVrac extends BaseDRMESDetailCreationVrac {

    public function getDetail() {

        return $this->getParent()->getProduitDetail();
    }

    public function getIdentifiantLibelle() {
        $e = EtablissementClient::getInstance()->find($this->acheteur);
        if (!$e) {
            throw new sfException("Etablissement non trouvé");
        }
        return $e->__toString();
    }

    public function getVrac(){

        return VracClient::getInstance()->createContratFromDrmDetails($this);
    }

    public function isContratExterne() {

        return false;
    }

    public function isSansContrat() {

        return false;
    }

    public function getDateEnlevement(){
        if(!$this->_get('date_enlevement')){
            return $this->getDocument()->getDate();
        }

        return $this->_get('date_enlevement');
    }

    public function setAcheteur($acheteur) {
        $this->_set('acheteur', $acheteur);
        if ($this->getHash()) {
          $this->getParent()->remove($this->getKey());
          $this->getParent()->add($this->getTheoriticalKey(), $this);
        }
    }

    public function setKey($k) {
        $this->key = $k;
    }

    public function getKey() {
        if (parent::getKey()) {
            return parent::getKey();
        }

        return $this->getTheoriticalKey();
    }


    private function getTheoriticalKey() {
        if (!$this->identifiant) {
            if(!$this->getDocument()->_id) {
                throw new sfException("DRM id must be set");
            }
            $this->identifiant = $this->getDocument()->_id."-".uniqid();
        }

        return $this->identifiant.'-'.$this->acheteur;
    }

}
