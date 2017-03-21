<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');
sfContext::createInstance($configuration);

$t = new lime_test(24);
$t->comment("Création d'une facture à partir des DRM pour une société");

$paramFacturation =  array(
    "modele" => "DRM",
    "date_facturation" => date('Y').'-08-01',
    "date_mouvement" => null,
    "type_document" => GenerationClient::TYPE_DOCUMENT_FACTURES,
    "message_communication" => null,
    "seuil" => null,
);

$societeViti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getSociete();

$mouvementsFacture = array($societeViti->identifiant => FactureClient::getInstance()->getFacturationForSociete($societeViti));
$mouvementsFacture = FactureClient::getInstance()->filterWithParameters($mouvementsFacture, $paramFacturation);
$prixHt = 0.0;
foreach ($mouvementsFacture[$societeViti->identifiant] as $mvt) {
  $prixHt += $mvt->value[MouvementfactureFacturationView::VALUE_VOLUME] * $mvt->value[MouvementfactureFacturationView::VALUE_CVO];
}
$prixHt = $prixHt * -1;
$prixTaxe = $prixHt * 0.2;
$prixTTC = $prixHt+$prixTaxe;

$facture = FactureClient::getInstance()->createAndSaveFacturesBySociete($societeViti, $paramFacturation);

$facture->save();
$t->ok($facture, "La facture est créée");

$t->is($facture->identifiant, $societeViti->identifiant, "La facture appartient à la société demandé");

$t->is($facture->total_ht, $prixHt, "Le total HT est de ".$prixHt." €");
$t->is($facture->total_ttc, $prixTTC, "Le total TTC est de ".$prixTTC."  €");
$t->is($facture->total_taxe, $prixTaxe, "Le total de taxe est de ".$prixTaxe."  €");

$nbLignes = 0;
$doublons = 0;
foreach($facture->lignes as $lignes) {
    $libellesUnique = array();
    foreach($lignes->details as $ligne) {
        $nbLignes++;
        if(array_key_exists($ligne->libelle . $ligne->origine_type, $libellesUnique)) {
            $doublons++;
        }
        $libellesUnique[$ligne->libelle . $ligne->origine_type] = 1;
    }
}

$t->ok(!$doublons, "Aucune ligne (par libellé) en doublon");

if($application == "ivbd") {
    $t->is($nbLignes, 2, "La facture à 2 lignes");
} elseif($application == "bivc") {
    $t->is($nbLignes, 2, "La facture à 2 lignes");
} else {
    $t->is($nbLignes, 3, "La facture à 3 lignes");
}

if($application == "ivbd") {
    $t->is($facture->campagne, (date('Y')+1)."", "La campagne est de la facture est sur l'année viticole");
} else {
    $t->is($facture->campagne, date('Y'), "La campagne est l'année courante");
}

$t->ok(preg_match("/^[0-9]{5}$/", $facture->numero_archive), "Le numéro d'archive a été créé et est composé de 5 chiffres");

if($application == "ivbd") {
    $t->is($facture->numero_piece_comptable, "1".substr($facture->campagne, -2).$facture->numero_archive, "Le numéro de facture est bien formé");
} elseif($application == "ivso") {
    $t->is($facture->numero_piece_comptable, "C".substr($facture->campagne, -2).$facture->numero_archive, "Le numéro de facture est bien formé");
} else {
    $t->is($facture->numero_piece_comptable, substr($facture->campagne, -2).$facture->numero_archive, "Le numéro de facture est bien formé");
}

$t->is($facture->versement_comptable, 0, "La facture n'est pas versé comptablement");

$generation = FactureClient::getInstance()->createGenerationForOneFacture($facture);

$t->ok($generation, "La génération est créée");

$t->comment("Test d'une nouvelle facturation sur la société pour s'assurer qu'aucune facture ne sera créée");

$t->ok(!FactureClient::getInstance()->createAndSaveFacturesBySociete($societeViti, $paramFacturation), "La facture n'a pas été créée");

$t->comment("Création d'un avoir à partir de la facture");

$t->ok($facture->isRedressable(), "La facture est redressable");

$prixHt = $prixHt * -1;
$prixTaxe = $prixHt * 0.2;
$prixTTC = $prixHt+$prixTaxe;

$avoir = FactureClient::getInstance()->defactureCreateAvoirAndSaveThem($facture);
$t->ok($avoir, "L'avoir est créé");
$t->is($avoir->identifiant, $societeViti->identifiant, "L'avoir appartient à la même société que la facture");
$t->ok($avoir->isAvoir(), "L'avoir est marqué comme un avoir");
$t->is($avoir->total_ht, $prixHt, "Le total TTC est de ".$prixHt." €");
$t->is($avoir->total_ttc, $prixTTC, "Le total TTC est de ".$prixTTC." €");
$t->is($avoir->total_taxe, $prixTaxe, "Le total TTC est de ".$prixTaxe." €");
$t->ok(!$avoir->isRedressable(), "L'avoir n'est pas redressable");

$t->is($facture->avoir, $avoir->_id, "L'avoir est conservé dans la facture");
$t->ok($facture->isRedressee(), "La facture est au statut redressé");
$t->ok(!$facture->isRedressable(), "La facture n'est pas redressable");
