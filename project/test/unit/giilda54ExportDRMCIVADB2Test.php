<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if($application != "civa") {
    $t = new lime_test(0);
    exit(0);
}

$viti = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_teledeclaration')->getEtablissement();
$societe = $viti->getSociete();
$periode = date('Ym');
$date = date('Ymd');
$dateFinMois = new DateTime();
$dateFinMois->modify('last day of this month');
$dateFinMois = $dateFinMois->format('Ymd');

//Suppression des DRM précédentes
foreach(DRMClient::getInstance()->viewByIdentifiant($viti->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
  $csv = CSVDRMClient::getInstance()->find(str_replace("DRM", "CSVDRM", $k));
    if($csv) {
        $csv->delete(false);
    }
}

$preLigneCaveCSV = "CAVE;".$periode.";".$viti->identifiant.";";
$preLigneCRDCSV = "CRD;".$periode.";".$viti->identifiant.";";

$csv = <<<EOF
$preLigneCaveCSV;AOC;;AOC Alsace blanc;;;;Chasselas;;AOC Alsace blanc Chasselas (1B001S 5);suspendu;entrees;recolte;20;;;
$preLigneCaveCSV;AOC;;AOC Alsace blanc;;;;Chasselas;;AOC Alsace blanc Chasselas (1B001S 5);suspendu;sorties;export;10;DE;;
$preLigneCaveCSV;AOC;;AOC Alsace Lieu-dit;;;Blanc;Riesling;;AOC Alsace Lieu-dit Riesling (1B070S04);suspendu;entrees;recolte;10;;;
$preLigneCaveCSV;AOC;;AOC Alsace Communale;;Côte de Rouffach;Rouge;Pinot Noir;;AOC Alsace Communale Côte de Rouffach Pinot Noir (1R057S);suspendu;entrees;recolte;20;;;
$preLigneCaveCSV;AOC;;AOC Alsace Communale;;Vallée Noble;Blanc;Pinot Gris;;AOC Alsace Communale Vallée Noble Pinot Gris (1B062S03);suspendu;entrees;recolte;10;;;
$preLigneCaveCSV;AOC;;AOC Alsace Grand Cru;;Sommerberg;;Riesling;;AOC Alsace Grand Cru Sommerberg Riesling (1B039S 4);suspendu;entrees;recolte;10;;;
$preLigneCaveCSV;AOC;;AOC Alsace Grand Cru;;Sommerberg;;Riesling;;AOC Alsace Grand Cru Sommerberg Riesling (1B039S 4);suspendu;sorties;export;10;ES;;
$preLigneCaveCSV;AOC;;AOC Alsace Grand Cru;VT;Sommerberg;;Pinot Gris;;AOC Alsace Grand Cru Sommerberg Pinot Gris VT (1B039D13);suspendu;entrees;recolte;10;;;
$preLigneCaveCSV;AOC;;AOC Alsace Grand Cru;VT;Sommerberg;;Pinot Gris;;AOC Alsace Grand Cru Sommerberg Pinot Gris VT (1B039D13);suspendu;sorties;export;10;ES;;
$preLigneCaveCSV;AOC;;AOC Alsace Pinot noir;;;;Pinot Noir Rosé;;AOC Alsace Pinot noir (1S001S 1);suspendu;entrees;recolte;10;;;
$preLigneCaveCSV;AOC;;AOC Alsace Pinot noir;;;;Pinot Noir Rosé;;AOC Alsace Pinot noir (1S001S 1);suspendu;entrees;repli;10;;;
$preLigneCaveCSV;AOC;;AOC Crémant d'Alsace;;;;Blanc;;AOC Crémant d'Alsace Blanc (1B001M00);suspendu;entrees;recolte;10;;;
$preLigneCaveCSV;AOC;;AOC Alsace blanc;;;;Chasselas;;AOC Alsace blanc Chasselas (1B001S 5);acquitte;sorties;export;10;BE;;
$preLigneCRDCSV;Vert;TRANQ;Bouteille 75 cl;;;;;;;Banalisées suspendues;stock_debut;debut;100;;;
$preLigneCRDCSV;Vert;TRANQ;Bouteille 75 cl;;;;;;;Banalisées suspendues;entrees;achats;3;;;
$preLigneCRDCSV;Vert;TRANQ;Bouteille 75 cl;;;;;;;Banalisées suspendues;entrees;retours;2;;;
$preLigneCRDCSV;Vert;TRANQ;Bouteille 75 cl;;;;;;;Banalisées suspendues;entrees;excedents;1;;;
$preLigneCRDCSV;Vert;TRANQ;Bouteille 75 cl;;;;;;;Banalisées suspendues;sorties;utilisations;100;;;
$preLigneCRDCSV;Vert;TRANQ;Bouteille 75 cl;;;;;;;Banalisées suspendues;sorties;destructions;2;;;
$preLigneCRDCSV;Vert;TRANQ;Bouteille 75 cl;;;;;;;Banalisées suspendues;sorties;manquants;1;;;
$preLigneCRDCSV;Vert;TRANQ;Bouteille 75 cl;;;;;;;Banalisées suspendues;stock_fin;fin;100;;;
$preLigneCRDCSV;Vert;TRANQ;BIB 3l;;;;;;;Banalisées suspendues;stock_debut;fin;100;;;
$preLigneCRDCSV;Vert;TRANQ;BIB 3l;;;;;;;Banalisées suspendues;sorties;utilisations;10;;;
$preLigneCRDCSV;Vert;TRANQ;BIB 3l;;;;;;;Banalisées suspendues;stock_fin;fin;100;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 75 cl;;;;;;;Banalisées suspendues;stock_debut;debut;100;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 75 cl;;;;;;;Banalisées suspendues;entrees;achats;3;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 75 cl;;;;;;;Banalisées suspendues;entrees;retours;2;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 75 cl;;;;;;;Banalisées suspendues;entrees;excedents;1;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 75 cl;;;;;;;Banalisées suspendues;sorties;utilisations;3;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 75 cl;;;;;;;Banalisées suspendues;sorties;destructions;2;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 75 cl;;;;;;;Banalisées suspendues;sorties;manquants;1;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 75 cl;;;;;;;Banalisées suspendues;stock_fin;fin;100;;;
$preLigneCRDCSV;Vert;MOUSSEUX;Bouteille 37,5 cl;;;;;;;Banalisées suspendues;stock_debut;debut;100;;;
$preLigneCRDCSV;Vert;MOUSSEUX;Bouteille 37,5 cl;;;;;;;Banalisées suspendues;entrees;achats;3;;;
$preLigneCRDCSV;Vert;MOUSSEUX;Bouteille 37,5 cl;;;;;;;Banalisées suspendues;entrees;retours;2;;;
$preLigneCRDCSV;Vert;MOUSSEUX;Bouteille 37,5 cl;;;;;;;Banalisées suspendues;entrees;excedents;1;;;
$preLigneCRDCSV;Vert;MOUSSEUX;Bouteille 37,5 cl;;;;;;;Banalisées suspendues;sorties;utilisations;100;;;
$preLigneCRDCSV;Vert;MOUSSEUX;Bouteille 37,5 cl;;;;;;;Banalisées suspendues;sorties;destructions;2;;;
$preLigneCRDCSV;Vert;MOUSSEUX;Bouteille 37,5 cl;;;;;;;Banalisées suspendues;sorties;manquants;1;;;
$preLigneCRDCSV;Vert;MOUSSEUX;Bouteille 37,5 cl;;;;;;;Banalisées suspendues;stock_fin;fin;100;;;
EOF;

$tmpfname = tempnam("/tmp", "DRM_");
$temp = fopen($tmpfname, "w");
fwrite($temp, $csv);
fclose($temp);

$t = new lime_test(31);

$t->comment("Export d'une DRM");

$drm = DRMClient::getInstance()->createDoc($viti->identifiant, $periode);
$import = new DRMImportCsvEdi($tmpfname, $drm);
$t->ok($import->checkCSV(), "Vérification de l'import");

$import->importCSV();

$drm->validate();
$drm->save();

$drm = DRMClient::getInstance()->find('DRM-'.$viti->identifiant.'-'.$periode);

$mouvements = MouvementfactureFacturationView::getInstance()->getMouvementsAll(0);
foreach($mouvements as $key => $mouvement) {
    if($mouvement->id_doc == $drm->_id) {
        continue;
    }
    unset($mouvements[$key]);
}

$export = new ExportMouvementsDRMDB2();
$csv = $export->export($mouvements);

$t->is(count($csv), 9, "L'export genère 9 csv");

$t->is(count($csv["01.DRMDEM"]), 1, "Une seul ligne pour les entrées");
$t->is($csv["01.DRMDEM"][0], substr($periode,0,4).";".(substr($periode,4,2)*1).";".$viti->num_interne.";;0;40;30;20;10;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;".$date.";\"TELEDECLARATION\"", "Les entrées sont bien reportées");

$t->is(count($csv["02.DRMDSA"]), 1, "Une seul ligne pour les sorties acquittées");
$t->is($csv["02.DRMDSA"][0], substr($periode,0,4).";".(substr($periode,4,2)*1).";".$viti->num_interne.";;0;0;0;0;0;0;0;0;0;0;0;0;0;".$date.";\"TELEDECLARATION\"", "Les sorties acquittées sont bien reportées");

$t->is(count($csv["03.DRMDSS"]), 1, "Une seul ligne pour les sorties suspendues");
$t->is($csv["03.DRMDSS"][0], substr($periode,0,4).";".(substr($periode,4,2)*1).";".$viti->num_interne.";;0;10;0;20;0;0;0;0;0;0;0;0;0;".$date.";\"TELEDECLARATION\"", "Les sorties suspendues sont bien reportées");

$t->is(count($csv["04.DRMDSE"]), 1, "Une seul ligne pour les sorties exonérées");
$t->is($csv["04.DRMDSE"][0], substr($periode,0,4).";".(substr($periode,4,2)*1).";".$viti->num_interne.";;0;0;0;0;0;0;0;0;0;".$date.";\"TELEDECLARATION\"", "Les sorties exonérées sont bien reportées");

$t->is(count($csv["05.DRMDSO"]), 1, "Une seul ligne pour les sorties autres");
$t->is($csv["05.DRMDSO"][0], substr($periode,0,4).";".(substr($periode,4,2)*1).";".$viti->num_interne.";;0;0;0;0;0;0;0;0;0;".$date.";\"TELEDECLARATION\"", "Les sorties autres sont bien reportées");

$t->is(count($csv["06.DRMAX"]), 3, "3 lignes pour les exports");

$t->is(count($csv["07.DRMCRD"]), 2, "2 lignes pour les CRDS");

$t->is(count($csv["08.DRMENT"]), 1, "Une seul ligne pour le récap");
$t->is($csv["08.DRMENT"][0], substr($periode,0,4).";".(substr($periode,4,2)*1).";".$viti->num_interne.";;0;\"\";0;0;0;221.39;44.28;265.67;30;265.67;40;30;20;10;0;0;0;0;10;0;20;0;0;0;0;0;0;0;0;0;".$dateFinMois.";".$date.";\"TELEDECLARATION\";\"\";\"\";;0.75;0.38;0;0", "Le recap est bien reporté");

$t->is(count($csv["09.ORIGINES"]), 13, "13 lignes de mouvements");

$t->comment("Export d'une DRM à néant");

$periode = DRMClient::getInstance()->getPeriodeSuivante($periode);
$dateFinMois = new DateTime(DRMClient::getInstance()->buildDate($periode));
$dateFinMois->modify('last day of this month');
$dateFinMois = $dateFinMois->format('Ymd');

$drm = DRMClient::getInstance()->createDoc($viti->identifiant, $periode);
$drm->save();
$drm->validate();
$drm->save();

$drm = DRMClient::getInstance()->find('DRM-'.$viti->identifiant.'-'.$periode);

$mouvements = MouvementfactureFacturationView::getInstance()->getMouvementsAll(0);

foreach($mouvements as $key => $mouvement) {
    if($mouvement->id_doc == $drm->_id) {
        continue;
    }
    unset($mouvements[$key]);
}

$export = new ExportMouvementsDRMDB2();
$csv = $export->export($mouvements);

$t->is(count($csv), 7, "L'export genère 7 csv");

$t->is(count($csv["01.DRMDEM"]), 1, "Une seul ligne pour les entrées");
$t->is($csv["01.DRMDEM"][0], substr($periode,0,4).";".(substr($periode,4,2)*1).";".$viti->num_interne.";;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;".$date.";\"TELEDECLARATION\"", "Les entrées sont vides");

$t->is(count($csv["02.DRMDSA"]), 1, "Une seul ligne pour les sorties acquittées");
$t->is($csv["02.DRMDSA"][0], substr($periode,0,4).";".(substr($periode,4,2)*1).";".$viti->num_interne.";;0;0;0;0;0;0;0;0;0;0;0;0;0;".$date.";\"TELEDECLARATION\"", "Les sorties acquittées sont vides");

$t->is(count($csv["03.DRMDSS"]), 1, "Une seul ligne pour les sorties suspendues");
$t->is($csv["03.DRMDSS"][0], substr($periode,0,4).";".(substr($periode,4,2)*1).";".$viti->num_interne.";;0;0;0;0;0;0;0;0;0;0;0;0;0;".$date.";\"TELEDECLARATION\"", "Les sorties suspendues sont vides");

$t->is(count($csv["04.DRMDSE"]), 1, "Une seul ligne pour les sorties exonérées");
$t->is($csv["04.DRMDSE"][0], substr($periode,0,4).";".(substr($periode,4,2)*1).";".$viti->num_interne.";;0;0;0;0;0;0;0;0;0;".$date.";\"TELEDECLARATION\"", "Les sorties exonérées sont vides");

$t->is(count($csv["05.DRMDSO"]), 1, "Une seul ligne pour les sorties autres");
$t->is($csv["05.DRMDSO"][0], substr($periode,0,4).";".(substr($periode,4,2)*1).";".$viti->num_interne.";;0;0;0;0;0;0;0;0;0;".$date.";\"TELEDECLARATION\"", "Les sorties autres sont vides");

$t->is(count($csv["08.DRMENT"]), 1, "Une seul ligne pour le récap");
$t->is($csv["08.DRMENT"][0], substr($periode,0,4).";".(substr($periode,4,2)*1).";".$viti->num_interne.";;0;\"\";0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;".$dateFinMois.";".$date.";\"TELEDECLARATION\";\"\";\"\";;0;0;0;0", "Le recap est bien reporté");

$t->is(count($csv["09.ORIGINES"]), 1, "1 ligne de mouvement");
