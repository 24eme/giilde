<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$conf = ConfigurationClient::getCurrent();
if (!($conf->declaration->exist('details/sorties/creationvrac')) || ($conf->declaration->get('details/sorties/creationvrac')->details != "CREATIONVRAC")) {
    $t = new lime_test(0);
    exit(0);
}


$t = new lime_test(7);

$nego = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_region')->getEtablissement();
$nego2 = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_region_2')->getEtablissement();
$produits = array_keys(ConfigurationClient::getInstance()->getCurrent()->getProduits());
$produit_hash = array_shift($produits);
$periode = date('Ym');

foreach(DRMClient::getInstance()->viewByIdentifiant($nego->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
}
foreach(DRMClient::getInstance()->viewByIdentifiant($nego2->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
}
foreach(VracClient::getInstance()->retrieveBySoussigne($nego->identifiant)->rows as $r) {
  $vrac = VracClient::getInstance()->find($r->id);
  $vrac->delete();
}
foreach(VracClient::getInstance()->retrieveBySoussigne($nego2->identifiant)->rows as $r) {
  $vrac = VracClient::getInstance()->find($r->id);
  $vrac->delete();
}

$nb_mvts_nego_init = count(MouvementfactureFacturationView::getInstance()->getMouvementsNonFacturesBySociete($nego->getSociete()));
$nb_mvts_nego2_init = count(MouvementfactureFacturationView::getInstance()->getMouvementsNonFacturesBySociete($nego2->getSociete()));

$t->comment("DRM qui crée des vracs");

$drm = DRMClient::getInstance()->createDoc($nego->identifiant, $periode, true);
$drm->save();

$t->ok($drm->isDRMNegoce(), "C'est une DRM Négoce");

$t->is(count(VracClient::getInstance()->retrieveBySoussigne($nego2->identifiant)->rows), 0, $drm->_id." : Pas de vrac pour le nego 2");

$details = $drm->addProduit($produit_hash, 'details');
$details->stocks_debut->initial = 1000;
$creationvrac = DRMESDetailCreationVrac::freeInstance($drm);
$creationvrac->volume = 100;
$creationvrac->prixhl = 150;
$creationvrac->acheteur = $nego2->identifiant;
$creationvrac->type_contrat = VracClient::TYPE_TRANSACTION_VIN_VRAC;
$details->sorties->creationvrac_details->addDetail($creationvrac);

$drm->update();
$drm->save();
$t->is($drm->getProduit($produit_hash, 'details')->get('stocks_fin/final'), 900, $drm->_id." : Vérification du stock final");

$drm->validate();
$drm->save();
$t->is(count(VracClient::getInstance()->retrieveBySoussigne($nego2->identifiant)->rows), 1, $drm->_id." : Un contrat vrac pour le nego 2");

$contrat = VracClient::getInstance()->find(VracClient::getInstance()->retrieveBySoussigne($nego->identifiant)->rows[0]->id);
$t->is($contrat->type_transaction, VracClient::TYPE_TRANSACTION_VIN_VRAC, "Une sortie contrat de type vrac produit un contrat de type vrac");

$mvts_nego = MouvementfactureFacturationView::getInstance()->getMouvementsNonFacturesBySociete($nego->getSociete());
$mvts_nego_2 = MouvementfactureFacturationView::getInstance()->getMouvementsNonFacturesBySociete($nego2->getSociete());
$t->is(count($mvts_nego) - $nb_mvts_nego_init, 0, $drm->_id." : on retrouve aucun mouvement facturable dans la vue facture du nego");
$t->is(count($mvts_nego_2) - $nb_mvts_nego2_init, 0, $drm->_id." : on retrouve aucun mouvement facturable dans la vue facture du négo 2");
