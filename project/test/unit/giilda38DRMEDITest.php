<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$nb_tests = 67;
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti_2')->getEtablissement();
$nego =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_region')->getEtablissement();
$produits = ConfigurationClient::getInstance()->getConfiguration(date('Y')."-01-01")->getProduits();

$produit_disabled = null;
foreach($produits as $produit) {
    if(!$produit->code_douane) {
        continue;
    }
    if(!$produit->isActif(date('Y')."-01-01")) {
        $produit_disabled = $produit;
        continue;
    }
    if(!isset($produit1)) {
        $produit1_hash = $produit->getHash();
        $produit1 = $produit;
        continue;
    }
    if(!isset($produit2)) {
        $produit2_hash = $produit->getHash();
        $produit2 = $produit;
        continue;
    }
    if(!isset($produitAlcool) && $produit->needTav()) {
        $produitAlcool_hash = $produit->getHash();
        $produitAlcool = $produit;
        continue;
    }
}

$produitdefault_tranq_hash = DRMConfiguration::getInstance()->getEdiDefaultProduitHash("1B455S");
if ($produitdefault_tranq_hash) {
    $nb_tests += 5;
}
$produitdefault_mou_hash = DRMConfiguration::getInstance()->getEdiDefaultProduitHash("1S437M 1");
if ($produitdefault_mou_hash) {
    $nb_tests += 5;
}
$produitdefault_autre_hash = DRMConfiguration::getInstance()->getEdiDefaultProduitHash("SPIRITUEUX_GUADELOUPE_SUP_18");
if ($produitdefault_autre_hash) {
    $nb_tests += 5;
}

if($application == "ivso") {
    $nb_tests += 1;
}
if($application == "bivc") {
    $nb_tests += 8;
}

//Suppression des DRM précédentes
foreach(DRMClient::getInstance()->viewByIdentifiant($viti->identifiant) as $k => $v) {
    $drm = DRMClient::getInstance()->find($k);
    $drm->delete(false);
    $csv = CSVDRMClient::getInstance()->find(str_replace("DRM", "CSVDRM", $k));
    $csv->delete(false);
}

$t = new lime_test($nb_tests);
$t->comment("Création d'une DRM via EDI avec aussi un produit non interpro ".$viti->identifiant);

