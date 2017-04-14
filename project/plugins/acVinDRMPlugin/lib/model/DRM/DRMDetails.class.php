<?php

/**
 * Model for DRMDetails
 *
 */
class DRMDetails extends BaseDRMDetails {

    public function getConfigDetails() {
        return $this->getDocument()->getConfig()->declaration->get($this->getKey());
    }

    public function getProduit($denomination_complementaire = null) {

        $slug = DRM::DEFAULT_KEY;
        if($denomination_complementaire){
          $slug = $this->createSHA1Denom($denomination_complementaire);
        }
        if (!$this->exist($slug)) {

            return false;
        }

        return $this->get($slug);
    }

    public function cleanNoeuds() {
        if (count($this) == 0) {
            return $this;
        }

        return null;
    }

    public function getTypeDRM() {
        if($this->getKey() == DRM::DETAILS_KEY_SUSPENDU) {

            return DRMClient::TYPE_DRM_SUSPENDU;
        }

        if($this->getKey() == DRM::DETAILS_KEY_ACQUITTE) {

            return DRMClient::TYPE_DRM_ACQUITTE;
        }
    }

    public function getTypeDRMLibelle() {
        if($this->getKey() == DRM::DETAILS_KEY_SUSPENDU) {

            return "Suspendu";
        }

        if($this->getKey() == DRM::DETAILS_KEY_ACQUITTE) {

            return "Acquitté";
        }

        return null;
    }

    public function addProduit($denomination_complementaire = null) {
        $detailDefaultKey = DRM::DEFAULT_KEY;
        $detail = null;
        if($denomination_complementaire){
          $detail = $this->add($this->createSHA1Denom($denomination_complementaire));
          $detail->denomination_complementaire = $denomination_complementaire;
        }else{
          $detail = $this->add($detailDefaultKey);
        }
        foreach ($this->getConfigDetails() as $detailConfigCat => $detailConfig) {
            foreach ($detailConfig as $detailConfigKey => $detailConfigNode) {
                $detail->getOrAdd($detailConfigCat)->getOrAdd($detailConfigKey, null);
                if ($detailConfigNode->hasDetails()) {
                    $detail->getOrAdd($detailConfigCat)->getOrAdd($detailConfigKey . "_details", null);
                }
            }
        }

        return $detail;
    }


    public function createSHA1Denom($denomination_complementaire){
      $denomSlugified = KeyInflector::slugify($denomination_complementaire);
      $completeHash = $this->getHash().'/'.$denomSlugified;
      $sha1 = hash("sha1",$completeHash);
      return substr($sha1,0,7);
    }

}
