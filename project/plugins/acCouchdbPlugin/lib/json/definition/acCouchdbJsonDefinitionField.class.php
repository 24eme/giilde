<?php

class acCouchdbJsonDefinitionField {
    private $key;
    private $name;
    private $class;
    protected $collection = false;
    protected $collection_class = '';
    protected $collection_inheritance = null;
    protected $is_multiple = false;
    protected $field_definition = null;
    protected $is_required = true;
    protected $type = null;

    const TYPE_ANYONE = 'anyone';
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_COLLECTION = 'collection';
    const TYPE_ARRAY_COLLECTION = 'array_collection';

    public function __construct($name, $type = self::TYPE_STRING, $required = true) {
        $this->key = acCouchdbJsonFields::formatFieldKey($name);
        $this->name = $name;
        $this->type = $type;

        $this->is_required = $required;
        return null;
    }

    /*public function getJsonField($numeric_key, $couchdb_document, $hash, $name = null) {
            if (is_null($name)) {
                $name = $this->name;
            }
            return new $this->class($name, $this->getDefaultValue(), $numeric_key, $couchdb_document, $hash);
    }*/

    public function getDefaultValue($couchdb_document, $hash) {
        return null;
    }

    public function getKey() {
        return $this->key;
    }
    
    public function getType() {
        return $this->type;
    }

    public function getName() {
        return $this->name;
    }

    public function getFieldClass() {
        return $this->class;
    }

    public function getCollectionClass() {
        return $this->collection_class;
    }
    
    public function getCollectionInheritance() {
        return $this->collection_inheritance;
    }

    public function isMultiple() {
        return $this->is_multiple;
    }

    public function isRequired() {
        return $this->is_required;
    }

    public function getDefinition() {
        return $this->field_definition;
    }

    public function getDefinitionByHash($hash) {
        if (!is_null($this->field_definition)) {
            return $this->field_definition->getDefinitionByHash($hash);
        } else {
            throw new acCouchdbException(sprintf('Hash definition does not exist : %s', $hash));
        }
    }
    
    public function isCollection() {
        return $this->collection;
    }
    
    public function isValid($value) {
        if ($this->type == self::TYPE_STRING) {
            return true;
        } elseif($this->type == self::TYPE_INTEGER ) {
            return is_null($value) || is_integer($value);
        } elseif($this->type == self::TYPE_FLOAT ) {
            return is_null($value) || is_integer($value) || is_float($value) ;
        } elseif ($this->type == self::TYPE_COLLECTION) {
            return ($value instanceof acCouchdbJson);
        } elseif ($this->type == self::TYPE_ARRAY_COLLECTION) {
            return ($value instanceof acCouchdbJson) && $value->isArray();
        } elseif ($this->type == self::TYPE_ANYONE) {
            return true;
        } else {
            throw new acCouchdbException("Type doesn't exit");
        }
    }
    
    public function getParsedValue($value)
    {
    	return ($value === "")? null : $value;
    }
    
    public function getPhpType() {
        if ($this->type == self::TYPE_STRING) {
            return "string";
        } elseif($this->type == self::TYPE_INTEGER ) {
            return "integer";
        } elseif($this->type == self::TYPE_FLOAT ) {
            return "float";
        } elseif ($this->type == self::TYPE_COLLECTION || $this->type == self::TYPE_ARRAY_COLLECTION) {
            return $this->getCollectionClass();
        } else {
            return "mixed";
        }
    }
}