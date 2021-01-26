<?php
class generationActions extends sfActions {
  private function getGenerationFromRequest(sfWebRequest $request) {
      $this->type = $request['type_document'];
      $this->identifiant = isset($request['identifiant'])? $request['identifiant'] : null;
      $this->nom = ($this->identifiant)? EtablissementClient::getInstance()->retrieveById($this->identifiant)->nom : null;
      $this->date_emission = $request['date_emission'];
      $this->generation = GenerationClient::getInstance()->find(GenerationClient::getInstance()->getId($this->type, $this->date_emission));
      $title = ($this->generation)? 'GENERATION - '.$this->generation->identifiant : 'GENERATION';
      sfContext::getInstance()->getResponse()->setTitle($title);
      $this->forward404Unless($this->generation);

      return $this->generation;
  }

  public function executeView(sfWebRequest $request) {
      $this->generation = $this->getGenerationFromRequest($request);

      $this->type_generation = $this->generation->type_document;
      $this->sous_generations_conf = [];

      if ($this->generation->statut === GenerationClient::GENERATION_STATUT_GENERE &&
          GenerationConfiguration::getInstance()->hasSousGeneration($this->type_generation))
      {
          $this->sous_generations_conf = GenerationConfiguration::getInstance()->getSousGeneration($this->type_generation);
      }
      $this->sous_generations = $this->generation->getSubGenerations();
  }

  public function executeList(sfWebRequest $request) {
      $this->type = $request['type_document'];
      $this->limit = $request->getParameter("limite", 100);
      $this->historyGeneration = GenerationClient::getInstance()->findHistoryWithType($this->type, $this->limit);
  }

  public function executeRegenerate(sfWebRequest $request) {
      $generation = $this->getGenerationFromRequest($request);
      $generation->regenerate();
      $generation->save();

      return $this->redirect('generation_view', array('type_document' => $generation->type_document, 'date_emission' => $generation->date_emission));
  }

  public function executeReload(sfWebRequest $request) {
      $generation = $this->getGenerationFromRequest($request);
      $generation->reload();
      $generation->save();

      return $this->redirect('generation_view', array('type_document' => $generation->type_document, 'date_emission' => $generation->date_emission));
  }

  public function executeDelete(sfWebRequest $request) {
      $this->generation = $this->getGenerationFromRequest($request);
      if($this->generation->statut != GenerationClient::GENERATION_STATUT_GENERE) {

      throw new sfException("La génération n'est pas supprimable car elle n'est pas finie");
      }
      if ($request->isMethod(sfWebRequest::POST)) {
          if ($request->getParameter('delete')) {
              $this->generation->delete();
          }
          return $this->redirect('generation_list', array('type_document' => $this->type));
      }
  }

}
