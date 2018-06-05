<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(77);

$nego2 =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_region_2')->getEtablissement();
$produits = array_keys(ConfigurationClient::getInstance()->getCurrent()->getProduits());
$produit_hash = array_shift($produits);
$produit2_hash = array_shift($produits);


//Suppression des DRM précédentes
foreach(DRMClient::getInstance()->viewByIdentifiant($nego2->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
}


$t->comment("Création d'une DRM importé en Janvier");

$periode = (date('Y'))."01";
$drm = DRMClient::getInstance()->createDoc($nego2->identifiant, $periode);
$drm->type_creation = "IMPORT";
$drm->save();

$t->ok($drm->isImport(), "La drm est une drm importé");

$details = $drm->addProduit($produit_hash, 'details');
$t->ok($details->canSetStockDebutMois(), "Le stock début de mois est éditable");
$details->stocks_debut->initial = 1000;
$details->sorties->ventefrancecrd = 100;
$drm->update();
$drm->save();
$drm->validate();
$drm->save();
$t->is(count(DRMClient::getInstance()->generateVersionCascade($drm)), 0, "La génération en cascade impacte aucune DRM");
$drm01Id = $drm->_id;


$t->comment("Création d'une DRM importé en Février");

$periode = (date('Y'))."02";
$drm = DRMClient::getInstance()->createDoc($nego2->identifiant, $periode);
$drm->type_creation = "IMPORT";
$drm->save();

$t->is($drm->_get('precedente'), $drm01Id, "La drm précédente est stocké");
$t->ok($drm->isImport(), "La drm est une drm importé");

$details = $drm->get($produit_hash.'/details/DEFAUT');
$t->ok(!$details->canSetStockDebutMois(), "Le stock début de mois n'est pas éditable");
$details->sorties->ventefrancecrd = 100;
$drm->update();
$drm->save();
$drm->validate();
$drm->save();
$t->is($details->stocks_fin->final, 800, "Le stock de fin de mois est cohérent");
$t->is(count(DRMClient::getInstance()->generateVersionCascade($drm)), 0, "La génération en cascade impacte aucune DRM");
$drm02Id = $drm->_id;


$t->comment("Création d'une DRM saisie en Mars");

$periode = (date('Y'))."03";
$drm = DRMClient::getInstance()->createDoc($nego2->identifiant, $periode);
$drm->save();

$t->is($drm->_get('precedente'), $drm02Id, "La drm précédente est stocké");
$t->ok(!$drm->isImport() && $drm->type_creation == DRMClient::DRM_CREATION_VIERGE, "La drm est en création vierge");

$details = $drm->get($produit_hash.'/details/DEFAUT');
$t->ok($details->canSetStockDebutMois(), "Le stock début est éditable");
$details->stocks_debut->initial = 2000;
$details->sorties->ventefrancecrd = 100;
$drm->update();
$drm->save();
$drm->validate();
$drm->save();

$t->is($details->stocks_fin->final, 1900, "Le stock de fin de mois est cohérent");
$t->is(count(DRMClient::getInstance()->generateVersionCascade($drm)), 0, "La génération en cascade impacte aucune DRM");
$drm03Id = $drm->_id;


$t->comment("Création d'une DRM saisie en Mai");

$periode = (date('Y'))."05";
$drm = DRMClient::getInstance()->createDoc($nego2->identifiant, $periode);
$drm->save();

$t->is($drm->_get('precedente'), null, "La drm précédente n'est stocké");
$t->ok(!$drm->isImport() && $drm->type_creation == DRMClient::DRM_CREATION_VIERGE, "La drm est en création vierge");

$details = $drm->get($produit_hash.'/details/DEFAUT');
$t->ok($details->canSetStockDebutMois(), "Le stock début est éditable");
$details->stocks_debut->initial = 1800;
$details->stocks_debut->dont_revendique = -400;
$details->sorties->ventefrancecrd = 100;
$drm->update();
$drm->save();
$drm->validate();
$drm->save();

$t->is($details->stocks_fin->final, 1700, "Le stock de fin de mois est cohérent");
$t->is(count(DRMClient::getInstance()->generateVersionCascade($drm)), 0, "La génération en cascade impacte aucune DRM");
$drm05Id = $drm->_id;


$t->comment("Création d'une DRM saisie en Avril");

$periode = (date('Y'))."04";
$drm = DRMClient::getInstance()->createDoc($nego2->identifiant, $periode);
$drm->save();

$t->is($drm->_get('precedente'), $drm03Id, "La drm précédente est stocké");
$t->ok(!$drm->isImport() && $drm->type_creation == DRMClient::DRM_CREATION_VIERGE, "La drm est en création vierge");

$details = $drm->get($produit_hash.'/details/DEFAUT');
$t->ok(!$details->canSetStockDebutMois(), "Le stock début n'est pas éditable");
$details->sorties->ventefrancecrd = 100;
$drm->update();
$drm->save();
$drm->validate();
$drm->save();

$t->is($details->stocks_fin->final, 1800, "Le stock de fin de mois est cohérent");
$t->is(count(DRMClient::getInstance()->generateVersionCascade($drm)), 0, "La génération en cascade impacte aucune DRM");
$drm04Id = $drm->_id;
$t->is(DRMClient::getInstance()->find($drm05Id)->_get('precedente'), $drm04Id, "La drm précédente est stocké");


$t->comment("Création d'une DRM télédéclaré en Juin");

$periode = (date('Y'))."06";
$drm = DRMClient::getInstance()->createDoc($nego2->identifiant, $periode, true);
$drm->save();

$t->is($drm->_get('precedente'), $drm05Id, "La drm précédente est stocké");
$t->ok($drm->isTeledeclare(), "La drm est télédéclaré");

$details = $drm->get($produit_hash.'/details/DEFAUT');
$t->ok($details->canSetStockDebutMois(), "Le stock début est éditable");
$details->stocks_debut->initial = 3000;
$details->sorties->ventefrancecrd = 100;
$drm->update();
$drm->save();
$drm->validate();
$drm->save();

$t->is($details->stocks_fin->final, 2900, "Le stock de fin de mois est cohérent");
$t->is(count(DRMClient::getInstance()->generateVersionCascade($drm)), 0, "La génération en cascade impacte aucune DRM");
$drm06Id = $drm->_id;


$t->comment("Création d'une DRM télédéclaré en Juillet");

$periode = (date('Y'))."07";
$drm = DRMClient::getInstance()->createDoc($nego2->identifiant, $periode, true);
$drm->save();

$t->is($drm->_get('precedente'), $drm06Id, "La drm précédente est stocké");
$t->ok($drm->isTeledeclare(), "La drm est télédéclaré");

$details = $drm->get($produit_hash.'/details/DEFAUT');
$t->ok(!$details->canSetStockDebutMois(), "Le stock début n'est pas éditable");
$details->sorties->ventefrancecrd = 100;
$drm->update();
$drm->save();
$drm->validate();
$drm->save();

$t->is($details->stocks_fin->final, 2800, "Le stock de fin de mois est cohérent");
$t->is(count(DRMClient::getInstance()->generateVersionCascade($drm)), 0, "La génération en cascade impacte aucune DRM");
$drm07Id = $drm->_id;

$t->comment("Création d'une DRM télédéclaré en Aout");

$periode = (date('Y'))."08";
$drm = DRMClient::getInstance()->createDoc($nego2->identifiant, $periode, true);
$drm->save();

$t->is($drm->_get('precedente'), null, "La drm précédente n'est pas stocké");
$t->ok($drm->isTeledeclare(), "La drm est télédéclaré");

$details = $drm->get($produit_hash.'/details/DEFAUT');
$t->ok($details->canSetStockDebutMois(), "Le stock début est éditable");
$details->stocks_debut->initial = 1000;
$details->sorties->ventefrancecrd = 50;

$drm->addProduit($produit2_hash, 'details');
$details2 = $drm->get($produit2_hash.'/details/DEFAUT');
$details2->stocks_debut->initial = 4000;
$details2->sorties->ventefrancecrd = 100;

$drm->update();
$drm->save();
$drm->validate();
$drm->save();

$t->is($drm->get($produit_hash.'/details/DEFAUT')->stocks_fin->final, 950, "Le stock de fin de mois du 1er produit est cohérent");
$t->is($drm->get($produit2_hash.'/details/DEFAUT')->stocks_fin->final, 3900, "Le stock de fin de mois du 2ème produit est cohérent");
$t->is(count(DRMClient::getInstance()->generateVersionCascade($drm)), 0, "La génération en cascade impacte aucune DRM");
$drm08Id = $drm->_id;

$t->comment("Création d'une DRM télédéclaré en Septembre");

$periode = (date('Y'))."09";
$drm = DRMClient::getInstance()->createDoc($nego2->identifiant, $periode, true);
$drm->save();

$t->is($drm->_get('precedente'), $drm08Id, "La drm précédente est stocké");
$t->ok($drm->isTeledeclare(), "La drm est télédéclaré");

$details = $drm->get($produit_hash.'/details/DEFAUT');
$details->sorties->ventefrancecrd = 150;

$drm->update();
$drm->save();
$drm->validate();
$drm->save();

$t->is($drm->get($produit_hash.'/details/DEFAUT')->stocks_fin->final, 800, "Le stock de fin de mois est cohérent");
$t->is($drm->get($produit2_hash.'/details/DEFAUT')->stocks_fin->final, 3900, "Le stock de fin de mois du 2ème produit est cohérent");
$t->is(count(DRMClient::getInstance()->generateVersionCascade($drm)), 0, "La génération en cascade impacte aucune DRM");
$drm09Id = $drm->_id;

$t->comment("Création d'une modificatrice pour la DRM de Janvier");

$drm = DRMClient::getInstance()->find($drm01Id)->generateModificative();
$drm->save();
$t->is($drm->_get('precedente'), null, "La drm précédente n'est pas stocké");
$t->ok($drm->isImport(), "La drm modificatrice est importé");

$details = $drm->get($produit_hash.'/details/DEFAUT');
$t->ok($details->canSetStockDebutMois(), "Le stock début est éditable");
$details->stocks_debut->initial = $details->stocks_debut->initial + 500;
$details->sorties->ventefrancecrd = 99;
$drm->update();
$drm->save();
$drm->validate();
$drm->save();

$t->is($details->stocks_fin->final, 1401, "Le stock de fin de mois est cohérent");
$cascades = DRMClient::getInstance()->generateVersionCascade($drm);
$t->ok(count($cascades) == 1 && $cascades[0] == $drm02Id."-M01", "La génération en cascade génère une modificatrice pour la DRM de février uniquement");


$t->comment("Test sur la modificatrice de Février");

$drm = DRMClient::getInstance()->getMaster($drm02Id);
$t->is($drm->_id, $drm02Id."-M01", "La master est la M01");
$t->is($drm->_get('precedente'), $drm01Id."-M01", "La drm précédente est la M01 d'Avril");
$t->is($drm->get($produit_hash.'/details/DEFAUT')->stocks_fin->final, 1301, "La modification de stock a été répercuté sur la DRM de février");


$t->comment("Création d'une modificatrice pour la DRM de Mars");

$drm = DRMClient::getInstance()->find($drm03Id)->generateModificative();
$drm->save();
$t->is($drm->_get('precedente'), $drm02Id, "La drm précédente n'est pas stocké");
$t->ok(!$drm->isImport() && $drm->type_creation == DRMClient::DRM_CREATION_VIERGE, "La drm modificatrice est importé");

$details = $drm->get($produit_hash.'/details/DEFAUT');
$t->ok($details->canSetStockDebutMois(), "Le stock début est éditable");
$details->stocks_debut->initial = $details->stocks_debut->initial + 500;
$details->sorties->ventefrancecrd = 99;
$drm->update();
$drm->save();
$drm->validate();
$drm->save();

$t->is($details->stocks_fin->final, 2401, "Le stock de fin de mois est cohérent");
$cascades = DRMClient::getInstance()->generateVersionCascade($drm);
$t->is(count($cascades), 2, "La génération en cascade génère 2 modificatrices");
$t->is($cascades[0], $drm04Id."-M01", "La génération en cascade génère une modificatrice pour les DRM d'avril");
$t->is($cascades[1], $drm05Id."-M01", "La génération en cascade génère une modificatrice pour les DRM de mai");

$t->comment("Test sur la modificatrice d'Avril");

$drm = DRMClient::getInstance()->getMaster($drm04Id);
$t->is($drm->_id, $drm04Id."-M01", "La master est la M01");
$t->is($drm->_get('precedente'), $drm03Id."-M01", "La drm précédente est la M01 de Mars");
$t->is($drm->get($produit_hash.'/details/DEFAUT')->stocks_fin->final, 2301, "La modification de stock a été répercuté sur la DRM d'avril");

$t->comment("Test sur la modificatrice de Mai");

$drm = DRMClient::getInstance()->getMaster($drm05Id);
$t->is($drm->_id, $drm05Id."-M01", "La master est la M01");
$t->is($drm->_get('precedente'), $drm04Id."-M01", "La drm précédente est la M01 d'Avril");
$t->is($drm->get($produit_hash.'/details/DEFAUT')->stocks_fin->final, 2201, "La modification de stock a été répercuté sur la DRM de mai");

$t->comment("Création d'une modificatrice pour la DRM de Juin");

$drm = DRMClient::getInstance()->find($drm06Id)->generateModificative();
$drm->save();
$t->is($drm->_get('precedente'), $drm05Id, "La drm précédente est stocké");
$t->ok($drm->isTeledeclare(), "La drm modificatrice est télédeclaré");

$details = $drm->get($produit_hash.'/details/DEFAUT');
$t->ok($details->canSetStockDebutMois(), "Le stock début est éditable");
$details->stocks_debut->initial = $details->stocks_debut->initial + 500;
$details->sorties->ventefrancecrd = 99;
$drm->update();
$drm->save();
$drm->validate();
$drm->save();

$t->is($details->stocks_fin->final, 3401, "Le stock de fin de mois est cohérent");
$cascades = DRMClient::getInstance()->generateVersionCascade($drm);
$t->ok(count($cascades) == 1 && $cascades[0] == $drm07Id."-M01", "La génération en cascade génère une modificatrice pour la DRM de Juillet uniquement");

$t->comment("Test sur la modificatrice de Juillet");

$drm = DRMClient::getInstance()->getMaster($drm07Id);
$t->is($drm->_id, $drm07Id."-M01", "La master est la M01");
$t->is($drm->_get('precedente'), $drm06Id."-M01", "La drm précédente est la M01 de juin");
$t->is($drm->get($produit_hash.'/details/DEFAUT')->stocks_fin->final, 3301, "La modification de stock a été répercuté sur la DRM de février");

$t->comment("Création d'une modificatrice pour la DRM d'Août en intervertissant les sorties des produits");

$drm = DRMClient::getInstance()->find($drm08Id)->generateModificative();
$drm->save();
$t->is($drm->_get('precedente'), null, "La drm précédente n'est pas stocké");
$t->ok($drm->isTeledeclare(), "La drm modificatrice est télédeclaré");

$details = $drm->get($produit_hash.'/details/DEFAUT');
$details->sorties->ventefrancecrd = 100;

$details2 = $drm->get($produit2_hash.'/details/DEFAUT');
$details2->sorties->ventefrancecrd = 50;

$drm->update();
$drm->save();
$drm->validate();
$drm->save();

$cascades = DRMClient::getInstance()->generateVersionCascade($drm);
$t->ok(count($cascades) == 1 && $cascades[0] == $drm09Id."-M01", "La génération en cascade génère une modificatrice pour la DRM de septembre");
