<?php
class SocieteRoute extends sfObjectRoute implements InterfaceSocieteRoute, InterfaceEtablissementRoute {

    protected $societe = null;

    protected function getObjectForParameters($parameters = null) {
      $this->societe = SocieteClient::getInstance()->find($parameters['identifiant']);
      if (sfContext::getInstance()->getUser()->hasTeledeclaration() && sfContext::getInstance()->getUser()->getCompte()->id_societe != $this->societe->_id) {
          throw new sfError404Exception("Vous n'avez pas le droit d'accéder à cette page");
      }
      $module = sfContext::getInstance()->getRequest()->getParameterHolder()->get('module');
      sfContext::getInstance()->getResponse()->setTitle(strtoupper($module).' - '.$this->societe->raison_sociale);
      return $this->societe;
    }

    protected function doConvertObjectToArray($object = null) {

        return array("identifiant" => $object->getIdentifiant());
    }

    public function getSociete() {
      if (!$this->societe) {
           $this->societe = $this->getObject();
      }
      return $this->societe;
    }

    public function getEtablissement() {
        
        return $this->getSociete()->getEtablissementPrincipal();
    }
}
