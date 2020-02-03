<?php

class CouchdbDebugManager {
    static public $queries = array();

    public static function addQuery($uri, $method, $parameters, $time, $memory) {
        $shortUri = $uri;
        $rawParameters = null;
        if(is_array($parameters) && count($parameters)) {
            $rawParameters = rawurldecode(http_build_query($parameters));
        }

        if($rawParameters) {
            $uri .= '?'.$rawParameters;
            $shortUri .= '?'.substr($rawParameters, 0, 50);
            if(strlen($rawParameters) > 50) {
                $shortUri .= '...';
            }
        }

        self::$queries[] = array('shortUri' => $shortUri, 'uri' => $uri, 'method' => $method, 'time' => $time, 'memory' => $memory);
    }

    public static function getQueries() {

        return self::$queries;
    }

    public static function getTotalMemory() {
        $total = 0;
        foreach(self::getQueries() as $query) {
            $total += $query['memory'];
        }

        return $total;
    }

    public static function getTotalTime() {
        $total = 0;
        foreach(self::getQueries() as $query) {
            $total += $query['time'];
        }

        return $total;
    }
}
