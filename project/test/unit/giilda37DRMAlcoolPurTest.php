<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$produit_hash_matiere_premiere = null;
$produit_hash_alcoolpur = null;
$produit_hash_vin = null;
$configuration = ConfigurationClient::getInstance()->getCurrent();
foreach($configuration->getProduits() as $produit) {
    if(!$produit->needTav()) { continue; }
    $produit_hash_alcoolpur = $produit->getHash();
    break;
}
foreach($configuration->getProduits() as $produit) {
    if(!preg_match('/MATIERES_PREMIERES/', $produit->code_douane)) { continue;}
    $produit_hash_matiere_premiere = $produit->getHash();
    break;
}
foreach($configuration->getProduits() as $produit) {
    if(preg_match('/MATIERES_PREMIERES/', $produit->code_douane)) { continue;}
    if($produit->needTav()) { continue; }
    $produit_hash_vin = $produit->getHash();
    break;
}

$transfer_exists = ($configuration->declaration->details->sorties->exist('transfertsrecolte') && ($configuration->declaration->details->sorties->transfertsrecolte->details == 'ALCOOLPUR'));

if(!$produit_hash_matiere_premiere || !$produit_hash_alcoolpur || !$transfer_exists) {
    $t = new lime_test(0);
    exit;
}

$t = new lime_test(32);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_teledeclaration')->getEtablissement();
$periode = date('Ym');

