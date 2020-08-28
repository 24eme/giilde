<?php
/**
 * BaseSubventionInfosCategorie
 * 
 * Base model for SubventionInfosCategorie


 
 */

abstract class BaseSubventionInfosCategorie extends SubventionNoeudCategorie {
                
    public function configureTree() {
       $this->_root_class_name = 'Subvention';
       $this->_tree_class_name = 'SubventionInfosCategorie';
    }
                
}