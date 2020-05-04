<?php

header('Content-type: text/plain');

try {
    require_once(dirname(__FILE__).'/../../config/ProjectConfiguration.class.php');

    $configuration = ProjectConfiguration::getApplicationConfiguration($application, 'prod', false);
    sfContext::createInstance($configuration);
    echo "Symfony OK\n";
} catch(Exception $e) {
    echo "Symfony Error\n";
    header("HTTP/1.0 500 Internal Server Error");
}

try {
    if(!acCouchdbManager::getClient()->databaseExists()) {
        throw new Exception();
    }
    echo "Couchdb OK\n";
} catch(Exception $e) {
    echo "Couchdb Error\n";
    header("HTTP/1.0 500 Internal Server Error");
}

try {
    if(acElasticaManager::getClient()->getDefaultIndex()->getStats()->getResponse()->hasError()) {
        throw new Exception();
    }
    echo "Elasticsearch OK\n";
} catch(Exception $e) {
    echo "Elasticsearch Error\n";
    header("HTTP/1.0 500 Internal Server Error");
}
