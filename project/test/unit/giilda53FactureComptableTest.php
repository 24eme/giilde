<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');
sfContext::createInstance($configuration);

$prefixComptable = null;
$codeCompteEcheance = null;
$codeCompteTVA = null;
$codeCompteLigne = null;
$nbLignes = 2;

if($application == "ivso") {
    $prefixComptable = "02";
    $codeCompteEcheance = "41100000";
    $codeCompteTVA = "44571502";
}

if($application == "ivbd") {
    $nbLignes = 1;
    $prefixComptable = "VEN";
    $codeCompteEcheance = "41110000";
    $codeCompteLigne = "75815000";
}

if($application == "bivc") {
    $prefixComptable = "VT";
    $codeCompteLigne = "75800000";
    $codeCompteEcheance = "41100000";
    $codeCompteTVA = "44571000";
}

if($application == "civa") {
    $t = new lime_test(0);
    exit(0);
}


$societeViti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getSociete();
$facture = null;
foreach (FactureSocieteView::getInstance()->findBySociete($societeViti) as $id => $facture) {
    $facture = FactureClient::getInstance()->find($id);
}

if($facture){
  foreach ($facture->lignes as $id => $mvt ) {
    $nbLignes += count($mvt->details);
  }
  $export = ExportFactureCSVFactory::getObject($application);
  ob_start();
  $export->printFacture($facture->_id);
  $exportCompta = ob_get_contents();
  ob_end_clean();
}

$t = new lime_test(1 + $nbLignes * 9);
$t->comment("Création d'un export de facturation à partir des facture pour une société");


$arrayCompta = explode("\n",$exportCompta);

$nbLignesCSV = 0;
foreach ($arrayCompta as $cpt => $row) {
    if(!$row){
      continue;
    }
    $fieldArray = explode(";",$row);

    $t->is(count($fieldArray), 23, "Le nombre de champs pour la ligne $cpt du fichier de compta est bien ".count($fieldArray));
    $t->is($fieldArray[0], $prefixComptable, "Le préfix est ".$prefixComptable);

    $dateF = preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',$fieldArray[1]);
    $t->ok($dateF, "La date de facturation a le bon format");

    $dateF = preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',$fieldArray[2]);
    $t->ok($dateF, "La date d'emission a le bon format");

    $t->is($facture->numero_piece_comptable, $fieldArray[3], "Le numéro pièce comptable est celui de la facture");

    $desc = $fieldArray[4];
    $t->ok($desc, "Il y a une description comptable $desc ");

    $codeCompte = $fieldArray[5];
    $prix = $fieldArray[10];
    $sens = $fieldArray[9];

    $found = false;
    if($fieldArray[14] == "LIGNE") {
        foreach ($facture->lignes as $k => $lignes) {
            foreach ($lignes->details as $detail) {
              if((($detail->exist('code_compte') && $detail->code_compte == $codeCompte) || $codeCompteLigne == $codeCompte) && ($detail->montant_ht == $prix)){
                $found = $detail->getHash();
              }
            }
        }
        $resMontantCodeCompte = boolval($found);
        $t->ok($resMontantCodeCompte, "Le code compte $codeCompte de la ligne correspond à la ligne $found de la facture $facture->_id");
        $t->ok($resMontantCodeCompte, "Le montant $prix de la ligne correspond à la ligne $found de la facture $facture->_id");
        $t->is($sens, "CREDIT", "La ligne est un CREDIT");
    }

    if($fieldArray[14] == "TVA") {
        $t->is($codeCompte, $codeCompteTVA, "Le code compte $codeCompte de la ligne ECHEANCE est correct");
        $t->is($prix, $facture->total_taxe, "Le montant $prix de la ligne ECHEANCHE est correct");
        $t->is($sens, "CREDIT", "La ligne est un CREDIT");
    }

    if($fieldArray[14] == "ECHEANCE") {
        $t->is($codeCompte, $codeCompteEcheance, "Le code compte $codeCompte de la ligne ECHEANCE est correct");
        $t->is($prix, $facture->total_ttc, "Le montant $prix de la ligne ECHEANCHE est correct");
        $t->is($sens, "DEBIT", "Le sens de la ligne ECHEANCE est DEBIT");
    }

    $nbLignesCSV++;
}

$t->is($nbLignesCSV, $nbLignes, "Le nombre de ligne pour la compta est ".$nbLignes);
