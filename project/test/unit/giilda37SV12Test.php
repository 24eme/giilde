<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(3);
$nego =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_region_2')->getEtablissement();
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$config = ConfigurationClient::getInstance()->getCurrent();
$produits = array_keys($config->getProduits());
$produit1_hash = array_shift($produits);
$produit1 = $config->get($produit1_hash);
$produit2_hash = array_shift($produits);
$produit2 = $config->get($produit2_hash);

$periode = "2017-2018";

$idSV12 = SV12Client::getInstance()->buildId($nego->identifiant, $periode);

//Suppression de la SV12 précédentes

if($sv12 = SV12Client::getInstance()->find($idSV12)) {
  acCouchdbManager::getClient()->delete($sv12);
}

$t->comment("Création d'une SV12 ".$nego->identifiant);

$sv12 = SV12Client::getInstance()->createOrFind($nego->identifiant, $periode);

$sv12->save();
$t->is($sv12->_id,"SV12-".$nego->identifiant."-2017-2018","LA sv12 a pour ID SV12-'.$nego->identifiant.'-2017-2018");

$sv12Contrat = $sv12->contrats->add(SV12Client::SV12_KEY_SANSVITI.'-'.$nego->identifiant.'-'.SV12Client::SV12_TYPEKEY_VENDANGE.'-'.str_replace('/', '-', $produit1_hash));
$sv12Contrat->updateNoContrat($config->get($produit1_hash), array('vendeur_identifiant' => $viti->identifiant, 'vendeur_nom' => $viti->nom, 'contrat_type' => SV12Client::SV12_TYPEKEY_VENDANGE,'volume' => 1.0));

$t->is(count($sv12->getMouvements()), 0, "La sv12 n'a pas de mouvement");

$sv12->validate();
$sv12->save();
$t->is(count($sv12->getMouvements()), 2, "La sv12 à 2 mouvements");
