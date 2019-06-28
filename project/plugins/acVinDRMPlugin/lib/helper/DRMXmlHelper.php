<?php

function noeudXml($produit, $noeud, &$xml, $exceptions = array()) {
	foreach ($noeud as $key => $children) {
		if (!is_numeric($key)) {
			$xml .= "<$key>";
			$xml .= noeudXml($produit, $children, $xml, $exceptions);
			$xml .= "</$key>";
		} else {
			$val = $produit->getTotalVolume($noeud);
			if ($val) {
				return (in_array($noeud->getKey(), $exceptions))? $val : sprintf("%01.02f", $val);
			} else {
				return (in_array($noeud->getKey(), array('stock-debut-periode', 'stock-fin-periode')))? 0 : null;
			}
		}
	}
}

function storeMultiArray(&$node, $keys, $value, $not_sum = false) {
	$k = array_shift($keys);
	if (!$k) {
		if (!is_array($node) && !$not_sum) {
			return $value + $node;
		}else{
			return $value;
		}
	}
	if (!is_array($node)) {
		$node = array();
	}
	$node[$k] = storeMultiArray($node[$k], $keys, $value, $not_sum);
	return $node;
}

function multiArray2XML($preXML) {
	$xml = '';
	foreach ($preXML as $k => $v) {
		if (is_array($v)) {
			$xml .= "<$k>";
			$xml .= multiArray2XML($v);
			$xml .= "</$k>";
		}else{
			$xml .= "<$k>";
			if ($k != "annee" && $k != "mois") {
      	$xml .= sprintf('%.04f', $v);
			}else{
				$xml .= sprintf('%02d', $v);
			}
      $xml .= "</$k>";
		}
	}
	return $xml;
}

function details2XmlDouane($detail, $isNegoce = false) {
	$detailKey = $detail->getParent()->getKey();
	$confDetail = null;
	$confKey = ($isNegoce)? 'douane_cat_negoce' : 'douane_cat';
	if(!$detail->getConfig()->getDocument()->declaration->exist($detailKey)){
		$confDetail = $detail->getConfig()->getDocument()->declaration->details;
	}else{
		$confDetail = $detail->getConfig()->getDocument()->declaration->$detailKey;
	}
	$preXML = array();
	$keyForceDisplay = array();
	foreach (array('stocks_debut', 'stocks_fin') as $type) {
		foreach($confDetail->get($type) as $k => $v) {
			if($confDetail->get($type)->get($k)->get($confKey)) {
				$keyForceDisplay[$type] = $k;
				break;
			}
		}
	}
	foreach (array('stocks_debut', 'entrees', 'sorties', 'stocks_fin') as $type) {
		foreach ($detail->get($type) as $k => $v) {
			if (($v || (($k == 'initial' || $k == 'final') && preg_match('/^stock/', $type))) && $confDetail->get($type)->exist($k) && $confDetail->get($type)->get($k)->get($confKey)) {
				$preXML = storeMultiArray($preXML, explode('/', $confDetail->get($type)->get($k)->get($confKey)),  $v);
				if (preg_match('/replacement/', $confDetail->get($type)->get($k)->get($confKey))) {
					$preXML = storeMultiArray($preXML, explode('/', 'entrees-periode/replacements/replacement-suspension/mois'),  $detail->getReplacementMonth(), true);
					$preXML = storeMultiArray($preXML, explode('/', 'entrees-periode/replacements/replacement-suspension/annee'), $detail->getReplacementYear(),  true);
				}
			}
		}
	}
	return multiArray2XML($preXML);
}

