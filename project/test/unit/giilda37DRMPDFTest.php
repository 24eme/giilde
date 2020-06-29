<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

$t = new lime_test(1);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_teledeclaration')->getEtablissement();
$periode = date('Ym');
$produits = array_keys(ConfigurationClient::getInstance()->getCurrent()->getProduits());
$produit_hash = array_shift($produits);

foreach(DRMClient::getInstance()->viewByIdentifiant($viti->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
}

$drm = DRMClient::getInstance()->createDoc($viti->identifiant, $periode, true);

$detail = $drm->addProduit($produit_hash, 'details');

$detail->stocks_debut->initial = 100;
$detail->sorties->ventefrancecrd = 10;

$drm->save();
$drm->validate();
$drm->save();

$latex = new DRMLatex($drm);

$t->ok($latex->getPDFFileContents(), "Génération du PDF");

$pdfFilename = $latex->getLatexDestinationDir()."drm_test_unitaire.pdf";

@unlink($pdfFilename);
rename($latex->getPDFFile(), $pdfFilename);
$t->info('PDF disponible : '.$pdfFilename);
