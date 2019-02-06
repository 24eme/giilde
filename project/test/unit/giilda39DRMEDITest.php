<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(37);
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti_2')->getEtablissement();
$produits = array_keys(ConfigurationClient::getInstance()->getConfiguration(date('Y')."-01-01")->getProduits());
$produit1_hash = array_shift($produits);
$produit1 = ConfigurationClient::getInstance()->getConfiguration(date('Y')."-01-01")->get($produit1_hash);
$produit2_hash = array_shift($produits);
$produit2 = ConfigurationClient::getInstance()->getConfiguration(date('Y')."-01-01")->get($produit2_hash);


//Suppression des DRM précédentes
foreach(DRMClient::getInstance()->viewByIdentifiant($viti->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
  $csv = CSVDRMClient::getInstance()->find(str_replace("DRM", "CSVDRM", $k));
  $csv->delete(false);
}

$t->comment("Création d'une DRM via EDI ".$viti->identifiant);

$periode = (date('Y'))."01";
$tmpfname = tempnam("/tmp", "DRM_");
$temp = fopen($tmpfname, "w");
fwrite($temp, "\xef\xbb\xbfCAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,stocks_debut,initial,951.4625,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,entrees,retourmarchandisetaxees,1,201712,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,sorties,ventefrancecrd,4.62,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,sorties,export,1.89,PAYS-BAS,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,sorties,export,0.9525,BELGIQUE,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,stocks_fin,final,945,,,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille75cl,,,,,,,collectif suspendu,stock_debut,debut,14742,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille 75 cl,,,,,,,collectif suspendu,sorties,utilisations,3118,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille 75cl,,,,,,,collectif suspendu,stock_fin,fin,11624,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",BLEU,tranquille,Bouteille150cl,,,,,,,collectif suspendu,stock_debut,debut,56,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",BLEU,tranquille,Bouteille 150 cl,,,,,,,collectif suspendu,sorties,utilisations,3,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",BLEU,tranquille,Bouteille 150cl,,,,,,,collectif suspendu,stock_fin,fin,53,,,,\n");
fclose($temp);

$drm = DRMClient::getInstance()->createDoc($viti->identifiant, $periode);
$import = new DRMImportCsvEdi($tmpfname, $drm);
$t->ok($import->checkCSV(), "Vérification de l'import");
if ($import->getCsvDoc()->hasErreurs()) {
  foreach ($import->getCsvDoc()->erreurs as $k => $err) {
    $t->ok(false, $err->diagnostic);
  }
}

$import->importCSV();

$t->is($drm->getProduit($produit1_hash, 'details')->get('stocks_debut/initial'), 951.4625, "le stock initial est celui attendu");
$t->is($drm->getProduit($produit1_hash, 'details')->get('sorties/ventefrancecrd'),4.62,"vente frande crd OK");
$t->is($drm->getProduit($produit1_hash, 'details')->get('sorties/export'),2.8425,"sortie export OK");
$t->is($drm->getProduit($produit1_hash, 'details')->get('stocks_fin/final'),945,"stock final OK");
$t->is($drm->getProduit($produit1_hash, 'details')->get('replacement_date'), "31/12/2017","Date de replacement OK");
if(DRMConfiguration::getInstance()->isObservationsAuto()) {
$t->is($drm->getProduit($produit1_hash, 'details')->get('observations'), ConfigurationClient::getInstance()->getConfiguration(date('Y')."-01-01")->libelle_detail_ligne->details->entrees->retourmarchandisetaxees->libelle_long, "Observations OK");
} else {
$t->is($drm->getProduit($produit1_hash, 'details')->get('observations'), "", "Observations OK");
}

$t->ok($drm->crds->exist('COLLECTIFSUSPENDU'), "CRD : noeud COLLECTIFSUSPENDU reconnu");
$t->is(count($drm->crds->COLLECTIFSUSPENDU), 2, "CRD possède deux centilisations");

$t->is($drm->crds->COLLECTIFSUSPENDU->get('TRANQ-VERT-750')->genre, "TRANQ", "Genre 75 cl OK");
$t->is($drm->crds->COLLECTIFSUSPENDU->get('TRANQ-VERT-750')->couleur, "VERT", "Couleur 75 cl OK");
$t->is($drm->crds->COLLECTIFSUSPENDU->get('TRANQ-VERT-750')->detail_libelle, "Bouteille 75 cl", "Libellé contenant OK");
$t->is($drm->crds->COLLECTIFSUSPENDU->get('TRANQ-VERT-750')->stock_debut, 14742, "stock debut 75 cl OK");
$t->is($drm->crds->COLLECTIFSUSPENDU->get('TRANQ-VERT-750')->sorties_utilisations, 3118, "utilisation 75 cl OK");
$t->is($drm->crds->COLLECTIFSUSPENDU->get('TRANQ-VERT-750')->stock_fin, 11624, "stock fin 75 cl OK");
$t->is($drm->crds->COLLECTIFSUSPENDU->get('TRANQ-BLEU-1500')->stock_fin, 53, "stock fin 150 cl OK");
unlink($tmpfname);
$drm->delete();

$periode = (date('Y'))."01";
$tmpfname = tempnam("/tmp", "DRM_");
$t->comment("Création d'une DRM avec des produits qu'en libellé via EDI ".$viti->identifiant);
$temp = fopen($tmpfname, "w");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getLibelleFormat().",suspendu,stocks_debut,initial,951.4625,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getLibelleFormat().",suspendu,sorties,ventefrancecrd,4.62,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getLibelleFormat().",suspendu,sorties,export,1.89,PAYS-BAS,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getLibelleFormat().",suspendu,sorties,export,0.9525,BELGIQUE,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getLibelleFormat().",suspendu,stocks_fin,final,944,,,,,,\n");
fclose($temp);
$drm = DRMClient::getInstance()->createDoc($viti->identifiant, $periode);
$import = new DRMImportCsvEdi($tmpfname, $drm);
$t->ok($import->checkCSV(), "Vérification de l'import");
if ($import->getCsvDoc()->hasErreurs()) {
  foreach ($import->getCsvDoc()->erreurs as $k => $err) {
    $t->ok(false, $err->diagnostic);
  }
}

$import->importCSV();

$t->is($drm->getProduit($produit1_hash, 'details')->get('stocks_debut/initial'), 951.4625, "le stock initial est celui attendu");
$drm->delete();
unlink($tmpfname);

$periode = (date('Y'))."01";

$tmpfname = tempnam("/tmp", "DRM_");
$t->comment("Création d'une DRM avec des produits avec code douane via EDI ".$viti->identifiant);
$temp = fopen($tmpfname, "w");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,stocks_debut,initial,951.4625,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,entrees,retourmarchandisetaxees,1,2017-12-20,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,sorties,ventefrancecrd,4.62,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,sorties,export,1.89,PAYS-BAS,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,sorties,export,1.9525,BELGIQUE,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,stocks_fin,final,944,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit2->getCodeDouane().",,,,,,,,,suspendu,stocks_debut,initial,951.4625,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit2->getCodeDouane().",,,,,,,,,suspendu,sorties,ventefrancecrd,4.62,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit2->getCodeDouane().",,,,,,,,,suspendu,sorties,export,1.89,PAYS-BAS,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit2->getCodeDouane().",,,,,,,,,suspendu,sorties,export,0.9525,BELGIQUE,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit2->getCodeDouane().",,,,,,,,,suspendu,stocks_fin,final,944,,,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille75cl,,,,,,,collectif suspendu,stock_debut,debut,14742,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille 75 cl,,,,,,,collectif suspendu,sorties,utilisations,3118,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille 75cl,,,,,,,collectif suspendu,stock_fin,fin,11624,,,,\n");
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

