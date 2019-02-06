<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if($application == "civa") {
    sfConfig::set('app_compte_synchro', true);
}

foreach (CompteTagsView::getInstance()->listByTags('test', 'test') as $k => $v) {
    if (preg_match('/SOCIETE-([^ ]*)/', implode(' ', array_values($v->value)), $m)) {
      $soc = SocieteClient::getInstance()->findByIdentifiantSociete($m[1]);
      foreach($soc->getEtablissementsObj() as $k => $etabl) {
        if ($etabl->etablissement) {
          foreach (VracClient::getInstance()->retrieveBySoussigne($etabl->etablissement->identifiant)->rows as $k => $vrac) {
            $vrac_obj = VracClient::getInstance()->find($vrac->id);
            $vrac_obj->delete();
          }
          foreach (DRMClient::getInstance()->viewByIdentifiant($etabl->etablissement->identifiant) as $id => $drm) {
            $drm = DRMClient::getInstance()->find($id);
            $drm->delete(false);
          }
          $etabl->etablissement->delete();
        }
      }
    }
}


$t = new lime_test(12);
$t->comment('création des différentes établissements');

$societeviti = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getSociete();
$etablissementviti = $societeviti->createEtablissement(EtablissementFamilles::FAMILLE_PRODUCTEUR);
$etablissementviti->region = EtablissementClient::REGION_CVO;
$etablissementviti->nom = "Etablissement viticulteur";
$etablissementviti->save();
$id = $etablissementviti->getSociete()->getidentifiant();
$compteviti = CompteClient::getInstance()->findByIdentifiant($id.'01');
$compteviti->addTag('test', 'test');
$compteviti->save();
$t->is($compteviti->tags->automatique->toArray(true, false), array('societe', 'ressortissant', 'producteur', 'etablissement'), "Création d'un etablissement viti met à jour le compte");
$t->is($etablissementviti->region, EtablissementClient::REGION_CVO, "L'établissement est en région CVO après le save");

$societeviti = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti_2')->getSociete();
$etablissementviti = $societeviti->createEtablissement(EtablissementFamilles::FAMILLE_PRODUCTEUR);
$etablissementviti->region = EtablissementClient::REGION_CVO;
$etablissementviti->nom = "Etablissement viticulteur 2";
$etablissementviti->no_accises = "FR123456789";
$etablissementviti->save();
$id = $etablissementviti->getSociete()->getidentifiant();
$compteviti = CompteClient::getInstance()->findByIdentifiant($id.'01');
$compteviti->addTag('test', 'test');
$compteviti->save();

$societenego = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_region')->getSociete();
$etablissementnego = $societenego->createEtablissement(EtablissementFamilles::FAMILLE_NEGOCIANT);
$etablissementnego->region = EtablissementClient::REGION_CVO;
$etablissementnego->no_accises = 'FR12345678912';
$etablissementnego->nom = "Etablissement negociant de la région";
$etablissementnego->save();
$id = $etablissementnego->getSociete()->getidentifiant();
$comptenego = CompteClient::getInstance()->findByIdentifiant($id.'01');
$comptenego->addTag('test', 'test');
$comptenego->save();
$t->is($comptenego->tags->automatique->toArray(true, false), array('societe', 'ressortissant', 'negociant', 'etablissement'), "Création d'un etablissement nego met à jour le compte");
$t->is($etablissementnego->region, EtablissementClient::REGION_CVO, "L'établissement est en région CVO après le save");

$societenego = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_region_2')->getSociete();
$etablissementnego = $societenego->createEtablissement(EtablissementFamilles::FAMILLE_NEGOCIANT);
$etablissementnego->region = EtablissementClient::REGION_CVO;
$etablissementnego->nom = "Etablissement negociant 2 de la région";
$etablissementnego->save();
$id = $etablissementnego->getSociete()->getidentifiant();
$comptenego = CompteClient::getInstance()->findByIdentifiant($id.'01');
$comptenego->addTag('test', 'test');
$comptenego->save();
$t->is($comptenego->tags->automatique->toArray(true, false), array('societe', 'ressortissant', 'negociant', 'etablissement'), "Création d'un etablissement nego 2 met à jour le compte");

$societemixte = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_viti_region')->getSociete();
$etablissementnegoinmixte = $societemixte->createEtablissement(EtablissementFamilles::FAMILLE_NEGOCIANT);
$etablissementnegoinmixte->region = EtablissementClient::REGION_CVO;
$etablissementnegoinmixte->nom = "Etablissement negociant dans societe mixte de la région";
$etablissementnegoinmixte->save();
$id = $etablissementnegoinmixte->getSociete()->getidentifiant();
$comptenegoinmixte = CompteClient::getInstance()->findByIdentifiant($id.'01');
$comptenegoinmixte->addTag('test', 'test');
$comptenegoinmixte->addTag('test', 'test_mixte_nego_region');
$comptenegoinmixte->save();
$t->is($comptenegoinmixte->tags->automatique->toArray(true, false), array('societe', 'ressortissant', 'negociant', 'etablissement'), "Création d'un etablissement nego dans une societe mixte met à jour le compte");