function formatXml($xml, $level = 0) {
	while (preg_match("/\<[a-zA-Z0-9\-\_]*\>\<\/[a-zA-Z0-9\-\_]*\>/", $xml)) {
		$xml = preg_replace("/\<[a-zA-Z0-9\-\_]*\>\<\/[a-zA-Z0-9\-\_]*\>/", "", $xml);
	}
	$xml = preg_replace("/\<([a-zA-Z0-9\-\_]*)\>\<([a-zA-Z0-9\-\_]*)\>/", "<$1>\n".str_repeat("\t", $level + 1)."<$2>", $xml);
	$xml = preg_replace("/\<(\/[a-zA-Z0-9\-\_]*)\>\<([a-zA-Z0-9\-\_]*)\>/", "<$1>\n".str_repeat("\t", $level)."<$2>", $xml);
	$xml = preg_replace("/\<(\/[a-zA-Z0-9\-\_]*)\>\<(\/[a-zA-Z0-9\-\_]*)\>/", "<$1>\n".str_repeat("\t", $level)."<$2>", $xml);
	$xml = preg_replace("/\<(\/[a-zA-Z0-9\-\_]*)\>\<(\/[a-zA-Z0-9\-\_]*)\>/", str_repeat("\t", 1)."<$1>\n".str_repeat("\t", $level)."<$2>", $xml);
	$xml = preg_replace("/\<([a-zA-Z0-9\-\_]*)\>\<([a-zA-Z0-9\-\_]*)\>/", "<$1>\n".str_repeat("\t", $level + 2)."<$2>", $xml);
	return str_repeat("\t", $level).$xml."\n";
}

function drm2CrdCiel($drm) {
	$crds = array();
	foreach ($drm->crds as $type => $tcrds) {
		foreach ($tcrds as $k => $crd) {
			if (!isset($crds[$type.$crd->genre])) {
				$crds[$type.$crd->genre] = array();
			}
			$subkey = sprintf('%.f', $crd->centilitrage);
			if (preg_match('/bib/i', $crd->detail_libelle)) {
				$subkey = "BIB".$subkey;
			}
			if (!isset($crds[$type.$crd->genre][$subkey])) {
				$crds[$type.$crd->genre][$subkey] = clone $crd;
			}else{
				$crds[$type.$crd->genre][$subkey]->stock_debut += $crd->stock_debut;
                                $crds[$type.$crd->genre][$subkey]->entrees_achats += $crd->entrees_achats;
                                $crds[$type.$crd->genre][$subkey]->entrees_excedents += $crd->entrees_excedents;
                                $crds[$type.$crd->genre][$subkey]->entrees_retours += $crd->entrees_retours;
                                $crds[$type.$crd->genre][$subkey]->sorties_destructions += $crd->sorties_destructions;
                                $crds[$type.$crd->genre][$subkey]->sorties_utilisations += $crd->sorties_utilisations;
                                $crds[$type.$crd->genre][$subkey]->sorties_manquants += $crd->sorties_manquants;
                                $crds[$type.$crd->genre][$subkey]->stock_fin += $crd->stock_fin;
			}
		}
	}
	return $crds;
}

function crdGenre2CategorieFiscale($g) {
	$crdGenre2CategorieFiscaleArray = array(DRMClient::DRM_CRD_CATEGORIE_TRANQ => 'T', DRMClient::DRM_CRD_CATEGORIE_MOUSSEUX => 'M');
	return $crdGenre2CategorieFiscaleArray[$g];
}
function crdType2TypeCapsule($t) {
	$crdType2TypeCapsuleArray = array(EtablissementClient::REGIME_CRD_COLLECTIF_SUSPENDU=>'COLLECTIVES_DROITS_SUSPENDUS',  EtablissementClient::REGIME_CRD_COLLECTIF_ACQUITTE=> 'COLLECTIVES_DROITS_ACQUITTES', EtablissementClient::REGIME_CRD_PERSONNALISE=>'PERSONNALISEES');
	return $crdType2TypeCapsuleArray[$t];
}
function documentAnnexeKey2XMLTag($d) {
	$documentAnnexeKey2XMLTagArray = array('DAE' => 'daa-dca', 'DAA/DAC' => 'daa-dca', 'DAADAC' => 'daa-dca', 'DSA/DSAC' => 'dsa-dsac', 'DSADSAC' => 'dsa-dsac', 'EMPREINTE'=>'numero-empreintes');
	return $documentAnnexeKey2XMLTagArray[$d];
}

