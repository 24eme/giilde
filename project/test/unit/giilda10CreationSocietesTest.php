<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

foreach (CompteTagsView::getInstance()->listByTags('test', 'test') as $k => $v) {
    if (preg_match('/SOCIETE-([^ ]*)/', implode(' ', array_values($v->value)), $m)) {
      $soc = SocieteClient::getInstance()->findByIdentifiantSociete($m[1]);
      foreach($soc->getEtablissementsObj() as $k => $etabl) {
          foreach (VracClient::getInstance()->retrieveBySoussigne($etabl->etablissement->identifiant)->rows as $k => $vrac) {
            $vrac_obj = VracClient::getInstance()->find($vrac->id);
            $vrac_obj->delete();
          }
          foreach (DRMClient::getInstance()->viewByIdentifiant($etabl->etablissement->identifiant) as $id => $drm) {
            $drm = DRMClient::getInstance()->find($id);
            $drm->delete(false);
          }
      }
      $soc->delete();
    }
}


$t = new lime_test(8);
$t->comment('création des différentes sociétés');

$codePostalRegion = "92100";

if($application == "ivbd") {
    $codePostalRegion = "24100";
}

$societeviti = SocieteClient::getInstance()->createSociete("société viti test", SocieteClient::TYPE_OPERATEUR);
$societeviti->pays = "FR";
$societeviti->code_postal = $codePostalRegion;
$societeviti->commune = "Neuilly sur seine";
$societeviti->insee = "94512";
$societeviti->save();
$id = $societeviti->getidentifiant();
$compteviti = CompteClient::getInstance()->findByIdentifiant($id.'01');
$compteviti->addTag('test', 'test');
$compteviti->addTag('test', 'test_viti');
$compteviti->save();
$t->is($compteviti->tags->automatique->toArray(true, false), array('societe', 'ressortissant'), "Création de société viti crée un compte du même type");

$societenegocvo = SocieteClient::getInstance()->createSociete("société négo de la région test", SocieteClient::TYPE_OPERATEUR);
$societenegocvo->pays = "FR";
$societenegocvo->code_postal = $codePostalRegion;
$societenegocvo->commune = "Neuilly sur seine";
$societenegocvo->save();
$id = $societenegocvo->getidentifiant();
$compte = CompteClient::getInstance()->findByIdentifiant($id.'01');
$compte->addTag('test', 'test');
$compte->addTag('test', 'test_nego_region');
$compte->save();
$t->is($compte->tags->automatique->toArray(true, false), array('societe', 'ressortissant'), "Création de société négo crée un compte du même type");

$societenegocvo = SocieteClient::getInstance()->createSociete("société négo 2 de la région test", SocieteClient::TYPE_OPERATEUR);
$societenegocvo->pays = "FR";
$societenegocvo->code_postal = $codePostalRegion;
$societenegocvo->commune = "Neuilly sur seine";
$societenegocvo->save();
$id = $societenegocvo->getidentifiant();
$compte = CompteClient::getInstance()->findByIdentifiant($id.'01');
$compte->addTag('test', 'test');
$compte->addTag('test', 'test_nego_region_2');
$compte->save();
$t->is($compte->tags->automatique->toArray(true, false), array('societe', 'ressortissant'), "Création de société négo 2 crée un compte du même type");

$societenegohors = SocieteClient::getInstance()->createSociete("société négo hors région test", SocieteClient::TYPE_OPERATEUR);
$societenegohors->pays = "BE";
$societenegohors->code_postal = "1000";
$societenegohors->commune = "Bruxelles";
$societenegohors->save();
$id = $societenegohors->getidentifiant();
$compte = CompteClient::getInstance()->findByIdentifiant($id.'01');
$compte->addTag('test', 'test');
$compte->addTag('test', 'test_nego_horsregion');
$compte->save();
$t->is($compte->tags->automatique->toArray(true, false), array('societe', 'ressortissant'), "Création de société négo hors région crée un compte du même type");

$societecourtier = SocieteClient::getInstance()->createSociete("société courtier test", SocieteClient::TYPE_COURTIER);
$societecourtier->pays = "FR";
$societecourtier->code_postal = $codePostalRegion;
$societecourtier->commune = "Neuilly sur seine";
$societecourtier->save();
$id = $societecourtier->getidentifiant();
$compte = CompteClient::getInstance()->findByIdentifiant($id.'01');
$compte->addTag('test', 'test_courtier');
$compte->addTag('test', 'test');
$compte->save();
$t->is($compte->tags->automatique->toArray(true, false), array('societe', 'intermediaire'), "Création de société courtier crée un compte du même type");

$societeintermediaire = SocieteClient::getInstance()->createSociete("société intermédiaire test", SocieteClient::TYPE_COURTIER);
$societeintermediaire->pays = "FR";
$societeintermediaire->code_postal = $codePostalRegion;
$societeintermediaire->commune = "Neuilly sur seine";
$societeintermediaire->save();
$id = $societeintermediaire->getidentifiant();
$compte = CompteClient::getInstance()->findByIdentifiant($id.'01');
$compte->addTag('test', 'test_intermediaire');
$compte->addTag('test', 'test');
$compte->save();
$t->is($compte->tags->automatique->toArray(true, false), array('societe', 'intermediaire'), "Création de société intermédiaire crée un compte du même type");

$societeintermediaire = SocieteClient::getInstance()->createSociete("société cooperative test", SocieteClient::TYPE_OPERATEUR);
$societeintermediaire->pays = "FR";
$societeintermediaire->code_postal = $codePostalRegion;
$societeintermediaire->commune = "Neuilly sur seine";
$societeintermediaire->save();
$id = $societeintermediaire->getidentifiant();
$compte = CompteClient::getInstance()->findByIdentifiant($id.'01');
$compte->addTag('test', 'test_cooperative');
$compte->addTag('test', 'test');
$compte->save();
$t->is($compte->tags->automatique->toArray(true, false), array('societe', 'ressortissant'), "Création de société intermédiaire crée un compte du même type");

$societeviti->date_modification = '2017-01-01';
$societeviti->save();
try {
  $societeviti->switchStatusAndSave();
  $t->is($societeviti->statut , SocieteClient::STATUT_SUSPENDU, "Changement de statut (suspendu) de la societe viti");
  $societeviti->date_modification = '2017-01-01';
  $societeviti->save();
  $societeviti->switchStatusAndSave();
  $t->is($societeviti->statut , SocieteClient::STATUT_ACTIF, "Changement de statut (actif) de la societe viti");
}catch(sfException $e) {
  $t->fail("Changement de statut de la societe viti");
}