$periode = (date('Y'))."01";
$tmpfname = tempnam("/tmp", "DRM_");
$temp = fopen($tmpfname, "w");
fwrite($temp, "\xef\xbb\xbfCAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,stocks_debut,initial,951.4625,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,entrees,retourmarchandisetaxees,1,201712,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,sorties,ventefrancecrd,4.62,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,sorties,export,1.89,PAYS-BAS,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,sorties,export,0.9525,BELGIQUE,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,stocks_fin,final,945,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,Vin de Savoie Ripaille (1B455S),suspendu,stocks_debut,initial,0,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,Vin de Savoie Ripaille (1B455S),suspendu,entrees,recolte,4,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,Vin de Savoie Ripaille (1B455S),suspendu,sorties,ventefrancecrd,4,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,Vin de Savoie Ripaille (1B455S),suspendu,stocks_fin,final,0,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,Vin de Savoie mousseux rosé (1S437M 1),suspendu,stocks_debut,initial,0,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,Vin de Savoie mousseux rosé (1S437M 1),suspendu,entrees,recolte,4,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,Vin de Savoie mousseux rosé (1S437M 1),suspendu,sorties,ventefrancecrd,4,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,Vin de Savoie mousseux rosé (1S437M 1),suspendu,stocks_fin,final,0,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,Autre vin de Savoie (SPIRITUEUX_GUADELOUPE_SUP_18),suspendu,stocks_debut,initial,0,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,Autre vin de Savoie (SPIRITUEUX_GUADELOUPE_SUP_18),suspendu,entrees,recolte,4,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,Autre vin de Savoie (SPIRITUEUX_GUADELOUPE_SUP_18),suspendu,sorties,ventefrancecrd,4,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,Autre vin de Savoie (SPIRITUEUX_GUADELOUPE_SUP_18),suspendu,stocks_fin,final,0,,,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille75cl,,,,,,,collectif suspendu,stock_debut,debut,14742,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille 75 cl,,,,,,,collectif suspendu,sorties,utilisations,3118,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille 75cl,,,,,,,collectif suspendu,stock_fin,fin,11624,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",BLEU,tranquille,Bouteille150cl,,,,,,,collectif suspendu,stock_debut,debut,56,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",BLEU,tranquille,Bouteille 150 cl,,,,,,,collectif suspendu,sorties,utilisations,3,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",BLEU,tranquille,Bouteille 150cl,,,,,,,collectif suspendu,stock_fin,fin,53,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",,PI,Bouteille75cl,,,,,,,collectif suspendu,stock_debut,debut,100,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",,PI,Bouteille 75cl,,,,,,,collectif suspendu,stock_fin,fin,100,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",,Alcools,Bouteille75cl,,,,,,,collectif suspendu,stock_debut,debut,100,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",,Alcools,Bouteille 75cl,,,,,,,collectif suspendu,stock_fin,fin,100,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",,COGNAC-ARMAGNAC,Bouteille75cl,,,,,,,collectif suspendu,stock_debut,debut,100,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",,COGNAC-ARMAGNAC,Bouteille 75cl,,,,,,,collectif suspendu,stock_fin,fin,100,,,,\n");
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
$t->is(count($drm->getProduitsDetails()), 4, "La DRM a bien 4 produits");
$t->is($drm->getProduit($produit1_hash, 'details')->get('stocks_debut/initial'), 951.4625, "le stock initial est celui attendu");
$t->is($drm->getProduit($produit1_hash, 'details')->get('sorties/ventefrancecrd'),4.62,"vente frande crd OK");
$t->is($drm->getProduit($produit1_hash, 'details')->get('sorties/export'),2.8425,"sortie export OK");

$t->is($drm->getProduit($produit1_hash, 'details')->get('stocks_fin/final'),945,"stock final OK");
if($drm->getProduit($produit1_hash, 'details')->exist('entrees/retourmarchandisetaxees_details')) {
    $t->is($drm->getProduit($produit1_hash, 'details')->get('entrees/retourmarchandisetaxees_details')->getFirst()->getDateFr(), "31/12/2017","Date de replacement OK");
} else {
    $t->is($drm->getProduit($produit1_hash, 'details')->get('replacement_date'), "31/12/2017","Date de replacement OK");
}
if(DRMConfiguration::getInstance()->isObservationsAuto()) {
    $t->is($drm->getProduit($produit1_hash, 'details')->get('observations'), ConfigurationClient::getInstance()->getConfiguration(date('Y')."-01-01")->libelle_detail_ligne->details->entrees->retourmarchandisetaxees->libelle_long, "Observations OK");
} else {
    $t->is($drm->getProduit($produit1_hash, 'details')->get('observations'), "", "Observations OK");
}
#tests de produit hors interpro
if ($produitdefault_tranq_hash) {
    $t->is(count($drm->get($produitdefault_tranq_hash)->details), 1, "Le produit tranquille hors intepro est bien reconnu comme produit défaut distinct");
    foreach($drm->get($produitdefault_tranq_hash)->details as $detail1) {
        break;
    }
    $t->ok($detail1->isDefaultProduit(), "Produit tranquille hors-interpro est bien détecté");
    $t->is($detail1->produit_libelle, "Vin de Savoie Ripaille", "Produit hors-interpro : libellé douanier repris du CSV");
    $t->is($detail1->getLibelle(), "Vin de Savoie Ripaille (Hors Interpro)", "Produit hors-interpro : libellé douanier repris du CSV");
    $t->is($detail1->code_inao, "1B455S", "Produit tranquille hors-interpro : code inao repris du CSV");
}
if ($produitdefault_mou_hash) {
    $t->is(count($drm->get($produitdefault_mou_hash)->details), 1, "Le produit mousseux hors intepro est bien reconnu comme produit défaut distinct");
    foreach($drm->get($produitdefault_mou_hash)->details as $detail2) {
        break;
    }
    $t->ok($detail2->isDefaultProduit(), "Produit mousseux hors-interpro est bien détecté");
    $t->is($detail2->produit_libelle, "Vin de Savoie mousseux rosé", "Produit hors-interpro : libellé douanier repris du CSV");
    $t->is($detail2->getLibelle(), "Vin de Savoie mousseux rosé (Hors Interpro)", "Produit hors-interpro : libellé douanier repris du CSV");
    $t->is($detail2->code_inao, "1S437M 1", "Produit mousseux hors-interpro : code inao repris du CSV");
}
if ($produitdefault_autre_hash) {
    $t->is(count($drm->get($produitdefault_autre_hash)->details), 1, "Le produit autre hors intepro est bien reconnu comme produit défaut distinct");
    foreach($drm->get($produitdefault_autre_hash)->details as $detail3) {
        break;
    }
    $t->ok($detail3->isDefaultProduit(), "Produit autre hors-interpro est bien détecté");
    $t->is($detail3->produit_libelle, "Autre vin de Savoie", "Produit hors-interpro : libellé douanier repris du CSV");
    $t->is($detail3->getLibelle(), "Autre vin de Savoie (Hors Interpro)", "Produit hors-interpro : libellé douanier repris du CSV");
    $t->is($detail3->code_inao, "SPIRITUEUX_GUADELOUPE_SUP_18", "Produit autre hors-interpro : code inao repris du CSV");
}
#FIN: test de produit hors interpro

$t->ok($drm->crds->exist('COLLECTIFSUSPENDU'), "CRD : noeud COLLECTIFSUSPENDU reconnu");
$t->is(count($drm->crds->COLLECTIFSUSPENDU), 5, "CRD possède deux centilisations");

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
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".substr($produit1->getLibelleFormat(), 0, -2).",suspendu,stocks_fin,final,944,,,,,,\n"); // Teste en retirant un caractère à la fin pour voir si la reconnaissance se fait si il n'y a pas d'ambiguité sur la résolution de libellé d'un produit
fclose($temp);
$drm = DRMClient::getInstance()->createDoc($viti->identifiant, $periode);
$drm->teledeclare = true;
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
$drm2->teledeclare = true;
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

if($drm->getProduit($produit1_hash, 'details')->exist('entrees/retourmarchandisetaxees_details')) {
    $t->is($drm2->getProduit($produit1_hash, 'details')->get('entrees/retourmarchandisetaxees_details')->getFirst()->getDateFr(), "20/12/2017","Date de replacement conservée");
} else {
    $t->is($drm2->getProduit($produit1_hash, 'details')->replacement_date, '20/12/2017', "Date de replacement conservée");
}


$t->is($drm2->getProduit($produit2_hash, 'details')->get('stocks_debut/initial'), 951.4625, "le stock initial est celui attendu");
$t->is($drm2->getProduit($produit2_hash, 'details')->get('stocks_fin/final'), 944, "le stock find est celui attendu");

$t->is($drm2->crds->COLLECTIFSUSPENDU->get('TRANQ-VERT-750')->stock_fin, 11624, "stock debut 75 cl OK");
$drm2->validate();
unlink($tmpfname);

$periode = (date('Y'))."02";
$tmpfname = tempnam("/tmp", "DRM_");
$t->comment("Dépendance des stocks de produits et CRD ".$viti->identifiant);
$temp = fopen($tmpfname, "w");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,stocks_debut,initial,944,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,sorties,ventefrancecrd,4,,,,,,\n");
fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,stocks_fin,final,940,,,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",Lie de vin,tranquille,Bouteille75cl,,,,,,,collectif suspendu,stock_debut,debut,0,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",Lie de vin,tranquille,Bouteille75cl,,,,,,,collectif suspendu,entrees,achats,11625,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",Lie de vin,tranquille,Bouteille 75 cl,,,,,,,collectif suspendu,sorties,utilisations,24,,,,\n");
fwrite($temp, "CRD,$periode,".$viti->identifiant.",".$viti->no_accises.",Lie de vin,tranquille,Bouteille 75cl,,,,,,,collectif suspendu,stock_fin,fin,11601,,,,\n");
fclose($temp);

