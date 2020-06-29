<?php

abstract class BaseMessages extends acCouchdbDocument {
    public function getDocumentDefinitionModel() {
        return 'Messages';
    }
}
