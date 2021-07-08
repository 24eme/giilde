<?php
/**
 * BaseDSNegoce
 *
 * Base model for DSNegoce
 *

 */

abstract class BaseDSNegoce extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'DSNegoce';
    }

}