$import->importCSV();

$t->is($drm2->getProduit($produit1_hash, 'details')->get('stocks_fin/final'), 944, "le stock find est celui attendu");
$t->is($drm2->getProduit($produit1_hash, 'details')->get('entrees/retourmarchandisetaxees'), 1, "retour a le bon volume");
$t->is($drm2->getProduit($produit1_hash, 'details')->replacement_date, '20/12/2017', "Date de replacement conservée");

$t->is($drm2->getProduit($produit2_hash, 'details')->get('stocks_debut/initial'), 951.4625, "le stock initial est celui attendu");
$t->is($drm2->getProduit($produit2_hash, 'details')->get('stocks_fin/final'), 944, "le stock find est celui attendu");

$t->is($drm2->crds->COLLECTIFSUSPENDU->get('TRANQ-VERT-750')->stock_fin, 11624, "stock debut 75 cl OK");
$drm2->validate();
unlink($tmpfname);

$periode = (date('Y'))."02";
$tmpfname = tempnam("/tmp", "DRM_");
$t->comment("Dépendance des stocks de produits et CRD ".$viti->identifiant);
$temp = fopen($tmpfname, "w");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,stocks_debut,initial,945,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,sorties,ventefrancecrd,4,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,stocks_fin,final,941,,,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",Lie de vin,tranquille,Bouteille75cl,,,,,,,collectif suspendu,stock_debut,debut,0,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",Lie de vin,tranquille,Bouteille75cl,,,,,,,collectif suspendu,entrees,achats,11625,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",Lie de vin,tranquille,Bouteille 75 cl,,,,,,,collectif suspendu,sorties,utilisations,24,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",Lie de vin,tranquille,Bouteille 75cl,,,,,,,collectif suspendu,stock_fin,fin,11601,,,,\n");
fclose($temp);

