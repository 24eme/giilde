<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if (getenv("NODELETE")) {
    $t = new lime_test(0);
    exit(0);
}
$nbtest = 36;
if(($application == "bivc")){
  $nbtest = 41;
}

$t = new lime_test($nbtest);

$t->comment('suppression des différentes sociétés, de leurs établissements et comptes');

$clientcond = false;
foreach (CompteTagsView::getInstance()->listByTags('test', 'test') as $k => $v) {
    if (preg_match('/SOCIETE-([^ ]*)/', implode(' ', array_values($v->value)), $m)) {
        $soc = SocieteClient::getInstance()->findByIdentifiantSociete($m[1]);
        foreach($soc->getEtablissementsObj() as $k => $etabl) {
            if($etabl->etablissement){
              foreach (VracClient::getInstance()->retrieveBySoussigne($etabl->etablissement->identifiant)->rows as $k => $vrac) {
                  $vrac_obj = VracClient::getInstance()->find($vrac->id);
                  $vrac_obj->delete();
                  $t->is(VracClient::getInstance()->find($vrac->id), null, "Suppression du contrat ".$vrac->id);
              }
              foreach (DRMClient::getInstance()->viewByIdentifiant($etabl->etablissement->identifiant) as $id => $drm) {
                  $drm = DRMClient::getInstance()->find($id);
                  $drm->delete(false);
                  $t->is(DRMClient::getInstance()->find($id), null, "Suppression de la DRM ".$id);
              }
          }
        }
        foreach (FactureSocieteView::getInstance()->findBySociete($soc) as $id => $facture) {
            $facture = FactureClient::getInstance()->find($id);
            $facture->delete();
            $t->is(FactureClient::getInstance()->find($id), null, "Suppression de la Facture ".$id);
        }
        $soc->delete();
        $t->is(CompteClient::getInstance()->findByIdentifiant($m[1].'01'), null, "Suppression de la sociétés ".$m[1]." provoque la suppression de son compte");
    }
}

if($doc = MouvementsFactureClient::getInstance()->find("MOUVEMENTSFACTURE-TEST")) {
    $doc->delete();
    $t->is(MouvementsFactureClient::getInstance()->find("MOUVEMENTSFACTURE-TEST"), null, "Suppression du document de mouvements de facturation MOUVEMENTSFACTURE-TEST");
}
