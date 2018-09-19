<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if($application != "civa") {
    $t = new lime_test(0);
    exit(0);
}

$viti = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_teledeclaration')->getEtablissement();
$societe = $viti->getSociete();
$periode = date('Ym');

//Suppression des DRM précédentes
foreach(DRMClient::getInstance()->viewByIdentifiant($viti->identifiant) as $k => $v) {
  $drm = DRMClient::getInstance()->find($k);
  $drm->delete(false);
  $csv = CSVDRMClient::getInstance()->find(str_replace("DRM", "CSVDRM", $k));
  $csv->delete(false);
}

$preLigneCaveCSV = "CAVE;".$periode.";".$viti->identifiant.";";
$preLigneCRDCSV = "CRD;".$periode.";".$viti->identifiant.";";

$csv = <<<EOF
$preLigneCaveCSV;AOC;;AOC Alsace blanc;;;;Chasselas;;AOC Alsace blanc Chasselas (1B001S 5);suspendu;stocks_debut;initial;100;;;
$preLigneCaveCSV;AOC;;AOC Alsace blanc;;;;Chasselas;;AOC Alsace blanc Chasselas (1B001S 5);suspendu;entrees;recolte;20;;;
$preLigneCaveCSV;AOC;;AOC Alsace blanc;;;;Chasselas;;AOC Alsace blanc Chasselas (1B001S 5);suspendu;sorties;export;10;DE;;
$preLigneCaveCSV;AOC;;AOC Alsace blanc;;;;Chasselas;;AOC Alsace blanc Chasselas (1B001S 5);suspendu;stocks_fin;final;90;;;
$preLigneCaveCSV;AOC;;AOC Alsace Lieu-dit;;;Blanc;Riesling;;AOC Alsace Lieu-dit Riesling (1B070S04);suspendu;stocks_debut;initial;100;;;
$preLigneCaveCSV;AOC;;AOC Alsace Lieu-dit;;;Blanc;Riesling;;AOC Alsace Lieu-dit Riesling (1B070S04);suspendu;entrees;recolte;10;;;
$preLigneCaveCSV;AOC;;AOC Alsace Lieu-dit;;;Blanc;Riesling;;AOC Alsace Lieu-dit Riesling (1B070S04);suspendu;stocks_fin;final;110;;;
$preLigneCaveCSV;AOC;;AOC Alsace Communale;;Côte de Rouffach;Rouge;Pinot Noir;;AOC Alsace Communale Côte de Rouffach Pinot Noir (1R057S);suspendu;stocks_debut;initial;100;;;
$preLigneCaveCSV;AOC;;AOC Alsace Communale;;Côte de Rouffach;Rouge;Pinot Noir;;AOC Alsace Communale Côte de Rouffach Pinot Noir (1R057S);suspendu;entrees;recolte;20;;;
$preLigneCaveCSV;AOC;;AOC Alsace Communale;;Côte de Rouffach;Rouge;Pinot Noir;;AOC Alsace Communale Côte de Rouffach Pinot Noir (1R057S);suspendu;stocks_fin;final;110;;;
$preLigneCaveCSV;AOC;;AOC Alsace Communale;;Vallée Noble;Blanc;Pinot Gris;;AOC Alsace Communale Vallée Noble Pinot Gris (1B062S03);suspendu;stocks_debut;initial;100;;;
$preLigneCaveCSV;AOC;;AOC Alsace Communale;;Vallée Noble;Blanc;Pinot Gris;;AOC Alsace Communale Vallée Noble Pinot Gris (1B062S03);suspendu;entrees;recolte;10;;;
$preLigneCaveCSV;AOC;;AOC Alsace Communale;;Vallée Noble;Blanc;Pinot Gris;;AOC Alsace Communale Vallée Noble Pinot Gris (1B062S03);suspendu;stocks_fin;final;110;;;
$preLigneCaveCSV;AOC;;AOC Alsace Grand Cru;;Sommerberg;;Riesling;;AOC Alsace Grand Cru Sommerberg Riesling (1B039S 4);suspendu;stocks_debut;initial;100;;;
$preLigneCaveCSV;AOC;;AOC Alsace Grand Cru;;Sommerberg;;Riesling;;AOC Alsace Grand Cru Sommerberg Riesling (1B039S 4);suspendu;entrees;recolte;10;;;
$preLigneCaveCSV;AOC;;AOC Alsace Grand Cru;;Sommerberg;;Riesling;;AOC Alsace Grand Cru Sommerberg Riesling (1B039S 4);suspendu;sorties;export;10;ES;;
$preLigneCaveCSV;AOC;;AOC Alsace Grand Cru;;Sommerberg;;Riesling;;AOC Alsace Grand Cru Sommerberg Riesling (1B039S 4);suspendu;stocks_fin;final;100;;;
$preLigneCaveCSV;AOC;;AOC Alsace Grand Cru;VT;Sommerberg;;Pinot Gris;;AOC Alsace Grand Cru Sommerberg Pinot Gris VT (1B039D13);suspendu;stocks_debut;initial;100;;;
$preLigneCaveCSV;AOC;;AOC Alsace Grand Cru;VT;Sommerberg;;Pinot Gris;;AOC Alsace Grand Cru Sommerberg Pinot Gris VT (1B039D13);suspendu;entrees;recolte;10;;;
$preLigneCaveCSV;AOC;;AOC Alsace Grand Cru;VT;Sommerberg;;Pinot Gris;;AOC Alsace Grand Cru Sommerberg Pinot Gris VT (1B039D13);suspendu;sorties;export;10;ES;;
$preLigneCaveCSV;AOC;;AOC Alsace Grand Cru;VT;Sommerberg;;Pinot Gris;;AOC Alsace Grand Cru Sommerberg Pinot Gris VT (1B039D13);suspendu;stocks_fin;final;100;;;
$preLigneCaveCSV;AOC;;AOC Alsace Pinot noir;;;;Pinot Noir Rosé;;AOC Alsace Pinot noir (1S001S 1);suspendu;stocks_debut;initial;100;;;
$preLigneCaveCSV;AOC;;AOC Alsace Pinot noir;;;;Pinot Noir Rosé;;AOC Alsace Pinot noir (1S001S 1);suspendu;entrees;recolte;10;;;
$preLigneCaveCSV;AOC;;AOC Alsace Pinot noir;;;;Pinot Noir Rosé;;AOC Alsace Pinot noir (1S001S 1);suspendu;stocks_fin;final;110;;;
$preLigneCaveCSV;AOC;;AOC Crémant d'Alsace;;;;Blanc;;AOC Crémant d'Alsace Blanc (1B001M00);suspendu;stocks_debut;initial;100;;;
$preLigneCaveCSV;AOC;;AOC Crémant d'Alsace;;;;Blanc;;AOC Crémant d'Alsace Blanc (1B001M00);suspendu;entrees;recolte;10;;;
$preLigneCaveCSV;AOC;;AOC Crémant d'Alsace;;;;Blanc;;AOC Crémant d'Alsace Blanc (1B001M00);suspendu;stocks_fin;final;110;;;
$preLigneCRDCSV;Vert;TRANQ;Bouteille 75 cl;;;;;;;Banalisées suspendues;stock_debut;debut;100;;;
$preLigneCRDCSV;Vert;TRANQ;Bouteille 75 cl;;;;;;;Banalisées suspendues;entrees;achats;3;;;
$preLigneCRDCSV;Vert;TRANQ;Bouteille 75 cl;;;;;;;Banalisées suspendues;entrees;retours;2;;;
$preLigneCRDCSV;Vert;TRANQ;Bouteille 75 cl;;;;;;;Banalisées suspendues;entrees;excedents;1;;;
$preLigneCRDCSV;Vert;TRANQ;Bouteille 75 cl;;;;;;;Banalisées suspendues;sorties;utilisations;3;;;
$preLigneCRDCSV;Vert;TRANQ;Bouteille 75 cl;;;;;;;Banalisées suspendues;sorties;destructions;2;;;
$preLigneCRDCSV;Vert;TRANQ;Bouteille 75 cl;;;;;;;Banalisées suspendues;sorties;manquants;1;;;
$preLigneCRDCSV;Vert;TRANQ;Bouteille 75 cl;;;;;;;Banalisées suspendues;stock_fin;fin;100;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 75 cl;;;;;;;Banalisées suspendues;stock_debut;debut;100;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 75 cl;;;;;;;Banalisées suspendues;entrees;achats;3;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 75 cl;;;;;;;Banalisées suspendues;entrees;retours;2;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 75 cl;;;;;;;Banalisées suspendues;entrees;excedents;1;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 75 cl;;;;;;;Banalisées suspendues;sorties;utilisations;3;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 75 cl;;;;;;;Banalisées suspendues;sorties;destructions;2;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 75 cl;;;;;;;Banalisées suspendues;sorties;manquants;1;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 75 cl;;;;;;;Banalisées suspendues;stock_fin;fin;100;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 37,5 cl;;;;;;;Banalisées suspendues;stock_debut;debut;100;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 37,5 cl;;;;;;;Banalisées suspendues;entrees;achats;3;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 37,5 cl;;;;;;;Banalisées suspendues;entrees;retours;2;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 37,5 cl;;;;;;;Banalisées suspendues;entrees;excedents;1;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 37,5 cl;;;;;;;Banalisées suspendues;sorties;utilisations;3;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 37,5 cl;;;;;;;Banalisées suspendues;sorties;destructions;2;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 37,5 cl;;;;;;;Banalisées suspendues;sorties;manquants;1;;;
$preLigneCRDCSV;Bleu;MOUSSEUX;Bouteille 37,5 cl;;;;;;;Banalisées suspendues;stock_fin;fin;100;;;
EOF;

