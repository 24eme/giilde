<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

foreach (CompteTagsView::getInstance()->listByTags('test', 'test_teledeclaration') as $k => $v) {
    if (preg_match('/SOCIETE-([^ ]*)/', implode(' ', array_values($v->value)), $m)) {
      $soc = SocieteClient::getInstance()->findByIdentifiantSociete($m[1]);
      $soc->delete();
    }
}

SocieteClient::getInstance()->clearSingleton();

$t = new lime_test(50);

$t->comment("Création de la société");

$societe = SocieteClient::getInstance()->createSociete("société viti test télédéclaration", SocieteClient::TYPE_OPERATEUR);
$societe->pays = "FR";
$societe->adresse = "42 rue dulud";
$societe->code_postal = "92100";
$societe->commune = "Neuilly sur seine";
$societe->save();

$id = $societe->getidentifiant();
$compte01 = $societe->getMasterCompte();
$compte01->add('droits', array('teledeclaration'));
$compte01->addTag('test', 'test');
$compte01->addTag('test', 'test_teledeclaration');
$compte01->save();

$t->comment("Création de l'établissement");

$etablissement = $societe->createEtablissement(EtablissementFamilles::FAMILLE_PRODUCTEUR);
$etablissement->nom = "établissement viti test télédéclaration";
$etablissement->save();

$etablissement2 = $societe->createEtablissement(EtablissementFamilles::FAMILLE_NEGOCIANT);
$etablissement2->nom = "établissement négociant test télédéclaration 2";
$etablissement2->adresse = "2 rue des Cailloux";
$etablissement2->email = "emaildejaesxistant@email.fr";
$etablissement2->save();

$etablissement3 = $societe->createEtablissement(EtablissementFamilles::FAMILLE_NEGOCIANT);
$etablissement3->nom = "établissement négociant test télédéclaration 2";
$etablissement3->telephone = "0101010101";
$etablissement3->no_accises = "FR1215";
$etablissement3->save();

$etablissement4 = $societe->createEtablissement(EtablissementFamilles::FAMILLE_PRODUCTEUR);
$etablissement4->nom = "établissement viti test télédéclaration 4";
$etablissement4->save();

$t->comment("Formulaire de création du compte avec une société qui n'a pas d'email");

$form = new CompteTeledeclarantCreationForm(CompteClient::getInstance()->find($compte01->_id));
$form->bind(array('_revision' => $compte01->_rev, 'email' => 'email@email.fr', 'mdp1' => 'testtest', 'mdp2' => 'testtest', 'siret' => "12345678912345", 'num_accises' => "FR12345678912"));

$t->ok($form->isValid(), "Le formulaire est valide");

$form->save();

$societe = SocieteClient::getInstance()->find($societe->_id);
$etablissement = EtablissementClient::getInstance()->find($etablissement->_id);
$etablissement2 = EtablissementClient::getInstance()->find($etablissement2->_id);
$etablissement3 = EtablissementClient::getInstance()->find($etablissement3->_id);
$etablissement4 = EtablissementClient::getInstance()->find($etablissement4->_id);
$compte = CompteClient::getInstance()->find($compte01->_id);

$t->ok(preg_match('/^{SSHA}/', $compte->mot_de_passe), "Le mot de passe du compte a été enregistré");
$t->ok($compte->exist('teledeclaration_active') && $compte->teledeclaration_active, "Le compte possède le flag teledeclaration_active");
$t->is($compte->email, "email@email.fr", "Le compte a l'email email@email.fr");

$t->is($societe->email, "email@email.fr", "La société a l'email email@email.fr");
$t->ok(!$societe->exist('teledeclaration_email'), "La société n'a pas d'email de télédéclaration");
$t->is($societe->siret, "12345678912345", "Le siret a bien été enregistré dans la société");

$t->is($etablissement->compte, $compte->_id, "L'établissement a le même compte que la société");
$t->is($etablissement->email, "email@email.fr", "L'établissement a l'email email@email.fr");
$t->is($etablissement->teledeclaration_email, "email@email.fr", "L'établissement a l'email de télédéclaration email@email.fr");
$t->is($etablissement->no_accises, "FR12345678912", "Le numéro d'accises a bien été enregistré dans l'établissement");

$t->is($etablissement2->email, "emaildejaesxistant@email.fr", "L'email de l'établissement n°2 n'a pas bougé");
$t->is($etablissement2->teledeclaration_email, "email@email.fr", "L'établissement n°2 a l'email de télédéclaration email@email.fr");
$t->is($etablissement2->no_accises, null, "Le numéro d'accises n'a pas bougé pour l'établissement 2");

$t->is($etablissement3->email, "email@email.fr", "L'email de l'établissement n°3 est email@email.fr");
$t->is($etablissement3->teledeclaration_email, "email@email.fr", "L'établissement n°3 a l'email de télédéclaration email@email.fr");
$t->is($etablissement3->no_accises, "FR1215", "Le numéro d'accises n'a pas bougé pour l'établissement n°3");

$t->comment("Formulaire de création du compte avec une société qui a déjà un email");

$compte->mot_de_passe = "{TEXT}1234";
$compte->teledeclaration_active = false;
$compte->save();

$form = new CompteTeledeclarantCreationForm(CompteClient::getInstance()->find($compte01->_id));

$t->is($form->getDefault('email') , "email@email.fr", "L'email a bien été pré-rempli dans le formulaire");

$form->bind(array('_revision' => $compte->_rev, 'email' => 'courriel@courriel.fr', 'mdp1' => 'testtest', 'mdp2' => 'testtest', 'siret' => null, 'num_accises' => null));
$form->save();

$t->ok($form->isValid(), "Le formulaire est valide");

