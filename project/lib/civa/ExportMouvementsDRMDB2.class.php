<?php

class ExportMouvementsDRMDB2
{

    public $produits = array("01.BLANC", "02.ROUGE", "03.GRDCRU", "04.CREMANT");
    public $produitsExport = array("01.BLANC", "02.ROUGE", "04.CREMANT", "03.GRDCRU");

    public $correspondances = array(
        "entrees/recolte"                    => "01.DRMDEM/06.Entrées",
        "entrees/achatnoncrd"                => "01.DRMDEM/10.Achats vrac + bouteilles sans CRD (Propriété)",
        "entrees/achatnoncrd/NEGOCE"         => "01.DRMDEM/14.Achats vrac + bouteilles sans CRD (Négociant)",
        "entrees/retourmarchandisetaxees"    => "01.DRMDEM/18.Quantités réintégrées CVO + Droits circulation 12a",
        "entrees/retourmarchandisenontaxees" => "01.DRMDEM/22.Quantités réintégrées CVO seule 12b",
        "entrees/repli"                      => "01.DRMDEM/26.Replis",
        "sorties/ventefrancecrd"             => "02.DRMDSA/06.A - (75 cl) CRD ou DS-DSAC France",
        "/dev/null"                          => "02.DRMDSA/10.A - CRD ou DS-DSAC France",
        "exporttaxe_details"                 => "02.DRMDSA/14.A bis - DSA-DSAC Hors France Métropolitaine",
        "sorties/vracsanscontratsuspendu"    => "03.DRMDSS/06.B - Hors région Alsace (UE - pays tiers ou autre EA en France)",
        "export_details"                     => "03.DRMDSS/06.B - Hors région Alsace (UE - pays tiers ou autre EA en France)",
        "vrac_details"                       => "03.DRMDSS/10.C - Vrac",
        "bouteillenue_details"               => "03.DRMDSS/14.D - Expeditions en Alsace en bouteilles",
        "sorties/exoversutilisateurauto"     => "04.DRMDSE/06.I - Vers un utilisateur autorisé",
        "sorties/consommationfamilialedegustation" => "04.DRMDSE/10.J - Dégustations à la propriété",
        "sorties/repli"                      => "05.DRMDSO/06.K - Replis",
        "sorties/lies"                       => "05.DRMDSO/10.L - Lies",
    );