$drm3 = DRMClient::getInstance()->createDoc($viti->identifiant, $periode);
$drm3->teledeclare = true;
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
unlink($tmpfname);

$t->comment("Conformité aux catalogues ou  ".$viti->identifiant);

$produit_disabled = null;

if($produit_disabled) {
    $temp = fopen($tmpfname, "w");
    fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit_disabled->getCertification()->getLibelle().",".$produit_disabled->getGenre()->getLibelle().",".$produit_disabled->getAppellation()->getLibelle().",".$produit_disabled->getMention()->getLibelle().",".$produit_disabled->getLieu()->getLibelle().",".$produit_disabled->getCouleur()->getLibelle().",".$produit_disabled->getCepage()->getLibelle().",,".$produit_disabled->getLibelleFormat().",suspendu,stocks_debut,initial,951.4625,,,,,,\n");
    fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit_disabled->getCertification()->getLibelle().",".$produit_disabled->getGenre()->getLibelle().",".$produit_disabled->getAppellation()->getLibelle().",".$produit_disabled->getMention()->getLibelle().",".$produit_disabled->getLieu()->getLibelle().",".$produit_disabled->getCouleur()->getLibelle().",".$produit_disabled->getCepage()->getLibelle().",,".$produit_disabled->getLibelleFormat().",suspendu,entrees,retourmarchandisetaxees,1,201712,,,,,\n");
    fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit_disabled->getCertification()->getLibelle().",".$produit_disabled->getGenre()->getLibelle().",".$produit_disabled->getAppellation()->getLibelle().",".$produit_disabled->getMention()->getLibelle().",".$produit_disabled->getLieu()->getLibelle().",".$produit_disabled->getCouleur()->getLibelle().",".$produit_disabled->getCepage()->getLibelle().",,".$produit_disabled->getLibelleFormat().",suspendu,sorties,ventefrancecrd,4.62,,,,,,\n");
    fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit_disabled->getCertification()->getLibelle().",".$produit_disabled->getGenre()->getLibelle().",".$produit_disabled->getAppellation()->getLibelle().",".$produit_disabled->getMention()->getLibelle().",".$produit_disabled->getLieu()->getLibelle().",".$produit_disabled->getCouleur()->getLibelle().",".$produit_disabled->getCepage()->getLibelle().",,".$produit_disabled->getLibelleFormat().",suspendu,sorties,export,1.89,PAYS-BAS,,,,,\n");
    fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit_disabled->getCertification()->getLibelle().",".$produit_disabled->getGenre()->getLibelle().",".$produit_disabled->getAppellation()->getLibelle().",".$produit_disabled->getMention()->getLibelle().",".$produit_disabled->getLieu()->getLibelle().",".$produit_disabled->getCouleur()->getLibelle().",".$produit_disabled->getCepage()->getLibelle().",,".$produit_disabled->getLibelleFormat().",suspendu,sorties,export,0.9525,BELGIQUE,,,,,\n");
    fwrite($temp, "CAVE,$periode,".$viti->identifiant.",".$viti->no_accises.",".$produit_disabled->getCertification()->getLibelle().",".$produit_disabled->getGenre()->getLibelle().",".$produit_disabled->getAppellation()->getLibelle().",".$produit_disabled->getMention()->getLibelle().",".$produit_disabled->getLieu()->getLibelle().",".$produit_disabled->getCouleur()->getLibelle().",".$produit_disabled->getCepage()->getLibelle().",,".$produit_disabled->getLibelleFormat().",suspendu,stocks_fin,final,945,,,,,,\n");
    fclose($temp);

    $drm4 = DRMClient::getInstance()->createDoc($viti->identifiant, $periode);
    $drm4->teledeclare = true;
    $import = new DRMImportCsvEdi($tmpfname, $drm4);
    $t->ok(!$import->checkCSV(), "Un produit non actif ne doit pas être permis");
    unlink($tmpfname);
} else {
    $t->pass("Un produit non actif ne doit pas être permis");
}

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
$drm5->teledeclare = true;
$import = new DRMImportCsvEdi($tmpfname, $drm5);
$t->ok(!$import->checkCSV(), "On ne peut pas changer une CRD avec du stock");
foreach($import->getCsvDoc()->erreurs as $k => $err) {
    break;
}
$t->is($err->num_ligne, 5, "L'erreur de CRD pointe la bonne ligne");

