<?php
use_helper('IvbdStatistique');
$millesime = preg_replace("/([0-9]+)-([0-9]+)/","$1",ConfigurationClient::getInstance()->getCurrentCampagne());
$csvArray = array();
$csvProduitCumulVolumes = array();

$csv = 'Produit;Volume total;Cours total;Volume Bio; Cours Bio;Volume Château; Cours Château;Volume Générique; Cours Générique'."\n";
foreach ($result['agg_page']['buckets'] as $type_contrats) {
	$type_contrats_libelle = getConditionnementLibelle(strtoupper($type_contrats['key']));
	$csvArray[$type_contrats_libelle] = array();

	$total_total_volume_categorie = 0.0;
	$total_total_ca_categorie = 0.0;

	$total_total_volume_bio = 0.0;
	$total_total_ca_bio = 0.0;

	$total_total_volume_domaine = 0.0;
	$total_total_ca_domaine = 0.0;

	$total_total_volume_generique = 0.0;
	$total_total_ca_generique = 0.0;

	foreach ($type_contrats['agg_page']['buckets'] as $produit_contrats_mentions) {
		$produitLibelle = getProduitLibelle($produit_contrats_mentions['key']);
		$csvArray[$type_contrats_libelle][$produit_contrats_mentions['key']] = array();

		$total_volume_categorie = $produit_contrats_mentions["total_volume_categorie"]["value"];
		$total_ca_categorie = $produit_contrats_mentions["total_ca_categorie"]["value"];
		$prix_moyen_categorie = ($total_volume_categorie)? $total_ca_categorie / $total_volume_categorie : 0.0;

		$total_total_volume_categorie += $total_volume_categorie;
		$total_total_ca_categorie += $total_ca_categorie;

		$total_volume_bio = $produit_contrats_mentions["total_volume_bio"]["value"];
		$total_ca_bio = $produit_contrats_mentions["total_ca_bio"]["value"];
		$prix_moyen_bio = ($total_volume_bio)? $total_ca_bio / $total_volume_bio : 0.0;

		$total_total_volume_bio += $total_volume_bio;
		$total_total_ca_bio += $total_ca_bio;

		$total_volume_domaine = $produit_contrats_mentions["total_volume_domaine"]["value"];
		$total_ca_domaine = $produit_contrats_mentions["total_ca_domaine"]["value"];
		$prix_moyen_domaine = ($total_volume_domaine)? $total_ca_domaine / $total_volume_domaine : 0.0;

		$total_total_volume_domaine += $total_volume_domaine;
		$total_total_ca_domaine += $total_ca_domaine;

		$total_volume_generique = $total_volume_categorie - $total_volume_domaine - $total_volume_bio;
		$total_ca_generique = $total_ca_categorie - $total_ca_domaine - $total_ca_bio;
		$prix_moyen_generique = ($total_volume_generique)? $total_ca_generique / $total_volume_generique : 0.0;

		$total_total_volume_generique += $total_volume_generique;
		$total_total_ca_generique += $total_ca_generique;

		$csv.= $produitLibelle.";".
					 formatNumber($total_volume_categorie,2).";".
					 formatNumber($prix_moyen_categorie,2).";".
					 formatNumber($total_volume_bio,2).";".
					 formatNumber($prix_moyen_bio,2).";".
					 formatNumber($total_volume_domaine,2).";".
					 formatNumber($prix_moyen_domaine,2).";".
					 formatNumber($total_volume_generique,2).";".
					 formatNumber($prix_moyen_generique,2)."\n";
	}

		$total_prix_moyen_categorie = ($total_total_volume_categorie)? $total_total_ca_categorie / $total_total_volume_categorie : 0.0;
		$total_prix_moyen_bio = ($total_total_volume_bio)? $total_total_ca_bio / $total_total_volume_bio : 0.0;
		$total_prix_moyen_domaine = ($total_total_volume_domaine)? $total_total_ca_domaine / $total_total_volume_domaine : 0.0;
		$total_prix_moyen_generique = ($total_total_volume_generique)? $total_total_ca_generique / $total_total_volume_generique : 0.0;

	$csv.= "TOTAL;".
					 formatNumber($total_total_volume_categorie,2).";".
					 formatNumber($total_prix_moyen_categorie,2).";".
					 formatNumber($total_total_volume_bio,2).";".
					 formatNumber($total_prix_moyen_bio,2).";".
					 formatNumber($total_total_volume_domaine,2).";".
					 formatNumber($total_prix_moyen_domaine,2).";".
					 formatNumber($total_total_volume_generique,2).";".
					 formatNumber($total_prix_moyen_generique,2)."\n";

}



echo $csv;
