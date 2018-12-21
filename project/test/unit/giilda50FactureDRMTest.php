<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');
sfContext::createInstance($configuration);

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

$t = new lime_test(31);

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
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
foreach(DRMClient::getInstance()->viewByIdentifiant($viti->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
}

$produits = array_keys($conf->getProduits());
$produit_hash = array_shift($produits);
$periode = date('Ym');
$drm = DRMClient::getInstance()->createDoc($viti->identifiant, $periode, true);
$details = $drm->addProduit($produit_hash, 'details');
$details->stocks_debut->initial = 1000;
$details->sorties->ventefrancecrd = 200;
$drm->update();
$drm->save();
$drm->validate();
$drm->save();

$mouvementsFactureMasse = FactureClient::getInstance()->getMouvementsNonFacturesBySoc(FactureClient::getInstance()->getMouvementsForMasse(null));
$mouvementsFactureMasse = FactureClient::getInstance()->filterWithParameters($mouvementsFactureMasse, array_merge($paramFacturation, array('seuil' => 999999)));

$t->is(count($mouvementsFactureMasse), 0, "Avec un seuil à 99999 aucune société à facturer");

$mouvementsFactureMasse = FactureClient::getInstance()->getMouvementsNonFacturesBySoc(FactureClient::getInstance()->getMouvementsForMasse(null));
$mouvementsFactureMasse = FactureClient::getInstance()->filterWithParameters($mouvementsFactureMasse, $paramFacturation);

$mouvementsFacture = array($societeViti->identifiant => FactureClient::getInstance()->getFacturationForSociete($societeViti));
$mouvementsFacture = FactureClient::getInstance()->filterWithParameters($mouvementsFacture, $paramFacturation);

$t->is(json_encode($mouvementsFactureMasse[$societeViti->identifiant]), json_encode($mouvementsFacture[$societeViti->identifiant]), "La méthode récupération massive des mouvements est ok");

$prixHt = 0.0;
$nbmvt = 0;
if(isset($mouvementsFacture[$societeViti->identifiant])) {
foreach ($mouvementsFacture[$societeViti->identifiant] as $mvt) {
  $prixHt += $mvt->prix_ht;
  $nbmvt++;
}
}
$prixHt = $prixHt;
$prixTaxe = $prixHt * 0.2;
$prixTTC = $prixHt+$prixTaxe;

$facture = FactureClient::getInstance()->createAndSaveFacturesBySociete($societeViti, $paramFacturation);

$facture->save();
$t->ok($facture, "La facture est créée");

$t->is($facture->identifiant, $societeViti->identifiant, "La facture appartient à la société demandé");

$t->ok($facture->emetteur->adresse, "L'adresse de l'emetteur est rempli");

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

$t->is($nbLignes, $nbmvt, "La facture à ".$nbmvt." lignes");

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

if($application == 'ivso' && false) {
  $md5sumAttendu = "d65f0fd40ecbecd6c5b12b6931bda08f";
  $facture->campagne = "2017";
  $facture->numero_piece_comptable = "C1700006";
  $facture->numero_archive = "00006";
  $facture->date_facturation = '2017-08-01';
  $facture->date_echeance = '2017-08-31';
  $facture->code_comptable_client = '2743';
  $facture->identifiant = '002743';
  $facture->numero_adherent = '002743';

  foreach($facture->getLignes() as $key => $ligne){
     $ligne->libelle = "DRM de mars 2017";
  }
  $factureLatex = new FactureLatex($facture);
  $latexFileName = $factureLatex->getLatexFile();
  $md5sum = md5_file($latexFileName);
  $t->is($md5sumAttendu, $md5sum, "Le md5 du pdf doit être le suivant ".$md5sumAttendu." path = ".$latexFileName);
}else{
  $t->is(1, 1, "Pas de test sur la structure latex");
}

$t->comment("Facturation createOrigine");

$mouvement = new stdClass();
$mouvement->origine = FactureClient::FACTURE_LIGNE_ORIGINE_TYPE_DRM;
$mouvement->vrac_destinataire = "Test destinaire";
$mouvement->detail_libelle = "000001";
$mouvement->vrac_destinataire = date('Ymd')."000001";
$mouvement->vrac_numero = "VRAC-2018000099999";

if($application == "ivso") {
    $origine = (FactureConfiguration::getInstance()->isPdfProduitFirst()) ? "Contrat ".$mouvement->vrac_destinataire : "Contrat n° 99999 (".$mouvement->vrac_destinataire.") ";
} else {
    $origine = (FactureConfiguration::getInstance()->isPdfProduitFirst()) ? "Contrat ".$mouvement->vrac_destinataire : "Contrat n° ".$mouvement->detail_libelle." (".$mouvement->vrac_destinataire.") ";
}

$t->is(MouvementfactureFacturationView::getInstance()->createOrigine(SocieteClient::TYPE_OPERATEUR, $mouvement), $origine, "Le calcule de l'origine est correct");

$t->comment("Facturation d'une DRM négo");

$societeNego = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_region')->getSociete();
$nego = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_region')->getEtablissement();

foreach(DRMClient::getInstance()->viewByIdentifiant($nego->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
}


$drm = DRMClient::getInstance()->createDoc($nego->identifiant, $periode, true);
$drm->save();

$details = $drm->addProduit($produit_hash, 'details');
$details->entrees->recolte = 100;
$drm->update();
$drm->validate();
$drm->save();

$prixHt = round($details->entrees->recolte / 12, 4) * $details->getCVOTaux();

$paramFacturation["date_mouvement"] = preg_replace("/([0-9]{4})([0-9]{2})/", '\1-\2-31', $periode);
$facture = FactureClient::getInstance()->createAndSaveFacturesBySociete($societeNego, $paramFacturation);

if($hasCVONegociant) {
    $t->ok($facture, "La facture est créée");
    $t->is($facture->total_ht, round($prixHt, 4), "Le total HT est de ".$prixHt." €");
} else {
    $t->ok(!$facture, "La facture n'est pas créée");
    $t->pass("Rien à facturer");
}