unlink($tmpfname);

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
$drm6->teledeclare = true;
$import = new DRMImportCsvEdi($tmpfname, $drm6);
$t->ok(!$import->checkCSV(), "On ne peut pas changer le stock CRD déclaré lors de la DRM précédente");
foreach($import->getCsvDoc()->erreurs as $k => $err) {
    break;
}
$t->is($err->num_ligne, 5, "L'erreur de CRD pointe la bonne ligne");

unlink($tmpfname);
$temp = fopen($tmpfname, "w");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,stocks_debut,initial,944,,,,,,\n");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,stocks_fin,final,944,,,,,,\n");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",".$produit2->getCodeDouane().",,,,,,,,,suspendu,stocks_debut,initial,944,,,,,,\n");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",".$produit2->getCodeDouane().",,,,,,,,,suspendu,stocks_fin,final,944,,,,,,\n");
fclose($temp);
$drm7 = DRMClient::getInstance()->createDoc($viti->identifiant, $periode5);
$drm7->teledeclare = true;
$import = new DRMImportCsvEdi($tmpfname, $drm7);
$import->importCSV();
$t->is($drm7->crds->COLLECTIFSUSPENDU->get('TRANQ-VERT-750')->stock_debut, 11624, "stock debut 75 cl OK");
$t->is($drm7->crds->COLLECTIFSUSPENDU->get('TRANQ-VERT-750')->stock_fin, 11624, "stock fin 75 cl OK");
$drm7->delete();
unlink($tmpfname);