$tmpfname = tempnam("/tmp", "DRM_");
$temp = fopen($tmpfname, "w");

fwrite($temp, $csv);
fclose($temp);

$t = new lime_test(1);

$drm = DRMClient::getInstance()->createDoc($viti->identifiant, $periode);
$import = new DRMImportCsvEdi($tmpfname, $drm);
$t->ok($import->checkCSV(), "Vérification de l'import");
$t->ok($import->importCSV(),"Import de la DRM");

$drm->validate();
$drm->save();

/*$mouvements = MouvementfactureFacturationView::getInstance()->getMouvements(0, array(0, 1), MouvementfactureFacturationView::KEYS_MVT_TYPE + 1);
$mouvementsDRM = array();
foreach($mouvements as $mouvement) {
    if($id_doc != "DRM-".$viti->identifiant."-".$periode) {
        continue;
    }
}*/

$drm = DRMClient::getInstance()->find('DRM-'.$viti->identifiant.'-'.$periode);

$mouvements = $drm->mouvements->get($viti->identifiant);

$produitsDB2 = array("01.BLANC", "02.ROUGE", "03.GRDCRU", "04.CREMANT");

$correspondances = array(
    "entrees/recolte"                    => "01.DRMDEM/06.Entrées",
    "entrees/achatnoncrd"                => "01.DRMDEM/10.Achats vrac + bouteilles sans CRD (Propriété)",
    "entrees/achatnoncrd/NEGOCE"         => "01.DRMDEM/14.Achats vrac + bouteilles sans CRD (Négociant)",
    "entrees/retourmarchandisetaxees"    => "01.DRMDEM/18.Quantités réintégrées CVO + Droits circulation 12a",
    "entrees/retourmarchandisenontaxees" => "01.DRMDEM/22.Quantités réintégrées CVO seule 12b",
    "entrees/repli"                      => "01.DRMDEM/26.Replis",
    "sorties/vracsanscontratsuspendu"    => "02.DRMDSS/06.B - Hors région Alsace (UE - pays tiers ou autre EA en France)",
    "sorties/vrac"                       => "02.DRMDSS/10.C - Vrac",
    "sorties/bouteillenue"               => "02.DRMDSS/14.D - Expeditions en Alsace en bouteilles",
    "sorties/0"                          => "03.DRMDSE/06.I - Vers un utilisateur autorisé",
    "sorties/1"                          => "03.DRMDSE/10.J - Dégustations à la propriété",
    "sorties/2"                          => "04.DRMDSO/06.K - Replis",
    "sorties/3"                          => "04.DRMDSO/10.L - Lies",
    "sorties/4"                          => "05.DRMDSA/06.A - (75 cl) CRD ou DS/DSAC France",
    "sorties/5"                          => "05.DRMDSA/10.A - CRD ou DS/DSAC France",
    "sorties/6"                          => "05.DRMDSA/14.A bis - DSA/DSAC Hors France Métropolitaine",
);

