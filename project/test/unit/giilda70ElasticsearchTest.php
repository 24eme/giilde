<?php
require_once(dirname(__FILE__).'/../bootstrap/common.php');
sfContext::createInstance($configuration);


$index = acElasticaManager::getType('ETABLISSEMENT');
$elasticaQueryString = new acElasticaQueryQueryString();
$elasticaQueryString->setDefaultOperator('AND');
$elasticaQueryString->setQuery('*');

// Create the actual search object with some data.
$q = new acElasticaQuery();
$q->setQuery($elasticaQueryString);


$t = new lime_test(0);
try {
  $res = $index->search($q);
} catch (Exception $e) {
  $t->comment("Aucun test ne sera effectué, la base ElasticSearch n'existe pas ou n'est pas correctement configurée");
  $t->comment("Exception : \"".$e->getMessage()."\"");
  return;
}

$t = new lime_test(34);

$t->comment("La base ElasticSearch est accessible : les tests vont être executés");

$t->comment("Vérification de l'indexation des documents ETABLISSEMENT");

$maxEntities = 10000;

$q->setLimit($maxEntities);
$resultset = $index->search($q);
$nbEtbElasticsearch = count($resultset->getResults());

$t->ok(($nbEtbElasticsearch > 0), "On trouve des etablissements dans la base elasticsearch (".$nbEtbElasticsearch.")");

$etbByView = EtablissementAllView::getInstance()->findByInterpro("INTERPRO-declaration");

$t->is($nbEtbElasticsearch, intval($etbByView->total_rows), "Il y a le même nombre d'établissements dans la base elasticsearch (".$nbEtbElasticsearch.") et dans la base couchdb (".intval($etbByView->total_rows).")");

$t->comment("Vérification de l'indexation des documents COMPTE");

$index = acElasticaManager::getType('COMPTE');
$elasticaQueryString = new acElasticaQueryQueryString();
$elasticaQueryString->setDefaultOperator('AND');
$elasticaQueryString->setQuery('*');
$q = new acElasticaQuery();
$q->setQuery($elasticaQueryString);
$q->setLimit($maxEntities);
$resultset = $index->search($q);
$nbCompteElasticsearch = count($resultset->getResults());

$t->ok(($nbCompteElasticsearch > 0), "On trouve des comptes dans la base elasticsearch (".$nbCompteElasticsearch.")");

$compteByView = CompteAllView::getInstance()->findByInterproVIEW("INTERPRO-declaration");

$t->is($nbCompteElasticsearch, count($compteByView), "Il y a le même nombre de compte dans la base elasticsearch (".$nbCompteElasticsearch.") et dans la base couchdb (".count($compteByView).")");

$t->comment("Vérification de l'indexation des documents SOCIETE");

$index = acElasticaManager::getType('SOCIETE');
$elasticaQueryString = new acElasticaQueryQueryString();
$elasticaQueryString->setDefaultOperator('AND');
$elasticaQueryString->setQuery('*');
$q = new acElasticaQuery();
$q->setQuery($elasticaQueryString);
$q->setLimit($maxEntities);
$resultset = $index->search($q);
$nbSocieteElasticsearch = count($resultset->getResults());

$t->ok(($nbSocieteElasticsearch > 0), "On trouve des sociétés dans la base elasticsearch (".$nbSocieteElasticsearch.")");

$societeByView = SocieteAllView::getInstance()->findByInterpro("INTERPRO-declaration");

$t->is($nbSocieteElasticsearch, count($societeByView), "Il y a le même nombre de sociétés dans la base elasticsearch (".$nbSocieteElasticsearch.") et dans la base couchdb (".count($societeByView).")");

$typesDrm = array("SUSPENDU","ACQUITTE");
$typesDrmLibelles = array("Suspendu","Acquitté");

$currentP = ((DateTime::createFromFormat("Ymd",date("Ym")."01"))->modify("-1 month"))->format("Ym");

$aleaP = (DateTime::createFromFormat("Ymd",rand(2012, date("Y")-1).sprintf("%02d",rand(1,12))."01"))->format("Ym");
$periodes = array($aleaP,$currentP);

