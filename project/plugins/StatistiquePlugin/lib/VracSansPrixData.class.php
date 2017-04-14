<?php

class VracSansPrixData {
    public function __construct($date_debut, $date_fin = '') {
      $this->date_debut = $date_debut;
      $this->date_fin = $date_fin;
      $this->date_fin_effective = $date_fin;
      if (!$date_fin) {
        $this->date_fin_effective = date("Y-m-d");
      }
      $this->index = acElasticaManager::getType('VRAC');
    }

    public function query() {
      $elasticaQuery = new acElasticaQuery();
      $query = array("query"=> array("bool"=> array("must" => array(
        array("term" => array("doc.prix_initial_unitaire_hl" =>  "0")),
        array("range" => array("doc.date_signature" => array("gte" => $this->date_debut, "lte" => $this->date_fin_effective))),
        array('term' => array("doc.acheteur.region" => "REGION_CVO"))
      ))),
      "sort" => array(array("doc.acheteur_identifiant"=>array("order"=>"asc"), "doc.date_signature"=>array("order"=>"asc"), "doc.vendeur_identifiant"=>array("order"=>"asc"),))
      );

      $elasticaQuery->setParams($query);
      $elasticaQuery->setSize(10000);
      $results = $this->index->search($elasticaQuery);
      return $results->getResults();
    }

    public function getCSVs() {
      $prix_vendeurs = array();
      foreach($this->query() as $q) {
        $d = $q->getData();
        $prix_vendeurs[$d['doc']['acheteur_identifiant']][] = array($d['doc']['acheteur_identifiant'],
                                                              $d['doc']['acheteur']['raison_sociale'],
                                                              $d['doc']['acheteur']['adresse'],
                                                              $d['doc']['acheteur']['code_postal'],
                                                              $d['doc']['acheteur']['commune'],
                                                              preg_replace('/T.*/', '', $d['doc']['date_signature']),
                                                              $d['doc']['vendeur']['raison_sociale'],
                                                              $d['doc']['produit_libelle'],
                                                              $d['doc']['volume_propose']);
      }
      return $prix_vendeurs;
    }

    public function getPdfObjects() {
      $pdfs = array();
      foreach($this->getCSVs() as $k => $csv) {
        $pdfs[$k] = new VracSansPrixLatex($csv, array('date_debut'=> $this->date_debut, 'date_fin'=> $this->date_fin));
      }
      return $pdfs;
    }

}