    public $pays = array(
        "FR" => "001", "NL" => "003", "DE" => "004", "IT" => "005", "GB" => "006", "IE" => "007", "DK" => "008", "GR" => "009", "PT" => "010", "ES" => "011", "BE" => "017", "LU" => "018", "IS" => "024", "NO" => "028", "SE" => "030", "FI" => "032", "LI" => "037", "AT" => "038", "CH" => "039", "AD" => "043", "GI" => "044", "MT" => "046", "TR" => "052", "EE" => "053", "LV" => "054", "LT" => "055", "PL" => "060", "CZ" => "061", "SK" => "063", "HU" => "064", "RO" => "066", "BG" => "068", "AL" => "070", "UA" => "072", "BY" => "073", "MD" => "074", "RU" => "075", "GE" => "076", "AM" => "077", "AZ" => "078", "KZ" => "079", "TM" => "080", "UZ" => "081", "TJ" => "082", "KG" => "083", "SI" => "091", "HR" => "092", "BA" => "093", "RS" => "094", "ME" => "095", "MK" => "096", "MA" => "204", "DZ" => "208", "TN" => "212", "LY" => "216", "EG" => "220", "SD" => "224", "MR" => "228", "ML" => "232", "BF" => "236", "NE" => "240", "TD" => "244", "SN" => "248", "GM" => "252", "GN" => "260", "SL" => "264", "LR" => "268", "CI" => "272", "GH" => "276", "TG" => "280", "BJ" => "284", "NG" => "288", "CM" => "302", "CF" => "306", "GQ" => "310", "GA" => "314", "CG" => "318", "RW" => "324", "BI" => "328", "AO" => "330", "ET" => "334", "DJ" => "338", "SO" => "342", "KE" => "346", "UG" => "350", "TZ" => "352", "SC" => "355", "MG" => "370", "RE" => "372", "MU" => "373", "KM" => "375", "YT" => "377", "ZM" => "378", "ZA" => "388", "NA" => "389", "BW" => "391", "US" => "400", "CA" => "404", "GL" => "406", "PM" => "408", "MX" => "412", "BM" => "413", "GT" => "416", "BZ" => "421", "HN" => "424", "SV" => "428", "NI" => "432", "CR" => "436", "PA" => "442", "AI" => "446", "CU" => "448", "KN" => "449", "HT" => "452", "BS" => "453", "TC" => "454", "DO" => "456", "VI" => "457", "GP" => "458", "AG" => "459", "DM" => "460", "MQ" => "462", "KY" => "463", "JM" => "464", "LC" => "465", "VC" => "467", "VG" => "468", "BB" => "469", "MS" => "470", "TT" => "472", "AW" => "474", "AN" => "478", "CO" => "480", "VE" => "484", "SR" => "492", "GF" => "496", "MF" => "499", "EC" => "500", "PE" => "504", "BR" => "508", "CL" => "512", "BO" => "516", "PY" => "520", "UY" => "524", "AR" => "528", "CY" => "600", "LB" => "604", "SY" => "608", "IQ" => "612", "IR" => "616", "IL" => "624", "JO" => "628", "SA" => "632", "BH" => "640", "QA" => "644", "AE" => "647", "OM" => "649", "YE" => "653", "PK" => "662", "IN" => "664", "BD" => "666", "MV" => "667", "LK" => "669", "NP" => "672", "MM" => "676", "TH" => "680", "LA" => "684", "VN" => "690", "KH" => "696", "ID" => "700", "MY" => "701", "SG" => "706", "PH" => "708", "MN" => "716", "CN" => "720", "BT" => "721", "KR" => "728", "JP" => "732", "TW" => "736", "HK" => "740", "MO" => "743", "AU" => "800", "PG" => "801", "NZ" => "804", "SB" => "806", "NC" => "809", "AS" => "810", "WF" => "811", "VU" => "816", "WS" => "819", "PF" => "822", "AUTRE" => "999",
    );

    protected $structure = array();

    public function __construct() {
        $this->structure = $this->buildStructure();
    }

