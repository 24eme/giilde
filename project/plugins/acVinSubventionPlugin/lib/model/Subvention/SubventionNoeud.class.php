<?php
/**
 * Model for SubventionNoeud
 *
 */

abstract class SubventionNoeud extends acCouchdbDocumentTree {

    public function add($key = null, $item = null) {
        $item = parent::add($key, $item);
        foreach($item->getNoeudSchema($key) as $subkey => $schema) {
            if(!is_array($schema)) {
                continue;
            }
            $item->add($subkey);

            if(isset($schema['default'])) {
                $item->set($subkey, $schema['default']);
            }
        }

        return $item;
    }
}
