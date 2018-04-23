<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(4);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$produits = array_keys(ConfigurationClient::getInstance()->getCurrent()->getProduits());
$produit_hash = array_shift($produits);
$produit2_hash = array_shift($produits);


//Suppression des DRM précédentes
foreach(DRMClient::getInstance()->viewByIdentifiant($viti->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
}


$t->comment("Création d'une DRM acquittee : ".$viti->identifiant);

$periode = (date('Y'))."01";
$drm = DRMClient::getInstance()->createDoc($viti->identifiant, $periode);
$details_normal = $drm->addProduit($produit_hash, 'details');
$details_normal->stocks_debut->initial = 1000;
$details = $drm->addProduit($produit_hash, 'detailsACQUITTE');
$drm->save();
$drmcheck = DRMClient::getInstance()->find('DRM-'.$viti->identifiant.'-'.$periode);
$t->is($drmcheck->get($produit_hash)->exist('detailsACQUITTE'), true, "l'ajout d'un noeud acquitte est conservé");

$drm->update();
$drm->save();
$drmcheck = DRMClient::getInstance()->find('DRM-'.$viti->identifiant.'-'.$periode);
$t->is($drmcheck->get($produit_hash)->exist('detailsACQUITTE'), true, "idem après un update");

$drm->validate();
$drm->save();
$drmcheck = DRMClient::getInstance()->find('DRM-'.$viti->identifiant.'-'.$periode);
$t->is($drmcheck->get($produit_hash)->exist('detailsACQUITTE'), false, "sans mouvement, le noeud acquitte est supprimé");

$drm->devalidate();
$details = $drm->addProduit($produit_hash, 'detailsACQUITTE');
$details->stocks_debut->initial = 1000;
$drm->validate();
$drm->save();
$drmcheck = DRMClient::getInstance()->find('DRM-'.$viti->identifiant.'-'.$periode);
$t->is($drmcheck->get($produit_hash)->exist('detailsACQUITTE'), true, "avec du stock, le noeud acquitte est conservé");
