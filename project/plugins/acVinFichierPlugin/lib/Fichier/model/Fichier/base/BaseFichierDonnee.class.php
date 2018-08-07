<?php
/**
 * BaseFichierDonnee
 * 
 * Base model for BaseFichierDonnee

 * @property string $produit
 * @property string $categorie
 * @property string $valeur
 * @property string $tiers

 * @method string getProduit()
 * @method string setProduit()
 * @method string getCategorie()
 * @method string setCategorie()
 * @method string getValeur()
 * @method string setValeur()
 * @method string getTiers()
 * @method string setTiers()
 
 */

abstract class BaseFichierDonnee extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Fichier';
       $this->_tree_class_name = 'FichierDonnee';
    }
                
}