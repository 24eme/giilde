<?php

/**
 * Model for ConfigurationDetailLigne
 *
 */
class ConfigurationDetailLigne extends BaseConfigurationDetailLigne {

    const DETAILS_VRAC = 'VRAC';
    const DETAILS_CREATIONVRAC = 'CREATIONVRAC';
    const DETAILS_EXPORT = 'EXPORT';
    const DETAILS_COOPERATIVE = 'COOPERATIVE';
    const DETAILS_ALCOOLPUR = 'ALCOOLPUR';
    const DETAILS_REINTEGRATION = 'REINTEGRATION';

    public function isReadable() {

        return ($this->readable);
    }

    public function isWritable() {

        return ($this->readable) && ($this->writable);
    }

    public function isVrac() {
        return ($this->hasDetails() && (($this->getDetails() == self::DETAILS_VRAC) || ($this->getDetails() == self::DETAILS_CREATIONVRAC)));
    }

    public function needDouaneObservation($negoce = false) {

        return (!$negoce)? preg_match('/autres-entrees|autres-sorties|replacement-suspension|manipulations/', $this->douane_cat) :
                           preg_match('/autres-entrees|autres-sorties|replacement-suspension/', $this->douane_cat_negoce);
    }

    public function needDouaneDateReplacement() {
        if($this->getParent()->getParent()->getKey() != "details") {

            return false;
        }

        return preg_match('/replacement-suspension/', $this->douane_cat);
    }

    public function hasDetails() {

        return ($this->exist('details') && $this->get('details'));
    }

    public function getLibelle() {
        $detail = $this->getparent()->getParent()->getKey();
        return $this->getLibelleDetail($detail)->libelle;
    }

    public function getLibelleLong() {
        $detail = $this->getparent()->getParent()->getKey();
        return $this->getLibelleDetail($detail)->libelle_long;
    }

    public function getDescription()
    {
        $detail = $this->getparent()->getParent()->getKey();
        return $this->getLibelleDetail($detail)->description;
    }

    private function getLibelleDetail() {
        $detail = $this->getparent()->getParent()->getKey();
        return $this->getDocument()->libelle_detail_ligne->get($detail)->get($this->getParent()->getKey())->get($this->getKey());
    }

    public function isFavoris() {

        return $this->getDocument()->exist("mvts_favoris/".$this->getParent()->getParent()->getKey()."_".$this->getParent()->getKey()."_".$this->getKey());
    }

    public function isFacturable() {

        return $this->exist('facturable') && $this->get('facturable');
    }

    public function isFacturableInverseNegociant() {

        return $this->exist('facturable_negociant') && $this->get('facturable_negociant');
    }

    public function isWritableForEtablissement($etb, $isTeledeclaree = false) {
        if($this->douane_type == DRMClient::CRD_TYPE_ACQUITTE){
            if(!$isTeledeclaree){
                return false;
            }
            if(!$etb->exist('crd_regime') || !$etb->crd_regime){
                return false;
            }
            if(!$etb->hasRegimeCollectifAcquitte()){
                return false;
            }

        }
         if($this->douane_type == DRMClient::CRD_TYPE_SUSPENDU){
            if(!$isTeledeclaree){
                return true;
            }
            if(!$etb->exist('crd_regime') || !$etb->crd_regime){
                return true;
            }
            if(!$etb->hasRegimeCollectifSuspendu() &&
               !$etb->hasRegimePersonnalise()){
                return false;
            }
        }
        return true;
    }

}
