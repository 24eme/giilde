<?php
/**
 * Model for EtablissementLieuStockage
 *
 */

class EtablissementLieuStockage extends BaseEtablissementLieuStockage {
    public function getNumeroIncremental() {
        if(!preg_match("/^(C?[0-9]{10})([0-9]{3})$/", $this->numero, $matches)) {
             throw new sfException("Numéro de stockage mal formé : ".$this->numero);
        }

        return $matches[2];
    }

    public function getIdentifiant() {
        if(!preg_match("/^(C?[0-9]{10})([0-9]{3})$/", $this->numero, $matches)) {
             throw new sfException("Numéro de stockage mal formé : ".$this->numero);
        }

        return $matches[1];
    }

    public function isPrincipale() {

        return $this->getDocument()->getLieuStockagePrincipal(false, $this->getIdentifiant())->getNumeroIncremental() == $this->getNumeroIncremental();
    }
}
