<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');
sfContext::createInstance($configuration);

if(!count(ComptabiliteClient::getInstance()->findCompta()->getAllIdentifiantsAnalytiquesArrayForCompta())) {
    $t = new lime_test(0);
    exit(0);
}

$t = new lime_test(13);

if($doc = MouvementsFactureClient::getInstance()->find("MOUVEMENTSFACTURE-TEST")) {
    $doc->delete();
}

$keyCompta = key(ComptabiliteClient::getInstance()->findCompta()->getAllIdentifiantsAnalytiquesArrayForCompta());

$t->comment("Création d'un document de mouvements de facturation libre");

$societeViti = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getSociete();

$docMouvementsLibres = MouvementsFactureClient::getInstance()->createMouvementsFacture();
$docMouvementsLibres->set('_id', "MOUVEMENTSFACTURE-TEST");

$form = new FactureMouvementsEditionForm($docMouvementsLibres);

$values["libelle"] = "Test opération";
$values["mouvements"]["nouveau_1"]["identifiant"] = $societeViti->_id;
$values["mouvements"]["nouveau_1"]["identifiant_analytique"] = $keyCompta;
$values["mouvements"]["nouveau_1"]["libelle"] = "Bouchons";
$values["mouvements"]["nouveau_1"]["prix_unitaire"] = 1.50;
$values["mouvements"]["nouveau_1"]["quantite"] = 10.00;
$values["mouvements"]["nouveau_2"]["identifiant"] = $societeViti->_id;
$values["mouvements"]["nouveau_2"]["identifiant_analytique"] = $keyCompta;
$values["mouvements"]["nouveau_2"]["libelle"] = "Médailles";
$values["mouvements"]["nouveau_2"]["prix_unitaire"] = 3.00;
$values["mouvements"]["nouveau_2"]["quantite"] = 5.00;
$values["_revision"] = $docMouvementsLibres->_rev;

$form->bind($values);

$t->ok($form->isValid(), "Le formulaire est valide");

$form->save();

$totalHT = 30;
$totalTTC = 36;
$totalTaxe = 6;

$t->ok($docMouvementsLibres->_rev, "Le document a été enregistré");
$t->is($docMouvementsLibres->libelle, "Test opération", "Le libellé est bien enregistré");
$t->is($docMouvementsLibres->getNbMvts(), 2, "Le document à 2 mouvements de facturation");
$t->is($docMouvementsLibres->getNbSocietes(), 1, "La document à 1 société");
$t->is($docMouvementsLibres->getTotalHt(), $totalHT, "Le montant total HT est de 30 €");
$t->is($docMouvementsLibres->getTotalHtAFacture(), $totalHT, "Le montant total HT à facturer est de 30 €");

$t->comment("Génération de la facture");

$paramFacturation =  array(
    "modele" => "MouvementsFacture",
    "date_facturation" => date('Y-m-d'),
    "date_mouvement" =>  null,
    "type_document" => GenerationClient::TYPE_DOCUMENT_FACTURES,
    "message_communication" => null,
    "seuil" => null,
);

$mouvementsFacture = array($societeViti->identifiant => FactureClient::getInstance()->getFacturationForSociete($societeViti));
$mouvementsFacture = FactureClient::getInstance()->filterWithParameters($mouvementsFacture, $paramFacturation);

$facture = FactureClient::getInstance()->createAndSaveFacturesBySociete($societeViti, $paramFacturation);
$facture->save();

$t->is($facture->total_ht, $totalHT, "Le total TTC est de ".$totalHT." €");
$t->is($facture->total_ttc, $totalTTC, "Le total TTC est de ".$totalTTC." €");
$t->is($facture->total_taxe, $totalTaxe, "Le total TTC est de ".$totalTaxe." €");

$nbLignes = 0;
foreach($facture->lignes as $lignes) {
    foreach($lignes->details as $ligne) {
        $nbLignes++;
    }
}

$t->is($nbLignes, 2, "La facture à 2 lignes");

$generation = FactureClient::getInstance()->createGenerationForOneFacture($facture);

$t->ok($generation, "La génération est créée");

$t->comment("Test d'une nouvelle facturation sur la société pour s'assurer qu'aucune facture ne sera pas créée");

$t->ok(!FactureClient::getInstance()->createAndSaveFacturesBySociete($societeViti, $paramFacturation), "La facture n'a pas été créée");