foreach(DRMClient::getInstance()->viewByIdentifiant($viti->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
}

$drm = DRMClient::getInstance()->createDoc($viti->identifiant, $periode, true);

$drm->addProduit($produit_hash_matiere_premiere, "details");
$drm->addProduit($produit_hash_matiere_premiere, "detailsACQUITTE");
$drm->addProduit($produit_hash_matiere_premiere, "details", "Meilleur");
$drm->addProduit($produit_hash_alcoolpur, "details", null, 42);
$drm->addProduit($produit_hash_alcoolpur, "details");
$drm->addProduit($produit_hash_alcoolpur, "details", "Ratafia");
$drm->addProduit($produit_hash_vin, "details");

$produitMP = $drm->getProduit($produit_hash_matiere_premiere, "details");
$produitMPMeilleur = $drm->getProduit($produit_hash_matiere_premiere, "details", "Meilleur");
$produitA = $drm->getProduit($produit_hash_alcoolpur, "details");
$produitATav = $drm->getProduit($produit_hash_alcoolpur, "details", null, 42);
$produitB = $drm->getProduit($produit_hash_alcoolpur, "details", "Ratafia");
$produitVin = $drm->getProduit($produit_hash_vin, "details");

$t->comment("Test du détail TAV");

$produitMP->stocks_debut->initial = 1000;
$produitA->stocks_debut->initial = 1000;
$produitATav->stocks_debut->initial = 1000;
$produitB->stocks_debut->initial = 1000;
$produitVin->stocks_debut->initial = 1000;

$detailAlcool = DRMESDetailAlcoolPur::freeInstance($drm);
$detailAlcool->setProduit($produitA);
$detailAlcool->tav = 40;
$detailAlcool->volume = 100;
$produitMP->sorties->transfertsrecolte_details->addDetail($detailAlcool);

$detailAlcoolB = DRMESDetailAlcoolPur::freeInstance($drm);
$detailAlcoolB->setProduit($produitB);
$detailAlcoolB->tav = 30;
$detailAlcoolB->volume = 200;
$produitMP->sorties->transfertsrecolte_details->addDetail($detailAlcoolB);

$produitVin->sorties->transfertsrecolte = 100;

$drm->update();
$drm->generateMouvements();
$drm->save();

$t->is($drm->getProduit($produit_hash_matiere_premiere, 'details')->get('stocks_debut/initial'), 1000, $drm->_id." : vérification du stock initial MP");
$t->is($drm->getProduit($produit_hash_alcoolpur, 'details')->get('stocks_debut/initial'), 1000, $drm->_id." : vérification du stock initial alcool");
$t->is($drm->getProduit($produit_hash_alcoolpur, 'details', null, 42)->get('stocks_debut/initial'), 1000, $drm->_id." : vérification du stock initial alcool avec tav");
$t->is($drm->getProduit($produit_hash_alcoolpur, 'details', 'Ratafia')->get('stocks_debut/initial'), 1000, $drm->_id." : vérification du stock initial alcool 2");

$t->is($drm->getProduit($produit_hash_matiere_premiere, 'details')->get('stocks_fin/final'), 700, $drm->_id." : vérification du stock final MP");
$t->is($drm->getProduit($produit_hash_alcoolpur, 'details')->get('stocks_fin/final'), 1250, $drm->_id." : vérification du stock final alcool");
$t->is($drm->getProduit($produit_hash_alcoolpur, 'details', null, 42)->get('stocks_fin/final'), 1000, $drm->_id." : vérification du stock final alcool avec tav");

$t->is($drm->getProduit($produit_hash_matiere_premiere, 'details')->get('sorties/transfertsrecolte'), 300, $drm->_id." : transferts enregistrés dans les MP");
$t->is($drm->getProduit($produit_hash_alcoolpur, 'details')->get('entrees/transfertsrecolte'), 250, $drm->_id." : transferts enregistrés dans l'alcool");
$t->is($drm->getProduit($produit_hash_alcoolpur, 'details')->get('tav'), 40, $drm->_id." : tav enregistré dans l'alcool");

$t->is($drm->getProduit($produit_hash_alcoolpur, 'details')->getLibelle(), 'Alcools supérieur 18° (autres que Rhum) - 40°', $drm->_id." : libellé avec TAV");
$t->is($drm->getProduit($produit_hash_alcoolpur, 'details')->isAlcoolPur(), true, $drm->_id." : Alcool pur ok");
$t->is($drm->getProduit($produit_hash_alcoolpur, 'details', null, 42)->get('tav'), 42, $drm->_id." : vérification du tav alcool avec tav");
$t->is($drm->getProduit($produit_hash_vin, 'details')->get('sorties/transfertsrecolte'), 100, $drm->_id." : transfert enregistré dans le produit vin");

$t->is(count($drm->mouvements->get($drm->identifiant)), 5, "La DRM a 5 mouvements");

$produitMP->sorties->transfertsrecolte_details->getLast()->volume = 0;
$drm->update();
$drm->generateMouvements();
$drm->save();

$t->is(count($drm->mouvements->get($drm->identifiant)), 3, "La DRM a 3 mouvements");
$t->is($drm->getProduit($produit_hash_alcoolpur, 'details', 'Ratafia')->get('entrees/transfertsrecolte'), 0, $drm->_id." : plus de transfert dans l'alcool");

$t->comment("Test du formulaire");

$produitB->tav = 40;
$produitMP->stocks_debut->initial = 100;

$form = new DRMMatierePremiereForm($drm);
$hashForm = str_replace('/', '-', $produitMP->getHash());
$t->is($form['stocks_debut_'.$hashForm]->getValue(), $produitMP->stocks_debut->initial, $drm->_id." : Le stock de début est intialisé");

$hashProduitAForm = $produitMP->getHash().'-'.$produitA->getHash();
$hashProduitATavForm = $produitMP->getHash().'-'.$produitATav->getHash();
$hashProduitBForm = $produitMP->getHash().'-'.$produitB->getHash();

$t->is($form['sorties_'.$hashForm][$hashProduitAForm]['volume']->getValue(), 100, $drm->_id." : Le volume de sortie est de 100");
$t->is($form['sorties_'.$hashForm][$hashProduitAForm]['tav']->getValue(), 40, $drm->_id." : Le tav du produit est ok");
$t->is($form['sorties_'.$hashForm][$hashProduitATavForm]['tav']->getValue(), 42, $drm->_id." : Le tav du produit TAV est ok");
$t->is($form['sorties_'.$hashForm][$hashProduitBForm]['volume']->getValue(), null, $drm->_id." : Le volume de sortie est vide");
$t->is($form['sorties_'.$hashForm][$hashProduitBForm]['tav']->getValue(), 40, $drm->_id." : Le tav du produit est ok");
$t->is(count($form['sorties_'.$hashForm]), 3, $drm->_id." : Le formulaire a 3 produits");

$values = $form->getDefaults();
$values['stocks_debut_'.$hashForm] = 120;
$values['sorties_'.$hashForm][$hashProduitBForm]['tav'] = 25;
$values['sorties_'.$hashForm][$hashProduitAForm]['volume'] = 0;
$values['sorties_'.$hashForm][$hashProduitBForm]['volume'] = 100;

$form->bind($values);

$t->ok($form->isValid(), $drm->_id." : Le form est valide");

$form->save();

$t->is($produitMP->stocks_debut->initial, $values['stocks_debut_'.$hashForm], $drm->_id." : Le stock de début a été enregistré");
$t->is($produitMP->sorties->transfertsrecolte, 100, $drm->_id." : Le volume a bien été mise à jour dans la sortie de matière première");
$t->is(count($produitMP->sorties->transfertsrecolte_details->toArray(true, false)), 1, $drm->_id." : 1 seul détail dans le détail de la sortie de matière première");
$t->is($produitA->entrees->transfertsrecolte, 0, $drm->_id." : Le volume a bien été mise à jour dans l'alcool");
$t->is($produitB->entrees->transfertsrecolte, 400, $drm->_id." : Le volume a bien été mise à jour dans l'alcool");
$t->is($produitA->tav, 40, $drm->_id." : Le tav a bien été mise à jour dans l'alcool");
$t->is($produitB->tav, 25, $drm->_id." : Le tav a bien été mise à jour dans l'alcool");
