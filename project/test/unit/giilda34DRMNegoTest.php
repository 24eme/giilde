<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$conf = ConfigurationClient::getCurrent();

$t = new lime_test(7);

$nego = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_region')->getEtablissement();
$produits = array_keys(ConfigurationClient::getInstance()->getCurrent()->getProduits());
$produit_hash = array_shift($produits);
$periode = date('Ym');

foreach(DRMClient::getInstance()->viewByIdentifiant($nego->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
}

$t->comment("DRM Négociant avec récolte");

$drm = DRMClient::getInstance()->createDoc($nego->identifiant, $periode, true);
$drm->save();

$t->ok($drm->isDRMNegoce(), "C'est une DRM Négoce");

$details = $drm->addProduit($produit_hash, 'details');
$details->entrees->recolte = 100;

$drm->update();
$drm->validate();
$drm->save();

foreach($drm->mouvements->get($nego->identifiant) as $mouvement) {
    if($mouvement->type_hash == "entrees/recolte") {
        $t->is($mouvement->facturable, 1, "Le mouvement de récolte est facturable");
        $t->is($mouvement->coefficient_facturation, 1, "Le coefficient de facturation est 1");
        $t->is($mouvement->getQuantite(), $details->entrees->recolte, "La quantité est facturable est de ".$details->entrees->recolte);
        $t->is($mouvement->getPrixHt(), $details->entrees->recolte * $details->getCVOTaux(), "Le prix Ht est la quantité * La CVO du produit");
    }
}

$recapCvos = DRMClient::getInstance()->getRecapCvos($drm->identifiant, $drm->periode);

$t->is($recapCvos["TOTAL"]->totalVolumeDroitsCvo, $details->entrees->recolte, "Le volume du recap CVO est de ".$details->entrees->recolte);
$t->is($recapCvos["TOTAL"]->totalPrixDroitCvo, $details->entrees->recolte * $details->getCVOTaux(), "Le prix Ht de la du récap CVO est OK");
