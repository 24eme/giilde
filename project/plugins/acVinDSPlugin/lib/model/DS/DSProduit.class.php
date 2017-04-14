<?php
/**
 * Model for DSProduit
 *
 */

class DSProduit extends BaseDSProduit {

    public function updateProduit()
    {
        $this->produit_libelle = $this->getConfig()->getLibelleFormat(null, "%format_libelle%");
        $this->code_produit = $this->getConfig()->getCodeProduit();
    }

    public function isActif() {

        return (!is_null($this->stock_declare));
    }

    public function hasElaboration(){
        return strstr($this->produit_hash, 'EFF')!==false;
    }

    public function getConfig() {

        return ConfigurationClient::getCurrent()->get($this->produit_hash);
    }
}
