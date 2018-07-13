<?php

/**
 * Model for DAE
 *
 */
class DAE extends BaseDAE implements InterfaceDeclarantDocument {

    protected $declarant_document = null;

    public function __construct() {
        parent::__construct();
        $this->initDocuments();
    }

    public function constructId() {
        $numero = DAEClient::getInstance()->getNextIdentifiantForEtablissementAndDay($this->identifiant, $this->date);
        $this->set('_id', DAEClient::getInstance()->buildId($this->identifiant, $this->date,$numero));
    }


    protected function initDocuments() {
        $this->declarant_document = new DeclarantDocument($this);
    }

    /*** DECLARANT ***/

    public function getEtablissementObject() {
        return $this->getEtablissement();
    }

    public function storeDeclarant() {
        $this->declarant_document->storeDeclarant();
    }

    /*** FIN DECLARANT ***/
}