$paysDB2 = array(
    "FR" => "001", "NL" => "003", "DE" => "004", "IT" => "005", "GB" => "006", "IE" => "007", "DK" => "008", "GR" => "009", "PT" => "010", "ES" => "011", "BE" => "017", "LU" => "018", "IS" => "024", "NO" => "028", "SE" => "030", "FI" => "032", "LI" => "037", "AT" => "038", "CH" => "039", "AD" => "043", "GI" => "044", "MT" => "046", "TR" => "052", "EE" => "053", "LV" => "054", "LT" => "055", "PL" => "060", "CZ" => "061", "SK" => "063", "HU" => "064", "RO" => "066", "BG" => "068", "AL" => "070", "UA" => "072", "BY" => "073", "MD" => "074", "RU" => "075", "GE" => "076", "AM" => "077", "AZ" => "078", "KZ" => "079", "TM" => "080", "UZ" => "081", "TJ" => "082", "KG" => "083", "SI" => "091", "HR" => "092", "BA" => "093", "RS" => "094", "ME" => "095", "MK" => "096", "MA" => "204", "DZ" => "208", "TN" => "212", "LY" => "216", "EG" => "220", "SD" => "224", "MR" => "228", "ML" => "232", "BF" => "236", "NE" => "240", "TD" => "244", "SN" => "248", "GM" => "252", "GN" => "260", "SL" => "264", "LR" => "268", "CI" => "272", "GH" => "276", "TG" => "280", "BJ" => "284", "NG" => "288", "CM" => "302", "CF" => "306", "GQ" => "310", "GA" => "314", "CG" => "318", "RW" => "324", "BI" => "328", "AO" => "330", "ET" => "334", "DJ" => "338", "SO" => "342", "KE" => "346", "UG" => "350", "TZ" => "352", "SC" => "355", "MG" => "370", "RE" => "372", "MU" => "373", "KM" => "375", "YT" => "377", "ZM" => "378", "ZA" => "388", "NA" => "389", "BW" => "391", "US" => "400", "CA" => "404", "GL" => "406", "PM" => "408", "MX" => "412", "BM" => "413", "GT" => "416", "BZ" => "421", "HN" => "424", "SV" => "428", "NI" => "432", "CR" => "436", "PA" => "442", "AI" => "446", "CU" => "448", "KN" => "449", "HT" => "452", "BS" => "453", "TC" => "454", "DO" => "456", "VI" => "457", "GP" => "458", "AG" => "459", "DM" => "460", "MQ" => "462", "KY" => "463", "JM" => "464", "LC" => "465", "VC" => "467", "VG" => "468", "BB" => "469", "MS" => "470", "TT" => "472", "AW" => "474", "AN" => "478", "CO" => "480", "VE" => "484", "SR" => "492", "GF" => "496", "MF" => "499", "EC" => "500", "PE" => "504", "BR" => "508", "CL" => "512", "BO" => "516", "PY" => "520", "UY" => "524", "AR" => "528", "CY" => "600", "LB" => "604", "SY" => "608", "IQ" => "612", "IR" => "616", "IL" => "624", "JO" => "628", "SA" => "632", "BH" => "640", "QA" => "644", "AE" => "647", "OM" => "649", "YE" => "653", "PK" => "662", "IN" => "664", "BD" => "666", "MV" => "667", "LK" => "669", "NP" => "672", "MM" => "676", "TH" => "680", "LA" => "684", "VN" => "690", "KH" => "696", "ID" => "700", "MY" => "701", "SG" => "706", "PH" => "708", "MN" => "716", "CN" => "720", "BT" => "721", "KR" => "728", "JP" => "732", "TW" => "736", "HK" => "740", "MO" => "743", "AU" => "800", "PG" => "801", "NZ" => "804", "SB" => "806", "NC" => "809", "AS" => "810", "WF" => "811", "VU" => "816", "WS" => "819", "PF" => "822", "AUTRE" => "999",
);

