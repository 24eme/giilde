<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(21);
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti_2')->getEtablissement();
$produits = array_keys(ConfigurationClient::getInstance()->getCurrent()->getProduits());
$produit1_hash = array_shift($produits);
$produit1 = ConfigurationClient::getInstance()->getCurrent()->get($produit1_hash);
$produit2_hash = array_shift($produits);
$produit2 = ConfigurationClient::getInstance()->getCurrent()->get($produit2_hash);


//Suppression des DRM précédentes
foreach(DRMClient::getInstance()->viewByIdentifiant($viti->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
  $csv = CSVDRMClient::getInstance()->find(str_replace("DRM", "CSVDRM", $k));
  $csv->delete(false);
}

$t->comment("Création d'une DRM via EDI ".$viti->identifiant);

$tmpfname = tempnam("/tmp", "DRM_");
$temp = fopen($tmpfname, "w");
fwrite($temp, "CAVE,201801,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,stocks_debut,initial,951.4625,,,,,,\n");
fwrite($temp, "CAVE,201801,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,sorties,ventefrancecrd,4.62,,,,,,\n");
fwrite($temp, "CAVE,201801,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,sorties,export,1.89,PAYS-BAS,,,,,\n");
fwrite($temp, "CAVE,201801,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,sorties,export,0.9525,BELGIQUE,,,,,\n");
fwrite($temp, "CAVE,201801,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,stocks_fin,final,944,,,,,,\n");
fwrite($temp, "CRD,201801,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille75cl,,,,,,,collectif suspendu,stock_debut,debut,14742,,,,\n");
fwrite($temp, "CRD,201801,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille 75 cl,,,,,,,collectif suspendu,sorties,utilisations,3118,,,,\n");
fwrite($temp, "CRD,201801,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille 75cl,,,,,,,collectif suspendu,stock_fin,fin,11624,,,,\n");
fwrite($temp, "CRD,201801,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille150cl,,,,,,,collectif suspendu,stock_debut,debut,56,,,,\n");
fwrite($temp, "CRD,201801,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille 150 cl,,,,,,,collectif suspendu,sorties,utilisations,3,,,,\n");
fwrite($temp, "CRD,201801,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille 150cl,,,,,,,collectif suspendu,stock_fin,fin,53,,,,\n");
fclose($temp);

$periode = (date('Y'))."01";
$drm = DRMClient::getInstance()->createDoc($viti->identifiant, $periode);
$import = new DRMImportCsvEdi($tmpfname, $drm);
$t->ok($import->checkCSV(), "Vérification de l'import");
if ($import->getCsvDoc()->hasErreurs()) {
  foreach ($import->getCsvDoc()->erreurs as $k => $err) {
    $t->ok(false, $err->diagnostic);
  }
}
$t->ok($import->importCSV(),"Import de la DRM");

$t->is($drm->getProduit($produit1_hash, 'details')->get('stocks_debut/initial'), 951.4625, "le stock initial est celui attendu");
$t->is($drm->getProduit($produit1_hash, 'details')->get('sorties/ventefrancecrd'),4.62,"vente frande crd OK");
$t->is($drm->getProduit($produit1_hash, 'details')->get('sorties/export'),2.8425,"sortie export OK");
$t->is($drm->getProduit($produit1_hash, 'details')->get('stocks_fin/final'),944,"stock final OK");

$t->ok($drm->crds->exist('COLLECTIFSUSPENDU'), "CRD : noeud COLLECTIFSUSPENDU reconnu");
$t->is(count($drm->crds->COLLECTIFSUSPENDU), 2, "CRD possède deux centilisations");

$t->is($drm->crds->COLLECTIFSUSPENDU->get('TRANQ-VERT-750')->genre, "TRANQ", "Genre 75 cl OK");
$t->is($drm->crds->COLLECTIFSUSPENDU->get('TRANQ-VERT-750')->couleur, "VERT", "Couleur 75 cl OK");
$t->is($drm->crds->COLLECTIFSUSPENDU->get('TRANQ-VERT-750')->detail_libelle, "Bouteille 75 cl", "Libellé contenant OK");
$t->is($drm->crds->COLLECTIFSUSPENDU->get('TRANQ-VERT-750')->stock_debut, 14742, "stock debut 75 cl OK");
$t->is($drm->crds->COLLECTIFSUSPENDU->get('TRANQ-VERT-750')->sorties_utilisations, 3118, "utilisation 75 cl OK");
$t->is($drm->crds->COLLECTIFSUSPENDU->get('TRANQ-VERT-750')->stock_fin, 11624, "stock fin 75 cl OK");
$t->is($drm->crds->COLLECTIFSUSPENDU->get('TRANQ-VERT-1500')->stock_fin, 53, "stock fin 150 cl OK");
$drm->delete();
unlink($tmpfname);

$tmpfname = tempnam("/tmp", "DRM_");
$t->comment("Création d'une DRM avec des produits qu'en libellé via EDI ".$viti->identifiant);
$temp = fopen($tmpfname, "w");
fwrite($temp, "CAVE,201801,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getLibelleFormat().",suspendu,stocks_debut,initial,951.4625,,,,,,\n");
fwrite($temp, "CAVE,201801,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getLibelleFormat().",suspendu,sorties,ventefrancecrd,4.62,,,,,,\n");
fwrite($temp, "CAVE,201801,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getLibelleFormat().",suspendu,sorties,export,1.89,PAYS-BAS,,,,,\n");
fwrite($temp, "CAVE,201801,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getLibelleFormat().",suspendu,sorties,export,0.9525,BELGIQUE,,,,,\n");
fwrite($temp, "CAVE,201801,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getLibelleFormat().",suspendu,stocks_fin,final,944,,,,,,\n");
fclose($temp);
$periode = (date('Y'))."01";
$drm = DRMClient::getInstance()->createDoc($viti->identifiant, $periode);
$import = new DRMImportCsvEdi($tmpfname, $drm);
$t->ok($import->checkCSV(), "Vérification de l'import");
if ($import->getCsvDoc()->hasErreurs()) {
  foreach ($import->getCsvDoc()->erreurs as $k => $err) {
    $t->ok(false, $err->diagnostic);
  }
}
$t->ok($import->importCSV(),"Import de la DRM");
$t->is($drm->getProduit($produit1_hash, 'details')->get('stocks_debut/initial'), 951.4625, "le stock initial est celui attendu");
$drm->delete();
unlink($tmpfname);

$tmpfname = tempnam("/tmp", "DRM_");
$t->comment("Création d'une DRM avec des produits avec code douane via EDI ".$viti->identifiant);
$temp = fopen($tmpfname, "w");
fwrite($temp, "CAVE,201801,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,stocks_debut,initial,951.4625,,,,,,\n");
fwrite($temp, "CAVE,201801,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,sorties,ventefrancecrd,4.62,,,,,,\n");
fwrite($temp, "CAVE,201801,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,sorties,export,1.89,PAYS-BAS,,,,,\n");
fwrite($temp, "CAVE,201801,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,sorties,export,0.9525,BELGIQUE,,,,,\n");
fwrite($temp, "CAVE,201801,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,stocks_fin,final,944,,,,,,\n");
fclose($temp);
$periode = (date('Y'))."01";
$drm2 = DRMClient::getInstance()->createDoc($viti->identifiant, $periode);
$import = new DRMImportCsvEdi($tmpfname, $drm2);
$t->ok($import->checkCSV(), "Vérification de l'import");
if ($import->getCsvDoc()->hasErreurs()) {
  foreach ($import->getCsvDoc()->erreurs as $k => $err) {
    $t->ok(false, $err->diagnostic);
  }
}
$t->ok($import->importCSV(),"Import de la DRM");
$t->is($drm2->getProduit($produit1_hash, 'details')->get('stocks_debut/initial'), 951.4625, "le stock initial est celui attendu");
unlink($tmpfname);
