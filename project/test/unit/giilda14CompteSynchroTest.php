<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if($application == "civa") {
    sfConfig::set('app_compte_synchro', true);
}

foreach (CompteTagsView::getInstance()->listByTags('test', 'test_nom') as $k => $v) {
    if (preg_match('/SOCIETE-([^ ]*)/', implode(' ', array_values($v->value)), $m)) {
      $soc = SocieteClient::getInstance()->findByIdentifiantSociete($m[1]);
      $soc->delete();
    }
}

SocieteClient::getInstance()->clearSingleton();

$nomSociete = "société viti test contacts";
$nomModifieSociete = "société viti test contacts modifiées";
$nomEtablissement = "établissement viti test contacts";
$nomModifieEtablissement = "établissement viti test contacts modifiés";

$t = new lime_test(30);
$t->comment("Création d'une société");

$societe = SocieteClient::getInstance()->createSociete($nomSociete, SocieteClient::TYPE_OPERATEUR);
$societe->pays = "FR";
$societe->adresse = "42 rue dulud";
$societe->code_postal = "92100";
$societe->commune = "Neuilly sur seine";
$societe->email = 'email@example.org';
$societe->save();

$id = $societe->getidentifiant();
$compte01 = $societe->getMasterCompte();
$compte01->addTag('test', 'test');
$compte01->addTag('test', 'test_nom');
$compte01->save();

$compteStandalone = CompteClient::getInstance()->find($societe->getMasterCompte()->_id);

$t->is($societe->raison_sociale, $nomSociete, "La raison sociale de la société est :  \"".$nomSociete."\"");
$t->is($compteStandalone->nom, $societe->raison_sociale, "Le nom du compte et de la société sont identiques");
$t->is($compteStandalone->nom, $compteStandalone->nom_a_afficher, "Le \"nom\" et le \"nom à afficher\" du compte sont identiques");
$t->is($compteStandalone->code_postal, $societe->code_postal, "Le code postal du compte et de la société sont identiques");
$t->ok(in_array("societe", $compteStandalone->tags->automatique->toArray(true, false)),  "Le compte de la société possède le tag \"societe\"");

$t->comment("Modification des informations de la société");
$societe->code_postal = "75014";
$societe->telephone_mobile = "060000000";
$societe->save();
$compteStandalone = CompteClient::getInstance()->find($societe->getMasterCompte()->_id);

$t->is($compteStandalone->code_postal, $societe->code_postal, "Le code postal du compte et de la société sont identiques");
$t->is($compteStandalone->telephone_mobile, $societe->telephone_mobile, "Le téléphone mobile du compte et de la société sont identiques");

$t->comment("Création d'un établissement ayant la même adresse pour la société");

$etablissement = $societe->createEtablissement(EtablissementFamilles::FAMILLE_PRODUCTEUR);
$etablissement->nom = "établissement viti test contacts";
$etablissement->save();

$t->is($societe->raison_sociale, $nomSociete, "La raison sociale de la société est toujours :  \"".$nomSociete."\"");
$t->is($societe->getMasterCompte()->nom, $societe->raison_sociale, "Le nom du compte et de la société sont identiques");
$t->is($societe->getMasterCompte()->nom, $societe->getMasterCompte()->nom_a_afficher, "Le \"nom\" et le \"nom à afficher\" du compte sont identiques");
$t->is($societe->getMasterCompte()->_id, $etablissement->getMasterCompte()->_id, "La société et l'établissement ont le même id");
$t->ok(in_array("etablissement", CompteClient::getInstance()->find($societe->getMasterCompte()->_id)->tags->automatique->toArray(true, false)), "Le compte de la société possède le tag \"etablissement\"");

$t->comment("Modification de la raison sociale de la société");

$societe->raison_sociale = $nomModifieSociete;
$societe->save();

$t->is($societe->raison_sociale, $nomModifieSociete, "La raison sociale de la société est :  \"".$nomModifieSociete."\"");
$t->is($societe->getMasterCompte()->nom, $societe->raison_sociale, "Le nom du compte et de la société sont identiques");
$t->is($societe->getMasterCompte()->nom, $societe->getMasterCompte()->nom_a_afficher, "Le \"nom\" et le \"nom à afficher\" du compte sont identiques");
$t->is($societe->getMasterCompte()->_id, $etablissement->getMasterCompte()->_id, "La société et l'établissement ont toujours le même id");

$t->comment("Dissociation du compte d'établissement et de la société");

$etablissement->adresse = "rue dulud";
$etablissement->save();

$t->is($societe->raison_sociale, $nomModifieSociete, "La raison sociale de la société est toujours :  \"".$nomModifieSociete."\"");
$t->is($societe->getMasterCompte()->nom, $societe->raison_sociale, "Le nom du compte et de la société sont identiques");
$t->is($etablissement->nom, $nomEtablissement, "Le nom de l'établissement est : \"".$nomEtablissement."\"");
$t->is($etablissement->getMasterCompte()->nom, $etablissement->nom, "Le nom du compte et de l'établissement sont identiques");
$t->is($etablissement->getMasterCompte()->nom, $etablissement->getMasterCompte()->nom_a_afficher, "Le \"nom\" et le \"nom à afficher\" du compte de l'établissement sont identiques");
$t->ok(!in_array("etablissement", $societe->getMasterCompte()->tags->automatique->toArray(true, false)), "Le compte de la société ne possède plus le tag \"etablissement\"");
$t->ok(in_array("etablissement", CompteClient::getInstance()->find($etablissement->getMasterCompte()->_id)->tags->automatique->toArray(true, false)), "Le compte de l'établissement possède le tag \"etablissement\"");

$t->comment("Modification du nom de l'établissement");

$etablissement->nom = $nomModifieEtablissement;
$etablissement->save();

$t->is($societe->raison_sociale, $nomModifieSociete, "La raison sociale de la société est toujours :  \"".$nomModifieSociete."\"");
$t->is($societe->getMasterCompte()->nom, $societe->raison_sociale, "Le nom du compte et de la société sont identiques");

$t->is($etablissement->nom, $nomModifieEtablissement, "Le nom de l'établissement est : \"".$nomModifieEtablissement."\"");
$t->is($etablissement->getMasterCompte()->nom, $etablissement->nom, "Le nom du compte et de l'établissement sont identiques");
$t->is($etablissement->getMasterCompte()->nom, $etablissement->getMasterCompte()->nom_a_afficher, "Le \"nom\" et le \"nom à afficher\" du compte de l'établissement sont identiques");

$t->comment("Modification des informations de l'établissement");
$etablissement->code_postal = "75013";
$etablissement->telephone_mobile = "070000000";
$etablissement->save();
$compteStandalone = CompteClient::getInstance()->find($etablissement->getMasterCompte()->_id);

$t->is($compteStandalone->code_postal, $etablissement->code_postal, "Le code postal du compte et de la société sont identiques");
$t->is($compteStandalone->telephone_mobile, $etablissement->telephone_mobile, "Le téléphone mobile du compte et de la société sont identiques");
