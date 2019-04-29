<?php
/**
 * BaseDRMESDetailAlcoolPur
 * 
 * Base model for DRMESDetailAlcoolPur

 * @property string $identifiant
 * @property float $volume

 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method float getVolume()
 * @method float setVolume()
 
 */

abstract class BaseDRMESDetailAlcoolPur extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'DRM';
       $this->_tree_class_name = 'DRMESDetailAlcoolPur';
    }
                
}