function formatCodeINAO($s) {
	if (strlen($s) == 5) {
		return "$s ";
	}
	return $s;
}
function formatDateDouane($s) {
	return preg_replace('/([0-9][0-9])[^0-9]([0-9][0-9])[^0-9]([0-9][0-9][0-9][0-9])/', '\3-\2-\1', $s);
}

function centilisation2Douane($c, $libelle) {
	$bouteilles = array('0.001000' => 'CL_10',
		'0.001250' => 'CL_12_5',
		'0.001870' => 'CL_18_7',
		'0.002000' => 'CL_20',
		'0.002500' => 'CL_25',
		'0.003500' => 'CL_35',
		'0.003750' => 'CL_37_5',
		'0.005000' => 'CL_50',
		'0.006200' => 'CL_62',
		'0.007000' => 'CL_70',
		'0.007500' => 'CL_75',
		'0.010000' => 'CL_100',
		'0.015000' => 'CL_150',
		'0.017500' => 'CL_175',
		'0.020000' => 'CL_200');
	$bib = array('0.022500' => 'BIB_225',
		'0.030000' => 'BIB_300',
		'0.040000' => 'BIB_400',
		'0.050000' => 'BIB_500',
		'0.080000' => 'BIB_800',
		'0.100000' => 'BIB_1000');
		if (preg_match('/bib/i', $libelle)) {
			if (isset($bib[sprintf('%.f', $c)]) && $bib[sprintf('%.f', $c)]) {
				return $bib[sprintf('%.f', $c)];
			}
		}elseif (isset($bouteilles[sprintf('%.f', $c)]) && $bouteilles[sprintf('%.f', $c)]) {
			return $bouteilles[sprintf('%.f', $c)];
		}
		return "AUTRE";
}


function xmlGetNodesToTable($flatXmlNodes){
	$str = "";
	if(is_string($flatXmlNodes)){
		return "<tr><td colspan='2' class='text-center'><strong>".$flatXmlNodes."</strong></td></tr>";
	}
	foreach ($flatXmlNodes as $key => $value) {
		if($value === NULL){ continue; }
		if(preg_match("/^produit|volume$/",$key)){
			$str .="<tr><td colspan='2' class=''><strong>".$value."</strong></td></tr>";

		}elseif(preg_match("/^categorie-fiscale-capsules$/",$key) || preg_match("/^type-capsule$/",$key)){
			$str .="<tr><td class='text-center'><strong>".str_ireplace("/"," => ",preg_replace("/\/[0-9]+\//"," => ",$key))."</strong></td>"
			."<td  >".$value."</td></tr>";
		}else{
			$str .="<tr><td  style='min-width:400px;max-width:400px; text-align: left;'>".str_ireplace("/"," => ",preg_replace("/\/[0-9]+\//"," => ",$key))."</td>"
			."<td  >".$value."</td></tr>";
		}
	}
	return $str;
}

