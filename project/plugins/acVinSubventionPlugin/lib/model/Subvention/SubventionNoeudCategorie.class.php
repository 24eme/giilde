<?php
/**
 * Model for SubventionInfosCategorie
 *
 */

abstract class SubventionNoeudCategorie extends acCouchdbDocumentTree {

    public function getNoeudSchema() {
        $schema = $this->getDocument()->getNoeudSchema($this->getParent()->getKey());

        return $schema[$this->getKey()];
    }

    public function getSchemaItem($key, $config) {
        $schema = $this->getNoeudSchema();
        return isset($schema[$key][$config]) ? $schema[$key][$config] : null;
    }

    public function getLibelle() {
        $schema = $this->getDocument()->getNoeudSchema($this->getParent()->getKey());
        return isset($schema[$this->getKey().'_libelle']) ? $schema[$this->getKey().'_libelle'] : $this->getKey();
    }
}