$t->comment("Vérification de l'indexation des documents DRM et DRMMVT");


$produit_hash = null;
foreach(ConfigurationClient::getInstance()->getCurrent()->getProduits() as $produit) {
    if($produit->getTauxCVO(date("Y-m-d")) > 0 && !$produit_hash) {
        $produit_hash = $produit->getHash();
    }
}


foreach ($periodes as $p) {
  $t->comment("PERIODE = ".$p);
  $index = acElasticaManager::getType('DRM');
  $elasticaQueryString = new acElasticaQueryQueryString();
  $elasticaQueryString->setDefaultOperator('AND');
  $elasticaQueryString->setQuery('doc.periode:"'.$p.'"');
  $q = new acElasticaQuery();
  $q->setQuery($elasticaQueryString);
  $q->setLimit($maxEntities);
  $resultset = $index->search($q);


  $campagne = ConfigurationClient::getInstance()->buildCampagne((DateTime::createFromFormat("Ymd",$p."01"))->format('Y-m'));
  $drmElkValidees = array();
  foreach ($resultset->getResults() as $key => $er) {
    $d = $er->getData();
    $drmElk = $d["doc"];
    if($drmElk["valide"]["date_saisie"]){
      $drmElkValidees[$drmElk['drmid']] = $drmElk['drmid'];
    }
  }
  ksort($drmElkValidees);

  $drmByView = DRMDerniereView::getInstance()->findByCampagneAndPeriode($campagne,$p);
  $sommeDebutMoisCouchdb = 0;
  $sommeFinMoisCouchdb = 0;
  $drmViewValidees = array();
  $drmMasters = array();
  foreach ($drmByView as $drmView) {
    $drmViewValidees[$drmView->id] = $drmView->id;
    $d = DRMClient::getInstance()->find($drmView->id);
    $d = $d->getMaster();
    foreach ($d->getProduitsDetails(true) as $produitKey => $produit) {
      if(strpos($produitKey,$produit_hash) !== false && !in_array($d->_id.$produitKey,$drmMasters)){
        $drmMasters[] = $d->_id.$produitKey;
        $sommeDebutMoisCouchdb += $produit->total_debut_mois;
        $sommeFinMoisCouchdb += $produit->total;
      }
    }
  }

  $t->is(count($drmElkValidees), count($drmViewValidees), "(".$p.") Il y a le même nombre de DRM validées dans la base elasticsearch (".count($drmElkValidees).") et dans la base couchdb (".count($drmViewValidees).")");
  $t->is(count(array_diff($drmElkValidees,$drmViewValidees)),0,"(".$p.") Il y a les mêmes DRM validées dans elasticsearch et couchdb");

  $index = acElasticaManager::getType('DRMMVT');
  $elasticaQueryString = new acElasticaQueryQueryString();
  $elasticaQueryString->setDefaultOperator('AND');
  $elasticaQueryString->setQuery('doc.periode:"'.$p.'"');
  $q = new acElasticaQuery();
  $q->setQuery($elasticaQueryString);
  $q->setLimit($maxEntities);
  $resultset = $index->search($q);
  $drmMvtsElkStocks = array();
  $drmMvtsElk = array();

  $sommeDebutMoisElk = 0;
  $sommeFinMoisElk = 0;
  foreach ($resultset->getResults() as $key => $er) {
    $d = $er->getData();
    $drmMvt = $d["doc"];
    $mvtLocal = $drmMvt['mouvements'];
    if($mvtLocal["categorie"] == "stocks" && $drmMvt["valide"]["date_saisie"]){
      $drmMvtsElkStocks[$mvtLocal["id"]] = $mvtLocal;
      if(strpos($mvtLocal["produit_hash"],$produit_hash) !== false){
        if($mvtLocal["type_hash"] == "total_debut_mois"){
          $sommeDebutMoisElk += $mvtLocal["volume"];
        }
        if($mvtLocal["type_hash"] == "total"){
          $sommeFinMoisElk += $mvtLocal["volume"];
        }
      }
    }else{
      $drmMvtsElk[$mvtLocal["id"]] = $mvtLocal;
    }
  }

  $format_produit_hash = false;
  $format_appelation = false;
  $denomination_complementaire = false;
  $type_drm = false;
  $type_drm_libelle = false;

  foreach ($drmMvtsElkStocks as $key => $drmMvtElkStocks) {
    if(!isset($drmMvtElkStocks["produit_hash"]) || !preg_match("/\/cepages\/[0-9A-Za-z-]+\/details(ACQUITTE)?\/[0-9A-Za-z-]+$/",$drmMvtElkStocks["produit_hash"])){
      $format_produit_hash = $key;
    }
    if(!isset($drmMvtElkStocks["appellation"]) || !$drmMvtElkStocks["appellation"]){
      $format_appelation = $key;
    }
    if(!array_key_exists('denomination_complementaire',$drmMvtElkStocks)){
      $denomination_complementaire = $key;
    }
    if(!isset($drmMvtElkStocks["type_drm"]) || !in_array($drmMvtElkStocks["type_drm"],$typesDrm)){
      $type_drm = $key;
    }
    if(!isset($drmMvtElkStocks["type_drm_libelle"]) || !in_array($drmMvtElkStocks["type_drm_libelle"],$typesDrmLibelles)){
      $type_drm_libelle = $key;
    }
  }

  $t->is($format_produit_hash,false, "(".$p.") Dans les mouvements stocks de DRMMVT le format des hash produits sont corrects");
  $t->is($format_appelation,false, "(".$p.") Dans les mouvements stocks de DRMMVT le format des appellations sont corrects");
  $t->is($denomination_complementaire,false, "(".$p.") Dans les mouvements stocks de DRMMVT les dénominations complémentaires sont présentes");
  $t->is($type_drm,false, "(".$p.") Dans les mouvements stocks de DRMMVT les types de DRM ne sont pas valident");
  $t->is($type_drm_libelle,false, "(".$p.") Dans les mouvements stocks de DRMMVT les types libellés de DRM ne sont pas valident");

  $t->is(round($sommeDebutMoisCouchdb,2),round($sommeDebutMoisElk,2),"(".$p.") les stocks début de mois couchdb et elasticsearch sont similaires.");
  $t->is(round($sommeFinMoisCouchdb,2),round($sommeFinMoisElk,2),"(".$p.") les stocks fin de mois couchdb et elasticsearch sont similaires.");

  $format_produit_hash = false;
  $format_appelation = false;
  $denomination_complementaire = false;
  $type_drm = false;
  $type_drm_libelle = false;

  foreach ($drmMvtsElk as $key => $drmMvtElk) {
    if(!isset($drmMvtElk["produit_hash"]) || !preg_match("/\/cepages\/[0-9A-Za-z-]+\/details(ACQUITTE)?\/[0-9A-Za-z-]+/",$drmMvtElk["produit_hash"])){
      $format_produit_hash = $key;
    }
    if(!isset($drmMvtElk["appellation"]) || !$drmMvtElk["appellation"]){
      $format_appelation = $key;
    }
    if(!array_key_exists('denomination_complementaire',$drmMvtElk)){
      $denomination_complementaire = $key;
    }
    if(!isset($drmMvtElk["type_drm"]) || !in_array($drmMvtElk["type_drm"],$typesDrm)){
      $type_drm = $key;
    }
    if(!isset($drmMvtElk["type_drm_libelle"]) || !in_array($drmMvtElk["type_drm_libelle"],$typesDrmLibelles)){
      $type_drm_libelle = $key;
    }
  }

  $t->is($format_produit_hash,false, "(".$p.") Dans les mouvements proprietes de DRMMVT le format des hash produits sont corrects");
  $t->is($format_appelation,false, "(".$p.") Dans les mouvements proprietes de DRMMVT le format des appellations sont corrects");
  $t->is($denomination_complementaire,false, "(".$p.") Dans les mouvements proprietes de DRMMVT les dénominations complémentaires sont présentes");
  $t->is($type_drm,false, "(".$p.") Dans les mouvements proprietes de DRMMVT les types de DRM ne sont pas valident");
  $t->is($type_drm_libelle,false, "(".$p.") Dans les mouvements proprietes de DRMMVT les types libellés de DRM ne sont pas valident");
}
