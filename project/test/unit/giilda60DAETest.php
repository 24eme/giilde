<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');
sfContext::createInstance($configuration);

$conf = ConfigurationClient::getInstance()->getCurrent();


$t = new lime_test(3);


$t->comment("Création d'un DAE pour un viti");

$societeViti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getSociete();
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

$identifiant = $viti->identifiant;
$daesViti = DAEClient::getInstance()->findByIdentifiant($identifiant);
foreach ($daesViti as $dae) {
    $d = DAEClient::getInstance()->find($dae->_id);
    $d->delete();
}

$dateSortie = date('Y-m-d');

$produit_hash = null;
foreach(ConfigurationClient::getInstance()->getCurrent()->getProduits() as $produit) {
    $produit_hash = $produit->getHash();
    break;
}
$periode = date('Ym');


$type_acheteur = DAEClient::ACHETEUR_TYPE_IMPORTATEUR;
$destination = "France";
$millesime = "2016";
$volume = 36.15;
$contenant = 'Bouteille 37cl';
$prix_ht = 250.25;
$label = "Chimique";

$dae = DAEClient::getInstance()->createDAE($identifiant,$dateSortie,$produit_hash,$type_acheteur,$destination,$millesime,$volume,$contenant,$prix_ht,$label);
$dae->save();

$daes = DAEClient::getInstance()->findByIdentifiantAndDate($identifiant,$dateSortie)->getDatas();

$t->is(count($daes), 1, "Un DAE a été enregistré pour $identifiant");
$dae_h = array_shift($daes);
$t->is($dae_h->_id, "DAE-".$identifiant."-".str_ireplace("-",'',$dateSortie)."-001", "Le DAE a bien pour identifiant DAE-".$identifiant."-".$dateSortie."-001");

$t->is($dae_h->produit_hash, $produit_hash, "Le produit enregistré est bien X");
