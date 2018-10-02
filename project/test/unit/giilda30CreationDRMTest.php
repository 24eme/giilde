<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(42);
$t->comment("création d'une DRM avec des sorties facturables et non");

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$produits = array_keys(ConfigurationClient::getInstance()->getCurrent()->getProduits());
$produit_hash = array_shift($produits);
$periode = date('Ym');

//Suppression des DRM précédentes
foreach(DRMClient::getInstance()->viewByIdentifiant($viti->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
}

$nb_mouvements_facture = count(MouvementfactureFacturationView::getInstance()->getMouvementsNonFacturesBySociete($viti->getSociete()));

// Début des tests

$drm = DRMClient::getInstance()->createDoc($viti->identifiant, $periode, true);
$drm->save();
$t->isnt($drm->periode, null, $drm->_id." : période indiquée");
$t->isnt($drm->declarant->raison_sociale, null, $drm->_id." : raison sociale du déclaration renseignée");

$details = $drm->addProduit($produit_hash, 'details');
$details->stocks_debut->initial = 1000;
$drm->update();
$drm->save();
$t->is($drm->getProduit($produit_hash, 'details')->get('stocks_fin/final'), 1000, $drm->_id." : vérification du stock final");

$details->sorties->ventefrancecrd = 200;
$details->sorties->destructionperte = 100;
$drm->update();
$drm->save();
$t->is($drm->getProduit($produit_hash, 'details')->get('stocks_fin/final'), 700, $drm->_id." : le stock final est impacté par les sorties de 300hl");

$export = DRMESDetailExport::freeInstance($drm);
$export->identifiant = 'BE';
$export->volume = 50;
$details->sorties->export_details->addDetail($export);
$export_key1 = $export->getKey();

$export = DRMESDetailExport::freeInstance($drm);
$export->identifiant = 'BE';
$export->volume = 50;
$details->sorties->export_details->addDetail($export);
$export_key2 = $export->getKey();
$t->is($drm->getProduit($produit_hash, 'details')->get("sorties/export_details")->get($export_key2)->getKey(), $export_key2, $drm->_id." : les clés d'export sont conservées");

$drm->update();
$drm->save();

$drm = DRMClient::getInstance()->findMasterByIdentifiantAndPeriode($viti->identifiant, $periode);
$t->is($drm->getProduit($produit_hash, 'details')->get('stocks_fin/final'), 600, $drm->_id." : le stock final est impacté par les sorties de 450hl");
$t->is(count($drm->getProduit($produit_hash, 'details')->get('sorties/export_details')), 2, $drm->_id." : la DRM a bien 2 sorties export");

$t->comment("validation de la DRM et génération des mouvements");
$drm->validate();
$drm->save();

$mvts_viti = $drm->mouvements->{$drm->identifiant};
$t->is(count($mvts_viti) * count($drm->mouvements), 4, $drm->_id." : la validation a généré trois mouvements chez le viti");
$mvt_export = null;
foreach ($mvts_viti as $mvt) {
  if ($mvt->type_hash == 'sorties/ventefrancecrd') {
    $mvt_crd = $mvt;
  }elseif ($mvt->type_hash == 'sorties/destructionperte') {
    $mvt_dest = $mvt;
  }elseif ($mvt->type_hash == 'export_details') {
    $mvt_export = $mvt;
  }
}
$t->is($mvt_crd->facturable, 1, $drm->_id." : le mouvement de sortie crd est facturable");
$t->is($mvt_dest->facturable, 0, $drm->_id." : le mouvement de sortie destruction n'est pas facturable");
$t->isnt($mvt_export->date, null, $drm->_id." : le mouvement d'export a une date ".$mvt_export->date);

$t->is(count(MouvementfactureFacturationView::getInstance()->getMouvementsNonFacturesBySociete($viti->getSociete())) - $nb_mouvements_facture, 3, $drm->_id." : on retrouve le mouvement facturable dans la vue facture");

$t->comment("Génère une modificatrice et change les exports");
$drm_mod = $drm->generateModificative();
$drm_mod->save();
$t->is($drm_mod->getProduit($produit_hash, 'details')->get('stocks_fin/final'), 600, $drm_mod->_id." : le stock final est conservé par la modificatrice");

$drm_mod->getProduit($produit_hash, 'details')->get('sorties/export_details')->remove($export_key1);
$drm_mod->update();
$t->is($drm_mod->getProduit($produit_hash, 'details')->get('stocks_fin/final'), 650, $drm_mod->_id." : le stock final est impacté par la suppression d'un des exports");

$export = DRMESDetailExport::freeInstance($drm);
$detail = $drm_mod->getProduit($produit_hash, 'details')->get('sorties/export_details')->get($export_key2);
$export->identifiant = $detail->identifiant;
$export->volume = 100;
$export->key = $detail->key;
$drm_mod->getProduit($produit_hash, 'details')->get('sorties/export_details')->addDetail($export);
$drm_mod->update();
$drm_mod->save();
$t->is($detail->getKey(), $export->getKey(), $drm_mod->_id." : Le détails doit avoir la même clé que l'export qui le remplace");
$t->is($drm_mod->getProduit($produit_hash, 'details')->get('sorties/export_details')->get($export_key2)->volume, 100, $drm_mod->_id." : le volume de l'export a été changé");
$t->is($drm_mod->getProduit($produit_hash, 'details')->get('stocks_fin/final'), 600, $drm_mod->_id." : le stock final est impacté par la modification de l'exports");
$t->is(count($drm_mod->getProduit($produit_hash, 'details')->get('sorties/export_details')), 1, $drm_mod->_id." : il ne reste plus qu'un export");
$drm_mod->validate();
$drm_mod->save();
$mvts_viti = $drm_mod->mouvements->{$drm_mod->identifiant};
$t->is(count($mvts_viti) * count($drm_mod->mouvements), 2, $drm_mod->_id." : la validation a généré deux mouvements (tous pour le viti)");


$drm_mod->setPaiementDouaneFrequence(DRMPaiement::FREQUENCE_ANNUELLE);
$drm_mod->save();
$societe = SocieteClient::getInstance()->find($viti->id_societe);
$t->is($societe->paiement_douane_frequence, DRMPaiement::FREQUENCE_ANNUELLE, $drm_mod->_id." : Le changement de frequence douane a un impact sur la societe");

$drm_mod->setPaiementDouaneFrequence(DRMPaiement::FREQUENCE_MENSUELLE);
$drm_mod->save();
$societe = SocieteClient::getInstance()->find($viti->id_societe);
$t->is($societe->paiement_douane_frequence, DRMPaiement::FREQUENCE_MENSUELLE, $drm_mod->_id." : Un nouveau changement de frequence douane a un impact sur la societe");

$periodeNext = date("Y").sprintf("%02d",date("m")+1);

$drmNext = DRMClient::getInstance()->createDoc($viti->identifiant, $periodeNext, true);
$drmNext->save();

$details = $drmNext->addProduit($produit_hash, 'details');
$details->stocks_debut->initial = 1000;


## test produit en denom complémentaires
$produitLibelle = $details->getLibelle();
$details->sorties->consommationfamilialedegustation = 100;
$drmNext->update();
$drmNext->save();
$t->is($drmNext->getProduit($produit_hash, 'details')->get('stocks_fin/final'), 900, $drmNext->_id." : vérification du stock final sur le produit ".$details->getHash());

## Ajout d'un produit avec une dénomination
$millesime = "2016";
$t->comment("On instancie dans la DRM un produit avec denomination complémentaire : ".$millesime);

$detailsM = $drmNext->addProduit($produit_hash, 'details', $millesime);
$detailsM->stocks_debut->initial = 200;
$detailsM->sorties->consommationfamilialedegustation = 50;
$drmNext->update();
$drmNext->save();
$t->is($drmNext->getProduit($produit_hash, 'details', $millesime)->get('stocks_fin/final'), 150, $drmNext->_id." : vérification du stock final sur le produit ".$detailsM->getHash());

## Vérification que les deux produits existes et que leur clé soit différentes
$countProduit = 0;
$produitsByCertif = $drmNext->declaration->getProduitsDetailsByCertifications(true);
foreach ($produitsByCertif as $produitByCertif) {
  if(count($produitByCertif->produits) && (($produitByCertif->certification_libelle == "IGP") || ($produitByCertif->certification_libelle == "AOP") || ($produitByCertif->certification_libelle == "AOC"))){
    $countProduit = count($produitByCertif->produits);
  }
}
$t->is($countProduit, 2, $drmNext->_id." : le nombre de produits est bien 2");

## vérification que la dénomination complémentaire soit bien remplie dans le second produit
foreach ($produitsByCertif as $produitByCertif) {
  if(count($produitByCertif->produits) && (($produitByCertif->certification_libelle == "IGP") || ($produitByCertif->certification_libelle == "AOP" || ($produitByCertif->certification_libelle == "AOC")))){
    $produitsAdded = $produitByCertif->produits;
    $cpt = 0;
    foreach ($produitsAdded as $produitAdded) {
      if($cpt){
        $t->is($produitAdded->get('denomination_complementaire'), $millesime, $drmNext->_id." : La dénomination complémentaire vaut $millesime");
      }else{
        $t->is($produitAdded->get('denomination_complementaire'), null, $drmNext->_id." : La dénomination complémentaire est nulle ici.");
      }
      $cpt++;
    }
  }
}

## vérification que le getLibelle renvoie le millésime

foreach ($produitsByCertif as $produitByCertif) {
  if(count($produitByCertif->produits) && (($produitByCertif->certification_libelle == "IGP") || ($produitByCertif->certification_libelle == "AOP" || ($produitByCertif->certification_libelle == "AOC")))){
    $produitsAdded = $produitByCertif->produits;
    $cpt = 0;
    foreach ($produitsAdded as $produitAdded) {
      if($cpt){
        $t->is($produitAdded->getLibelle(), $produitLibelle." ".$millesime, $drmNext->_id." : Le libelle du produit est bien : ".$produitAdded->getLibelle());
      }else{
        $t->is($produitAdded->getLibelle(), $produitLibelle, $drmNext->_id." : Le libelle du produit est bien : ".$produitAdded->getLibelle());
      }
      $cpt++;
    }
  }
}

## changement de Dénomination complémentaire

$grandCru = "Grand Cru";
$hashOrigine = $detailsM->getHash();
$baseLibelle = "";
$denomComplLibelle = "";
$drmNext->get($hashOrigine)->set("denomination_complementaire",$grandCru);
$drmNext->update();
$drmNext->save();

$produitFounded = false;
foreach ($drmNext->declaration->getProduitsDetailsByCertifications(true) as $produitByCertif) {
  if(count($produitByCertif->produits) && (($produitByCertif->certification_libelle == "IGP") || ($produitByCertif->certification_libelle == "AOP" || ($produitByCertif->certification_libelle == "AOC")))){
    $produitsAdded = $produitByCertif->produits;
    $cpt = 0;
    foreach ($produitsAdded as $produitAdded) {
      if(!$cpt){
        $baseLibelle = $produitAdded->getLibelle();
      }else{
        $produitFounded = $produitAdded;
        $denomComplLibelle = $produitFounded->getLibelle();
      }
      $cpt++;
    }
  }
}
$hashDest = $produitFounded->getHash();
$keyDetailsDest = $produitFounded->getKey();

$t->is($drmNext->get($hashDest)->get('denomination_complementaire'), $grandCru, $drmNext->_id." : la dénomination complémentaire est bien devenue $grandCru ");
$t->isnt($hashOrigine, $hashDest, $drmNext->_id." : La hash a bougé parce que $millesime a été remplacé par $grandCru");
$t->is($drmNext->get($hashDest)->get('stocks_fin/final'), 150, $drmNext->_id." : vérification du stock final sur le produit ".$hashDest);
$t->is($drmNext->get($hashDest)->get('sorties/consommationfamilialedegustation'), 50, $drmNext->_id." : vérification de la conso familiale ".$hashDest);

$existNodeMillesime = $drmNext->exist($hashOrigine);
$t->ok(!$existNodeMillesime, $drmNext->_id." : il n'y a plus de produit avec $millesime ");

# test des mvts avant validation
$drm_mvts_sorted = $drmNext->getMouvementsCalculeByIdentifiant($drmNext->identifiant);
$mvts_sorted = DRMClient::getInstance()->sortMouvementsForDRM($drm_mvts_sorted);
$cpt = 0;
foreach ($mvts_sorted as $type_mvt_drm => $mvts_grouped) {
  foreach ($mvts_grouped as $hash_produit => $mvts) {
    foreach ($mvts as $key => $mvt) {
      if($cpt){
        $t->is($mvt->getProduitLibelle(), $denomComplLibelle, $drmNext->_id." : le libelle du mvt du produit est bien $denomComplLibelle");
      }else{
        $t->is($mvt->getProduitLibelle(), $baseLibelle, $drmNext->_id." : le libelle du mvt du produit est bien $baseLibelle");
      }
      $cpt++;
    }
  }
}

# test des mvts après validation
$drmNext->validate();
$drmNext->save();

$mvts_viti = $drmNext->mouvements->{$drmNext->identifiant};
$t->is(count($mvts_viti), 2, $drmNext->_id." : la validation a généré deux mouvements chez le viti");

$mvt_consoFam = null;
$cpt = 0;
foreach ($mvts_viti as $mvt) {
  if ($mvt->type_hash == 'sorties/consommationfamilialedegustation') {
    $mvt_consoFam = $mvt;
  }
  if(!$cpt){
    $getKeyOfMvthash = str_replace("details/","",strstr($mvt_consoFam->produit_hash,"details/"));
    $t->is($getKeyOfMvthash, "DEFAUT", $drmNext->_id." : la fin de la hash produit du mvt est bien DEFAUT");
    $t->is($mvt->getProduitLibelle(), $baseLibelle, $drmNext->_id." : le libelle du mvt du produit est bien $baseLibelle");
  }else{
    $getKeyOfMvthash = str_replace("details/","",strstr($mvt_consoFam->produit_hash,"details/"));
    $t->is($getKeyOfMvthash, $keyDetailsDest, $drmNext->_id." : la fin de la hash produit du mvt est bien $keyDetailsDest");
    $t->is($mvt->getProduitLibelle(), $denomComplLibelle, $drmNext->_id." : le libelle du mvt du produit est bien $denomComplLibelle");
  }
  $cpt++;
}

$drm_mvts_sorted = DRMMouvementsConsultationView::getInstance()->getMouvementsByEtablissementAndPeriode($drmNext->identifiant, $drmNext->periode);
$mvts_sorted = DRMClient::getInstance()->sortMouvementsForDRM($drm_mvts_sorted);
$cpt = 0;
$baseLibelleFound = false;
$denomComplLibelleFound = false;
foreach ($mvts_sorted as $type_mvt_drm => $mvts_grouped) {
  foreach ($mvts_grouped as $hash_produit => $mvts) {
    foreach ($mvts as $key => $mvt) {
        if($mvt->produit_libelle == $baseLibelle){
            $baseLibelleFound = true;
        }
        if($mvt->produit_libelle == $baseLibelle){
            $denomComplLibelleFound = true;
        }
        }
    }
}
$t->ok($baseLibelleFound, $drmNext->_id." : le libelle du mvt du produit est bien $baseLibelle");
$t->ok($denomComplLibelleFound, $drmNext->_id." : le libelle du mvt du produit est bien $denomComplLibelle");