$drm3 = DRMClient::getInstance()->createDoc($viti->identifiant, $periode);
$import = new DRMImportCsvEdi($tmpfname, $drm3);
$t->ok($import->checkCSV(), "Vérification de l'import");
if ($import->getCsvDoc()->hasErreurs()) {
  foreach ($import->getCsvDoc()->erreurs as $k => $err) {
    $t->ok(false, $err->diagnostic." : ".$err->csv_erreur);
  }
}

$import->importCSV();

$drm3->save();

$t->is($drm3->getProduit($produit1_hash, 'details')->get('sorties/ventefrancecrd'), 4, "Sortie ok");
$t->is($drm3->crds->COLLECTIFSUSPENDU->get('TRANQ-LIEDEVIN-750')->sorties_utilisations, 24, "Utilisation CRD 75 cl OK");

$t->is($drm3->getProduit($produit1_hash, 'details')->get('stocks_debut/initial'), 944, "le stock initial est celui attendu");
$t->is($drm3->crds->COLLECTIFSUSPENDU->get('TRANQ-LIEDEVIN-750')->stock_debut, 0, "stock debut 75 cl OK");

$t->is($drm3->getProduit($produit1_hash, 'details')->get('stocks_fin/final'), 940, "le stock final est celui attendu");
$t->is($drm3->crds->COLLECTIFSUSPENDU->get('TRANQ-LIEDEVIN-750')->stock_fin, 11601, "stock fin 75 cl OK");

$drm3->delete();

$t->comment("Conformité aux catalogues ou  ".$viti->identifiant);

$produit_disabled = null;

foreach($produits as $ph) {
  $p = ConfigurationClient::getInstance()->getConfiguration(date('Y')."-01-01")->get($ph);
  if (!$p->isActif(date('Y')."-01-01")) {
    $produit_disabled = $p;
    break;
  }
}
$temp = fopen($tmpfname, "w");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit_disabled->getCertification()->getLibelle().",".$produit_disabled->getGenre()->getLibelle().",".$produit_disabled->getAppellation()->getLibelle().",".$produit_disabled->getMention()->getLibelle().",".$produit_disabled->getLieu()->getLibelle().",".$produit_disabled->getCouleur()->getLibelle().",".$produit_disabled->getCepage()->getLibelle().",,".$produit_disabled->getLibelleFormat().",suspendu,stocks_debut,initial,951.4625,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit_disabled->getCertification()->getLibelle().",".$produit_disabled->getGenre()->getLibelle().",".$produit_disabled->getAppellation()->getLibelle().",".$produit_disabled->getMention()->getLibelle().",".$produit_disabled->getLieu()->getLibelle().",".$produit_disabled->getCouleur()->getLibelle().",".$produit_disabled->getCepage()->getLibelle().",,".$produit_disabled->getLibelleFormat().",suspendu,entrees,retourmarchandisetaxees,1,201712,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit_disabled->getCertification()->getLibelle().",".$produit_disabled->getGenre()->getLibelle().",".$produit_disabled->getAppellation()->getLibelle().",".$produit_disabled->getMention()->getLibelle().",".$produit_disabled->getLieu()->getLibelle().",".$produit_disabled->getCouleur()->getLibelle().",".$produit_disabled->getCepage()->getLibelle().",,".$produit_disabled->getLibelleFormat().",suspendu,sorties,ventefrancecrd,4.62,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit_disabled->getCertification()->getLibelle().",".$produit_disabled->getGenre()->getLibelle().",".$produit_disabled->getAppellation()->getLibelle().",".$produit_disabled->getMention()->getLibelle().",".$produit_disabled->getLieu()->getLibelle().",".$produit_disabled->getCouleur()->getLibelle().",".$produit_disabled->getCepage()->getLibelle().",,".$produit_disabled->getLibelleFormat().",suspendu,sorties,export,1.89,PAYS-BAS,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit_disabled->getCertification()->getLibelle().",".$produit_disabled->getGenre()->getLibelle().",".$produit_disabled->getAppellation()->getLibelle().",".$produit_disabled->getMention()->getLibelle().",".$produit_disabled->getLieu()->getLibelle().",".$produit_disabled->getCouleur()->getLibelle().",".$produit_disabled->getCepage()->getLibelle().",,".$produit_disabled->getLibelleFormat().",suspendu,sorties,export,0.9525,BELGIQUE,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit_disabled->getCertification()->getLibelle().",".$produit_disabled->getGenre()->getLibelle().",".$produit_disabled->getAppellation()->getLibelle().",".$produit_disabled->getMention()->getLibelle().",".$produit_disabled->getLieu()->getLibelle().",".$produit_disabled->getCouleur()->getLibelle().",".$produit_disabled->getCepage()->getLibelle().",,".$produit_disabled->getLibelleFormat().",suspendu,stocks_fin,final,945,,,,,,\n");
fclose($temp);