$structure = array();

foreach($correspondances as $item) {
    $parts = explode("/", $item);
    $file = $parts[0];
    $volumeType = $parts[1];

    if(!isset($structure[$file])) {
        $structure[$file] = array();
    }

    $structure[$file][$volumeType] = $produitsDB2;
}

function convertProduitDB2($hash) {
    if(!preg_match('#/AOC_ALSACE#', $hash)) {

        return null;
    }

    if(preg_match('#/GRDCRU#', $hash)) {

        return "03.GRDCRU";
    }

    if(preg_match("#/CREMANT#", $hash)) {

        return "04.CREMANT";
    }

    if(preg_match('#/(PN|PR|rouge)#', $hash)) {

        return "02.ROUGE";
    }

    return "01.BLANC";
}

function convertMouvementDB2($typeHash, $correspondances) {
    if(!isset($correspondances[$typeHash])) {

        return null;
    }

    return $correspondances[$typeHash];
}

function convertPaysDB2($code, $paysDB2) {
    if(!isset($paysDB2[$code])) {

        $code = "AUTRE";
    }

    return $paysDB2[$code];
}

$db2 = array();
$db2Export = array();
$db2CRD = array();
$drms = array();

foreach($mouvements as $mouvement) {
    $produit = convertProduitDB2($mouvement->produit_hash);
    $mouvementType = convertMouvementDB2($mouvement->type_hash, $correspondances);
    $identifiantPeriode = $drm->identifiant."-".$drm->periode;
    if(!isset($drms[$identifiantPeriode]) || $drms[$identifiantPeriode]->_id < $drm->_id) {
        $drms[$identifiantPeriode] = DRMClient::getInstance()->find($drm->_id, acCouchdbClient::HYDRATE_JSON);
    }
    if(!$produit) {
        continue;
    }
    if(preg_match("/export/", $mouvement->type_hash) && $mouvement->detail_identifiant) {
        $pays = convertPaysDB2($mouvement->detail_identifiant, $paysDB2);
        if(!isset($db2Export[$identifiantPeriode][$pays][$produit])) {
            $db2Export[$identifiantPeriode][$pays][$produit] = 0;
        }
        $db2Export[$identifiantPeriode][$pays][$produit] += $mouvement->volume;
        ksort($db2Export[$identifiantPeriode]);
        ksort($db2Export[$identifiantPeriode][$pays]);
    }
    if(!$mouvementType) {
        continue;
    }
    if(!isset($db2[$identifiantPeriode])) {
        $db2[$identifiantPeriode] = array();
    }
    if(!isset($db2[$identifiantPeriode][$produit])) {
        $db2[$identifiantPeriode][$produit] = array();
    }
    if(!isset($db2[$identifiantPeriode][$produit][$mouvementType])) {
        $db2[$identifiantPeriode][$produit][$mouvementType] = 0;
    }

    $db2[$identifiantPeriode][$produit][$mouvementType] += $mouvement->volume;
    ksort($db2[$identifiantPeriode][$produit]);
    //print_r($mouvement->toArray(true, false));
}

