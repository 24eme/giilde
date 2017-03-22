<?php 
use_helper('Statistique');
use_helper('BivcStatistique');

$csv = "Catégorie;Appellation;Couleur;France;Export;Négoce;TOTAL\n";
foreach ($result['agg_page']['buckets'] as $categorie) {
	$categorieLibelle = getFamilleLibelle(strtoupper($categorie['key']));
	foreach ($categorie['agg_page']['buckets'] as $appellation) {
		$appellationLibelle = getAppellationLibelle(strtoupper($appellation['key']));
		$totalFrance = formatNumber($appellation['total_france']['value']);
		$totalExport = formatNumber($appellation['total_export']['value']);
		$totalNegoce = formatNumber($appellation['total_negoce']['value']);
		$totalTotal = formatNumber($appellation['total_total']['value']);
		foreach ($appellation['agg_line']['buckets'] as $couleur) {
			$couleurLibelle = getCouleurLibelle($couleur['key']);
			$france =formatNumber($couleur['france']['agg_column']['value']);
			$export =formatNumber($couleur['export']['agg_column']['value']);
			$negoce =formatNumber($couleur['negoce']['value']);
			$total =formatNumber($couleur['total']['value']);
			if (!$france && !$export && !$negoce) {
				continue;
			}
			$csv .= $categorieLibelle.';'.$appellationLibelle.';'.$couleurLibelle.';'.$france.';'.$export.';'.$negoce.';'.$total."\n";
		}
		$csv .= $categorieLibelle.';'.$appellationLibelle.';TOTAL;'.$totalFrance.';'.$totalExport.';'.$totalNegoce.';'.$totalTotal."\n";
	}
	$csv .= $categorieLibelle.';TOTAL;TOTAL;'.formatNumber($categorie['sous_totaux_france']['value']).';'.formatNumber($categorie['sous_totaux_export']['value']).';'.formatNumber($categorie['sous_totaux_negoce']['value']).';'.formatNumber($categorie['sous_totaux_total']['value'])."\n";
}
$csv .= 'TOTAL;TOTAL;TOTAL;'.formatNumber($result['totaux_france']['value']).';'.formatNumber($result['totaux_export']['value']).';'.formatNumber($result['totaux_negoce']['value']).';'.formatNumber($result['totaux_total']['value'])."\n";
echo $csv;