$societe = SocieteClient::getInstance()->find($societe->_id);
$compte = CompteClient::getInstance()->find($compte01->_id);
$etablissement = EtablissementClient::getInstance()->find($etablissement->_id);
$etablissement2 = EtablissementClient::getInstance()->find($etablissement2->_id);
$etablissement3 = EtablissementClient::getInstance()->find($etablissement3->_id);

$t->ok(preg_match('/^{SSHA}/', $compte->mot_de_passe), "Le mot de passe du compte a été enregistré");

$t->ok($compte->exist('teledeclaration_active') && $compte->teledeclaration_active, "Le compte possède le flag teledeclaration_active");
$t->is($compte->email, "email@email.fr", "L'email du compte n'a pas bougé");

$t->is($societe->email, "email@email.fr", "L'email de la société n'a pas bougé");
$t->ok(!$societe->exist('teledeclaration_email'), "La société n'a pas d'email de télédéclaration");

$t->is($etablissement->compte, $compte->_id, "L'établissement a le même compte que la société");
$t->is($etablissement->email, "email@email.fr", "L'email de l'établissement n'a pas bougé");
$t->is($etablissement->no_accises, "FR12345678912", "Le numéro d'accises de l'établissement n'a pas bougé");
$t->is($etablissement->teledeclaration_email, "courriel@courriel.fr", "L'établissement a l'email de télédéclaration courriel@courriel.fr");
$t->is($societe->siret, "12345678912345", "Le siret de la société n'a pas bougé");

$t->is($etablissement2->email, "emaildejaesxistant@email.fr", "L'email de l'établissement n°2 n'a pas bougé");
$t->is($etablissement2->teledeclaration_email, "courriel@courriel.fr", "L'établissement n°2 a l'email de télédéclaration courriel@courriel.fr");

$t->is($etablissement3->email, "email@email.fr", "L'email de l'établissement n°3 est email@email.fr");
$t->is($etablissement3->teledeclaration_email, "courriel@courriel.fr", "L'établissement n°3 a l'email de télédéclaration courriel@courriel.fr");

$t->comment("Formulaire de modification d'un compte de télédéclarant modification de l'email");

$compte = CompteClient::getInstance()->find($compte01->_id);

$motDePasse = $compte->mot_de_passe;
$form = new CompteTeledeclarantForm($compte);

$t->is($form->getDefault('email'), "courriel@courriel.fr", "L'email a bien été pré-rempli dans le formulaire");

$form->bind(array('_revision' => $compte->_rev, 'email' => 'courriel2@courriel2.fr'));
$form->save();

$t->ok($form->isValid(), "Le formulaire est valide");

$societe = SocieteClient::getInstance()->find($societe->_id);
$compte = CompteClient::getInstance()->find($compte01->_id);
$etablissement = EtablissementClient::getInstance()->find($etablissement->_id);
$etablissement2 = EtablissementClient::getInstance()->find($etablissement2->_id);
$etablissement3 = EtablissementClient::getInstance()->find($etablissement3->_id);

$t->is($compte->email, "email@email.fr", "L'email du compte n'a pas bougé");
$t->is($compte->mot_de_passe, $motDePasse, "Le mot de passe n'a pas bougé");
$t->is($societe->email, "email@email.fr", "L'email de la société n'a pas bougé");
$t->is($etablissement->teledeclaration_email, "courriel2@courriel2.fr", "L'email a bien été mise à jour");
$t->is($etablissement->email, "email@email.fr", "L'email de l'établissement n'a pas bougé");
$t->is($etablissement2->teledeclaration_email, "courriel2@courriel2.fr", "L'email a bien été mise à jour");
$t->is($etablissement3->teledeclaration_email, "courriel2@courriel2.fr", "L'email a bien été mise à jour");

$t->comment("Formulaire de modification d'un compte de télédéclarant modification du mot de passe");

$compte = CompteClient::getInstance()->find($compte01->_id);

$motDePasse = $compte->mot_de_passe;
$form = new CompteTeledeclarantForm($compte);

$t->is($form->getDefault('email'), "courriel2@courriel2.fr", "L'email a bien été pré-rempli dans le formulaire");

$form->bind(array('_revision' => $compte->_rev, 'email' => 'courriel2@courriel2.fr', 'mdp1' => 'yopyopyop', 'mdp2' => 'yopyopyop'));
$form->save();

$t->ok($form->isValid(), "Le formulaire est valide");

$compte = CompteClient::getInstance()->find($compte01->_id);

$t->ok(($compte->mot_de_passe != $motDePasse), "Le mot de passe du compte a changé");

$t->comment("Formulaire de mot de passe oublié");

$compte = CompteClient::getInstance()->find($compte01->_id);

$motDePasse = $compte->mot_de_passe;
$form = new CompteTeledeclarantOublieForm($compte);
$form->bind(array('_revision' => $compte->_rev, 'mdp1' => 'nopnopnop', 'mdp2' => 'nopnopnop'));
$form->save();

$t->ok($form->isValid(), "Le formulaire est valide");

$compte = CompteClient::getInstance()->find($compte01->_id);
$etablissement = EtablissementClient::getInstance()->find($etablissement->_id);
$etablissement2 = EtablissementClient::getInstance()->find($etablissement2->_id);
$etablissement3 = EtablissementClient::getInstance()->find($etablissement3->_id);

$t->ok(($compte->mot_de_passe != $motDePasse), "Le mot de passe du compte a changé");

$t->is($etablissement->teledeclaration_email, "courriel2@courriel2.fr", "L'email n'a pas bougé");
$t->is($etablissement2->teledeclaration_email, "courriel2@courriel2.fr", "L'email n'a pas bougé");
$t->is($etablissement3->teledeclaration_email, "courriel2@courriel2.fr", "L'email n'a pas bougé");
