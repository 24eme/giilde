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

    public function getOrAddCrdNode($genre, $couleur, $contenanceEnHl, $libelle = null, $stock_debut = null) {

        $crd = $this->add($this->constructKey($genre, $couleur, $contenanceEnHl, $libelle));

        $crd->setContenance($contenanceEnHl);

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
        $this->constructKey($genre, $couleur, $contenanceEnHl, $crd->detail_libelle);
        return $crd;
    }

    public function constructKey($genre, $couleur, $contenanceEnHl, $libelle = null) {
        if ($libelle && preg_match('/bib/i', $libelle)) {
          return $genre . '-' . $couleur . '-BIB' . ($contenanceEnHl*self::FACTLITRAGE);
        }
        return $genre . '-' . $couleur . '-' . ($contenanceEnHl*self::FACTLITRAGE);
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
        $contenanceDefault = $contenances['Bouteille 75 cl'];

        $default_crds_config = DRMConfiguration::getInstance()->getDefaultCrds();

        foreach ($genres as $genre) {
            if(count($default_crds_config)){
                foreach ($default_crds_config as $key => $default) {
                    $this->getOrAddCrdNode($genre, $default['couleur'], $default['contenance']);
                }
            }else{
                $this->getOrAddCrdNode($genre, DRMClient::DRM_CRD_DEFAUT, $contenanceDefault);
                $this->getOrAddCrdNode($genre, DRMClient::DRM_CRD_VERT, $contenanceDefault);
                $this->getOrAddCrdNode($genre, DRMClient::DRM_CRD_BLEU, $contenanceDefault);
                $this->getOrAddCrdNode($genre, DRMClient::DRM_CRD_LIEDEVIN, $contenanceDefault);
            }
        }
    }



}