SocieteClient::getInstance()->clearSingleton();
$societemixte = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_viti_region')->getSociete();
$etablissementvitiinmixte = $societemixte->createEtablissement(EtablissementFamilles::FAMILLE_PRODUCTEUR);
$etablissementvitiinmixte->region = EtablissementClient::REGION_CVO;
$etablissementvitiinmixte->email = "viti@email.com";
$etablissementvitiinmixte->nom = "Etablissement negociant dans societe mixte de la région";
$etablissementvitiinmixte->save();
$comptevitiinmixte = $etablissementvitiinmixte->getMasterCompte();
$comptevitiinmixte->addTag('test', 'test');
$comptevitiinmixte->addTag('test', 'test_mixte_viti_region');
$comptevitiinmixte->save();
$t->is($comptevitiinmixte->tags->automatique->toArray(true, false), array('etablissement', 'producteur'), "Création d'un etablissement viti dans une societe mixte met à jour le compte");

$societenego_horsregion = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_horsregion')->getSociete();
$etablissementnego_horsregion = $societenego_horsregion->createEtablissement(EtablissementFamilles::FAMILLE_NEGOCIANT);
$etablissementnego_horsregion->region = EtablissementClient::REGION_HORS_CVO;
$etablissementnego_horsregion->nom = "Etablissement negociant hors région";
$etablissementnego_horsregion->save();
$id = $etablissementnego_horsregion->getSociete()->getidentifiant();
$comptenego_horsregion = CompteClient::getInstance()->findByIdentifiant($id.'01');
$comptenego_horsregion->addTag('test', 'test');
$comptenego_horsregion->save();
$t->is($comptenego_horsregion->tags->automatique->toArray(true, false), array('societe', 'ressortissant', 'negociant', 'etablissement'), "Création d'un etablissement nego_horsregion met à jour le compte");
$t->is($etablissementnego_horsregion->region, EtablissementClient::REGION_HORS_CVO, "L'établissement est hors région CVO après le save");

$societecourtier = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_courtier')->getSociete();
$etablissementcourtier = $societecourtier->createEtablissement(EtablissementFamilles::FAMILLE_COURTIER);
$etablissementcourtier->nom = "Etablissement de courtage";
$etablissementcourtier->save();
$id = $etablissementcourtier->getSociete()->getidentifiant();
$comptecourtier = CompteClient::getInstance()->findByIdentifiant($id.'01');
$comptecourtier->addTag('test', 'test');
$comptecourtier->save();
$t->is($comptecourtier->tags->automatique->toArray(true, false), array('societe', 'intermediaire', 'etablissement', 'courtier'), "Création d'un etablissement courtier met à jour le compte");

$societeintermediaire = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_intermediaire')->getSociete();
$etablissementintermediaire = $societeintermediaire->createEtablissement(EtablissementFamilles::FAMILLE_REPRESENTANT);
$etablissementintermediaire->region = EtablissementClient::REGION_CVO;
$etablissementcourtier->nom = "Etablissement d'intermediaire de la région";
$etablissementintermediaire->save();
$id = $etablissementintermediaire->getSociete()->getidentifiant();
$compteintermediaire = CompteClient::getInstance()->findByIdentifiant($id.'01');
$compteintermediaire->addTag('test', 'test');
$compteintermediaire->save();
$t->is($compteintermediaire->tags->automatique->toArray(true, false), array('societe', 'intermediaire', 'etablissement', 'representant'), "Création d'un etablissement intermediaire met à jour le compte");

$societecoop = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_cooperative')->getSociete();
$etablissementcoop = $societecoop->createEtablissement(EtablissementFamilles::FAMILLE_COOPERATIVE);
$etablissementcoop->region = EtablissementClient::REGION_CVO;
$etablissementcoop->nom = "Etablissement coopérative de la région";
$etablissementcoop->save();
$id = $etablissementcoop->getSociete()->getidentifiant();
$comptecoop = CompteClient::getInstance()->findByIdentifiant($id.'01');
$comptecoop->addTag('test', 'test');
$comptecoop->save();
$t->is($comptecoop->tags->automatique->toArray(true, false), array('societe', 'ressortissant', 'cooperative', 'etablissement'), "Création d'un etablissement coop met à jour le compte");
