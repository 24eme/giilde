<?php

require_once(dirname(__FILE__) . '/../bootstrap/common.php');

if (! sfConfig::get('app_ldap_autoupdate', false)) {
    $t = new lime_test(0);
    exit(0);
}

$test      = new lime_test();
$LDAPUser  = new CompteLdap();
$LDAPGroup = new CompteGroupLdap();

$test->comment("Synchro avec le LDAP");

$groupe = 'groupe_test_1';

$comptes = [];
$comptesid = [];

foreach (['000001', '000002'] as $id) {
    $comptes[] = CompteClient::getInstance()->findByIdentifiant($id.'01');
}

foreach ($comptes as $compte) {
    $comptesid[] = ($compte->isSocieteContact()) ? $compte->getSociete()->identifiant : $compte->identifiant;
}

if ($LDAPGroup->exist($groupe)) {
    $test->ok($LDAPGroup->delete($groupe), "Suppression du groupe $groupe");
}

$test->ok($LDAPUser->saveCompte($comptes[0]), "Création de compte $comptesid[0]");
$test->ok($LDAPUser->saveCompte($comptes[1]), "Création de compte $comptesid[1]");

$test->isnt($LDAPGroup->exist($groupe), true, "Le groupe $groupe n'existe pas");

$test->ok($LDAPGroup->saveGroup($groupe, $comptesid[0]), "Ajout du compte $comptesid[0] dans le groupe $groupe qui n'existe pas");
$test->ok($LDAPGroup->exist($groupe), "Le groupe $groupe existe maintenant");
$membership = $LDAPGroup->getMembership($comptesid[0]);
$test->ok(in_array($groupe, $membership), "Le compte $comptesid[0] est dans le groupe $groupe créé");

$test->ok($LDAPGroup->saveGroup($groupe, $comptesid[1]), "Ajout du compte $comptesid[1] dans le groupe $groupe qui existe");
$membership = $LDAPGroup->getMembership($comptesid[1]);
$test->ok(in_array($groupe, $membership), "Le compte $comptesid[1] est dans le groupe $groupe déjà présent");

$test->ok($LDAPGroup->removeMember($groupe, $comptesid[1]), "Suppression du membre $comptesid[1] du groupe $groupe");
$test->ok($LDAPGroup->exist($groupe), "Le groupe $groupe existe toujours");
$membership = $LDAPGroup->getMembership($comptes[1]);
$test->isnt(in_array($groupe, $membership), true, "Le compte $comptesid[1] n'est plus dans le groupe");

$test->ok($LDAPGroup->removeMember($groupe, $comptesid[0]), "Suppression du dernier compte ($comptesid[0]) du groupe $groupe");
$test->ok(! $LDAPGroup->exist($groupe), "Le groupe $groupe n'existe plus");
$membership = $LDAPGroup->getMembership($comptesid[0]);
$test->ok(! in_array($groupe, $membership), "Le compte $comptesid[0] n'est plus dans le groupe $groupe");

$test->ok($LDAPUser->deleteCompte($comptes[0]), "Suppression du compte $comptesid[0]");
$test->ok(! $LDAPUser->exist($comptes[0]), "Le compte $comptesid[0] n'existe plus");

$test->ok($LDAPUser->deleteCompte($comptes[1]), "Suppression du compte $comptesid[1]");
$test->ok(! $LDAPUser->exist($comptes[1]), "Le compte $comptesid[1] n'existe plus");
