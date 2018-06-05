<?php
/**
 * BaseEtablissementLieuStockage
 * 
 * Base model for EtablissementLieuStockage

 * @property string $numero
 * @property string $nom
 * @property string $adresse
 * @property string $commune
 * @property string $code_postal

 * @method string getNumero()
 * @method string setNumero()
 * @method string getNom()
 * @method string setNom()
 * @method string getAdresse()
 * @method string setAdresse()
 * @method string getCommune()
 * @method string setCommune()
 * @method string getCodePostal()
 * @method string setCodePostal()
 
 */

abstract class BaseEtablissementLieuStockage extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Etablissement';
       $this->_tree_class_name = 'EtablissementLieuStockage';
    }
                
}