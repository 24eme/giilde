<?php

/**
 * Model for DRMCrds
 *
 */
class DRMCrds extends BaseDRMCrds {

    const FACTLITRAGE = 100000;

    public function getLibelle() {
        return "";
    }

    public function getOrAddCrdNode($genre, $couleur, $litrage, $libelle = null, $stock_debut = null, $litrageInHl = false) {

        $crd = $this->add($this->constructKey($genre, $couleur, $litrage, $libelle));

        if(!$litrageInHl) {
            $crd->centilitrage = $litrage / self::FACTLITRAGE;
        } else {
            $crd->centilitrage = $litrage;
        }
        $crd->couleur = $couleur;
        $crd->genre = $genre;
        $crd->stock_debut = 0;
        if ($stock_debut) {
            $crd->stock_debut = $stock_debut;
        }
        $contenances = VracConfiguration::getInstance()->getContenances();
        if ($libelle) {
          $crd->detail_libelle = $libelle;
        }else{
          $crd->detail_libelle = ($contenances)? array_search($crd->centilitrage, $contenances) : '';
        }
        $this->constructKey($genre, $couleur, $litrage, $crd->detail_libelle);
        return $crd;
    }

    public function constructKey($genre, $couleur, $litrage, $libelle = null) {
        if ($libelle && preg_match('/bib/i', $libelle)) {
          return $genre . '-' . $couleur . '-BIB' . $litrage;
        }
        return $genre . '-' . $couleur . '-' . $litrage;
    }

    public function udpateStocksFinDeMois() {
        foreach ($this->getFields() as $crd) {
            $crd->udpateStockFinDeMois();
        }
    }

    public function isEmptyNode(){
        return count($this->toArray());
    }

    public function crdsInitDefault($genres) {
        if ($this->isEmptyNode()) {
            return;
        }
        $contenances = VracConfiguration::getInstance()->getContenances();
        $contenance75 = $contenances['Bouteille 75 cl'] * self::FACTLITRAGE;

        foreach ($genres as $genre) {
            $this->getOrAddCrdNode($genre, DRMClient::DRM_CRD_VERT, $contenance75);
            $this->getOrAddCrdNode($genre, DRMClient::DRM_CRD_BLEU, $contenance75);
            $this->getOrAddCrdNode($genre, DRMClient::DRM_CRD_LIEDEVIN, $contenance75);
        }
    }

}
