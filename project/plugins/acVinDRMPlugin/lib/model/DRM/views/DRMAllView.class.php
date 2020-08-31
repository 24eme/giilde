<?php
class DRMAllView extends acCouchdbView
{
    const KEY_ETABLISSEMENT_IDENTIFIANT = 0;
    const KEY_CAMPAGNE = 1;
    const KEY_PERIODE = 2;
    const KEY_VERSION = 3;
    const KEY_TYPE_CREATION = 4; #Non utilisé
    const KEY_DATE_SAISIE = 5;
    const KEY_DOUANE_ENVOI = 6; #Non utilisé
    const KEY_DOUANE_ACCUSE = 7; #Non utilisé
    const KEY_NUMERO_ARCHIVE = 8;
    const KEY_TELEDECLARE = 9;
    const KEY_TRANSMISSION = 10;
    const KEY_HORODATAGE = 11;
    const KEY_COHERENTE = 12;
    const KEY_DIFF = 13;

    public static function getInstance() {

        return acCouchdbManager::getView('drm', 'all', 'DRM');
    }

    public function getStockFin($campagne, Etablissement $etablissement, $hash_produit) {
        $drms = $this->findByCampagneAndEtablissementAndProduit($campagne, null, $etablissement->identifiant, $hash_produit);
        $stock_fin = 0;
        foreach($drms as $drm) {
            $stock_fin = $drm->volume_stock_fin_mois;
        }

        return $stock_fin;
    }

    public function findAll() {    

        return $this->builds($this->client->reduce(false)->getView($this->design, $this->view)->rows);
    }

    public function builds($rows) {
        $drms = array();

        foreach($rows as $row) {
            $key = $row->key[self::KEY_ETABLISSEMENT_IDENTIFIANT] . '_' . $row->key[self::KEY_PERIODE] . '_' . $row->key[self::KEY_VERSION];
            $drms[$key] = $this->build($row);
        }

        return $drms;
    }

    public function build($row) {
        $drm = new stdClass();
        $drm->identifiant = $row->key[self::KEY_ETABLISSEMENT_IDENTIFIANT];
        $drm->campagne = $row->key[self::KEY_CAMPAGNE];
        $drm->periode = $row->key[self::KEY_PERIODE];
        $drm->version = $row->key[self::KEY_VERSION];
        $drm->valide = new stdClass();
        $drm->valide->date_saisie = $row->key[self::KEY_DATE_SAISIE];
        $drm->numero_archive = $row->key[self::KEY_NUMERO_ARCHIVE];
        $drm->_id = $this->client->buildId($drm->identifiant, $drm->periode, $drm->version);
        return $drm;
    }
}  