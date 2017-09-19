<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of acCouchdbJsonFields
 *
 * @author vince
 */
abstract class acCouchdbJsonFields {

    /**
     *
     * @var array
     */
    private $_fields = null;
    /**
     *
     * @var array 
     */
    private $_fields_name = null;
    /**
     *
     * @var string 
     */
    private $_definition_model = null;
    /**
     *
     * @var string
     */
    private $_definition_hash = null;
    /**
     *
     * @var string 
     */
    private $_hash = null;
    /**
     *
     * @var type 
     */
    private $_is_array = false;
    /**
     *
     * @var acCouchdbDocument
     */
    private $_document = null;

    private $_unloaded_data = null;

    /**
     *
     * @var acCouchdbDocument 
     */
    public function __construct(acCouchdbJsonDefinition $definition, acCouchdbDocument $document, $hash) {
        $this->_fields = array();
        $this->_fields_name = array();
        $this->_document = $document;
        $this->_definition_model = $definition->getModel();
        $this->_definition_hash = $definition->getHash();
        $this->_hash = $hash;
        $this->_is_array = false;
        $this->initializeDefinition();
    }

    protected function reset($document) {
        $this->_fields = array();
        $this->_fields_name = array();
        $this->_unloaded_data = null;
        $this->_document = $document;
    }

    /**
     * Retourne la définition du modèle associé
     * 
     * @return acCouchdbJsonDefinition 
     */
    public function getDefinition() {
        return acCouchdbManager::getDefinitionByHash($this->_definition_model, $this->_definition_hash);
    }

    /**
     * Retourne le document conteneur (permet donc de retourner à la racine)
     * @return acCouchdbDocument 
     * @deprecated
     * @see function getDocument()
     */
    public function getCouchdbDocument() {
        return $this->_document;
    }

    /**
     * Retourne le document conteneur (permet donc de retourner à la racine)
     * @return acCouchdbDocument 
     */
    public function getDocument() {
        return $this->_document;
    }

    public function getFields() {
        return $this->_fields;
    }

    /**
     * Permet de passer l'objet en mode "Tableau", il possédera donc des clés numériques
     * 
     * @param bool $value 
     */
    public function setIsArray($value) {
        $this->_is_array = $value;
    }
    
	/**
	 * 
	 * @return string
	 */
    public function getHash() {
        return $this->_hash;
    }
    
    
  public function getHashForKey() {
      return str_replace('/', '-', $this->_hash);
  }
  

    /**
     * Permet de savoir si l'objet est en mode tableau
     * @return bool 
     */
    public function isArray() {
        return $this->_is_array;
    }

    public function getFieldName($key) {
        if ($this->_is_array) {
            return $this->getFieldNameNumeric($key);
        } else {
            return $this->getFieldNameNormal($key);
        }
    }

    /**
     * Charge les données à partir de différents types : array, stdClass et acCouchdbJson
     * @param mixed $data 
     */
    public function load($data) {
        $this->_unloaded_data = $data;
    }
    
    public function loadData() {
        if (is_null($this->_unloaded_data)) {
            return;
        }

        $data = $this->_unloaded_data;
        $this->_unloaded_data = null;
        foreach ($data as $key => $item) {
            if (!$this->_exist($key)) {
                $this->_add($key);
            }
            $this->_set($key, $item);
        }
    }

    protected function fieldIsCollection($key) {
        return $this->getDefinition()->get($key)->isCollection();
    }

    protected function _add($key = null, $item = null) {
        if (!$this->getDefinition()->exist($key)) {
            throw new acCouchdbException(sprintf("The field \"%s\" does not exist in the schema.yml definition in the document \"%s\". Please remove it cute little dev !", $this->getHash()."/".$key, $this->getDocument()->get('_id')));
        }
        if ($this->_is_array) {
            $key = $this->addNumeric($key);
            $field = $this->getField($key);
        } else {
            $field = $this->addNormal($key);
        }
        
        if (!is_null($item)) {
            $this->set($key, $item);
        }
        return $field;
    }

    public function _set($key, $value) {
        return $this->setFromDataOrObject($key, $value);
    }

    /**
     *
     * @param string $key
     * @return mixed 
     */
    public function _get($key) {
        return $this->getField($key);
    }

    protected function _remove($key_or_hash) {
        $obj_hash = new acCouchdbHash($key_or_hash);
        if ($obj_hash->isAlone()) {
            if ($this->_is_array) {
                return $this->removeNumeric($key_or_hash);
            } else {
                return $this->removeNormal($key_or_hash);
            }
        } else {
            return $this->getField($obj_hash->getFirst())->remove($obj_hash->getAllWithoutFirst());
        }
    }