$temp = fopen($tmpfname, "w");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,stocks_debut,initial,944,,,,,,\n");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,stocks_fin,final,944,,,,,,\n");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",".$produit2->getCodeDouane().",,,,,,,,,suspendu,stocks_debut,initial,940,,,,,,\n");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",".$produit2->getCodeDouane().",,,,,,,,,suspendu,stocks_fin,final,940,,,,,,\n");
fclose($temp);
$drm8 = DRMClient::getInstance()->createDoc($viti->identifiant, $periode5);
$drm8->teledeclare = true;
$import = new DRMImportCsvEdi($tmpfname, $drm8);
$t->ok(!$import->checkCSV(), "On ne peut pas changer le stock de vin déclaré lors de la DRM précédente");
foreach($import->getCsvDoc()->erreurs as $k => $err) {
    break;
}
$t->is($err->num_ligne, 3, "L'erreur de CRD pointe la bonne ligne");
unlink($tmpfname);

$drm2->devalide();
$drm2->delete();

$periode5 = date('Y')."04";
$t->comment("Test non appurement + CRD sans couleur ".$viti->identifiant." $periode5");
$temp = fopen($tmpfname, "w");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,stocks_debut,initial,944,,,,,,\n");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,stocks_fin,final,944,,,,,,\n");
fwrite($temp, "ANNEXE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,,,NONAPUREMENT,,,01/06/2019,FR00000E00000,19FRG000000000000000\n");
fwrite($temp, "ANNEXE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,,,NONAPUREMENT,,,2019-06-02,FR00000E00001,19FRG000000000000001\n");
fwrite($temp, "ANNEXE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,,,NONAPUREMENT,,,2019-06-03,FR123456,19FRG000000000000002\n");
fwrite($temp, "ANNEXE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,,,NONAPUREMENT,,,2019-06-04,FR1234,19FRG000000000000003\n");
fwrite($temp, "ANNEXE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,,,NONAPUREMENT,,,2019-06-04,FR12345678,19FRG000000000000004\n");
fwrite($temp, "ANNEXE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,,,DAADAC,debut,,,,20FRG9999900000000001\n");
fwrite($temp, "ANNEXE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,,,DAADAC,fin,,,,20FRG9999900000000002\n");
fwrite($temp, "ANNEXE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,,,DSADSAC,debut,,,,123450\n");
fwrite($temp, "ANNEXE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,,,DSADSAC,fin,,,,123460\n");
fwrite($temp, "CRD,$periode5,".$viti->identifiant.",".$viti->no_accises.",,tranquille,Bouteille75cl,,,,,,,collectif suspendu,stock_debut,debut,100,,,,\n");
fwrite($temp, "CRD,$periode5,".$viti->identifiant.",".$viti->no_accises.",,tranquille,Bouteille75cl,,,,,,,collectif suspendu,stock_fin,fin,100,,,,\n");
fclose($temp);
$drm9 = DRMClient::getInstance()->createDoc($viti->identifiant, $periode5);
$drm9->teledeclare = true;
$import = new DRMImportCsvEdi($tmpfname, $drm9);
$t->ok(!$import->checkCSV(), "Repère bien l'erreur sur le non appurement");
$t->is(count($import->getCsvDoc()->erreurs), 4, "a bien que les erreurs de accises/bureau de douane et le warning relatif au DAE");
foreach($import->getCsvDoc()->erreurs as $k => $err) {
    if (!preg_match("/Le numéro d'accise du destinataire est mal formatté/", $err->diagnostic) && !preg_match("/DAE/", $err->diagnostic)) {
        $t->ok(false, "Erreur non voulue lors de l'import : ".$err->diagnostic);
    }
}

