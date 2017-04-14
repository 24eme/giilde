<?php

/**
 * Model for RevendicationEtablissements
 *
 */
class RevendicationEtablissements extends BaseRevendicationEtablissements {

    public function storeProduits($num_ligne, $row,$hashLibelle, $bailleur) {
            $produit_to_store = $this->produits->add($row[RevendicationCsvFile::CSV_COL_CODE_PRODUIT]);
            $produit_to_store->storeProduit($num_ligne,$row,$hashLibelle, $bailleur);
    }


    public function updateProduitsAndVolume($produitsNode, $old_key ,$new_key, $new_libelle, $row, $num_ligne, $new_volume) {

        $old_produit = $this->produits->get($old_key);
        if(!$this->exist($new_key))
        {
            $old_produit->updateProduit($new_key, $new_libelle);
            $this->produits->add($new_key,$old_produit);
        }
        $this->produits->get($new_key)->supprProduit();
    }

    public function storeDeclarant($etb) {
        $this->declarant_cvi = $etb->key[EtablissementFindByCviView::KEY_ETABLISSEMENT_CVI];
        $this->declarant_nom = $etb->value[EtablissementFindByCviView::VALUE_ETABLISSEMENT_NOM];
        $this->commune = $etb->value[EtablissementFindByCviView::VALUE_ETABLISSEMENT_COMMUNE];
    }

    public function storeDeclarantAuto() {
        $etablissement = $this->getEtablissement(acCouchdbClient::HYDRATE_JSON);

        $this->declarant_cvi = $etablissement->cvi;
        $this->declarant_nom = $etablissement->nom;
        $this->commune = $etablissement->siege->commune;
    }

    public function addProduit($produit_hash) {
        $code_douane = ConfigurationClient::getCurrent()->get($produit_hash)->getCodeDouane();
        $libelle = ConfigurationClient::getCurrent()->get($produit_hash)->getLibelleFormat( null, "%format_libelle%");

        $item_produit = $this->produits->add($code_douane);
        $item_produit->libelle_produit_csv = $libelle;
        $item_produit->produit_hash = $produit_hash;
        $item_produit->produit_libelle = $libelle;

        return $item_produit;
    }

    public function getEtablissement($hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {

        return EtablissementClient::getInstance()->find($this->getKey(), $hydrate);
    }
}
