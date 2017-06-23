<?php

class acCouchdbJsonDefinition {

    private $_fields = null;
    private $_hash = null;
    private $_model = null;
    private $_inheritance = null;


    public function __construct($model, $hash) {
        $this->_fields = array();
        $this->_model = $model;
        $this->_hash = $hash;
    }

    public function getModel() {
        return $this->_model;
    }

    public function getHash() {
        return $this->_hash;
    }

    public function getInheritance() {

        return $this->_inheritance;
    }

    public function setInheritance($inheritance) {

        return $this->_inheritance = $inheritance;
    }

    public function add(acCouchdbJsonDefinitionField $field) {
        if ($this->has($field->getKey())) {
            return $this->get($field->getKey());
        }
        $this->_fields[$field->getKey()] = $field;

        return $field;
    }

    public function getRequiredFields() {
        $this->_required_fields = array();
        foreach($this->_fields as $key => $field) {
            if (!$field->isMultiple() && $field->isRequired()) {
                $this->_required_fields[$key] = $field;
            }
        }
        return $this->_required_fields;
    }

    public function getFields() {
        return $this->_fields;
    }

    protected function has($key) {
        return isset($this->_fields[$key]);
    }

    protected function hasField($key) {
        if ($this->has($key)) {
            return true;
        }

        if ($this->has('*')) {
           return true;
        }

        return false;
    }

    public function exist($key) {
        return $this->hasField($key);
    }

    public function get($key) {
        if ($this->has($key)) {
            return $this->_fields[$key];
        }

        if ($this->has('*')) {
           return $this->_fields['*'];
        }

        throw new acCouchdbException(sprintf("This field doesn't exist : %s", $key));
    }

    public function getDefinitionByHash($hash) {
        $obj_hash = new acCouchdbHash($hash);
        if (!$obj_hash->isEmpty()) {
            return $this->get($obj_hash->getFirst())->getDefinitionByHash($obj_hash->getAllWithoutFirst());
        } else {
            return $this;
        }
    }

    public function getDefinitionByClassName($class_name) {

        return $this->getDefinitionByHash($this->findHashByClassName($class_name));
    }

    public function findHashByClassName($class_name) {
        foreach($this->_fields as $field) {
            if ($field instanceof acCouchdbJsonDefinitionFieldCollection) {
                if ($field->getCollectionClass() == $class_name) {
                    return $field->getDefinition()->getHash();
                } else {
                    $result = $field->getDefinition()->findHashByClassName($class_name);
                    if (!is_null($result)) {
                        return $result;
                    }
                }
            }
        }

        return null;
    }

    public function toArray() {
        $tab = array();
        foreach($this->getFields() as $key => $field) {
            if($field->isCollection()) {
                $tab[$field->getName()] = $field->getDefinition()->toArray();
            } else {
                $args = array('type' => $field->getType());
                if(!$field->isRequired()) {
                    $args['required'] = false;
                }
                $tab[$key] = $args;
            }
        }

        return $tab;
    }
}