$drm4 = DRMClient::getInstance()->createDoc($viti->identifiant, $periode);
$import = new DRMImportCsvEdi($tmpfname, $drm4);
$t->ok(!$produit_disabled || !$import->checkCSV(), "Un produit non actif ne doit pas être permis");
unlink($tmpfname);

$temp = fopen($tmpfname, "w");
$periode5 = date('Y')."02";
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,stocks_debut,initial,944,,,,,,\n");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,stocks_fin,final,944,,,,,,\n");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",".$produit2->getCodeDouane().",,,,,,,,,suspendu,stocks_debut,initial,944,,,,,,\n");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",".$produit2->getCodeDouane().",,,,,,,,,suspendu,stocks_fin,final,944,,,,,,\n");
fwrite($temp, "CRD,$periode5,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille75cl,,,,,,,collectif acquitte,stock_debut,debut,11624,,,,\n");
fwrite($temp, "CRD,$periode5,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille 75cl,,,,,,,collectif acquitte,stock_fin,fin,11624,,,,\n");
fclose($temp);
$drm5 = DRMClient::getInstance()->createDoc($viti->identifiant, $periode5);
$import = new DRMImportCsvEdi($tmpfname, $drm5);
$t->ok(!$import->checkCSV(), "On ne peut pas changer une CRD avec du stock");
foreach($import->getCsvDoc()->erreurs as $k => $err) {
  break;
}
$t->is($err->num_ligne, 5, "L'erreur de CRD pointe la bonne ligne");

$temp = fopen($tmpfname, "w");
$periode5 = date('Y')."02";
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,stocks_debut,initial,944,,,,,,\n");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,stocks_fin,final,944,,,,,,\n");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",".$produit2->getCodeDouane().",,,,,,,,,suspendu,stocks_debut,initial,944,,,,,,\n");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",".$produit2->getCodeDouane().",,,,,,,,,suspendu,stocks_fin,final,944,,,,,,\n");
fwrite($temp, "CRD,$periode5,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille75cl,,,,,,,collectif suspendu,stock_debut,debut,0,,,,\n");
fwrite($temp, "CRD,$periode5,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille75cl,,,,,,,collectif suspendu,entrees,achats,11624,,,,\n");
fwrite($temp, "CRD,$periode5,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille 75cl,,,,,,,collectif suspendu,stock_fin,fin,11624,,,,\n");
fclose($temp);
$drm6 = DRMClient::getInstance()->createDoc($viti->identifiant, $periode5);
$import = new DRMImportCsvEdi($tmpfname, $drm6);
$t->ok(!$import->checkCSV(), "On ne peut pas changer le stock CRD déclaré lors de la DRM précédente");
foreach($import->getCsvDoc()->erreurs as $k => $err) {
  break;
}
$t->is($err->num_ligne, 5, "L'erreur de CRD pointe la bonne ligne");

$drm2->devalide();
$drm2->delete();
unlink($tmpfname);