function xmlProduitsToTable($flatXml,$reg){
	$produits = array();
	foreach ($flatXml as $key => $value) {
		if(preg_match("/^$reg\/produit\/[0-9]+\//",$key)){
			$oneNode = true;
			$match = array();
			preg_match("/($reg\/produit\/[0-9]+\/)(.*)/",$key,$match);
			$radix = $match[1];
			$inaoCode = isset($flatXml[$radix."code-inao"])? $flatXml[$radix."code-inao"] : $flatXml[$radix."libelle-fiscal"];
			$inaoKey = $radix.$inaoCode;

			if(!array_key_exists($inaoKey,$produits)){
				$produits[$inaoKey] = array();
				$produits[$inaoKey]["produit"] = $flatXml[$radix."libelle-personnalise"]." (".$inaoCode.")";
			}
			if(!preg_match("/libelle-personnalise/",$key) && !preg_match("/code-inao/",$key)){
				$produits[$inaoKey][str_ireplace($radix,"",$key)] = $value;
			}
		}elseif(preg_match("/^$reg\/produit\//",$key)){
			$oneNode = true;
			$match = array();
			preg_match("/($reg\/produit\/)(.*)/",$key,$match);
			$radix = $match[1];
			$inaoCode = isset($flatXml[$radix."code-inao"])? $flatXml[$radix."code-inao"] : $flatXml[$radix."libelle-fiscal"];
			$inaoKey = $radix.$inaoCode;

			if(!array_key_exists($inaoKey,$produits)){
				$produits[$inaoKey] = array();
				$produits[$inaoKey]["produit"] = $flatXml[$radix."libelle-personnalise"]." (".$inaoCode.")";
			}
			if(!preg_match("/libelle-personnalise/",$key) && !preg_match("/code-inao/",$key)){
				$produits[$inaoKey][str_ireplace($radix,"",$key)] = $value;
			}
		}
	}
	$str = "";
	foreach ($produits as $inaoKey => $produit) {
		$str.= xmlGetNodesToTable($produit);
	}
	if(!count($produits)){
		$str.= xmlGetNodesToTable("Pas de noeud");
	}
	return $str;
}

function xmlCrdsToTable($flatXml,$reg){

	$crds = array();
	foreach ($flatXml as $key => $value) {
		if(preg_match("/^$reg\/([0-9]+\/)?/",$key)){
			$match = array();
			preg_match("/($reg\/([0-9]+\/)?)(.*)/",$key,$match);
			$radix = $match[1];
			if(!array_key_exists($radix,$crds)){
				$crds[$radix] = array();
				$crds[$radix]["crd"] = $flatXml[$radix."type-capsule"]." (".$flatXml[$radix."categorie-fiscale-capsules"].")";
			}
			if(!preg_match("/type-capsule/",$key) && !preg_match("/categorie-fiscale-capsules/",$key)){
				$p_rad = str_ireplace("/","\/",$radix)."centilisation\/([0-9]+\/)?";

				$match2 = array();
				preg_match("/$p_rad/",$key,$match2);
				if(count($match2)){
					$c_key = $flatXml[$match2[0]."@attributes/volume"];
					if(!array_key_exists($c_key,$crds[$radix])){
						$crds[$radix][$c_key] = array();
						$crds[$radix][$c_key]['volume'] = $c_key;
					}
					if(!preg_match("/@attributes\/volume/",$key) && $value){
					$crds[$radix][$c_key][preg_replace("/$p_rad/","",$key)] = $value;
					}
				}
			}
		}
	}
	$str = "";
	foreach ($crds as $rad => $crdCat) {
		foreach ($crdCat as $k => $crdVol) {
			$str.= xmlGetNodesToTable($crdVol);
		}
	}
	if(!count($crds)){
		$str.= xmlGetNodesToTable("Pas de noeud");
	}
	return $str;
}

function xmlPartOfToTable($flatXml,$regexs = array(),$withRemove = false){
	$partOfFlatXml = array();
	foreach ($flatXml as $key => $value) {
		foreach ($regexs as $reg) {
			if(preg_match("/^$reg/",$key)){
				$newKey = $key;
				if($withRemove) $newKey = str_ireplace($reg."/","",$key);
				if($reg != "compte-crd" || $value){
					$partOfFlatXml[$newKey] = $value;

				}
			}
		}
	}
	return xmlGetNodesToTable($partOfFlatXml);
}

function xmlProduitLibelle($produit) {
	return "<![CDATA[".str_replace('&', ' et ', trim(html_entity_decode((($produit->produit_libelle) ? $produit->produit_libelle : $produit->getLibelle('%format_libelle% %la%')), ENT_QUOTES | ENT_HTML401)))."]]>";
}

