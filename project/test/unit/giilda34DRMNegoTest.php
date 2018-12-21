<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$conf = ConfigurationClient::getInstance()->getCurrent();

$hasCVONegociant = false;
foreach ($conf->declaration->filter('details') as $configDetails) {
    foreach ($configDetails as $details) {
        foreach($conf->declaration->details->getDetailsSorted($details) as $detail) {
            if($detail->isFacturableInverseNegociant()) {
                $hasCVONegociant = true;
            }
        }
    }
}

$nego = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_region')->getEtablissement();
$produits = array_keys($conf->getProduits());
$produit_hash = array_shift($produits);
$periode = date('Ym');

foreach(DRMClient::getInstance()->viewByIdentifiant($nego->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
}

if(!$hasCVONegociant) {
    $t = new lime_test(3);

    $t->comment("DRM Négociant non facture");

    $drm = DRMClient::getInstance()->createDoc($nego->identifiant, $periode, true);
    $drm->save();

    $t->ok($drm->isDRMNegociant(), "C'est une DRM Négoce");
    if($application == "civa") {
        $t->ok($drm->isFacturable(), "C'est une DRM facturable");
    } else {
        $t->ok(!$drm->isFacturable(), "C'est une DRM non facturable");
    }

    $details = $drm->addProduit($produit_hash, 'details');
    $details->entrees->recolte = 100;
    $details->sorties->ventefrancecrd = 100;

    $drm->update();
    $drm->validate();
    $drm->save();

    $recapCvos = DRMClient::getInstance()->getRecapCvos($drm->identifiant, $drm->periode);

    if($application == "civa") {
        $t->is(round($recapCvos["TOTAL"]->totalVolumeDroitsCvo, 4), 100, "Volume facturation à 100");
    } else {
        $t->is(round($recapCvos["TOTAL"]->totalVolumeDroitsCvo, 4), 0, "Aucun volume n'est facturable");
    }

    exit;
}

$t = new lime_test(10 + 2*12);

$t->comment("DRM Négociant avec récolte");

$drm = DRMClient::getInstance()->createDoc($nego->identifiant, $periode, true);
$drm->save();

$t->ok($drm->isDRMNegociant(), "C'est une DRM Négoce");
$t->ok(!$drm->isFacturable(), "C'est une DRM non facturable");

$details = $drm->addProduit($produit_hash, 'details');
$details->entrees->recolte = 100;
$details->sorties->ventefrancecrd = 100;
$details->sorties->destructionperte = 5;

$drm->update();
$drm->validate();
$drm->save();

$nbMouvementEntreeRecolte = 0;
$nbMouvementSortieDestructionPerte = 0;
$volumeTotal = 0;
$prixTotal = 0;
$facturable = true;
$coefficientFacturation = 1;

$dateMouvement = new DateTime($drm->getDate());
foreach($drm->mouvements->get($nego->identifiant) as $mouvement) {
    if($mouvement->type_hash == "entrees/recolte") {
        $nbMouvementEntreeRecolte += 1;
        $volumeTotal += $mouvement->getQuantite();
        $prixTotal += $mouvement->getPrixHt();
        if(!$mouvement->facturable) {
            $facturable = false;
        }
        if(!$mouvement->coefficient_facturation) {
            $coefficientFacturation = -1;
        }
        $t->ok($mouvement->volume > 0, "Le volume est supérieur à 0");
        $t->is($mouvement->date, $dateMouvement->format('Y-m-d'), "La date du mouvement est ".$dateMouvement->format('Y-m-d'));
        $dateMouvement = $dateMouvement->modify("last day of next month");
    }

    if($mouvement->type_hash == "sorties/destructionperte") {
        $nbMouvementSortieDestructionPerte += 1;
    }
}



$t->is($nbMouvementEntreeRecolte, DRMConfiguration::getInstance()->getMouvementDivisableNbMonth(), "Les mouvements de recolté on été scindé en ".DRMConfiguration::getInstance()->getMouvementDivisableNbMonth());
$t->is($nbMouvementSortieDestructionPerte, 1, "1 seul mouvement de destruction perte");
$t->ok($facturable, "Tous les mouvements sont factruables");
$t->is($coefficientFacturation, 1, "Le coefficient de facturation de tous les mouvements est 1");
$t->is($volumeTotal, $details->entrees->recolte, "L'ensemble des mouvements couvrent le volume total d'entrée récolte");
$t->is($prixTotal, $details->entrees->recolte * $details->getCVOTaux(), "Le prix Ht est la quantité * La CVO du produit");

$recapCvos = DRMClient::getInstance()->getRecapCvos($drm->identifiant, $drm->periode);

$t->is(round($recapCvos["TOTAL"]->totalVolumeDroitsCvo, 4), $details->entrees->recolte - $details->sorties->destructionperte, "Le volume du recap CVO est de ".$details->entrees->recolte);
$t->is(round($recapCvos["TOTAL"]->totalPrixDroitCvo, 4), ($details->entrees->recolte - $details->sorties->destructionperte) * $details->getCVOTaux(), "Le prix Ht de la du récap CVO est OK");
