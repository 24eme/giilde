<?php
/**
 * Model for DRMCrd
 *
 */

class DRMCrd extends BaseDRMCrd {

    public function getType() {
	      return $this->getParent()->getKey();
    }

    public function getLibelle(){
        return ($this->couleur === DRMClient::DRM_CRD_DEFAUT || !$this->couleur)
            ? preg_replace('/Bouteilles? */i', '', $this->detail_libelle)
            : DRMClient::$drm_crds_couleurs[$this->couleur].' - '.str_replace('Bouteille', '', $this->detail_libelle);
    }

     public function getShortLibelle(){
        return DRMClient::$drm_crds_couleurs[$this->couleur].' '.str_replace('Bouteille', '', $this->detail_libelle);
    }

    public function udpateStockFinDeMois() {
        $this->stock_fin = $this->stock_debut + $this->entrees_achats + $this->entrees_retours + $this->entrees_excedents - $this->sorties_utilisations - $this->sorties_destructions - $this->sorties_manquants;
    }

    public function isBib() {
      if (preg_match('/BIB/i', $this->getType()) ||
	  preg_match('/BIB/i', centilisation2douane($this->centilitrage, $this->detail_libelle)) ||
	  preg_match('/BIB/i', $this->detail_libelle)) {
        return true;
      }
      return false;
    }

    public function setContenance($h){
        $this->centilitrage = $h;
    }
    public function getContenance(){
        return $this->centilitrage;
    }

}
