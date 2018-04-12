<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');
sfContext::createInstance($configuration);

$t = new lime_test(25);

$t->comment("Création d'une facture à partir des SV12 pour une société");

$paramFacturation =  array(
    "modele" => "SV12",
    "date_facturation" => date('Y').'-01-01',
    "date_mouvement" => null,
    "type_document" => GenerationClient::TYPE_DOCUMENT_FACTURES,
    "message_communication" => null,
    "seuil" => null,
);

$societeViti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getSociete();

$facture = FactureClient::getInstance()->createAndSaveFacturesBySociete($societeViti, $paramFacturation);

$facture->save();
$t->ok($facture, "La facture est créé");
$t->is($facture->identifiant, $societeViti->identifiant, "La facture appartient à la société demandé");
$t->ok($facture->emetteur->adresse, "L'adresse de l'emetteur est rempli");
