<?php

/**
 * Model for DRMCepage
 *
 */
class DRMCepage extends BaseDRMCepage {

    public function getChildrenNode() {

        return $this->filter('^details');
    }

    public function getCouleur() {

        return $this->getParentNode();
    }

    public function getProduits() {

        return array($this->getHash() => $this);
    }

    public function addDetailsNoeud($detailsKey) {
        if($detailsKey != DRM::DETAILS_KEY_ACQUITTE && $detailsKey != DRM::DETAILS_KEY_SUSPENDU) {

            throw new sfException(sprintf("La clé détail %s n'est pas autorisé", $detailsKey));
        }

        return $this->add($detailsKey);
    }

    public function reorderByConf() {

        return null;
    }

    public function getProduitsDetails($teledeclarationMode = false, $detailsKey = null) {
        $details = array();
        foreach ($this->getChildrenNode() as $key => $items) {
            if(!is_null($detailsKey) && $detailsKey != $key) {
                continue;
            }

            foreach($items as $item) {
                $details[$item->getHash()] = $item;
            }
        }

        return $details;
    }

    public function hasProduitDetailsWithStockNegatif() {
        foreach ($this->getProduitsDetails() as $detail) {
            if ($detail->total < 0) {
                return true;
            }
        }

        return false;
    }

    public function getLieuxArray() {

        throw new sfException('this function need to call before lieu tree');
    }

}
