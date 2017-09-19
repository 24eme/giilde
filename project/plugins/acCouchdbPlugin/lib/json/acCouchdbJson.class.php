<?php

class acCouchdbJson extends acCouchdbJsonFields implements IteratorAggregate, ArrayAccess, Countable {

    /**
     *
     * @var string
     */
    private $_filter = null;
    private $_filter_persisent = false;
    private $_parent = null;

    /**
     *
     * @param acCouchdbJsonDefinition $definition
     * @param acCouchdbDocument $document
     * @param string $hash 
     */
    public function __construct(acCouchdbJsonDefinition $definition, acCouchdbDocument $document, $hash) {
        parent::__construct($definition, $document, $hash);
    }

    /**
     *
     * @param string $key_or_hash
     * @return mixed
     */
    public function get($key_or_hash) {
        $objHash = $this->getHashObject($key_or_hash);

        if ($objHash["w"] === null) {
            if (!$this->isArray() && $method = $this->getAccessor($objHash["f"])) {

                return $this->$method();
            }
            return $this->_get($objHash["f"]);
        } else {
            return $this->get($objHash["f"])->get($objHash["w"]);
        }
    }

    /**
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key) {
        return $this->get($key);
    }

    /**
     *
     * @param string $key_or_hash
     * @return mixed
     */
    public function getOrAdd($key_or_hash) {
        $objHash = $this->getHashObject($key_or_hash);

        if ($objHash["w"] === null) {

            return $this->add($objHash["f"]);
        }

        return $this->add($objHash["f"])->getOrAdd($objHash["w"]);
    }

    public function set($key_or_hash, $value) {
        $objHash = $this->getHashObject($key_or_hash);

        if ($objHash["w"] === null) {
            if (!$this->isArray() && $method = $this->getMutator($objHash["f"])) {

                return $this->$method($value);
            }

            return $this->_set($objHash["f"], $value);
        } else {

            return $this->get($objHash["f"])->set($objHash["w"], $value);
        }
    }

    public function __set($key, $value) {
        return $this->set($key, $value);
    }

    public function move($key_or_hash, $new_key_or_hash) {
        $object = $this->get($key_or_hash);
        if ($key_or_hash != $new_key_or_hash) {
            if ($this->exist($new_key_or_hash)) {
                throw new acCouchdbException($new_key_or_hash." : new key already exist");
            }
            $clone = clone $object;
            $this->remove($key_or_hash);
            $new = $this->getOrAdd($new_key_or_hash);
            return $new->getParent()->set($new->getKey(), $clone);
        }
        return $object;
    }
    
    public function moveAndClean($key_or_hash, $new_key_or_hash) {
    	$node = $this->get($key_or_hash)->getParent()->getHash();
        $object = $this->move($key_or_hash, $new_key_or_hash);
        $this->clean($node);
        return $object;
    }
    
    public function clean($key_or_hash) {
    	$object = $this->getDocument()->get($key_or_hash);
    	if ($object->count() == 0) {
    		$object->delete();
    		return true;
    	}
    	return false;
    }
    
    public function delete() {
    	return $this->getParent()->remove($this->getKey());
    }

    public function remove($key_or_hash) {
        return $this->_remove($key_or_hash);
    }

    public function add($key = null, $item = null) {
        return $this->_add($key, $item);
    }

    public function exist($key_or_hash) {
    	$obj_hash = new acCouchdbHash($key_or_hash);
        if ($obj_hash->isAlone()) {
        	return $this->_exist($obj_hash->getFirst());
        } else {
        	$exist = true;
        	$object = $this;
        	foreach ($obj_hash->toArray() as $key) {
        		if ($object->_exist($key)) {
        			$object = $object->get($key);
        		} else {
        			$exist = false;
        			break;
        		}
        	}
        	return $exist;
        }
    }

    public function __call($method, $arguments) {
        if (in_array($verb = substr($method, 0, 3), array('set', 'get'))) {
            $name = substr($method, 3);
            if ($this->exist($name)) {
                return call_user_func_array(
                        array($this, $verb), array_merge(array($name), $arguments)
                );
            } else {
                throw new acCouchdbException(sprintf('Method undefined : %s', $method));
            }
        } else {
            throw new acCouchdbException(sprintf('Method undefined : %s', $method));
        }
    }

    public function toJson() {
        return $this->getData();
    }

