<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');
sfContext::createInstance($configuration);

if(!SV12Configuration::getInstance()->isActif()) {
    $t = new lime_test(0);
    exit(0);
}

$t = new lime_test(4);

$societeViti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getSociete();
$t->comment("Création d'une facture à partir des SV12 pour la société ".$societeViti);

$paramFacturation =  array(
    "modele" => "SV12",
    "date_facturation" => date('Y').'-01-01',
    "date_mouvement" => null,
    "type_document" => GenerationClient::TYPE_DOCUMENT_FACTURES,
    "message_communication" => null,
    "seuil" => null,
);


$facture = FactureClient::getInstance()->createAndSaveFacturesBySociete($societeViti, $paramFacturation);
$facture->save();

$t->ok($facture, "La facture est créé pour ".$societeViti);
$t->is($facture->identifiant, $societeViti->identifiant, "La facture appartient à la société demandé");
$t->ok($facture->emetteur->adresse, "L'adresse de l'emetteur est rempli");

$t->comment("Création d'un avoir à partir de la facture de ".$societeViti);
$avoir = FactureClient::getInstance()->defactureCreateAvoirAndSaveThem($facture);
$t->is($avoir->total_ht, $facture->total_ht * -1, "l'avoir a en valeur absolu le même total");
