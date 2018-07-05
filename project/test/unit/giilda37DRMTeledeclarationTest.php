<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

sfConfig::set('app_teledeclaration_contact_contrat', array());
sfConfig::set('app_mail_from_email', "test_from_mail@mail.org");
sfConfig::set('app_teledeclaration_interpro', "Interpro");

$t = new lime_test(4);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_teledeclaration')->getEtablissement();
$periode = date('Ym');

foreach(DRMClient::getInstance()->viewByIdentifiant($viti->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
}

$mailManager = new DRMEmailManager(sfContext::getInstance()->getMailer());
$drm = DRMClient::getInstance()->createDoc($viti->identifiant, $periode, true);
$drm->email_transmission = "email_transmission@mail.org";
$mailManager->setDRM($drm);

$messages = $mailManager->composeMailValidation();

$t->is(count($messages), 2, "L'envoi compte 2 messages");

@mkdir(sfConfig::get('sf_test_dir')."/output");
file_put_contents(sfConfig::get('sf_test_dir')."/output/email_drm_validation_teldeclaration.eml", $messages[0]);

$t->ok(strpos($messages[0], "de INTERPRO : test_from_mail@mail.org") !== false, "Les infos de contact dans le mail sont bonnes");

$t->ok($messages[0] instanceof Swift_Mime_SimpleMessage, "Mail généré : ".sfConfig::get('sf_test_dir')."/output/email_drm_validation_teldeclaration.eml");

$t->is($mailManager->sendMailValidation(false), array($viti->getEmailTeledeclaration(), $drm->email_transmission), "Les retours des email sont ok");
