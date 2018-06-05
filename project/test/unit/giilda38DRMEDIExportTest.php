<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(21);
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti_2')->getEtablissement();
$conf = ConfigurationClient::getInstance()->getCurrent();
$produits = array_keys($conf->getProduits());
$produit1_hash = array_shift($produits);
$produit1 = ConfigurationClient::getInstance()->getCurrent()->get($produit1_hash);
$contenancesCRD = VracConfiguration::getInstance()->getContenances();

//Suppression des DRM précédentes
foreach(DRMClient::getInstance()->viewByIdentifiant($viti->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
  $csv = CSVDRMClient::getInstance()->find(str_replace("DRM", "CSVDRM", $k));
  $csv->delete(false);
}

$periode = (date('Y')-1)."01";
$drm = DRMClient::getInstance()->createDoc($viti->identifiant, $periode);

$tmpfname = tempnam("/tmp", "DRM_");
$temp = fopen($tmpfname, "w");
fwrite($temp, "CAVE,201801,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,stocks_debut,initial,951.4625,,,,,,\n");
fwrite($temp, "CAVE,201801,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,sorties,ventefrancecrd,4.62,,,,,,\n");
fwrite($temp, "CAVE,201801,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,sorties,export,1.89,PAYS-BAS,,,,,\n");
fwrite($temp, "CAVE,201801,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,sorties,export,0.9525,BELGIQUE,,,,,\n");
fwrite($temp, "CAVE,201801,".$viti->identifiant.",".$viti->no_accises.",".$produit1->getCertification()->getLibelle().",".$produit1->getGenre()->getLibelle().",".$produit1->getAppellation()->getLibelle().",".$produit1->getMention()->getLibelle().",".$produit1->getLieu()->getLibelle().",".$produit1->getCouleur()->getLibelle().",".$produit1->getCepage()->getLibelle().",,".$produit1->getLibelleFormat().",suspendu,stocks_fin,final,944,,,,,,\n");
fwrite($temp, "CRD,201801,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille75cl,,,,,,,collectif suspendu,stock_debut,debut,14742,,,,\n");
fwrite($temp, "CRD,201801,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille 75 cl,,,,,,,collectif suspendu,sorties,utilisations,3118,,,,\n");
fwrite($temp, "CRD,201801,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille 75cl,,,,,,,collectif suspendu,stock_fin,fin,11624,,,,\n");
fwrite($temp, "CRD,201801,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille150cl,,,,,,,collectif suspendu,stock_debut,debut,56,,,,\n");
fwrite($temp, "CRD,201801,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille 150 cl,,,,,,,collectif suspendu,sorties,utilisations,3,,,,\n");
fwrite($temp, "CRD,201801,".$viti->identifiant.",".$viti->no_accises.",VERT,tranquille,Bouteille 150cl,,,,,,,collectif suspendu,stock_fin,fin,53,,,,\n");
fclose($temp);

$periode = (date('Y'))."01";
$drm = DRMClient::getInstance()->createDoc($viti->identifiant, $periode);
$import = new DRMImportCsvEdi($tmpfname, $drm);
$import->importCSV();
$drm->save();
$drm->validate();
$drm->generateMouvements();
$drm->save();
$drmProduit = $drm->get($produit1->getHash())->get('details/DEFAUT');
//unlink($tmpfname);

$export = new DRMExportCsvEdi($drm);

$csv = $export->exportEDI();

$typeOK = true;
$identifiantOK = true;
$numAccisesOK = true;
$certifOK = true;
$genreOK = true;
$appellationOK = true;
$mentionOK = true;
$lieuOK = true;
$couleurOK = true;
$cepageOK = true;
$denominationOK = true;
$libelleOK = true;
$typeDRMOK = true;
$categorieMouvementOK = true;
$typeMouvementOK = true;
$volumeOK = true;
$exportOK = true;
$contratOK = true;

$nblignes = 0;
$nbMouvements = 0;
$nbStocks = 0;

foreach(explode("\n", $csv) as $line) {
    if(preg_match("/^#/", $line) || !$line) {
        continue;
    }
    $data = str_getcsv($line, ";");
    $nblignes += 1;

    if(!in_array($data[DRMCsvEdi::CSV_TYPE], array("CAVE", "CRD", "ANNEXE"))) {
        $typeOK = false;
    }

    if($data[DRMCsvEdi::CSV_IDENTIFIANT] != $viti->identifiant.' ('.$viti->cvi.')') {
        $identifiantOK = false;
    }

    if($data[DRMCsvEdi::CSV_NUMACCISE] != $viti->no_accises) {
        $numAccisesOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CAVE" && $data[DRMCsvEdi::CSV_CAVE_CERTIFICATION] != $produit1->getCertification()->getLibelle()) {
        $certifOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CRD" && !in_array($data[DRMCsvEdi::CSV_CAVE_CERTIFICATION], DRMClient::$drm_crds_couleurs)) {
        $certifOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CAVE" && $data[DRMCsvEdi::CSV_CAVE_GENRE] != $produit1->getGenre()->getLibelle()) {
        $genreOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CRD" && !in_array($data[DRMCsvEdi::CSV_CAVE_GENRE], DRMClient::$drm_crds_genre)) {
        $genreOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CAVE" && $data[DRMCsvEdi::CSV_CAVE_APPELLATION] != $produit1->getAppellation()->getLibelle()) {
        $appellationOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CRD" && !in_array($data[DRMCsvEdi::CSV_CAVE_APPELLATION], array_keys($contenancesCRD))) {
        $appellationOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CAVE" && $data[DRMCsvEdi::CSV_CAVE_MENTION] != $produit1->getMention()->getLibelle()) {
        $mentionOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CRD" && $data[DRMCsvEdi::CSV_CAVE_MENTION]) {
        $mentionOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CAVE" && $data[DRMCsvEdi::CSV_CAVE_LIEU] != $produit1->getLieu()->getLibelle()) {
        $lieuOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CRD" && $data[DRMCsvEdi::CSV_CAVE_LIEU]) {
        $lieuOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CAVE" && $data[DRMCsvEdi::CSV_CAVE_COULEUR] != $produit1->getCouleur()->getLibelle()) {
        $couleurOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CRD" && $data[DRMCsvEdi::CSV_CAVE_COULEUR]) {
        $couleurOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CAVE" && $data[DRMCsvEdi::CSV_CAVE_CEPAGE] != $produit1->getCepage()->getLibelle()) {
        $cepageOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CRD" && $data[DRMCsvEdi::CSV_CAVE_CEPAGE]) {
        $cepageOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CAVE" && $data[DRMCsvEdi::CSV_CAVE_LIBELLE_COMPLEMENTAIRE] != $drmProduit->denomination_complementaire) {
        $denominationOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CRD" && $data[DRMCsvEdi::CSV_CAVE_LIBELLE_COMPLEMENTAIRE]) {
        $denominationOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CAVE" && $data[DRMCsvEdi::CSV_CAVE_LIBELLE_PRODUIT] != $produit1->getLibelleFormat() . " (".$produit1->getCodeDouane().")") {
        $libelleOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CRD" && $data[DRMCsvEdi::CSV_CAVE_LIBELLE_PRODUIT]) {
        $libelleOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CAVE" && in_array($data[DRMCsvEdi::CSV_CAVE_TYPE_DRM], DRMClient::$types_libelles)) {
        $typeDRMOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CRD" && !in_array($data[DRMCsvEdi::CSV_CAVE_TYPE_DRM], EtablissementClient::$regimes_crds_libelles)) {
        $typeDRMOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CAVE" && !$conf->declaration->details->exist($data[DRMCsvEdi::CSV_CAVE_CATEGORIE_MOUVEMENT])) {
        $categorieMouvementOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CRD" && !in_array($data[DRMCsvEdi::CSV_CAVE_CATEGORIE_MOUVEMENT], array('stock_debut', 'entrees', 'sorties', 'stock_fin'))) {
        $categorieMouvementOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CAVE" && !$conf->declaration->details->exist($data[DRMCsvEdi::CSV_CAVE_CATEGORIE_MOUVEMENT]."/".$data[DRMCsvEdi::CSV_CAVE_TYPE_MOUVEMENT])) {
        $typeMouvementOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CRD" && !in_array($data[DRMCsvEdi::CSV_CAVE_TYPE_MOUVEMENT], array('debut', 'achat', 'excedents', 'retours', 'utilisations', 'destructions', 'manquants', 'fin'))) {
        $typeMouvementOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CAVE" && !is_numeric($data[DRMCsvEdi::CSV_CAVE_VOLUME])) {
        $volumeOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CRD" && !is_numeric($data[DRMCsvEdi::CSV_CAVE_VOLUME])) {
        $volumeOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CAVE" && $data[DRMCsvEdi::CSV_CAVE_TYPE_MOUVEMENT] == "export" && !ConfigurationClient::getInstance()->findCountryByLibelle($data[DRMCsvEdi::CSV_CAVE_EXPORTPAYS])) {
        $exportOK = false;
    }

    if(($data[DRMCsvEdi::CSV_TYPE] != "CAVE" || $data[DRMCsvEdi::CSV_CAVE_TYPE_MOUVEMENT] != "export") && $data[DRMCsvEdi::CSV_CAVE_EXPORTPAYS]) {
        $exportOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CAVE" && $data[DRMCsvEdi::CSV_CAVE_TYPE_MOUVEMENT] == "vrac" && !$data[DRMCsvEdi::CSV_CAVE_CONTRATID]) {
        $contratOK = false;
    }

    if(($data[DRMCsvEdi::CSV_TYPE] != "CAVE" || $data[DRMCsvEdi::CSV_CAVE_TYPE_MOUVEMENT] != "vrac") && $data[DRMCsvEdi::CSV_CAVE_CONTRATID]) {
        $contratOK = false;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CAVE" && preg_match('/stock/', $data[DRMCsvEdi::CSV_CAVE_CATEGORIE_MOUVEMENT])) {
        $nbStocks += 1;
    }

    if($data[DRMCsvEdi::CSV_TYPE] == "CAVE" && !preg_match('/^stock/', $data[DRMCsvEdi::CSV_CAVE_CATEGORIE_MOUVEMENT])) {
        $nbMouvements += 1;
    }
}
$t->ok($nblignes > 0, "Plus d'une ligne analysée");
$t->ok($typeOK, "Vérification de la colonne type");
$t->ok($identifiantOK, "Vérification de la colonne identifiant");
$t->ok($numAccisesOK, "Vérification de la colonne n°accises");
$t->ok($certifOK, "Vérification de la colonne produit certification");
$t->ok($genreOK, "Vérification de la colonne produit genre");
$t->ok($appellationOK, "Vérification de la colonne produit appellation");
$t->ok($mentionOK, "Vérification de la colonne produit mention");
$t->ok($lieuOK, "Vérification de la colonne produit lieu");
$t->ok($couleurOK, "Vérification de la colonne produit couleur");
$t->ok($cepageOK, "Vérification de la colonne produit cepage");
$t->ok($denominationOK, "Vérification de la colonne produit dénomination");
$t->ok($libelleOK, "Vérification de la colonne produit libelle complet");
$t->ok($typeDRMOK, "Vérification de la colonne type de DRM");
$t->ok($categorieMouvementOK, "Vérification de la colonne catégorie de mouvement");
$t->ok($typeMouvementOK, "Vérification de la colonne type de mouvement");
$t->ok($volumeOK, "Vérification de la colonne volume");
$t->ok($exportOK, "Vérification de la colonne export");
$t->ok($contratOK, "Vérification de la colonne contrat");
$t->is($nbMouvements, count($drm->mouvements->get($drm->identifiant)), "Tous les mouvements sont présents dans le csv");
$t->ok($nbStocks >= count($drm->getProduitsDetails())*2 && $nbStocks % 2 == 0, "Les lignes de stocks sont toutes présentes");
