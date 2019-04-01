<?php 
use_helper('BivcStatistique');

$csv = 'Conditionnement;Appellation;Couleur;Qté sans prix;Qté avec prix;CA;Moyenne'."\n";
foreach ($result['agg_page']['buckets'] as $conditionnement) {
	$conditionnementLibelle = getConditionnementLibelle(strtoupper($conditionnement['key']));
	foreach ($conditionnement['agg_page']['buckets'] as $appellation) {
		$appellationLibelle = getAppellationLibelle(strtoupper($appellation['key']));
		$totalSansPrix = formatNumber($appellation['total_sans_prix']['value']);
		$totalAvecPrix = formatNumber($appellation['total_avec_prix']['value']);
		$totalCa = formatNumber($appellation['total_ca']['value']);
		$totalMoyenne = formatNumber($appellation['total_moyenne']['value'], 2);
		foreach ($appellation['agg_line']['buckets'] as $couleur) {
			$couleurLibelle = getCouleurLibelle($couleur['key']);
			$sansPrix =formatNumber($couleur['vol_sans_prix']['agg_column']['value']);
			$avecPrix =formatNumber($couleur['vol_avec_prix']['agg_column']['value']);
			$ca = formatNumber($couleur['ca']['agg_column']['value']);
			$moyenne = formatNumber($couleur['moyenne']['value'], 2);
			$csv .= $conditionnementLibelle.';'.$appellationLibelle.';'.$couleurLibelle.';'.$sansPrix.';'.$avecPrix.';'.$ca.';'.$moyenne."\n";
		}
		$csv .= $conditionnementLibelle.';'.$appellationLibelle.';TOTAL;'.$totalSansPrix.';'.$totalAvecPrix.';'.$totalCa.';'.$totalMoyenne."\n";
	}
}
echo $csv;