    protected function _exist($key) {
        $this->loadData();
        if ($this->_is_array) {
            return $this->hasFieldNumeric($key);
        } else {
            return $this->hasFieldNormal($key);
        }
    }

    protected function getData() {
        $this->loadData();
        $data = array();
        foreach ($this->_fields as $key => $field) {
            if ($this->_is_array) {
                if ($this->fieldIsCollection($key)) {
                    $data[] = $field->getData();
                } else {
                    $data[] = $this->getDefinition()->get($key)->getParsedValue($field);
                }
            } else {
                if ($this->fieldIsCollection($key)) {
                    $data[$this->getFieldName($key)] = $field->getData();
                } else {
                    $data[$this->getFieldName($key)] = $this->getDefinition()->get($key)->getParsedValue($field);
                }
            }
        }

        if ($this->_is_array) {
            return $data;
        } else {
            return (Object) $data;
        }
    }

    public static function formatFieldKey($key) {
        if(!isset(acCouchdbManager::$keysFormat[$key])) {

            acCouchdbManager::$keysFormat[$key] = sfInflector::underscore($key);
        }

        return acCouchdbManager::$keysFormat[$key];
    }

    protected function getCouchdbManager() {

        return acCouchdbManager::getInstance();
    }

    protected function getModelAccessor() {
        $manager = $this->getCouchdbManager();
        if (!isset($manager->_custom_accessors[$this->_definition_model])) {
            $manager->_custom_accessors[$this->_definition_model][$this->_definition_hash] = array();

            return array();
        }
        $modelAccessor = $manager->_custom_accessors[$this->_definition_model];
        if (!isset($modelAccessor[$this->_definition_hash])) {
            $manager->_custom_accessors[$this->_definition_model][$this->_definition_hash] = array();

            return array();
        }

        return $modelAccessor[$this->_definition_hash];
    }

    protected function getModelMutator() {
        $manager = $this->getCouchdbManager();
        if (!isset($manager->_custom_mutators[$this->_definition_model])) {
            $manager->_custom_mutators[$this->_definition_model][$this->_definition_hash] = array();

            return array();
        }
        $modelMutator = $manager->_custom_mutators[$this->_definition_model];
        if (!isset($modelMutator[$this->_definition_hash])) {
            $manager->_custom_mutators[$this->_definition_model][$this->_definition_hash] = array();

            return array();
        }

        return $modelMutator[$this->_definition_hash];
    }

    protected function hasAccessor($key) {
        if(!$this instanceof acCouchdbDocumentStorable) {
            return false;
        }

        $fieldName = self::formatFieldKey($key);
        $model_accessor = $this->getModelAccessor();

        if (array_key_exists($fieldName, $model_accessor) && is_null($model_accessor[$fieldName])) {
            return false;
        } elseif (isset($model_accessor[$fieldName])) {
            return $model_accessor[$fieldName];
        } else {
            $accessor = 'get' . sfInflector::camelize($fieldName);
            if ($accessor != 'get' && method_exists($this, $accessor)) {
                acCouchdbManager::getInstance()->_custom_accessors[$this->_definition_model][$this->_definition_hash][$fieldName] = $accessor;

                return $accessor;
            }

            acCouchdbManager::getInstance()->_custom_accessors[$this->_definition_model][$this->_definition_hash][$fieldName] = null;

            return false;
        }
    }

    protected function hasMutator($key) {
        if(!$this instanceof acCouchdbDocumentStorable) {
            return false;
        }

        $fieldName = self::formatFieldKey($key);
        $model_mutator = $this->getModelMutator();

        if (array_key_exists($fieldName, $model_mutator) && is_null($model_mutator[$fieldName])) {
            return false;
        } elseif (isset($model_mutator[$fieldName])) {
            return $model_mutator[$fieldName];
        } else {
            $mutator = 'set' . sfInflector::camelize($fieldName);
            if ($mutator != 'set' && method_exists($this, $mutator)) {
                acCouchdbManager::getInstance()->_custom_mutators[$this->_definition_model][$this->_definition_hash][$fieldName] = $mutator;
                return $mutator;
            } else {
                acCouchdbManager::getInstance()->_custom_mutators[$this->_definition_model][$this->_definition_hash][$fieldName] = null;
                return false;
            }
        }
    }

    protected function getMutator($key) {
        $mutator = $this->hasMutator($key);
        if ($mutator) {
            return $mutator;
        }
        return null;
    }

    protected function getAccessor($key) {
        $accessor = $this->hasAccessor($key);
        if ($accessor) {
            return $accessor;
        }
        return null;
    }