    public function export($mouvements, $periode_max = null) {
        $drms = array();

        foreach($mouvements as $key => $mouvement) {
            if($mouvement->origine != "DRM") {
                unset($mouvements[$key]);
                continue;
            }
            if($mouvement->region != EtablissementClient::REGION_CVO) {
                unset($mouvements[$key]);
                continue;
            }
            $identifiantPeriode = preg_replace("/DRM-(.+)-(.+)-?.*$/", '\1-\2', $mouvement->id_doc);
            if(isset($drms[$identifiantPeriode]) && $drms[$identifiantPeriode]->_id >= $mouvement->id_doc) {
                continue;
            }
            if ($periode_max) {
              $myperiode = preg_replace('/[^-]*-/', '', $identifiantPeriode);
              if ($myperiode > $periode_max) {
                continue;
              }
            }
            $drm = DRMClient::getInstance()->find($mouvement->id_doc, acCouchdbClient::HYDRATE_JSON);
            if(!isset($drms[$identifiantPeriode]) && (!isset($drm->transmission_douane->success) || !$drm->transmission_douane->success)) {
                continue;
            }
            $drms[$identifiantPeriode] = DRMClient::getInstance()->find($mouvement->id_doc, acCouchdbClient::HYDRATE_JSON);
        }

        $db2Identifiants = array();
        $db2Base = array();
        foreach($drms as $identifiantPeriode => $null) {
            $parts = explode("-", $identifiantPeriode);
            $identifiant = $parts[0];

            $db2Identifiants[$identifiantPeriode] = EtablissementClient::getInstance()->find("ETABLISSEMENT-".$identifiant, acCouchdbClient::HYDRATE_JSON)->num_interne;
            $db2Base[$identifiantPeriode] = array();
        }

        $db2Mouvements = array_merge($db2Base, $this->aggregateMouvements($mouvements));
        $db2MouvementsExport = $this->aggregateMouvementsExport($mouvements);
        $db2CRD = $this->aggregateCRD($drms);
        $db2Total = array_merge($db2Base, $this->aggregateTotal($mouvements, $db2CRD));

        $csv = array();

        foreach($db2Mouvements as $identifiantPeriode => $volumes) {
            $parts = explode("-", $identifiantPeriode);
            if (!isset($db2Identifiants[$identifiantPeriode])) {
              continue;
            }
            $identifiant = $db2Identifiants[$identifiantPeriode];
            $periode = $parts[1];
            foreach($this->structure as $file => $infos) {
                $ligne = substr($periode, 0, 4).";".(substr($periode, 4, 2)*1).";" . $identifiant .";;0";
                foreach($infos as $mouvementType => $produits) {
                    foreach($produits as $produit) {
                        if($file."/".$mouvementType == "01.DRMDEM/26.Replis" && $produit != "01.BLANC") {
                            continue;
                        }
                        $volume = 0;
                        if(isset($volumes[$produit][$file."/".$mouvementType])) {
                            $volume = $volumes[$produit][$file."/".$mouvementType];
                        }
                        $ligne .= ";".round($volume, 2);
                    }
                }
                $ligne .= ";".str_replace("-", "", $drms[$identifiantPeriode]->valide->date_signee).";\"TELEDECLARATION\"";
                $csv[$file][] = $ligne;
            }
        }

        $csv["06.DRMAX"] = array();
        foreach($db2MouvementsExport as $identifiantPeriode => $infos) {
            $parts = explode("-", $identifiantPeriode);
            if (!isset($db2Identifiants[$identifiantPeriode])) {
              continue;
            }
            $identifiant = $db2Identifiants[$identifiantPeriode];
            $periode = $parts[1];
            $compteur = 1;
            foreach($infos as $pays => $produits) {
                $ligne = substr($periode, 0, 4).";".(substr($periode, 4, 2)*1).";". $identifiant .";;0;".$compteur.";".$pays*1;
                foreach($this->produitsExport as $produit) {
                    $volume = 0;
                    if(isset($produits[$produit])) {
                        $volume = $produits[$produit];
                    }
                    $ligne .= ";".round($volume, 2);
                }
                $ligne .= ";".str_replace("-", "", $drms[$identifiantPeriode]->valide->date_signee).";\"TELEDECLARATION\"";

                $csv["06.DRMAX"][] = $ligne;
                $compteur++;
            }
        }

        $csv["07.DRMCRD"] = array();
        foreach($db2CRD as $identifiantPeriode => $centilisations) {
            $parts = explode("-", $identifiantPeriode);
            if (!isset($db2Identifiants[$identifiantPeriode])) {
              continue;
            }
            $identifiant = $db2Identifiants[$identifiantPeriode];
            $periode = $parts[1];
            $compteur = 1;
            foreach($centilisations as $centilisation => $sorties) {
                $ligne = substr($periode, 0, 4).";".(substr($periode, 4, 2)*1).";". $identifiant.";;0;".$compteur.";\"".$centilisation."\"";
                foreach($sorties as $sortie) {
                    $ligne .= ";".$sortie;
                }
                $ligne .= ";".str_replace("-", "", $drms[$identifiantPeriode]->valide->date_signee).";\"TELEDECLARATION\"";

                $csv["07.DRMCRD"][] = $ligne;
                $compteur++;
            }
        }

        $csv["08.DRMENT"] = array();
        foreach($db2Total as $identifiantPeriode => $total) {
            $parts = explode("-", $identifiantPeriode);
            if (!isset($db2Identifiants[$identifiantPeriode])) {
              continue;
            }
            $identifiant = $db2Identifiants[$identifiantPeriode];
            $periode = $parts[1];
            $annee = substr($periode, 0, 4);
            $mois = substr($periode, 4, 2);

            if(!isset($total["prix_ht"])) { $total["prix_ht"] = 0; }
            if(!isset($total["tva"])) { $total["tva"] = 0; }
            if(!isset($total["prix_ttc"])) { $total["prix_ttc"] = 0; }
            if(!isset($total["quantite"])) { $total["quantite"] = 0; }
            if(!isset($total["crd_tranq_utilisation"])) { $total["crd_tranq_utilisation"] = 0; }
            if(!isset($total["crd_mousseux_utilisation"])) { $total["crd_mousseux_utilisation"] = 0; }

            $total["prix_ht"] = floor(($total["prix_ht"] * 100)) / 100;
            $total["tva"] = round($total["prix_ht"] * 0.20, 2);
            $total["prix_ttc"] = $total["prix_ht"] + $total["tva"];

            $ligne = substr($periode, 0, 4).";".(substr($periode, 4, 2)*1).";". $identifiant.";;0;\"\";0;0;0;".$total["prix_ht"].";".$total["tva"].";".$total["prix_ttc"].";".$total["quantite"].";".$total["prix_ttc"];

            foreach($this->structure as $file => $infos) {
                foreach($this->produits as $produit) {
                    $volume = 0;
                    if(isset($total[$file][$produit])) {
                        $volume = $total[$file][$produit];
                    }
                    $ligne .= ";".round($volume, 2);
                }
            }

            $dateStock = new DateTime($annee."-".$mois."-01");
            $dateStock->modify('last day of this month');

            $ligne .= ";".$dateStock->format('Ymd').";".str_replace("-", "", $drms[$identifiantPeriode]->valide->date_signee).";\"TELEDECLARATION\";\"\";\"\";;".round($total['crd_tranq_utilisation'], 2).";".round($total['crd_mousseux_utilisation'], 2).";0;0";

            $csv["08.DRMENT"][] = $ligne;
        }

        $csv["09.ORIGINES"] = array();
        foreach($mouvements as $mouvement) {
            $identifiantPeriode = preg_replace("/DRM-(.+)-(.+)-?.*$/", '\1-\2', $mouvement->id_doc);
            if (!isset($db2Identifiants[$identifiantPeriode])) {
              continue;
            }

            $csv["09.ORIGINES"][] = $mouvement->origines;
        }

        return $csv;
    }

