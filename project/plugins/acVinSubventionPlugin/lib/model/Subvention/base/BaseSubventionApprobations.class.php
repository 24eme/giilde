<?php
/**
 * BaseSubventionApprobations
 * 
 * Base model for SubventionApprobations


 
 */

abstract class BaseSubventionApprobations extends SubventionNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'Subvention';
       $this->_tree_class_name = 'SubventionApprobations';
    }
                
}