function xmlGetProduitsDetails($drm, $bool, $suspendu_acquitte) {
	$produits = array();
	$produits_faits = array();
	foreach ($drm->getProduitsDetails($bool, $suspendu_acquitte) as $produit) {
		$produit_libelle = xmlProduitLibelle($produit);
		if (isset($produits_faits[$produit_libelle])) {
			continue;
		}
		$produits_faits[$produit_libelle] = $produit_libelle;
		$produits[] = $produit;
	}
	if (preg_match('/08$/', $drm->periode)) {
		$drm_juillet = DRMClient::getInstance()->find(preg_replace('/08$/', '07', $drm->_id));
		if ($drm_juillet) {
			$drm_juillet->init(array("keepStock" => false));
			foreach ($drm_juillet->getProduitsDetails($bool, $suspendu_acquitte) as $produit) {
				$produit_libelle = xmlProduitLibelle($produit);
				if (isset($produits_faits[$produit_libelle])) {
					continue;
				}
				$produits_faits[$produit_libelle] = $produit_libelle;
				$produits[] = $produit;
			}
		}
	}
	if ($drm->isNegoce()) {
	    $drmNegoce = new DRM();
	    $drmNegoce->periode = $drm->periode;
	    foreach ($produits as $p) {
	        $produit = $drmNegoce->addProduit($p->getCorrespondanceNegoce(), $suspendu_acquitte);
	        $produit->total_debut_mois += $p->total_debut_mois;
	        if ($p->exist('observations')) {
	            $obs = $produit->getOrAdd('observations');
	            $obs = ($produit->exist('observations'))? $produit->observations.' - '.$p->observations : $p->observations;
	        }
	        if ($p->exist('replacement_date')) {
	            $repl = $produit->getOrAdd('replacement_date');
	            $repl = ($produit->exist('replacement_date'))? $produit->replacement_date : $p->replacement_date;
	        }
	        foreach (array('stocks_debut', 'entrees', 'sorties', 'stocks_fin') as $item) {
	            foreach ($p->{$item} as $mv => $val) {
	                if (strpos($mv, '_details') !== false && $p->{$item}->{$mv}->exist('volume')) {
	                    $produit->{$item}->{str_replace('_details', '', $mv)} += $p->{$item}->{$mv}->volume;
	                } elseif (strpos($mv, '_details') === false) {
	                   $produit->{$item}->{$mv} += $p->{$item}->{$mv};
	                }
	            }
	        }
	    }
	    $produits = $drmNegoce->getProduitsDetails($bool, $suspendu_acquitte);
	}
	return $produits;
}

function getCielProduits() {
    $e = $this->getEtablissementObject();
    if ($e->famille == EtablissementFamilles::FAMILLE_PRODUCTEUR || $e->sous_famille == EtablissementFamilles::SOUS_FAMILLE_VINIFICATEUR) {
        return $this->getDetails();
    } else {
        $drm = new DRM();
        $drm->periode = $this->periode;
        foreach ($this->getDetails() as $detail) {
            $produit = $drm->addProduit($detail->getCorrespondanceNegoce());

            $produit->total_debut_mois += $detail->total_debut_mois;
            $produit->acq_total_debut_mois += $detail->acq_total_debut_mois;
            if ($detail->observations) {
                $produit->observations = ($produit->observations)? $produit->observations.' - '.$detail->observations : $detail->observations;
            }
            foreach (array('stocks_debut', 'entrees', 'sorties', 'stocks_fin') as $item) {
                foreach ($detail->{$item} as $mv => $val) {
                    if ($produit->{$item}->{$mv} instanceof DRMESDetails) {
                        continue;
                    }
                    $produit->{$item}->{$mv} += $detail->{$item}->{$mv};
                }
            }
        }
        $drm->update();
        foreach ($drm->getDetails() as $detail) {
            $detail->libelle = $detail->getCertification()->getKey().' '.$detail->libelle;
        }
        return $drm->getDetails();
    }
}
