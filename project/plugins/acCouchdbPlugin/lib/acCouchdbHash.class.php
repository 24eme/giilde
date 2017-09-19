<?php

/* This file is part of the acCouchdbHash package.
 * Copyright (c) 2011 Actualys
 * Authors :
 * Tangui Morlier <tangui@tangui.eu.org>
 * Vincent Laurent <vince.laurent@gmail.com>
 * Jean-Baptiste Le Metayer <lemetayer.jb@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * acCouchdbHash model.
 *
 * @package    acCouchdbPlugin
 * @subpackage lib
 * @author     Tangui Morlier <tangui@tangui.eu.org>
 * @author     Vincent Laurent <vince.laurent@gmail.com>
 * @author     Jean-Baptiste Le Metayer <lemetayer.jb@gmail.com>
 * @version    0.1
 */
class acCouchdbHash {

    private $_hash = array();



    /**
     *
     * @return array
     */
    public function toArray()
    {
    	return $this->_hash;
    }

    /**
     *
     * @param string $hash
     * @return void
     */
    public function __construct($hash) {
        $tab_hash = explode('/', $hash);
        $this->_hash = array();
        foreach ($tab_hash as $item) {
            if (trim($item) != '') {
                $this->_hash[] = $item;
            }
        }
    }

    /**
     *
     * @return boolean
     */
    public function isAlone() {

        return (!(count($this->_hash) > 1));
    }

    /**
     *
     * @return boolean
     */
    public function isEmpty() {

        return (!(count($this->_hash) > 0));
    }

    /**
     *
     * @return string
     */
    public function getFirst() {
        if (count($this->_hash) > 0) {
            return $this->_hash[0];
        } else {
            return null;
        }
    }

    /**
     *
     * @return string
     */
    public function getLast() {
        if (count($this->_hash) > 0) {
            return $this->_hash[count($this->_hash) - 1];
        } else {
            return null;
        }
    }

    /**
     *
     * @return string
     */
    public function getAllWithoutFirst() {
        if (!$this->isAlone()) {
            $result = '';
            $separator = '';
            for ($i = 1; $i < count($this->_hash); $i++) {
                $result .= $separator . (string) $this->_hash[$i];
                $separator = '/';
            }
            return $result;
        } else {
            return null;
        }
    }

    public static function getResultArray($hash) {
        if($hash && strpos($hash, '/') === false) {
            return array(
                "f" => $hash,
                "w" => null
            );
        }

        $hashArray = array_filter(explode('/', $hash), 'strlen');
        $first = array_shift($hashArray);

        return array(
            "f" => $first,
            "w" => (count($hashArray) > 0) ? implode('/', $hashArray) : null,
        );
    }

}
