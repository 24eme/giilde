<?php
/**
 * Model for SubventionInfosCategorie
 *
 */

class SubventionInfosCategorie extends BaseSubventionInfosCategorie {
    public function getInfosSchema() {
        $schema = $this->getDocument()->getInfosSchema();

        return $schema[$this->getKey()];
    }

    public function getInfosSchemaItem($key, $config) {
        $schema = $this->getInfosSchema();

        return isset($schema[$key][$config]) ? $schema[$key][$config] : null;
    }
}