    public function setFacture($origines) {
      $drms = array();
      foreach($origines as $o) {
        $ids = explode(':', $o);
        if (!isset($drm[$ids[0]])){
          $drm = DRMClient::getInstance()->find($ids[0]);
          $drms[$ids[0]] = $drm->_id;
          foreach($drm->getMouvements() as $k => $mvts) {
            foreach($mvts as $key => $m) {
              $m->facture = 1;
            }
          }
          $drm->save();
        }
      }
    }

    protected function aggregateMouvements($mouvements) {
        $db2 = array();
        foreach($mouvements as $mouvement) {
            if($mouvement->type_drm != "SUSPENDU") {
                continue;
            }
            if(!preg_match("/".$mouvement->etablissement_identifiant."/", $mouvement->id_doc)) {
                continue;
            }
            if(!$this->convertProduit($mouvement->produit_hash)) {
                continue;
            }
            $produit = $this->convertProduit($mouvement->produit_hash);
            $mouvementType = $this->convertMouvement($mouvement->type_hash);
            $identifiantPeriode = preg_replace("/DRM-(.+)-(.+)-?.*$/", '\1-\2', $mouvement->id_doc);
            if(!isset($db2[$identifiantPeriode])) {
                $db2[$identifiantPeriode] = array();
            }
            if(!isset($db2[$identifiantPeriode][$produit])) {
                $db2[$identifiantPeriode][$produit] = array();
            }
            if(!isset($db2[$identifiantPeriode][$produit][$mouvementType])) {
                $db2[$identifiantPeriode][$produit][$mouvementType] = 0;
            }

            if($mouvementType == "01.DRMDEM/26.Replis" && $produit != "01.BLANC") {
                continue;
            }

            $db2[$identifiantPeriode][$produit][$mouvementType] += $mouvement->quantite * ((preg_match('/entrees/', $mouvement->type_hash)) ? -1 : 1);
            ksort($db2[$identifiantPeriode][$produit]);
        }
        return $db2;
    }

