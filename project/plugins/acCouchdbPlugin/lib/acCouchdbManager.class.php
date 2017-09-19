<?php


class acCouchdbManager {
    protected static $_instance;

    protected $_client = null;
    protected $_clients_model = array();

    protected $_views = array();

    protected $_definition = array();
    protected $_definition_tree_hash = array();
    protected $_definition_hash = array();

    public $_custom_accessors = array();
    public $_custom_mutators = array();

    public static $keysFormat = array();
    public static $hashObjects = array();

    protected $_schema = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if ( ! isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function initializeClient($dsn, $dbname) {
        self::getInstance()->_client = new acCouchdbClient($dsn, $dbname);
		if (!self::getInstance()->_client->databaseExists())
	  		throw new sfException($dbname." does not exist");
		
		return self::getInstance()->_client;
    }

    /**
     *
     * @param string $model
     * @return acCouchdbClient 
     */
    public static function getClient($model = null) {
        if (is_null($model)) {
            return self::getInstance()->_client;
        } else {
            return self::getInstance()->getClientByModel($model);
        }
    }

    protected function getClientByModel($model) {
        if (!isset(self::getInstance()->_clients_model[$model])) {
            $class_name = $model.'Client';
            if (class_exists($class_name)) {
                self::getInstance()->_clients_model[$model] = new $class_name(self::getClient()->dsn(), self::getClient()->getDatabaseName());
            } else {
                throw new acCouchdbException(sprintf("This model client doesn't exist : %s", $class_name));
            }
        }

        return self::getInstance()->_clients_model[$model];
    }

    public static function getView($design, $view, $model = null, $class_name = null) {
    	$key = sfInflector::camelize($design.'_'.$view.'_'.$model);
    	if (!isset(self::getInstance()->_views[$key])) {
            if ($class_name == null) {
    		  $class_name = sfInflector::camelize($design.'_'.$view).'View';
            }
    		if (class_exists($class_name)) {
                self::getInstance()->_views[$key] = new $class_name(self::getClient($model), $design, $view);
            } else {
                throw new acCouchdbException(sprintf("This view class doesn't exist : %s", $class_name));
            }
    	}

    	return self::getInstance()->_views[$key];
    }
    
    public static function getSchema() {
        if (is_null(self::getInstance()->_schema)) {
            self::getInstance()->_schema = sfYaml::load(self::getInstance()->getSchemaYaml());
        }

        return self::getInstance()->_schema;
    }

    public static function setSchema($schema) {
        self::getInstance()->_schema = $schema;
    }

    public static function getDefinition($model) {
        if (!isset(self::getInstance()->_definition[$model])) {
            self::getInstance()->_definition[$model] = acCouchdbJsonDefinitionParser::parse($model, 
                                                                                            self::getInstance()->getSchema(), 
                                                                                            new acCouchdbJsonDefinition($model, ''));
        }

        return self::getInstance()->_definition[$model];
    }

    public static function getDefinitionByHash($model, $hash) {
        if (!isset(self::getInstance()->_definition_hash[$model][$hash])) {
            self::getInstance()->_definition_hash[$model][$hash] = self::getDefinition($model)->getDefinitionByHash($hash);
        } 

        return self::getInstance()->_definition_hash[$model][$hash];
    }

    public static function getDefinitionHashTree($model, $class_tree) {
        if (!isset(self::getInstance()->_definition_tree_hash[$class_tree])) {
            self::getInstance()->_definition_tree_hash[$class_tree] = self::getDefinition($model)->getDefinitionByClassName($class_tree);
        }
        
        return self::getInstance()->_definition_tree_hash[$class_tree];
    }

    protected function getSchemaYaml() {
    	$yaml = "";
    	$files = sfFinder::type('file')->name('/^schema\.yml$/')->in(array(sfConfig::get('sf_config_dir'), sfConfig::get('sf_plugins_dir')));

    	foreach($files as $file) {
    		$yaml .= file_get_contents($file);
    	}

    	return $yaml;
    }
}
