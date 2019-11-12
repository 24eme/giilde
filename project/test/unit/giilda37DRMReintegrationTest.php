<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$produit = null;
$configuration = ConfigurationClient::getInstance()->getCurrent();
foreach($configuration->getProduits() as $produit) {
    $produit = $produit->getHash();
    break;
}

$mouvementReintegration = null;
$mouvementSansDetail = null;
foreach($configuration->declaration->details as $details) {
    foreach ($details as $detail) {
        if(!$detail->hasDetails()) {
            $mouvementSansDetail = $detail;
            continue;
        }
        if($detail->getDetails() != ConfigurationDetailLigne::DETAILS_REINTEGRATION) {
            continue;
        }
        $mouvementReintegration = $detail;
        break;
    }
    if($mouvementReintegration) {
        break;
    }
}


if(!$mouvementReintegration) {
    $t = new lime_test(0);
    exit;
}

$mouvementReintegrationHash = $mouvementReintegration->getParent()->getKey().'/'.$mouvementReintegration->getKey().'_details';

$t = new lime_test(27);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_teledeclaration')->getEtablissement();
$periode = date('Ym');

foreach(DRMClient::getInstance()->viewByIdentifiant($viti->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
}

$drm = DRMClient::getInstance()->createDoc($viti->identifiant, $periode, true);
$drm->addProduit($produit, "details");

$t->comment('Test sur le modèle');

$produit = $drm->getProduit($produit, "details");
$t->ok($produit->exist($mouvementReintegrationHash), 'Le details pour le mouvement en reintegration existe');
$t->ok(!$produit->exist($mouvementSansDetail->getParent()->getKey().'/'.$mouvementSansDetail->getKey().'_details'), "Le mouvement classique n'a pas de détails");

$produit->stocks_debut->initial = 1000;

$detail = DRMESDetailReintegration::freeInstance($drm);
$detail->volume = 100;
$detail->date = date('Y-m-d');

$produit->get($mouvementReintegrationHash)->addDetail($detail);
$drm->update();
$drm->save();

$t->ok($drm->_rev, "La DRM $drm->_id a été sauvgardé");

$t->is(count($produit->get($mouvementReintegrationHash)->toArray(true, false)), 1, "Le tableau contient 1 détail");
$t->is($produit->get($mouvementReintegrationHash)->getFirst()->volume, 100, "Le volume est enregistré");
$t->is($produit->get($mouvementReintegrationHash)->getFirst()->date, date('Y-m-d'), "La date est enregistré");
$t->is($produit->get($mouvementReintegrationHash)->getFirst()->identifiant, date('Y-m-d'), "L'identifant est la date");
$t->is($produit->get($mouvementReintegrationHash)->getFirst()->getIdentifiantLibelle(), date('d/m/Y'), "Le libellé de l'identifiant est la date en français");
$t->is($produit->get($mouvementReintegrationHash)->getFirst()->getReplacementYear(), date('Y'), "La fonction \"getReplacementYear\" renvoi l'année");
$t->is($produit->get($mouvementReintegrationHash)->getFirst()->getReplacementMonth(), date('m'), "La fonction \"getReplacementMonth\" renvoi le mois");
$t->is(preg_replace('/-[a-z0-9]+$/', '', $produit->get($mouvementReintegrationHash)->getFirst()->getKey()), date('Ymd'), "La clé commence par la date");
$t->is($produit->get(str_replace("_details", "", $mouvementReintegrationHash)), 100, "Le volume total de réintegration est 100");
$t->ok(!$produit->exist('replacement_date'), "Pas besoin de date de replacement");

$t->comment('Test du formulaire');

$form = new DRMDetailReintegrationForm($produit->get($mouvementReintegrationHash), array(), array('isTeledeclarationMode' => true));

$t->is(count($form), 3, "Le formulaire à 3 éléments");
$t->is($form[$detail->getKey()]['identifiant']->getValue(), date('d/m/Y'), "La date est initialisée en français");
$t->is($form[$detail->getKey()]['volume']->getValue(), 100, "Le volume est initialisé");

$values = $form->getDefaults();
$emptyItemKey = null;
foreach($values as $key => $value) {
    if($key != '_revision' && empty($value['identifiant']) && empty($value['volume'])) {
        $emptyItemKey = $key;
        break;
    }
}

$values[$detail->getKey()]['volume'] = 110;

$values[$emptyItemKey]['volume'] = 200;
$values[$emptyItemKey]['identifiant'] = date('d/m/').(date('Y')-1);

$form->bind($values);
$t->ok($form->isValid(), "Le formulaire est valide");

$form->update();
$drm->update();
$drm->save();

$t->is($produit->get($mouvementReintegrationHash)->getFirst()->volume, 110, "Le volume a été modifié");
$t->is($produit->get($mouvementReintegrationHash)->getFirst()->date, date('Y-m-d'), "La date a été conservée");
$t->is($produit->get($mouvementReintegrationHash)->getLast()->volume, 200, "Le nouveau volume est intégré");
$t->is($produit->get($mouvementReintegrationHash)->getLast()->date, (date('Y')-1).date('-m-d'), "La nouvelle date est intégrée");
$t->is($produit->get(str_replace("_details", "", $mouvementReintegrationHash)), 310, "Le volume total de réintegration est 310");


$drm->generateMouvements();

$t->is(count($drm->mouvements->get($drm->identifiant)->toArray(true, false)), 2, 'La DRM à 2 mouvements');
$t->is($drm->mouvements->get($drm->identifiant)->getLast()->facturable, 1, 'Le mouvement est facturable');
$t->is($drm->mouvements->get($drm->identifiant)->getLast()->volume, 200, 'La volume du dernier mouvement est 200');
$t->is($drm->mouvements->get($drm->identifiant)->getFirst()->detail_identifiant, date('Y-m-d'), "L'identifiant du détail est la date");
$t->is($drm->mouvements->get($drm->identifiant)->getFirst()->detail_libelle, date('d/m/Y'), "Le libelle du détail est la date en fr");
