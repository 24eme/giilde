<?php

class Couchdb2elBuildMappingTask extends sfBaseTask {


    protected static $defaultProperties = array("seq" => array("type" => "long"),
                                                                "source" => array("type" => "string", "index" => "not_analyzed"),
                                                                "id" => array("type" => "string", "index" => "not_analyzed"),
                                                                "changes" => array("properties" => array("rev" => array("type" => "string", "index" => "not_analyzed"))));

    protected static $analysedString = array("type" => "string");
    protected static $notAnalysedString = array("index" => "not_analyzed", "type" => "string");
    protected static $formattedDate = array("format" => "strict_date_optional_time||epoch_millis", "type" => "date");
    protected static $formattedFloat = array("type" => "long");

    protected function configure() {
        // // add your own arguments here
        $this->addArguments(array(
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default')

        ));

        $this->namespace = 'couchdb2el';
        $this->name = 'build-mapping';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
    [build-mapping|INFO] créer un mapping pour l'indexeur elasticsearch
    Appel :
      [php symfony couchdb2el:build-mapping --application="app" |INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
      // initialize the database connection
      $databaseManager = new sfDatabaseManager($this->configuration);
      $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

      if(!$options['application']){
        throw new sfException("Il faut une application pour utiliser cette tache : php symfony couchdb2el:build-mapping --application=\"app\"");
      }

      $mappings = array("mappings" => array());
      $schemas = acCouchdbManager::getInstance()->getSchema();
      foreach ($schemas as $schemaName => $schema) {
        $schemaNameMapping = strtoupper($schemaName);
        if(!isset($schema["indexable"]) || $schema["indexable"]){
          $mappings["mappings"][$schemaNameMapping] = array();
          $mappings["mappings"][$schemaNameMapping]["properties"] = self::$defaultProperties;
          $mappings["mappings"][$schemaNameMapping]["properties"]["doc"] = array();
          $mappings["mappings"][$schemaNameMapping]["properties"]["doc"]["properties"] = $this->transformSchemaForMapping($schema);
        }
      }

      echo json_encode($mappings,JSON_PRETTY_PRINT );
    }

    protected function transformSchemaForMapping($schemaCouchdb){
      if(!isset($schemaCouchdb["definition"])){
        throw new sfException("Le schema couchdb suivant comporte des avaries : \n".json_encode($schemaCouchdb,JSON_PRETTY_PRINT) );
      }

      $fieldsCouchdb = $schemaCouchdb["definition"];
      $schemaMapping = array();
      foreach ($fieldsCouchdb as $fieldsKey => $fields) {
          foreach ($fields as $fieldName => $fieldCouchdb) {
            $fieldMapping = $this->transformFieldForMapping($fieldCouchdb,$fieldName);
            if($fieldMapping){
              $schemaMapping[$fieldName] = $fieldMapping;
            }
          }
      }
      return $schemaMapping;
    }

    protected function transformFieldForMapping($fieldCouchdb,$fieldName = null){
      if(is_array($fieldCouchdb) && isset($fieldCouchdb["indexable"]) && !$fieldCouchdb["indexable"]){
        return null;
      }

      if(isset($fieldCouchdb["required"])){
        unset($fieldCouchdb["required"]);
      }
      if(isset($fieldCouchdb["require"])){
        unset($fieldCouchdb["require"]);
      }

      if(is_array($fieldCouchdb) && !count($fieldCouchdb)){
        return self::$notAnalysedString;
      }

      if(is_array($fieldCouchdb) && isset($fieldCouchdb["searchable"]) && $fieldCouchdb["searchable"]){
        return self::$analysedString;;
      }
      if(is_array($fieldCouchdb) && isset($fieldCouchdb["type"])){
        if($fieldCouchdb["type"] == "date"){
          return self::$formattedDate;
        }

        if($fieldCouchdb["type"] == "float"){
          return self::$formattedFloat;
        }

        //array_collection
        if($fieldCouchdb["type"] == "array_collection"){
          $localFields = $fieldCouchdb["definition"]["fields"];
          $localFieldsKeys = array_keys($localFields);
          //simple array_collection
          if((count($localFieldsKeys) == 1) && (($first = array_shift($localFieldsKeys)) == "*") && !count($localFields[$first])){
            return self::$notAnalysedString;
          }
        }

        //collection
        if($fieldCouchdb["type"] == "collection"){
          $localFields = $fieldCouchdb["definition"]["fields"];

          $simple = true;
          //simple collection
          if($simple){
            $collection_mapping = array();
            foreach ($localFields as $localFieldKey => $localField) {
              $collection_mapping[$localFieldKey] = $this->transformFieldForMapping($localField,$localFieldKey);
            }
            return $collection_mapping;
          }
        }
      }
    }
}