$import->importCSV();
$drm9->save();
$t->is(count($drm9->releve_non_apurement), 3, "releve de non apurement présent");
$t->is($drm9->releve_non_apurement["19FRG000000000000000"]->getDateEmission(true), '2019-06-01', "la 1ere date est bonne");
$t->is($drm9->releve_non_apurement["19FRG000000000000001"]->getDateEmission(true), '2019-06-02', "la 2de date est bonne");
$t->is($drm9->releve_non_apurement["19FRG000000000000002"]->getDateEmission(true), '2019-06-03', "la 3ème date est bonne");
$t->ok(!isset($drm9->releve_non_apurement["19FRG000000000000003"]), "le 4ème relevé de non appurement a bien été exclu (moins de 8 caractères)");
$t->ok(!isset($drm9->releve_non_apurement["19FRG000000000000004"]), "le 5ème relevé de non appurement a bien été exclu (de 9 à 10 caractères)");
foreach ($drm9->crds as $k1 => $coul) {
    foreach($coul as $k2 => $crd) {
        $t->is($crd->couleur, 'DEFAUT', "sans couleur, la CRD couleur par défaut est DEFAUT");
    }
}
$t->ok(!$drm9->documents_annexes->exist('DAADAC'), "Les numéros de DAE ne sont bien pas enregistrés en annexes");
$t->ok($drm9->documents_annexes->exist('DSADSAC'), "Enregistrement des documents d'accompagnement papier");
$t->is($drm9->documents_annexes->DSADSAC->debut, 123450, 'DSA debut est bon');
$t->is($drm9->documents_annexes->DSADSAC->fin, 123460, 'DSA fin est bon');

unlink($tmpfname);

$drm9->devalide();
$drm9->delete();

$periode5 = date('Y')."04";
$t->comment("Reconnaissance de produit Alcool avec le degré");
$temp = fopen($tmpfname, "w");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produitAlcool->getLibelleFormat()." (".$produitAlcool->getCodeDouane()."),suspendu,complement,TAV,45,,,,,,\n");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produitAlcool->getLibelleFormat()." (".$produitAlcool->getCodeDouane()."),suspendu,stocks_debut,initial,100,,,,,,\n");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produitAlcool->getLibelleFormat()." - 20° (".$produitAlcool->getCodeDouane()."),suspendu,complement,TAV,20,,,,,,\n");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produitAlcool->getLibelleFormat()." - 20° (".$produitAlcool->getCodeDouane()."),suspendu,stocks_debut,initial,50,,,,,,\n");
fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produitAlcool->getLibelleFormat()." - 60° (".$produitAlcool->getCodeDouane()."),suspendu,stocks_debut,initial,70,,,,,,\n");
fclose($temp);

$drm10 = DRMClient::getInstance()->createDoc($viti->identifiant, $periode5, true);
$import = new DRMImportCsvEdi($tmpfname, $drm10);
$import->importCSV();
$drm10->save();
$drm10->get($produitAlcool_hash.'/details/DEFAUT')->tav = 60;
$drm10->validate();
$drm10->save();

