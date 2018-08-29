<?php 
use_helper('Statistique');
use_helper('BivcStatistique');

$csv = "Appellation;Catégorie;Stock initial;Stock actuel;TOTAL mvt\n";
foreach ($result['agg_page']['buckets'] as $appellation) {
	$appellationKey = str_replace('\\', '', $appellation['key']);
	$appellationLibelle = preg_replace('/AOC */', '', ConfigurationClient::getCurrent()->get($appellationKey)->getLibelleFormat());
	$globalLibelle = preg_replace('/AOC */', '', ConfigurationClient::getCurrent()->get($appellationKey)->getAppellation()->getLibelleFormat());
	$totalStockInitial = (formatNumber($appellation['total_stock_initial']['value']) != 0)? formatNumber($appellation['total_stock_initial']['value']) : null;
	$totalStockFinal = (formatNumber($appellation['total_stock_final']['value']) != 0)? formatNumber($appellation['total_stock_final']['value']) : null;
	$totalTotal = (formatNumber($appellation['total_total']['value']) != 0)? formatNumber($appellation['total_total']['value']) : null;
	foreach ($appellation['agg_line']['buckets'] as $categorie) {
		$categorieLibelle = getFamilleLibelle($categorie['key']);
		$stockInitial = (formatNumber($categorie['stock_initial']['agg_column']['value']) != 0)? formatNumber($categorie['stock_initial']['agg_column']['value']) : null;
		$stockFinal = (formatNumber($categorie['stock_final']['agg_column']['value']) != 0)? formatNumber($categorie['stock_final']['agg_column']['value']) : null;
		$totalMvt = (formatNumber($categorie['total']['value']) != 0)? formatNumber($categorie['total']['value']) : null;
		$csv .= $globalLibelle.';'.$appellationLibelle.';'.$categorieLibelle.';'.$stockInitial.';'.$stockFinal.';'.$totalMvt."\n";
	}
	$csv .= $globalLibelle.';'.$appellationLibelle.';TOTAL;'.$totalStockInitial.';'.$totalStockFinal.';'.$totalTotal."\n";
}
#$csv .= 'TOTAL;TOTAL;TOTAL;'.formatNumber($result['totaux_stock_initial']['value']).';'.formatNumber($result['totaux_stock_final']['value']).';'.formatNumber($result['totaux_total']['value'])."\n";
echo $csv;
