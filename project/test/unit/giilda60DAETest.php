<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');
sfContext::createInstance($configuration);

if(!method_exists(ConfigurationClient::getConfiguration(date('Y-m-d')),'getConfigurationProduit')){
    $t = new lime_test(0);
    $t->comment("Le module DAE n'est pas disponible dans cette environnement : La class Configuration ne possède pas de méthode getConfigurationProduit");
    return;
}

$conf = ConfigurationClient::getInstance()->getCurrent();

$t = new lime_test(14);

$t->comment("Création d'un DAE pour un viti");

$societeViti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getSociete();
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

$identifiant = $viti->identifiant;
$daesViti = DAEClient::getInstance()->findByIdentifiant($identifiant);
foreach ($daesViti as $dae) {
    $d = DAEClient::getInstance()->find($dae->_id);
    $d->delete();
}
foreach(DRMClient::getInstance()->viewByIdentifiant($viti->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
}
foreach (VracClient::getInstance()->retrieveBySoussigne($viti->identifiant)->rows as $k => $vrac) {
    $vrac_obj = VracClient::getInstance()->find($vrac->id);
    $vrac_obj->delete();
}

$dateSortie = date('Y-m-d');

$produit_hash = null;
foreach(ConfigurationClient::getInstance()->getCurrent()->getProduits() as $produit) {
    $produit_hash = $produit->getHash();
    break;
}
$periode = date('Ym');

$type_acheteur = DAEClient::ACHETEUR_TYPE_IMPORTATEUR;
$date = (new DateTime())->modify('-1 month')->format('Y-m-d');
$destination = "FR";
$millesime = "2017";
$quantite = 36.15;
$contenant = 'Bouteille 75 cl';
$prix_ht = 250.25;
$label = 'BIO';
$acheteurAccises = "FR12345678910";
$acheteurNom = 'Nom du super importateur';

$id = 'DAE-'.$identifiant.'-'.str_replace('-', '', $date).'-001';

$dae = DAEClient::getInstance()->createSimpleDAE($identifiant, $date);

$t->is($dae->date, $date, "La date du dae est \"".$date."\"");
$t->is($dae->date_saisie, date('Y-m-d'), "La date de saisie du dae est \"".date('Y-m-d')."\"");

$form = new DAENouveauForm($dae);

$values = array(
    'produit_key' => $produit_hash,
    'label_key' => $label,
    'mention_key' => null,
    'millesime' => $millesime,
    'type_acheteur_key' => $type_acheteur,
    'destination_key' => $destination,
    'quantite' => $quantite,
    'contenance_key' => $contenant,
    'prix_unitaire' => $prix_ht,
    'no_accises_acheteur' => $acheteurAccises,
    'nom_acheteur' => $acheteurNom,
);

$form->bind($values);

$t->ok($form->isValid(), "Le formulaire est valide");

$form->save();

$t->is($dae->contenance_hl, 1, "Le ratio de la contenance en hl est de \"1\"");
$t->is($dae->conditionnement_key, 'HL', "L'unité de conditonnement en abrégé est \"hl\"");
$t->is($dae->conditionnement_libelle, 'Hectolitre', "L'unité de conditonnement est \"Hectolitre\"");
$t->is($dae->contenance_libelle, 0.0075, "Le libellé de la contenant est \"0.0075\"");
$t->is($dae->volume_hl, $quantite, "Le volume en hl est \"".$quantite."\"");
$t->is($dae->prix_hl, $prix_ht, "Le prix à hl est \"".$prix_ht."\"");

$t->is($dae->_id, $id, "L'id du doc est \"$id\"");

$dae = DAEClient::getInstance()->findLastByIdentifiantDate($identifiant, $date);

$t->is($dae->_id, $id, "Le dernier DAE est bien récupéré par rapport à la date du jour");

$t->comment("Export des commercialisation");

if ($conf->declaration->exist('details/sorties/vrac') && $conf->declaration->get('details/sorties/vrac')->details == "VRAC") {
    $vrac = new Vrac();
    $etablissementcourtier = $societecourtier = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_courtier')->getEtablissement();
    $vrac->initCreateur($etablissementcourtier->getIdentifiant());
    $vrac->teledeclare = false;
    $vrac->acheteur_identifiant = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_region')->getEtablissement()->getIdentifiant();
    $vrac->vendeur_identifiant =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement()->getIdentifiant();
    $vrac->setProduit($produit_hash);
    $vrac->type_transaction = VracClient::TYPE_TRANSACTION_VIN_VRAC;
    $vrac->jus_quantite = 100;
    $vrac->prix_initial_unitaire = 70;
    $vrac->validate();
    $vrac->save();
}

$drm = DRMClient::getInstance()->createDoc($viti->identifiant, (new DateTime())->modify('-1 month')->format('Ym'));
$drm->save();
$details = $drm->addProduit($produit_hash, 'details');
$details->stocks_debut->initial = 1000;

if ($conf->declaration->exist('details/sorties/vrac') && $conf->declaration->get('details/sorties/vrac')->details == "VRAC") {
    $contrat = DRMESDetailVrac::freeInstance($drm);
    $contrat->identifiant = $vrac->_id;
    $contrat->volume = 100;
    $details->sorties->vrac_details->addDetail($contrat);
} elseif($conf->declaration->exist('details/sorties/creationvrac') && $conf->declaration->get('details/sorties/creationvrac')->details == "CREATIONVRAC") {
    $creationvrac = DRMESDetailCreationVrac::freeInstance($drm);
    $creationvrac->volume = 100;
    $creationvrac->prixhl = 70;
    $creationvrac->acheteur = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_region')->getEtablissement()->getIdentifiant();
    $creationvrac->type_contrat = VracClient::TYPE_TRANSACTION_VIN_VRAC;
    $details->sorties->creationvrac_details->addDetail($creationvrac);
}

$drm->save();
$drm->validate();
$drm->save();

$export = new DAEExportCsv();

$t->is(count(explode("\n", $export->exportByEtablissementAndCampagne($identifiant, ConfigurationClient::getInstance()->buildCampagne($dae->date)))), 4, "Le csv complet de l'établissement contient 3 lignes");

$mouvements = DRMMouvementsConsultationView::getInstance()->getMouvementsByEtablissement($viti->identifiant);
foreach($mouvements as $mouvement) {
    if(!$mouvement->vrac_numero) {
        continue;
    }
    // echo $export->exportMouvementDRMContrat($mouvement);
    $t->ok($export->exportMouvementDRMContrat($mouvement), "Il y a bien une ligne de CSV pour le contrat vrac");
}

// echo $export->exportDAE($dae);
$t->ok($export->exportDAE($dae), "Il y a bien une ligne de CSV pour le DAE");
