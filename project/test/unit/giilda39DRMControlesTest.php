<?php
require_once(dirname(__FILE__).'/../bootstrap/common.php');


$t = new lime_test(19);


$t->comment("création d'une DRM avec des contrôles");

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$viti->crd_regime = EtablissementClient::REGIME_CRD_COLLECTIF_SUSPENDU;
$viti->save();

$produits = array_keys(ConfigurationClient::getInstance()->getCurrent()->getProduits());
$produit_hash = array_shift($produits);
$periode = date('Ym');

//Suppression des DRM précédentes
foreach(DRMClient::getInstance()->viewByIdentifiant($viti->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
}

$drm = DRMClient::getInstance()->createDoc($viti->identifiant, $periode, true);

//Création de transmission
$drm->add("transmission_douane")->add("success", false);
$drm->add("transmission_douane")->add("coherente", false);
$drm->declarant->no_accises = 12345;
$drm->societe->siret = 123456789;
$drm->updateControles();
$drm->save();

$t->is($drm->controles->coherence->nb, 1, "Absence de cohérence crée un controle");
$t->is($drm->controles->transmission->nb, 1, "Erreur de transmission crée un controle");
$t->is($drm->controles->exist("vigilance"), false, "Pas de vigilance");
$t->is($drm->controles->exist("erreur"), false, "Pas d'erreur dans la DRM");
$t->is($drm->controles->exist("engagement"), false, "Pas d'engagement");



$drm->remove("transmission_douane");
$drm->updateControles();
$drm->save();

$t->is($drm->exist("transmission_douane"), false, "Suppression de la transmission douane");
$t->is($drm->exist("controles"), false, "----->> Plus de controle dans la DRM");

//Création de l'erreur
$produits = array_keys(ConfigurationClient::getInstance()->getCurrent()->getProduits());
$produit_hash = array_shift($produits);

$details = $drm->addProduit($produit_hash, 'details');
$details->entrees->recolte = 100;
$details->sorties->destructionperte = 106;

$drm->update();
$drm->updateControles();
$drm->save();

$t->is($drm->controles->exist("erreur"), true, "Erreur dans la DRM crée un controle");

$details->sorties->destructionperte = 50;
$drm->update();
$drm->updateControles();
$drm->save();

$t->is($drm->exist("controles"), false, "----->> Plus de controle dans la DRM");

//Création de vigilance
$t->is(is_null($drm->declarant->no_accises), false, "Numéro d'accise: ". $drm->declarant->no_accises);
$drm->declarant->no_accises = null;
$t->is(is_null($drm->declarant->no_accises), true, "Numéro d'accise vide");

$drm->updateControles();
$drm->save();
$t->is($drm->controles->exist("vigilance"), false, "Point de vigilance dans la DRM");

$drm->declarant->no_accises = 12345;
$drm->updateControles();
$drm->save();

$t->is($drm->exist("controles"), false, "----->> Plus de controle dans la DRM");



$t->is($drm->isValidee(), false, "DRM non validée");
//Création de vigilance
$drm->societe->siret = null;
$viti->crd_regime = null;
$viti->save();

//Création de l'erreur
$details->sorties->destructionperte = 200;

//Création de transmission
$drm->add("transmission_douane")->add("success", false);
$drm->add("transmission_douane")->add("coherente", false);

$drm->updateControles();
$drm->update();
$drm->save();

$t->is(count($drm->controles), 4, "La DRM possède 4 controles: 1 transmissions, 1 cohérence, 1 vigilance et 1 erreur");

$drm->validate();
$t->is($drm->isValidee(), date("Y-m-d"), "DRM validée");

$drm->updateControles();
$drm->update();
$drm->save();
$t->is(count($drm->controles), 2, "La DRM ne possède que 2 contrôles:  1 transmissions et 1 cohérence");

$drm->transmission_douane->success = true;
$drm->transmission_douane->coherente = true;

$drm->updateControles();
$drm->update();
$drm->save();

$t->is($drm->exist("transmission_douane"), true, "La DRM a été transmise et coherente");
$t->is($drm->exist("controles"), false, "----->> Plus de controle dans la DRM");