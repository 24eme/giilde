<?php
use_helper('IvbdStatistique');
$options = $options->getRawValue();
$configuration = ConfigurationClient::getConfiguration($options['fromDate']);
$cm = new CampagneManager('08-01');
$millesime = strstr($cm->getCampagneByDate($options['fromDate']),'-',true);
$csvArray = array();
$csvProduitCumulVolumes = array();
$csv = 'Type contrat;Produit;Nombre de contrats;Qté en hl;Prix moyen;Volume '.$millesime.'; Cours '.$millesime.';Volume autres millesimes;Cours autres millesimes;Volume tous contrats'."\n";
foreach ($result['agg_page']['buckets'] as $type_contrats) {
	$type_contrats_libelle = getConditionnementLibelle(strtoupper($type_contrats['key']));
	$csvArray[$type_contrats_libelle] = array();
	foreach ($type_contrats['agg_page']['buckets'] as $produit_contrats_millesimes) {

        $produitLibelle = getProduitLibelle($produit_contrats_millesimes['key'],$configuration);

		$csvArray[$type_contrats_libelle][$produitLibelle] = array();

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
			$toutMillesimesVolume += ($contrats_millesimes['vol_prix']['agg_column']['value'])? floatval($contrats_millesimes['vol_prix']['agg_column']['value']) : 0.0;
			$toutMillesimesPrix += ($contrats_millesimes['ca']['agg_column']['value'])? floatval($contrats_millesimes['ca']['agg_column']['value']) : 0.0;
			if($contrats_millesimes['key'] == $millesime){
				$millesimesNb = $contrats_millesimes['doc_count'];
				$millesimesVolume = ($contrats_millesimes['vol_prix']['agg_column']['value'])? floatval($contrats_millesimes['vol_prix']['agg_column']['value']) : 0.0;
				$millesimesPrix = ($contrats_millesimes['ca']['agg_column']['value'])? floatval($contrats_millesimes['ca']['agg_column']['value']) : 0.0;
				$millesimesMoyenne  = ($contrats_millesimes['moyenne']['value'])? floatval($contrats_millesimes['moyenne']['value']) : 0.0;
			}else{
				$horsMillesimesNb += $contrats_millesimes['doc_count'];
				$horsMillesimesVolume += ($contrats_millesimes['vol_prix']['agg_column']['value'])? floatval($contrats_millesimes['vol_prix']['agg_column']['value']) : 0.0;
				$horsMillesimesPrix += ($contrats_millesimes['ca']['agg_column']['value'])? floatval($contrats_millesimes['ca']['agg_column']['value']) : 0.0 ;
			}
		}

		$toutMillesimesMoyenne = ($toutMillesimesVolume)? floatval($toutMillesimesPrix / $toutMillesimesVolume / 225) : 0.0;
		$horsMillesimesMoyenne = ($horsMillesimesVolume)? floatval($horsMillesimesPrix / $horsMillesimesVolume / 225) : 0.0;
		if(!array_key_exists($type_contrats_libelle,$csvProduitCumulVolumes)){
			$csvProduitCumulVolumes[$type_contrats_libelle] = array();
		}
        if(!array_key_exists($produitLibelle,$csvProduitCumulVolumes[$type_contrats_libelle])){
			$csvProduitCumulVolumes[$type_contrats_libelle][$produitLibelle] = 0.0;
		}
		$csvProduitCumulVolumes[$type_contrats_libelle][$produitLibelle] += $toutMillesimesVolume;

		$csvArray[$type_contrats_libelle][$produitLibelle]['type_contrats_libelle'] = $type_contrats_libelle;
		$csvArray[$type_contrats_libelle][$produitLibelle]['produitLibelle'] = $produitLibelle;
		$csvArray[$type_contrats_libelle][$produitLibelle]['nbContrats'] = $nbContrats;
		$csvArray[$type_contrats_libelle][$produitLibelle]['toutMillesimesVolume'] = $toutMillesimesVolume;
		$csvArray[$type_contrats_libelle][$produitLibelle]['toutMillesimesMoyenne'] = $toutMillesimesMoyenne;
		$csvArray[$type_contrats_libelle][$produitLibelle]['millesimesVolume'] = $millesimesVolume;
		$csvArray[$type_contrats_libelle][$produitLibelle]['millesimesMoyenne'] = $millesimesMoyenne;
		$csvArray[$type_contrats_libelle][$produitLibelle]['horsMillesimesVolume'] = $horsMillesimesVolume;
		$csvArray[$type_contrats_libelle][$produitLibelle]['horsMillesimesMoyenne'] = $horsMillesimesMoyenne;
		}
	}


	foreach ($csvArray as $type_contrats_libelle => $produits) {
		foreach ($produits as $produitKey => $produitValues) {
			$csv .= $produitValues['type_contrats_libelle'].';'.
							$produitValues['produitLibelle'].';'.
							$produitValues['nbContrats'].';'.
							formatNumber($produitValues['toutMillesimesVolume'],2).';'.
							formatNumber($produitValues['toutMillesimesMoyenne'],2).';'.
							formatNumber($produitValues['millesimesVolume'],2).';'.
							formatNumber($produitValues['millesimesMoyenne'],2).';'.
							formatNumber($produitValues['horsMillesimesVolume'],2).';'.
							formatNumber($produitValues['horsMillesimesMoyenne'],2).';'.
							formatNumber($csvProduitCumulVolumes[$type_contrats_libelle][$produitKey],2)."\n";
		}
	}

echo $csv;