$keyProductTav45 = $drm10->get($produitAlcool_hash.'/details')->createSHA1Denom(null, 45);
$keyProductTav20 = $drm10->get($produitAlcool_hash.'/details')->createSHA1Denom(null, 20);
$keyProductTav60 = 'DEFAUT';

$t->is(count($drm10->get($produitAlcool_hash.'/details')->toArray(true, false)), 3, "3 produits ont été créés");
$t->is($drm10->get($produitAlcool_hash.'/details/'.$keyProductTav45)->tav, 45, "Tav du 1er produit");
$t->is($drm10->get($produitAlcool_hash.'/details/'.$keyProductTav45)->stocks_debut->initial, 100, "Le stock initial du 1er produit");
$t->is($drm10->get($produitAlcool_hash.'/details/'.$keyProductTav45)->getLibelle(), $produitAlcool->getLibelleFormat().' - 45°', "Libellé du 1er produit");

$t->is($drm10->get($produitAlcool_hash.'/details/'.$keyProductTav20)->tav, 20, "Tav du 2ème produit");
$t->is($drm10->get($produitAlcool_hash.'/details/'.$keyProductTav20)->stocks_debut->initial, 50, "Stock initial du 2ème produit");
$t->is($drm10->get($produitAlcool_hash.'/details/'.$keyProductTav20)->getLibelle(), $produitAlcool->getLibelleFormat().' - 20°', "Le libelle du 2ème produit contient le tav");
$t->is($drm10->get($produitAlcool_hash.'/details/'.$keyProductTav60)->tav, 60, "Tav du 3ème produit");
$t->is($drm10->get($produitAlcool_hash.'/details/'.$keyProductTav60)->stocks_debut->initial, 70, "Stock initial du 3ème produit");
$t->is($drm10->get($produitAlcool_hash.'/details/'.$keyProductTav60)->getLibelle(), $produitAlcool->getLibelleFormat().' - 60°', "Libellé du 3ème produit avec le tav");
unlink($tmpfname);

