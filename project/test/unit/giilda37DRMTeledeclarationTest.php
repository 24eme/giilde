<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

sfConfig::set('app_teledeclaration_contact_contrat', array());
sfConfig::set('app_mail_from_email', "test_from_mail@mail.org");
sfConfig::set('app_teledeclaration_interpro', "Interpro");

$t = new lime_test(13);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_teledeclaration')->getEtablissement();
$periode = date('Ym');

$produits = array_keys(ConfigurationClient::getInstance()->getCurrent()->getProduits());
$produit_hash = array_shift($produits);

foreach(DRMClient::getInstance()->viewByIdentifiant($viti->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
}

$drm = DRMClient::getInstance()->createDoc($viti->identifiant, $periode, true);

$drm->addProduit($produit_hash, 'details');
$drm->addProduit($produit_hash, 'detailsACQUITTE');

$details = $drm->getProduit($produit_hash, 'details');
$detailsAcquitte = $drm->getProduit($produit_hash, 'detailsACQUITTE');

$t->comment("CRD");

$crd = $drm->crds->add(DRMClient::CRD_TYPE_SUSPENDU)->getOrAddCrdNode(DRMClient::DRM_CRD_CATEGORIE_TRANQ, DRMClient::DRM_CRD_VERT, 0.0075, "Bouteille 75 cl", 20);

$t->is($crd->getKey(), DRMClient::DRM_CRD_CATEGORIE_TRANQ."-".DRMClient::DRM_CRD_VERT."-750", "La clé est formaté correctement");
$t->is($crd->centilitrage, 0.0075, "Le centilitrage est en hl");

$t->comment("Annexe");

$t->ok(!$details->exist('observations'), "Aucune observation de base");
$details->sorties->manquant = 100;
$drm->update();
if(DRMConfiguration::getInstance()->isObservationsAuto()) {
    $t->is($details->observations, $details->sorties->getConfig()->get('manquant')->getLibelleLong(), "Une observation a bien été ajouté automatiquement avec le libellé du mouvement");
} else {
    $t->is($details->observations, "","Le champs observation a bien été ajouté avec une chaine de caractère vide");
}

$details->sorties->manquant = null;
$drm->update();

$t->ok(!$details->exist('observations'), "Si le volume est supprimé le champ observation aussi");

$t->comment("Validation");

$details->entrees->retourmarchandisetaxees = 100;
$drm->update();
if(DRMConfiguration::getInstance()->isObservationsAuto()) {
    $details->observations = null;
}

$validation = new DRMValidation($drm, true);

$t->ok($validation->hasErreur('observations'), "Un point bloquant obligeant la saisie des observations est levé");
$t->ok($validation->hasErreur('replacement_date'), "Un point bloquant obligeant la saisie de la date de replacement est levé");

$details->entrees->retourmarchandisetaxees = null;
$details->sorties->manquant = null;
$detailsAcquitte->sorties->autre = 100;
$drm->update();

$validation = new DRMValidation($drm, true);

$t->ok(!$validation->hasErreur('observations'), "Le point bloquant obligeant la saisie des observations n'est pas levé si le produit est acquitte");


$t->comment("Mail");

$mailManager = new DRMEmailManager(sfContext::getInstance()->getMailer());
$drm->email_transmission = "email_transmission@mail.org";
$mailManager->setDRM($drm);

$messages = $mailManager->composeMailValidation(true);

$t->is(count($messages), 2, "L'envoi compte 2 messages");

@mkdir(sfConfig::get('sf_test_dir')."/output");
file_put_contents(sfConfig::get('sf_test_dir')."/output/email_drm_validation_teldeclaration_douane_transmission_douane.eml", $messages[0]);
$t->ok(strpos($messages[0], " test_from_mail@ma") !== false, "Les infos de contact dans le mail sont bonnes");

$t->ok($messages[0] instanceof Swift_Mime_SimpleMessage, "Mail généré : ".sfConfig::get('sf_test_dir')."/output/email_drm_validation_teldeclaration_douane_transmission_douane.eml");

$t->is($mailManager->sendMailValidation(false), array($viti->getEmailTeledeclaration(), $drm->email_transmission), "Les retours des email sont ok");

$messages = $mailManager->composeMailValidation(false);

@mkdir(sfConfig::get('sf_test_dir')."/output");
file_put_contents(sfConfig::get('sf_test_dir')."/output/email_drm_validation_teldeclaration.eml", $messages[0]);

$t->ok($messages[0] instanceof Swift_Mime_SimpleMessage, "Mail généré : ".sfConfig::get('sf_test_dir')."/output/email_drm_validation_teldeclaration.eml");