    private function getFieldNameNormal($key) {
        if ($this->_exist($key)) {
            if ($this->getDefinition()->get($key)->isMultiple()) {
                return $this->_fields_name[$key];
            } else {
                return $this->getDefinition()->get($key)->getName();
            }
        } else {
            throw new acCouchdbException(sprintf('field inexistant : %s (%s)', $key, $this->getHash()));
        }
    }

    protected function definitionValidation() {
        foreach ($this->_fields as $key => $field) {
            if (!$this->getDefinition()->get($key)->isValid($field)) {
                throw new acCouchdbException(sprintf("Value not valid : %s required %s (%s)", gettype($field), $this->getDefinition()->get($key)->getType(), $this->getHash() . "/" . $key));
            }
            if ($this->getDefinition()->get($key)->isCollection()) {
                $field->definitionValidation();
            }
        }
    }

    private function getFieldNameNumeric($key) {
        if ($this->_exist($key)) {
            return $key;
        } else {
            throw new acCouchdbException(sprintf('field inexistant : %s', $key));
        }
    }

    private function getField($key) {
        if ($this->_is_array) {
            return $this->getFieldNumeric($key);
        } else {
            return $this->getFieldNormal($key);
        }
    }

    private function getFieldNormal($key) {
        if ($this->_exist($key)) {
            return $this->_fields[self::formatFieldKey($key)];
        } else {
            throw new acCouchdbException(sprintf('field inexistant : %s (%s%s)', $key, $this->_definition_model, $this->getHash()));
        }
    }

    private function getFieldNumeric($key) {
        if ($this->_exist($key)) {
            return $this->_fields[$key];
        } else {
            throw new acCouchdbException(sprintf('field inexistant : %s (%s%s)', $key, $this->_definition_model, $this->getHash()));
        }
    }

    private function addNormal($key) {
        if ($this->_exist($key)) {
            return $this->getField($key);
        }
        $name = $key;
        $key = self::formatFieldKey($key);
        // ajouter le hash et le document
        $field = $this->getDefinition()->get($key)->getDefaultValue($this->_document, $this->_hash . '/' . $name);
        $this->_fields[$key] = $field;
        if ($this->getDefinition()->get($key)->isMultiple()) {
            $this->_fields_name[$key] = $name;
        }
        return $field;
    }

    private function addNumeric($key) {
        if ($key === null) {
           $this->loadData(); 
        }
        if ($key !== null && $this->_exist($key)) {
            
            return $key;
        }
        
        $field = $this->getDefinition()->get('*')->getDefaultValue($this->_document, $this->_hash . '/' . count($this->_fields));
        $this->_fields[count($this->_fields)] = $field;
        
        return count($this->_fields)-1;
    }

    /**
     *
     * @param string $key
     * @param mixed $data_or_object
     * @return mixed 
     */
    private function setFromDataOrObject($key, $data_or_object) {
        if($data_or_object instanceof acCouchdbJson || $data_or_object instanceof stdClass || is_array($data_or_object)) {
            $field = $this->_get($key);
	    if(!is_object($field)) {
		throw new acCouchdbException(sprintf('Wrong value : %s for %s key', $data_or_object, $key));
	    }
            if ($data_or_object instanceof acCouchdbJson) {
            	$field->load($data_or_object->getData());
	    }else{
		$field->load($data_or_object);
            }
	    return $field;
        }
        if (!$this->exist($key)) {
           throw new acCouchdbException(sprintf('field inexistant : %s (%s%s)', $key, $this->_definition_model, $this->getHash()));
        }
        if ($this->isArray()) {
            $this->_fields[$key] = $data_or_object;
        } else {
            $this->_fields[self::formatFieldKey($key)] = $data_or_object;
        }
        return $data_or_object;
    }
    
    public function clear() {
        if (!$this->_is_array) {
            throw new acCouchdbException("You can only clear an array collection");
        }
        
        $this->_fields = array();
    }

    private function removeNormal($key) {
        if ($this->_exist($key)) {
            unset($this->_fields[self::formatFieldKey($key)]);
            return true;
        }
        return false;
    }

    private function removeNumeric($key) {
        if ($this->_exist($key)) {
            unset($this->_fields[$key]);
            return true;
        }
        return false;
    }

    private function hasFieldNormal($key) {
        $fieldKey = self::formatFieldKey($key);

        return (isset($this->_fields[$fieldKey]) || array_key_exists($fieldKey, $this->_fields));
    }

    private function hasFieldNumeric($key) {
        return array_key_exists($key, $this->_fields);
    }

    /**
     * Ajoute les différents champs requis du modèle
     */
    private function initializeDefinition() {
        foreach ($this->getDefinition()->getRequiredFields() as $field_definition) {
            $this->_add($field_definition->getKey(), null);
        }
    }
}