$periode6 = date('Y')."05";
$temp = fopen($tmpfname, "w");
fwrite($temp, "CAVE,$periode6,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produitAlcool->getAppellation()->getLibelle()." - 20 (".$produitAlcool->getCodeDouane()."),suspendu,stocks_debut,initial,50,,,,,,\n");
fwrite($temp, "CAVE,$periode6,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produitAlcool->getAppellation()->getLibelle()." - 20° (".$produitAlcool->getCodeDouane()."),suspendu,complement,TAV,20,,,,,,\n");
fwrite($temp, "CAVE,$periode6,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produitAlcool->getAppellation()->getLibelle()." - 45° (".$produitAlcool->getCodeDouane()."),suspendu,stocks_debut,initial,100,,,,,,\n");
fwrite($temp, "CAVE,$periode6,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produitAlcool->getAppellation()->getLibelle()." - 45° (".$produitAlcool->getCodeDouane()."),suspendu,complement,TAV,45,,,,,,\n");
fwrite($temp, "CAVE,$periode6,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produitAlcool->getAppellation()->getLibelle()." - 60° (".$produitAlcool->getCodeDouane()."),suspendu,stocks_debut,initial,70,,,,,,\n");
fwrite($temp, "CAVE,$periode6,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produitAlcool->getAppellation()->getLibelle()." - 60° (".$produitAlcool->getCodeDouane()."),suspendu,sorties,ventefrancecrd,10,,,,,,\n");
fwrite($temp, "CAVE,$periode6,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produitAlcool->getAppellation()->getLibelle()." - 60° (".$produitAlcool->getCodeDouane()."),suspendu,complement,TAV,60,,,,,,\n");

fclose($temp);

$drm11 = DRMClient::getInstance()->createDoc($viti->identifiant, $periode6, true);
$import = new DRMImportCsvEdi($tmpfname, $drm11);
$import->importCSV();
$t->is(count($drm11->get($produitAlcool_hash.'/details')->toArray(true, false)), 3, "3 produits ont été créés");
$t->is($drm11->get($produitAlcool_hash.'/details/'.$keyProductTav60)->stocks_fin->final, 60, "Stock final du 3 ème produit");

unlink($tmpfname);

$drm10->devalidate();
$drm10->delete();
$drm11->delete();


if($application == "ivso") {
    $periode5 = date('Y')."04";
    $t->comment("Reconnaissance du Floc de Gascogne pour IVSO");

    $temp = fopen($tmpfname, "w");
    fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",AUTRES_PI_INF_18,,,,,,,,Floc de Gascogne Blanc - 17°,suspendu,stocks_debut,initial,0,,,,,,\n");
    fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",AUTRES_PI_INF_18,,,,,,,,Floc de Gascogne Blanc - 17°,suspendu,entrees,achatcrd,100,,,,,,\n");
    fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",AUTRES_PI_INF_18,,,,,,,,Floc de Gascogne Blanc - 17°,suspendu,sorties,ventefrancecrd,100,,,,,,\n");
    fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",AUTRES_PI_INF_18,,,,,,,,Floc de Gascogne Blanc - 17°,suspendu,complement,TAV,17,,,,,,\n");
    fclose($temp);

    $drm10 = DRMClient::getInstance()->createDoc($viti->identifiant, $periode5, true);
    $import = new DRMImportCsvEdi($tmpfname, $drm10);
    $import->importCSV();
    $drm10->save();
    $details = $drm10->getProduitsDetails();
    foreach($details as $detail) {
    }

    $t->is($detail->getLibelle(), "Floc de Gascogne Blanc - 17°", "Reconnaissance du produit");
} elseif($application == "bivc") {
    $periode5 = date('Y')."04";
    $t->comment("création vrac");

    $temp = fopen($tmpfname, "w");
    fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,stocks_debut,initial,944,,,,,,\n");
    fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,sorties,creationvractirebouche,50,,,,450,".$nego->no_accises.",".$nego->raison_sociale."\n");
    fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,sorties,creationvractirebouche,50,,-,\n");
    fwrite($temp, "CAVE,$periode5,".$viti->identifiant.",".$viti->no_accises.",,,,,,,,,".$produit1->getAppellation()->getLibelle()." (".$produit1->getCodeDouane()."),suspendu,stocks_fin,final,844,,,,,,\n");
    fclose($temp);

    $drm10 = DRMClient::getInstance()->createDoc($viti->identifiant, $periode5, true);
    $drm10->teledeclare = true;
    $drm10->save();
    $t->comment("DRM id : ".$drm10->_id);
    $import = new DRMImportCsvEdi($tmpfname, $drm10);
    $import->checkCSV();
    $t->is(count($import->getCsvDoc()->erreurs), 1, "a bien que une erreur d'absence d'acheteur");
    if ($import->getCsvDoc()->hasErreurs()) {
        foreach ($import->getCsvDoc()->erreurs as $k => $err) {
            if (!preg_match('/acheteur doit être renseigné/', $err->diagnostic)) {
                $t->ok(false, $err->diagnostic);
            }
        }
    }
    $import->importCSV();
    $drm10->save();
    $details = $drm10->getProduitsDetails();
    foreach($details as $detail) {
    }
    $t->is($drm10->getProduit($produit1_hash, 'details')->get('stocks_debut/initial'), 944, "le stock initial est celui attendu");
    $t->is($drm10->getProduit($produit1_hash, 'details')->get('sorties/creationvractirebouche'),50,"Vrac tiré bouché");
    $t->is($drm10->getProduit($produit1_hash, 'details')->get('stocks_fin/final'), 894, "le stock final est celui attendu");
    $t->ok($drm10->getProduit($produit1_hash, 'details')->exist('sorties/creationvractirebouche_details'), "details créé");
    $i = 0;
    foreach($drm10->getProduit($produit1_hash, 'details')->get('sorties/creationvractirebouche_details') as $drmid => $v) {
        $i++;
        if ($i == 1) {
            $t->is($v->volume, 50, "volume du contrat ".$i." est ok");
            $t->is($v->acheteur, $nego->identifiant, "l'acheteur 1 a bien été reconnu (".$nego->no_accises.")");
            $t->is($v->prixhl, 450, "le prix du contrat de l'acheteur 1 a bien été reconnu");
        }else{
            $t->ok(false, "l'absence de l'acheteur 2 ne doit pas être enregistré");
        }
    }
}