foreach($drms as $drm) {
    $identifiantPeriode = $drm->identifiant."-".$drm->periode;
    foreach($drm->crds as $regimes) {
        foreach($regimes as $ligne) {
            $centilisation = str_replace(".", ",", ($ligne->centilitrage*10000));
            if(!isset($db2CRD[$identifiantPeriode][$centilisation])) {
                $db2CRD[$identifiantPeriode][$centilisation]["08.UTILISATION/VERT"] = 0;
                $db2CRD[$identifiantPeriode][$centilisation]["09.UTILISATION/BLEU"] = 0;
                $db2CRD[$identifiantPeriode][$centilisation]["10.DESTRUCTION/VERT"] = 0;
                $db2CRD[$identifiantPeriode][$centilisation]["11.DESTRUCTION/BLEU"] = 0;
            }
            if($ligne->couleur == "VERT" && $ligne->sorties_utilisations) {
                $db2CRD[$identifiantPeriode][$centilisation]["08.UTILISATION/VERT"] += $ligne->sorties_utilisations;
            }
            if($ligne->couleur == "VERT" && $ligne->sorties_destructions) {
                $db2CRD[$identifiantPeriode][$centilisation]["10.DESTRUCTION/VERT"] += $ligne->sorties_destructions;
            }
            if($ligne->couleur == "BLEU" && $ligne->sorties_utilisations) {
                $db2CRD[$identifiantPeriode][$centilisation]["09.UTILISATION/BLEU"] += $ligne->sorties_utilisations;
            }
            if($ligne->couleur == "BLEU" && $ligne->sorties_destructions) {
                $db2CRD[$identifiantPeriode][$centilisation]["11.DESTRUCTION/BLEU"] += $ligne->sorties_destructions;
            }
        }
        krsort($db2CRD[$identifiantPeriode]);
    }
}

