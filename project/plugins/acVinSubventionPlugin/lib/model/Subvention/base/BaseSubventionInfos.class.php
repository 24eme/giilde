<?php
/**
 * BaseSubventionInfos
 * 
 * Base model for SubventionInfos


 
 */

abstract class BaseSubventionInfos extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Subvention';
       $this->_tree_class_name = 'SubventionInfos';
    }
                
}