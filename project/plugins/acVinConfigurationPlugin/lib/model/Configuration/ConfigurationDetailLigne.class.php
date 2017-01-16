<?php

/**
 * Model for ConfigurationDetailLigne
 *
 */
class ConfigurationDetailLigne extends BaseConfigurationDetailLigne {

    public function isReadable() {

        return ($this->readable);
    }

    public function isWritable() {

        return ($this->readable) && ($this->writable);
    }

    public function isVrac() {

        return ($this->vrac > 0);
    }

    public function hasDetails() {

        return ($this->details);
    }

    public function getLibelle() {

        return $this->getLibelleDetail()->libelle;
    }

    public function getLibelleLong() {

        return $this->getLibelleDetail()->libelle_long;
    }

    public function getDescription() {

        return $this->getLibelleDetail()->description;
    }

    private function getLibelleDetail() {

        return $this->getDocument()->libelle_detail_ligne->get($this->getParent()->getKey())->get($this->getKey());
    }

    public function isFavoris() {

        return $this->getDocument()->exist("mvts_favoris/".$this->getParent()->getKey()."_".$this->getKey());
    }

    public function isWritableForEtablissement($etb) {
        if(($this->getKey() == "retourmarchandisetaxeesacquitte") || ($this->getKey() == "ventefrancebibcrdacquitte") || ($this->getKey() == "ventefrancebouteillecrdacquitte")){
            if(!$etb->exist('crd_regime')){
                return false;
            }
            if(($etb->crd_regime == EtablissementClient::REGIME_CRD_COLLECTIF_SUSPENDU) || ($etb->crd_regime == EtablissementClient::REGIME_CRD_PERSONNALISE)){
                return false;
            }
        }
         if(($this->getKey() == "retourmarchandisetaxees") || ($this->getKey() == "ventefrancebibcrd") || ($this->getKey() == "ventefrancebouteillecrd")){
            if(!$etb->exist('crd_regime')){
                return true;
            }
            if(($etb->crd_regime != EtablissementClient::REGIME_CRD_COLLECTIF_SUSPENDU) && ($etb->crd_regime != EtablissementClient::REGIME_CRD_PERSONNALISE)){
                return false;
            }
        }
        return true;
    }

}
