<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$conf = ConfigurationClient::getCurrent();
if (!($conf->declaration->exist('details/sorties/vrac')) || ($conf->declaration->get('details/sorties/vrac')->details != "VRAC")) {
    $t = new lime_test(0);
    exit(0);
}

$t = new lime_test(49);

$nego = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_region')->getEtablissement();
$nego_horsregion = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_horsregion')->getEtablissement();
$nego2 =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_region_2')->getEtablissement();
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$produit_hash = null;
$produitSansCvo_hash = null;
foreach(ConfigurationClient::getInstance()->getCurrent()->getProduits() as $produit) {
    if($produit->getTauxCVO(date("Y-m-d")) > 0 && !$produit_hash) {
        $produit_hash = $produit->getHash();
    }
    if($produit->getTauxCVO(date("Y-m-d")) <= 0 && !$produitSansCvo_hash) {
        $produitSansCvo_hash = $produit->getHash();
    }
}
$periode = date('Ym');

//Suppression des DRM précédentes
foreach(DRMClient::getInstance()->viewByIdentifiant($viti->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
}

foreach(DRMClient::getInstance()->viewByIdentifiant($nego->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
}

foreach(SV12Client::getInstance()->viewByIdentifiant($nego2->identifiant) as $k => $v) {
  $sv12 = SV12Client::getInstance()->find($k);
  SV12Client::getInstance()->deleteDocument($sv12);
}

//Début des tests
$t->comment("création d'une DRM");

$drm = DRMClient::getInstance()->createDoc($viti->identifiant, $periode, true);
$drm->save();
$details = $drm->addProduit($produit_hash, 'details');
$details->stocks_debut->initial = 1000;

$t->comment("Ajout d'une sortie vrac");

$t->ok(!$details->isContratExterne(), "Le produit ne permet pas des sorties de contrats externe");

$vrac = array_shift(VracClient::getInstance()->getBySoussigne($drm->campagne, $nego->identifiant)->rows);
$vracObj = VracClient::getInstance()->find($vrac->value[VracSoussigneIdentifiantView::VRAC_VIEW_VALUE_ID]);

$contrat = DRMESDetailVrac::freeInstance($drm);
$contrat->identifiant = $vrac->value[VracSoussigneIdentifiantView::VRAC_VIEW_VALUE_ID];
$contrat->volume = 100;
$contrat = $details->sorties->vrac_details->addDetail($contrat);
$details->sorties->ventefrancecrd = 100;
$contrat_key = $contrat->getKey();
if (!$contrat_key) {
  $t->fail($drm->_id." : Pas de clé trouvée pour la sortie vrac");
}else {
  $t->is($drm->getProduit($produit_hash, 'details')->get("sorties/vrac_details")->get($contrat_key)->getKey(), $contrat_key, $drm->_id." : les clés de contrat sont conservées");
}

$t->is($contrat->getIdentifiantLibelle(), $vrac->value[VracSoussigneIdentifiantView::VRAC_VIEW_VALUE_NUMERO_ARCHIVE], "Construction du libellé du détail avec le numéro d'archive");

//Début des tests
$t->comment("Ajout d'une sortie vrac sans CVO");
$detailsSansCvo = $drm->addProduit($produitSansCvo_hash, 'details');
$detailsSansCvo->stocks_debut->initial = 1000;

$t->ok($detailsSansCvo->isContratExterne(), "Le produit permet des sorties de contrats externe");

$contrat = DRMESDetailVrac::freeInstance($drm);
$contrat->identifiant = "4587416";
$contrat->volume = 100;
$contrat = $detailsSansCvo->sorties->vrac_details->addDetail($contrat);

$t->is($contrat->getIdentifiantLibelle(), "externe ".$contrat->identifiant, "Construction du libellé du détail avec \"externe\"");

$t->comment("Mise à jour et sauvegarde la DRM");
$drm->update();
$drm->save();

$drm = DRMClient::getInstance()->findMasterByIdentifiantAndPeriode($viti->identifiant, $periode);
$t->is($drm->getProduit($produit_hash, 'details')->get('stocks_fin/final'), 800, $drm->_id." : le stock final est impacté");
$t->is(count($drm->getProduit($produit_hash, 'details')->get('sorties/vrac_details')), 1, $drm->_id." : le produit avec CVO a bien 1 sortie vrac");
$t->is(count($drm->getProduit($produit_hash, 'details')->get('sorties/vrac_details')), 1, $drm->_id." : le produit sans CVO a bien 1 sortie vrac");

$t->comment("validation de la DRM et génération des mouvements");
$drm->validate();
$drm->save();

$drm->updateVracs();

$mvts_viti = $drm->mouvements->{$drm->identifiant};
$t->is(count($mvts_viti), 3, $drm->_id." : la validation a généré trois mouvements viti");
foreach ($mvts_viti as $mvt) {
    if ($mvt->type_hash == 'sorties/ventefrancecrd') {
        $mvt_crd = $mvt;
    }
    if ($mvt->type_hash == 'vrac_details' && $mvt->vrac_numero) {
        $mvt_vrac = $mvt;
    }
    if ($mvt->type_hash == 'vrac_details' && !$mvt->vrac_numero) {
        $mvt_vrac_externe = $mvt;
    }
}

if($application == "ivbd") {
    $t->is($mvt_vrac->facturable, 0, $drm->_id." : le mouvement de sortie vrac n'est pas facturable");
    $t->is($mvt_vrac->cvo, 0, $drm->_id." : la cvo du mouvement de sortie vrac est de 0");
} else {
    $t->is($mvt_vrac->facturable, 1, $drm->_id." : le mouvement de sortie vrac est facturable");
    $t->is($mvt_vrac->cvo, $mvt_crd->cvo / 2, $drm->_id." : la cvo du mouvement de sortie vrac est de 50%");
}

$t->ok(!$mvt_vrac_externe->isVrac(), $drm->_id." : Le mouvement de vrac externe n'est pas considéré comme du vrac");
$t->is($mvt_vrac_externe->categorie, FactureClient::FACTURE_LIGNE_PRODUIT_TYPE_VINS_EXTERNE, $drm->_id." : La catégorie du mouvement de vrac externe est ".FactureClient::FACTURE_LIGNE_PRODUIT_TYPE_VINS_EXTERNE);
$t->is($mvt_vrac_externe->facturable, 0, $drm->_id." : le mouvement de vrac externe n'est pas facturable");
$t->is($mvt_vrac_externe->cvo, 0, $drm->_id." : la cvo du mouvement de vrac externe est de 0");

$mvts_nego = $drm->mouvements->{$nego->identifiant};
$t->is(count($mvts_nego), 1, $drm->_id." : la validation a généré un mouvement chez le nego");
foreach ($mvts_nego as $mvt_nego) {
  break;
}

if($application == "ivbd") {
    $t->is($mvt_nego->cvo, $mvt_crd->cvo, $drm->_id." : la cvo du mouvement de sortie vrac nego est de 100%");
} else {
    $t->is($mvt_nego->cvo, $mvt_vrac->cvo, $drm->_id." : la cvo du mouvement de sortie vrac nego est de 50%");
}

$mouvementsFacturable = MouvementfactureFacturationView::getInstance()->getMouvementsNonFacturesBySociete($viti->getSociete());
foreach($mouvementsFacturable as $key => $mouvement) {
    if($mouvement->origine != "DRM") {
        unset($mouvementsFacturable[$key]);
    }
}


if($application == "ivbd") {
    $t->is(count($mouvementsFacturable), 1, $drm->_id." : on retrouve uns seul mouvement facturable dans la vue facture du viti");
} else {
    $t->is(count($mouvementsFacturable), 2, $drm->_id." : on retrouve les 2 mouvements facturables dans la vue facture du viti");
}

$t->is(count(MouvementfactureFacturationView::getInstance()->getMouvementsNonFacturesBySociete($nego->getSociete())), 1, $drm->_id." : on retrouve le mouvement facturable dans la vue facture du négo");

$t->comment("Testes sur les enlévements liés au contrat ".$vracObj->_id." de volume proposé : ".$vracObj->volume_propose);
$enlevements = VracClient::getInstance()->buildEnlevements($vracObj);
$t->is(count($enlevements), 1, $vracObj->_id." : on retrouve bien un enlévement");
$enlevement = array_shift($enlevements);
$t->is($enlevement->volume, 100, $vracObj->_id." : on retrouve l'enlévement de volume 100 hl.");

$vracObj = VracClient::getInstance()->find($vrac->value[VracSoussigneIdentifiantView::VRAC_VIEW_VALUE_ID]);
$t->is($vracObj->volume_enleve, 100, $vracObj->_id." : Le contrat a pour volume enlevé : 100");

$t->comment("Génère une nouvelle modificatrice et change le contrat pour un contrat hors region");
$drm_mod = $drm->generateModificative();
$vrac_details = $drm_mod->getProduit($produit_hash, 'details')->get('sorties/vrac_details');
$vrac_details->remove($contrat_key);
$vrac = array_shift(VracClient::getInstance()->getBySoussigne($drm->campagne, $nego_horsregion->identifiant)->rows);
$contrat = DRMESDetailVrac::freeInstance($drm);
$contrat->identifiant = $vrac->value[VracSoussigneIdentifiantView::VRAC_VIEW_VALUE_ID];
$contrat->volume = 100;
$detailVrac = $vrac_details->addDetail($contrat);


$drm_mod->update();
$drm_mod->validate();
$drm_mod->save();

$t->is($detailVrac->date_enlevement, $drm->getDate(), $drm_mod->_id." : La sortie de vrac a une date d'enlèvement");

$t->is($drm_mod->getProduit($produit_hash, 'details')->get('stocks_fin/final'), 800, $drm_mod->_id." : le stock final est impacté par la modification de l'exports");
$t->is(count($drm_mod->mouvements), 3, $drm_mod->_id." : la validation a impacté trois tiers (viti, nego, nego hors region)");
$mvts_nego_horsRegion =  $drm_mod->mouvements->{$nego_horsregion->identifiant}->toArray(true, false);
$mvt_nego_horsRegion = (count($mvts_nego_horsRegion))? array_shift($mvts_nego_horsRegion)['facturable'] : 0;
$t->is($mvt_nego_horsRegion, 0, $drm_mod->_id." : le mouvement du négo hors région n'est pas facturable");

$somme_cvo = 0;
foreach($drm_mod->mouvements->{$nego->identifiant} as $k => $mvt) {
  $somme_cvo += $mvt->cvo * $mvt->volume;
}
foreach($drm_mod->mouvements->{$viti->identifiant} as $k => $mvt) {
  $somme_cvo += $mvt->cvo * $mvt->volume;
}
$t->is($somme_cvo, 0, $drm_mod->_id." : Le viti est facturé de toute la cvo");

$drm_mod = DRMClient::getInstance()->find($drm_mod->_id);
$drm_mod->devalide();
$drm_mod->save();
$t->is($drm_mod->isValidee(), false, $drm_mod->_id." : La DRM est réouverte");

$drm_mod = DRMClient::getInstance()->find($drm_mod->_id);
$vrac_details = $drm_mod->getProduit($produit_hash, 'details')->get('sorties/vrac_details');
$vrac_details->remove($contrat_key);
$contrat = DRMESDetailVrac::freeInstance($drm_mod);
$contrat->identifiant = $vrac->value[VracSoussigneIdentifiantView::VRAC_VIEW_VALUE_ID];
$contrat->volume = 50;
$detailVrac = $vrac_details->addDetail($contrat);

$drm_mod->update();
$drm_mod->validate();
$drm_mod->save();

$drm_mod->updateVracs();

$t->is(boolval($drm_mod->isValidee()), true, $drm_mod->_id." : La DRM est de nouveau validée");

$vracObj = VracClient::getInstance()->find($vrac->value[VracSoussigneIdentifiantView::VRAC_VIEW_VALUE_ID]);
$t->is($vracObj->volume_enleve, 150, $vracObj->_id." : Le contrat a pour volume enlevé : 150");

$t->comment("Test sur les mouvements de facturation");

$mouvementsFacturation = FactureClient::getInstance()->getFacturationForSociete($viti->getSociete());
$t->is(count($mouvementsFacturation), (($application == "ivbd") ? 2 : 3), "Il y a ".(($application == "ivbd") ? 2 : 3)." mouvements de facturation");

$mouvementVrac = null;
foreach($mouvementsFacturation as $mouvement) {
    if($mouvement->matiere == "contrat_vins") {
        $mouvementVrac = $mouvement;
        break;
    }
}

$t->is($mouvementVrac->date, date('Y-m-31'), "La date est OK");
$t->is($mouvementVrac->etablissement_identifiant, $viti->identifiant, "L'identifiant de l'établissement est OK");
$t->is($mouvementVrac->produit_hash, $drm->getProduit($produit_hash, 'details')->getHash(), "La hash produit est OK");
$t->is($mouvementVrac->produit_libelle, $drm->getProduit($produit_hash, 'details')->getLibelle(), "Le libellé produit est OK");
$t->is($mouvementVrac->vrac_destinataire, $nego_horsregion->nom, "Le destinataire vrac est OK");
$t->is($mouvementVrac->type_libelle, $conf->declaration->get('details/sorties/vrac')->getLibelle(), "Le libellé du mouvement est OK");
$t->is($mouvementVrac->origine, "DRM", "L'origine est OK");
$t->is($mouvementVrac->matiere, "contrat_vins", "La matière est OK");
$t->is($mouvementVrac->detail_libelle, $vracObj->numero_archive, "Le detail libellé est OK");
if($application == "ivso") {
    $t->is(MouvementfactureFacturationView::getInstance()->createOrigine(SocieteClient::TYPE_OPERATEUR, $mouvementVrac), "Contrat n° ".intval(substr($vracObj->_id, -6))." (".$nego_horsregion->nom.") ", "Le libellé vrac pour la facture est OK");
} else {
    $t->is(MouvementfactureFacturationView::getInstance()->createOrigine(SocieteClient::TYPE_OPERATEUR, $mouvementVrac), "Contrat n° ".$vracObj->numero_archive." (".$nego_horsregion->nom.") ", "Le libellé vrac pour la facture est OK");
}
$t->is($mouvementVrac->quantite, $vracObj->volume_enleve, "La quantité est OK");
$t->is($mouvementVrac->prix_unitaire, $conf->get($produit_hash)->getTauxCVO(date('Y-m-d')), "Le prix unitaire est OK");
$t->is($mouvementVrac->prix_ht, $vracObj->volume_enleve * $conf->get($produit_hash)->getTauxCVO(date('Y-m-d')), "Le prix HT est OK");
$t->is($mouvementVrac->id_doc, $drm->_id, "L'id DRM est OK");
$t->is($mouvementVrac->vrac_numero, str_replace("VRAC-", "", $vracObj->_id), "L'identifiant du contrat est ok");

$mouvKeyOrigine1 = null;
$mouvKeyOrigine2 = null;
foreach($drm_mod->mouvements->get($viti->identifiant) as $mouv) {
    if($mouv->detail_identifiant == $vracObj->_id && !$mouvKeyOrigine2) {
        $mouvKeyOrigine2 = $mouv->getKey();
        continue;
    }
    if($mouv->detail_identifiant == $vracObj->_id && !$mouvKeyOrigine1) {
        $mouvKeyOrigine1 = $mouv->getKey();
        continue;
    }
}
$t->is(count($mouvementVrac->origines), 2, "Il y a 2 origines");
$t->is($mouvementVrac->origines[0], $drm_mod->_id.":".$mouvKeyOrigine1, "L'origine n°1 est ok");
$t->is($mouvementVrac->origines[1], $drm_mod->_id.":".$mouvKeyOrigine2, "L'origine n°2 est ok");
