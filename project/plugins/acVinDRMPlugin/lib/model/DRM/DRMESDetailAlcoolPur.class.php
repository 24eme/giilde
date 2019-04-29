<?php
/**
 * Model for DRMESDetailExport
 *
 */

class DRMESDetailAlcoolPur extends BaseDRMESDetailAlcoolPur {

    public function getDetail() {

        return $this->getParent()->getProduitDetail();
    }

    public function getProduitDetail() {

        return $this->getParent()->getProduitDetail();
    }

    public function getIdentifiantLibelle() {

        return "";
    }

    public function getKey() {
        if (parent::getKey()) {
            return parent::getKey();
        }
        return $this->getTheoriticalKey();
    }

    public function setProduit($p) {
        $this->identifiant = $p->getHash();
    }

    public function getProduit() {
        if (!$this->identifiant) {
            throw new sfException('set produit first');
        }
        return $this->getDocument()->get($this->identifiant);
    }

    private function getTheoriticalKey() {
        if (!$this->identifiant) {
            if(!$this->getDocument()->_id) {
                throw new sfException("DRM id must be set");
            }
            $this->identifiant = $this->getDocument()->_id."-".uniqid();
        }
        return str_replace('/', '-', $this->identifiant);
    }

    private function updateVolume() {
        $p = $this->getProduit();
        if($this->getTav() !== 0.0 ){
          $p->entrees->add('transfertsrecolte', $this->volume * 100 / $this->getTav());
        }
    }

    public function setVolume($volume) {
        $r = $this->_set('volume', $volume);
        $this->updateVolume();

        return $r;
    }

    public function setTav($tav) {
        $p = $this->getProduit();
        $r = $p->add('tav', $tav);
        $this->updateVolume();

        return $r;
    }

    public function getTav() {
        $p = $this->getProduit();
        return $p->tav;
    }

}
