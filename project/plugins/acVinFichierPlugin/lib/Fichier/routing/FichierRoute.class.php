<?php
class FichierRoute extends sfObjectRoute {

    protected $fichier = null;

    protected function getObjectForParameters($parameters) {

        $this->fichier = FichierClient::getInstance()->find($parameters['id']);
        if (!$this->fichier) {

            throw new sfError404Exception(sprintf('No Fichier found with the id "%s".', $parameters['id']));
        }
        return $this->fichier;
    }

    protected function doConvertObjectToArray($object) {  
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getFichier() {
        if (!$this->fichier) {
            $this->getObject();
        }
        return $this->fichier;
    }

}