    protected function aggregateMouvementsExport($mouvements) {
        $db2 = array();
        foreach($mouvements as $mouvement) {
            if(!preg_match("/".$mouvement->etablissement_identifiant."/", $mouvement->id_doc)) {
                continue;
            }
            if(!$this->convertProduit($mouvement->produit_hash)) {
                continue;
            }
            if(!preg_match("/export/", $mouvement->type_hash) || !$mouvement->detail_identifiant) {
                continue;
            }
            $produit = $this->convertProduit($mouvement->produit_hash, false);
            $identifiantPeriode = preg_replace("/DRM-(.+)-(.+)-?.*$/", '\1-\2', $mouvement->id_doc);
            $pays = $this->convertPays($mouvement->detail_identifiant);
            if(!isset($db2[$identifiantPeriode][$pays][$produit])) {
                $db2[$identifiantPeriode][$pays][$produit] = 0;
            }
            $db2[$identifiantPeriode][$pays][$produit] += $mouvement->quantite;
            ksort($db2[$identifiantPeriode]);
            ksort($db2[$identifiantPeriode][$pays]);
        }

        return $db2;
    }

    protected function aggregateCRD($drms) {
        $db2 = array();

        foreach($drms as $drm) {
            $identifiantPeriode = $drm->identifiant."-".$drm->periode;
            if(!isset($drm->crds)) {
                continue;
            }
            if(!isset($db2[$identifiantPeriode])) {
                $db2[$identifiantPeriode] = array();
            }
            foreach($drm->crds as $regimes) {
                foreach($regimes as $ligne) {
                    if(!$ligne->sorties_utilisations) {
                        continue;
                    }
                    if(!preg_match('/Bouteille/i', $ligne->detail_libelle)) {
                        continue;
                    }
                    if($ligne->couleur == "BLEU") {
                        continue;
                    }
                    $centilisation = str_replace(".", ",", ($ligne->centilitrage*10000));
                    if(!isset($db2[$identifiantPeriode][$centilisation])) {
                        $db2[$identifiantPeriode][$centilisation]["08.UTILISATION/TRANQ"] = 0;
                        $db2[$identifiantPeriode][$centilisation]["09.UTILISATION/MOUSSEUX"] = 0;
                        $db2[$identifiantPeriode][$centilisation]["10.AUTRE/TRANQ"] = 0;
                        $db2[$identifiantPeriode][$centilisation]["11.AUTRE/MOUSSEUX"] = 0;
                    }
                    if($ligne->genre == "TRANQ" && $ligne->sorties_utilisations) {
                        $db2[$identifiantPeriode][$centilisation]["08.UTILISATION/TRANQ"] += $ligne->sorties_utilisations;
                    }
                    if($ligne->genre == "MOUSSEUX" && $ligne->sorties_utilisations) {
                        $db2[$identifiantPeriode][$centilisation]["09.UTILISATION/MOUSSEUX"] += $ligne->sorties_utilisations;
                    }                }
                krsort($db2[$identifiantPeriode]);
            }
        }

        return $db2;
    }