    public function fromArray(array $values) {
        foreach ($values as $key => $value) {
            if ($this->getDefinition()->exist($key)) {
	        	if ($this->fieldIsCollection($key) && !$value) {
	        		$value = array();
	        	}
                        
	        	if($this->fieldIsCollection($key)) {
                                $item = $this->add($key);
                                if($item->isArray()) {
                                   $item->clear();
                                }
	        		$item->fromArray($value);
	        	} else {
                            $this->add($key);
                            $this->set($key, $value);  
	        	}
            }
        }
    }

    /**
     * @deprecated
     * @see call function toArray(false, false)
     */
    public function toSimpleFields() {
        return $this->toArray(false, false);
    }

    public function toArray($deep_array = true, $fetch_object = true) {
        $array_fields = array();
        foreach ($this as $key => $field) {
            if ($deep_array && $this->fieldIsCollection($key) && !$fetch_object) {
                $array_fields[$key] = $field->toArray($deep_array, $fetch_object);
            } elseif ($deep_array && $this->fieldIsCollection($key) && $fetch_object) {
                $array_fields[$key] = $this->get($key);
            } elseif (!$this->fieldIsCollection($key)) {
                $array_fields[$key] = $this->get($key);
            }
        }

        return $array_fields;
    }

    

    public function getParentHash() {
        return preg_replace('/\/[^\/]+$/', '', $this->getHash());
    }

    public function getParent() {
        if (is_null($this->_parent)) {
            $this->_parent = $this->getDocument()->get($this->getParentHash());
        }
        return $this->_parent;
    }

    public function getKey() {
        return preg_replace('/^.*\//', '\1', $this->getHash());
    }

    public function getFirst() {
        return $this->getIterator()->getFirst();
    }

    public function getFirstKey() {
        return $this->getIterator()->getFirstKey();
    }

    public function getLast() {
        return $this->getIterator()->getLast();
    }

    public function getLastKey() {
        return $this->getIterator()->getLastKey();
    }
    
    public function getNextSister() {
    	$next = null;
    	for ($item = $this->getParent()->getIterator(); $item->valid(); $item->getNextSister()) {
    		if ($this->getKey() == $item->key()) {
    			$next = $item->getNextSister();
    			break;
    		}
		}
		return $next;
    }
    
	public function getPreviousSister() {
    	$previous = null;
    	for ($item = $this->getParent()->getIterator(); $item->valid(); $item->getNextSister()) {
    		if ($this->getKey() == $item->key()) {
    			$previous = $item->getPreviousSister();
    			break;
    		}
		}
		return $previous;
    }

    protected function getHashObject($key_or_hash) {
        if(!isset(acCouchdbManager::$hashObjects[$key_or_hash])) {
            $hashObject = acCouchdbHash::getResultArray($key_or_hash);
            if($hashObject["w"] !== null) {
                acCouchdbManager::$hashObjects[$key_or_hash] = serialize($hashObject);
            }
        } else {
            $hashObject = unserialize(acCouchdbManager::$hashObjects[$key_or_hash]);
        }

        return $hashObject;
    }

    protected function loadAllData() {
       $this->loadData();
       foreach ($this as $key => $field) {
            if ($this->fieldIsCollection($key)) {
                if(!is_object($field)) {

                    throw new sfException(sprintf("%s/%s should be a collection (string value given)", $this->getHash(), $key));
                }
                $field->loadAllData();
            }
        } 
    }

    protected function init($params = array()) {
        foreach ($this as $key => $field) {
            if ($this->fieldIsCollection($key)) {
                $field->init($params);
            }
        }
    }

    protected function update($params = array()) {
        foreach ($this as $key => $field) {
            if ($this->fieldIsCollection($key)) {
    	        if (!is_object($field))
    		       throw new sfException("Cannot update the object corresponding to '$key' : not an object");
    	           $field->update($params);
                }
        }
    }

    public function filter($expression, $persisent = false) {
        $this->_filter = $expression;
        $this->_filter_persisent = $persisent;
        return $this;
    }

    public function clearFilter() {
        $this->_filter = null;
        $this->_filter_persisent = false;
    }

    public function getIterator() {
        $this->loadData();
        $iterator = new acCouchdbJsonArrayIterator($this, $this->_filter);
        if (!$this->_filter_persisent) {
            $this->clearFilter();
        }
        return $iterator;
    }

    public function offsetGet($index) {
        return $this->get($index);
    }

    public function offsetSet($index, $newval) {
        return $this->set($index, $newval);
    }

    public function offsetExists($index) {
        return $this->exist($index);
    }

    public function offsetUnset($offset) {
        return $this->remove($offset);
    }

    public function count() {
        return $this->getIterator()->count();
    }

}
