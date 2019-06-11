<?php
/**
 * BaseDRMNonApurement
 *
 * Base model for DRMNonApurement



 */

abstract class BaseDRMNonApurementItem extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'DRM';
       $this->_tree_class_name = 'DRMNonApurementItem';
    }

}
