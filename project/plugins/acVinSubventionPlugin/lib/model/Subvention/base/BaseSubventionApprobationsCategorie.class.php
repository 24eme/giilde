<?php
/**
 * BaseSubventionApprobationsCategorie
 * 
 * Base model for SubventionApprobationsCategorie


 
 */

abstract class BaseSubventionApprobationsCategorie extends SubventionNoeudCategorie {
                
    public function configureTree() {
       $this->_root_class_name = 'Subvention';
       $this->_tree_class_name = 'SubventionApprobationsCategorie';
    }
                
}