print_r($db2);
print_r($db2Export);
print_r($db2CRD);

foreach($db2 as $identifiantPeriode => $volumes) {
    $parts = explode("-", $identifiantPeriode);
    $identifiant = $parts[0];
    $periode = $parts[1];
    foreach($structure as $file => $infos) {
        echo $file.":";
        echo substr($periode, 0, 4).",".substr($periode, 4, 2).",".$identifiant.",,0";
        foreach($infos as $mouvementType => $produits) {
            foreach($produits as $produit) {
                $volume = 0;
                if(isset($volumes[$produit][$file."/".$mouvementType])) {
                    $volume = $volumes[$produit][$file."/".$mouvementType];
                }
                echo ",".$volume;
            }
        }
        echo ",".str_replace("-", "", $drms[$identifiantPeriode]->valide->date_signee).",\"TELDECLARATION\"\n";
    }
}

foreach($db2Export as $identifiantPeriode => $infos) {
    $parts = explode("-", $identifiantPeriode);
    $identifiant = $parts[0];
    $periode = $parts[1];
    $compteur = 1;
    foreach($infos as $pays => $produits) {
        echo "06.DRMAX:".substr($periode, 0, 4).",".substr($periode, 4, 2).",".$identifiant.",,0,".$compteur.",".$pays*1;
        foreach($produitsDB2 as $produit) {
            $volume = 0;
            if(isset($produits[$produit])) {
                $volume = $produits[$produit];
            }
            echo ",".$volume*-1;
        }
        echo ",".str_replace("-", "", $drms[$identifiantPeriode]->valide->date_signee).",\"TELDECLARATION\"\n";
        $compteur++;
    }
}

foreach($db2CRD as $identifiantPeriode => $centilisations) {
    $parts = explode("-", $identifiantPeriode);
    $identifiant = $parts[0];
    $periode = $parts[1];
    $compteur = 1;
    foreach($centilisations as $centilisation => $sorties) {
        echo "07.DRMCRD:".substr($periode, 0, 4).",".substr($periode, 4, 2).",".$identifiant.",,0,".$compteur.",\"".$centilisation."\"";
        foreach($sorties as $sortie) {
            echo ",".$sortie;
        }
        echo ",".str_replace("-", "", $drms[$identifiantPeriode]->valide->date_signee).",\"TELDECLARATION\"\n";
        $compteur++;
    }
}
