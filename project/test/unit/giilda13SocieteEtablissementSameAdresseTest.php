<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if($application == "civa") {
    sfConfig::set('app_compte_synchro', true);
}

foreach (CompteTagsView::getInstance()->listByTags('test', 'test_same') as $k => $v) {
    if (preg_match('/SOCIETE-([^ ]*)/', implode(' ', array_values($v->value)), $m)) {
      $soc = SocieteClient::getInstance()->findByIdentifiantSociete($m[1]);
      $soc->delete();
    }
}

SocieteClient::getInstance()->clearSingleton();

$t = new lime_test(19);
$t->comment("création d'une société et un etablissement avec meme adresse et meme contact");

$societe = SocieteClient::getInstance()->createSociete("société viti test contacts", SocieteClient::TYPE_OPERATEUR);
$societe->pays = "FR";
$societe->adresse = "42 rue dulud";
$societe->code_postal = "92100";
$societe->commune = "Neuilly sur seine";
$societe->email = 'email@example.org';
$societe->save();
$id = $societe->getidentifiant();
$compte01 = $societe->getMasterCompte();
$compte01->addTag('test', 'test');
$compte01->addTag('test', 'test_same');
$compte01->save();
$t->is($compte01->email, $societe->email, $societe->_id." : le compte de la societe a le même mail que la societe");

$etablissement = $societe->createEtablissement(EtablissementFamilles::FAMILLE_PRODUCTEUR);
$etablissement->nom = "établissement viti test contacts";
$etablissement->save();

$t->is($etablissement->isSameAdresseThan($societe), true, $etablissement->_id." : un etablissement créé depuis une société a la même adresse");
$t->is($etablissement->isSameContactThan($societe), true, $etablissement->_id." : un etablissement créé depuis une société a les même contacts");
$t->is($etablissement->getMasterCompte()->_id, $societe->getMasterCompte()->_id, $etablissement->_id." : un etablissement créé depuis une société a le même compte");
$t->ok(SocieteClient::getInstance()->find($societe->_id)->etablissements->exist($etablissement->_id), $etablissement->_id." : L'établissement est referencé dans la société");

$etablissement->adresse = "rue dulud";
$etablissement->save();
$compte02 = $etablissement->getMasterCompte();
$compte02->addTag('test', 'test');
$compte02->addTag('test', 'test_same');
$compte02->save();

$t->is($etablissement->isSameAdresseThan($societe), false, $etablissement->_id." : un etablissement dont on a changé l'adresse n'a plus la même adresse que la societe");
$t->is($etablissement->isSameContactThan($societe), true, $etablissement->_id." : un etablissement dont on a changé l'adresse  a les même contacts que la societe");
$t->isnt($etablissement->getMasterCompte()->_id, $societe->getMasterCompte()->_id, $etablissement->_id." : un etablissement dont on a changé l'adresse n'a pas le même compte de la societe");
$t->ok(SocieteClient::getInstance()->find($societe->_id)->contacts->exist($etablissement->getMasterCompte()->_id), $etablissement->_id." : Le compte de l'établissement est referencé dans la société");

$idContactEtablissement = $etablissement->getMasterCompte()->_id;

$etablissement->email = "contact@exemple.fr";
$etablissement->save();
$t->is($etablissement->isSameAdresseThan($societe), false, $etablissement->_id." : un etablissement dont on a changé l'adresse et l'email n'a plus la même adresse que la societe");
$t->is($etablissement->isSameContactThan($societe), false, $etablissement->_id." : un etablissement dont on a changé l'email a les même contacts que la societe");
$t->isnt($etablissement->getMasterCompte()->_id, $societe->getMasterCompte()->_id, $etablissement->_id." : un etablissement dont on a changé l'adresse n'a pas le même compte que la societe");

$etablissement->adresse = NULL;
$etablissement->email = NULL;
$etablissement->save();
$t->is($etablissement->isSameAdresseThan($societe), true, $etablissement->_id." : un etablissement dont l'adresse et l'email sont RAZ, a la même adresse que la societe");
$t->is($etablissement->isSameContactThan($societe), true, $etablissement->_id." : un etablissement dont l'adresse et l'email sont RAZ, a les même contacts que la societe");
$t->is($etablissement->getMasterCompte()->_id, $societe->getMasterCompte()->_id, $etablissement->_id." : un etablissement dont l'adresse et l'email sont RAZ a le même compte que la societe");
$t->ok(!SocieteClient::getInstance()->find($societe->_id)->contacts->exist($idContactEtablissement), $etablissement->_id." : Le compte de l'établissement n'est plus referencé dans la société");

$societe->raison_sociale = "société viti test contacts modifié";
$societe->adresse = "rue du chateau";
$societe->email = "email2@example.org";
$societe->save();
$etablissement = EtablissementClient::getInstance()->find($etablissement->_id);

$t->is($etablissement->adresse, $societe->adresse, $etablissement->_id." : l'établissement à la même adresse que la société après modification");
$t->is($etablissement->email, $societe->email, $etablissement->_id." : l'établissement à le même email que la société après modification");
$t->is($etablissement->getMasterCompte()->_id, $societe->getMasterCompte()->_id, $etablissement->_id." : l'établissement à le même compte que la société");
