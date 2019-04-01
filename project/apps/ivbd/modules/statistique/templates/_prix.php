<?php
use_helper('IvbdStatistique');
$millesime = preg_replace("/([0-9]+)-([0-9]+)/","$1",ConfigurationClient::getInstance()->getCurrentCampagne());
$csvArray = array();
$csvProduitCumulVolumes = array();

$csv = 'Type contrat;Produit;Nombre de contrats;Qté en hl;Prix moyen;Volume '.$millesime.'; Cours '.$millesime.';Volume autres millesimes;Cours autres millesimes;Volume tous contrats'."\n";
foreach ($result['agg_page']['buckets'] as $type_contrats) {
	$type_contrats_libelle = getConditionnementLibelle(strtoupper($type_contrats['key']));
	$csvArray[$type_contrats_libelle] = array();
	foreach ($type_contrats['agg_page']['buckets'] as $produit_contrats_millesimes) {
		$produitLibelle = getProduitLibelle($produit_contrats_millesimes['key']);
		$csvArray[$type_contrats_libelle][$produit_contrats_millesimes['key']] = array();

		$nbContrats = $produit_contrats_millesimes["doc_count"];

		$toutMillesimesPrix = 0.0;
		$toutMillesimesVolume = 0.0;
		$toutMillesimesMoyenne = 0.0;

		$millesimesNb = 0;
		$millesimesPrix = 0.0;
		$millesimesVolume = 0.0;
		$millesimesMoyenne = 0.0;

		$horsMillesimesNb = 0;
		$horsMillesimesPrix = 0.0;
		$horsMillesimesVolume = 0.0;
		$horsMillesimesMoyenne = 0.0;

		foreach ($produit_contrats_millesimes['agg_line']['buckets'] as $contrats_millesimes) {
			$toutMillesimesVolume += formatNumber($contrats_millesimes['vol_prix']['agg_column']['value'],2);
			$toutMillesimesPrix += formatNumber($contrats_millesimes['ca']['agg_column']['value'],2);
			if($contrats_millesimes['key'] == $millesime){
				$millesimesNb = $contrats_millesimes['doc_count'];
				$millesimesVolume = formatNumber($contrats_millesimes['vol_prix']['agg_column']['value'],2);
				$millesimesPrix = formatNumber($contrats_millesimes['ca']['agg_column']['value'],2);
				$millesimesMoyenne  = formatNumber($contrats_millesimes['moyenne']['value'],2);
			}else{
				$horsMillesimesNb += $contrats_millesimes['doc_count'];
				$horsMillesimesVolume += formatNumber($contrats_millesimes['vol_prix']['agg_column']['value'],2);
				$horsMillesimesPrix += formatNumber($contrats_millesimes['ca']['agg_column']['value'],2);
			}
		}

		$toutMillesimesMoyenne = ($toutMillesimesVolume)? formatNumber($toutMillesimesPrix / $toutMillesimesVolume / 225,2) : 0.0;
		$horsMillesimesMoyenne = ($horsMillesimesVolume)? formatNumber($horsMillesimesPrix / $horsMillesimesVolume / 225,2) : 0.0;
		if(!array_key_exists($produit_contrats_millesimes['key'],$csvProduitCumulVolumes)){
			$csvProduitCumulVolumes[$produit_contrats_millesimes['key']] = 0.0;
		}
		$csvProduitCumulVolumes[$produit_contrats_millesimes['key']] += $toutMillesimesVolume;

		$csvArray[$type_contrats_libelle][$produit_contrats_millesimes['key']]['type_contrats_libelle'] = $type_contrats_libelle;
		$csvArray[$type_contrats_libelle][$produit_contrats_millesimes['key']]['produitLibelle'] = $produitLibelle;
		$csvArray[$type_contrats_libelle][$produit_contrats_millesimes['key']]['nbContrats'] = $nbContrats;
		$csvArray[$type_contrats_libelle][$produit_contrats_millesimes['key']]['toutMillesimesVolume'] = $toutMillesimesVolume;
		$csvArray[$type_contrats_libelle][$produit_contrats_millesimes['key']]['toutMillesimesMoyenne'] = $toutMillesimesMoyenne;
		$csvArray[$type_contrats_libelle][$produit_contrats_millesimes['key']]['millesimesVolume'] = $millesimesVolume;
		$csvArray[$type_contrats_libelle][$produit_contrats_millesimes['key']]['millesimesMoyenne'] = $millesimesMoyenne;
		$csvArray[$type_contrats_libelle][$produit_contrats_millesimes['key']]['horsMillesimesVolume'] = $horsMillesimesVolume;
		$csvArray[$type_contrats_libelle][$produit_contrats_millesimes['key']]['horsMillesimesMoyenne'] = $horsMillesimesMoyenne;
		}
	}


	foreach ($csvArray as $type_contrats_libelle => $produits) {
		foreach ($produits as $produitKey => $produitValues) {
			$csv .= $produitValues['type_contrats_libelle'].';'.
							$produitValues['produitLibelle'].';'.
							$produitValues['nbContrats'].';'.
							$produitValues['toutMillesimesVolume'].';'.
							$produitValues['toutMillesimesMoyenne'].';'.
							$produitValues['millesimesVolume'].';'.
							$produitValues['millesimesMoyenne'].';'.
							$produitValues['horsMillesimesVolume'].';'.
							$produitValues['horsMillesimesMoyenne'].';'.
							$csvProduitCumulVolumes[$produitKey]."\n";
		}
	}

echo $csv;
