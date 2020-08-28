<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');
sfContext::createInstance($configuration);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_teledeclaration')->getEtablissement();
$operation = 'COVID';

$subvention = SubventionClient::getInstance()->find('SUBVENTION-'.$viti->identifiant.'-'.$operation, acCouchdbClient::HYDRATE_JSON);
if($subvention) {
    acCouchdbManager::getClient()->deleteDoc($subvention);
}

$t = new lime_test(7);

$t->comment('Creation du document');

$subvention = SubventionClient::getInstance()->createDoc($viti->identifiant, $operation);

$t->is($subvention->_id, 'SUBVENTION-'.$viti->identifiant.'-'.$operation, 'id de document généré');

$subvention->save();

$t->ok($subvention->_rev, 'Enregistrement du document');
$t->is($subvention->declarant->raison_sociale, $viti->raison_sociale, "Declrant Raison sociale");
$t->is($subvention->declarant->siret, $viti->siret, "Declarant Siret");
$t->ok($subvention->infos->exist('contacts/nom'), "Le schema a été initialisé");
$t->ok(!$subvention->infos->exist('contacts_libelle'), "Le libellé n'a pas été pris en compte dans le schema");
$t->is($subvention->infos->contacts->getLibelle(), "Contacts de la personne en charge du dossier au sein de l’entreprise", "On peut récupérer le libellé");