    protected function aggregateTotal($mouvements, $db2CRD) {
        $db2 = array();

        foreach($mouvements as $mouvement) {
            if($mouvement->type_drm != "SUSPENDU") {
                continue;
            }
            if(!preg_match("/".$mouvement->etablissement_identifiant."/", $mouvement->id_doc)) {
                continue;
            }
            if(!$this->convertProduit($mouvement->produit_hash)) {
                continue;
            }
            $produit = $this->convertProduit($mouvement->produit_hash);
            $file = $this->convertFile($mouvement->type_hash);
            $mouvementType = $this->convertMouvement($mouvement->type_hash);

            if($mouvementType == "01.DRMDEM/26.Replis" && $produit != "01.BLANC") {
                continue;
            }

            $identifiantPeriode = preg_replace("/DRM-(.+)-(.+)-?.*$/", '\1-\2', $mouvement->id_doc);

            if(!isset($db2[$identifiantPeriode])) {
                $db2[$identifiantPeriode] = array("prix_ht" => 0, "quantite" => 0, "crd_tranq_utilisation" => 0, "crd_mousseux_utilisation" => 0);
            }
            if(!isset($db2[$identifiantPeriode][$file])) {
                $db2[$identifiantPeriode][$file] = array();
            }
            if(!isset($db2[$identifiantPeriode][$file][$produit])) {
                $db2[$identifiantPeriode][$file][$produit] = 0;
            }
            if($mouvement->facturable && $mouvement->prix_unitaire > 0) {
                $db2[$identifiantPeriode]["prix_ht"] += $mouvement->prix_ht;
                $db2[$identifiantPeriode]["quantite"] += $mouvement->quantite;
            }
            $db2[$identifiantPeriode][$file][$produit] += $mouvement->quantite * ((preg_match('/entrees/', $mouvement->type_hash)) ? -1 : 1);

            ksort($db2[$identifiantPeriode][$file]);
            ksort($db2[$identifiantPeriode]);
        }

        foreach($db2CRD as $identifiantPeriode => $centilisations) {
            foreach($centilisations as $centilisation => $sorties) {
                if(!isset($db2[$identifiantPeriode])) {
                    $db2[$identifiantPeriode] = array("prix_ht" => 0, "quantite" => 0, "crd_tranq_utilisation" => 0, "crd_mousseux_utilisation" => 0);
                }
                $db2[$identifiantPeriode]["crd_tranq_utilisation"] += $sorties["08.UTILISATION/TRANQ"] * (float) str_replace(",", ".", $centilisation) / 10000;
                $db2[$identifiantPeriode]["crd_mousseux_utilisation"] += $sorties["09.UTILISATION/MOUSSEUX"] * (float) str_replace(",", ".", $centilisation) / 10000;
            }
        }

        return $db2;
    }

    protected function convertProduit($hash, $rouge = true) {
        if(!preg_match('#/AOC_ALSACE#', $hash)) {

            return null;
        }

        if(preg_match('#/GRDCRU#', $hash)) {

            return "03.GRDCRU";
        }

        if(preg_match("#/CREMANT#", $hash)) {

            return "04.CREMANT";
        }

        if($rouge && preg_match('#/(PN|PR|rouge)#', $hash)) {

            return "02.ROUGE";
        }

        return "01.BLANC";
    }

    protected function convertMouvement($typeHash) {
        if(!isset($this->correspondances[$typeHash])) {

            return null;
        }

        return $this->correspondances[$typeHash];
    }

    protected function convertFile($typeHash) {
        if(!isset($this->correspondances[$typeHash])) {

            return null;
        }

        $parts = explode("/", $this->correspondances[$typeHash]);

        return $parts[0];
    }

    protected function buildStructure() {
        $structure = array();
        foreach($this->correspondances as $item) {
            $parts = explode("/", $item);
            $file = $parts[0];
            $volumeType = $parts[1];

            if(!isset($structure[$file])) {
                $structure[$file] = array();
            }

            $structure[$file][$volumeType] = $this->produits;
        }

        return $structure;
    }

    function convertPays($code) {
        if(!isset($this->pays[$code])) {

            $code = "AUTRE";
        }

        return $this->pays[$code];
    }
}
