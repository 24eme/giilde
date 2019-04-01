<?php
use_helper('IvbdStatistique');
if ($lastPeriode) {
	$csv = "Produit;Stock initial;Stock initial N-1;Somme mouvements;Somme mouvements N-1;Stock final;Stock final N-1\n";
	$result = $result->getRawValue();
	$lastPeriode = $lastPeriode->getRawValue();
	foreach ($result as $key => $values) {
		$key = sfOutputEscaper::unescape($key);
		$csv .= $key.';';
			if (isset($lastPeriode[$key])) {
					foreach ($lastPeriode[$key] as $lastPeriodeCaseKey => $lastPeriodeCaseValue) {
						$csv .= $values[$lastPeriodeCaseKey].";".$lastPeriode[$key][$lastPeriodeCaseKey];
						$csv .=  ($lastPeriodeCaseKey == count($lastPeriode[$key]) - 1)? "\n" : ';';
					}
					unset($lastPeriode[$key]);
			} else {
					foreach ($values as $k => $v) {
						$csv .= $values[$k].";";
						$csv .=  ($k == count($values) - 1)? "\n" : ';';
					}
			}
	}
	if(count($lastPeriode)){
		foreach ($lastPeriode as $lastPeriodeKey => $lastPeriodeCaseValue) {
			$key = sfOutputEscaper::unescape($lastPeriodeKey);
			$csv .= $key.';';
			foreach ($lastPeriode[$key] as $lastPeriodeKeyKey => $lastPeriodeKeyValue) {
				$csv .= ";".$lastPeriodeKeyValue;
				$csv .=  ($lastPeriodeKeyKey == count($lastPeriode[$key]) - 1)? "\n" : ';';
			}
		}
	}
}else{
	$csv =  "Produit;Stock initial;Somme mouvements;Stock final\n";
	$tStockInitial = 0;
	$tStockFinal = 0;
	$tTotal = 0;
	foreach ($result['agg_page']['buckets'] as $produitLieu) {
		$produitLieuKey = $produitLieu['key'];
		$produitLieuLibelle = preg_replace('/AOC /', '', ConfigurationClient::getCurrent()->get($produitLieuKey)->getLibelleFormat());

				$totalStockInitial = 0;
				$totalStockFinal = 0;
				$totalTotal = 0;

				foreach ($produitLieu['agg_line']['buckets'] as $couleur) {
					$couleurLibelle = getCouleurLibelle($couleur['key']);
					$stockInitial = (formatNumber($couleur['stock_initial']['agg_column']['value']) != 0)? formatNumber($couleur['stock_initial']['agg_column']['value']) : null;
					$stockFinal = (formatNumber($couleur['stock_final']['agg_column']['value']) != 0)? formatNumber($couleur['stock_final']['agg_column']['value']) : null;
					$totalMvt = (formatNumber($couleur['total']['value']) != 0)? formatNumber($couleur['total']['value']) : null;
				  $csv .= $produitLieuLibelle.' '.$couleurLibelle.';'.$stockInitial.';'.$totalMvt.";".$stockFinal."\n";
					$totalStockInitial += ($stockInitial)? $stockInitial : 0;
					$totalTotal += ($totalMvt)? $totalMvt : 0;
					$totalStockFinal += ($stockFinal)? $stockFinal : 0;
				}
				$tStockInitial += $totalStockInitial;
				$tStockFinal += $totalStockFinal;
				$tTotal += $totalTotal;
				$totalStockInitial = nullify($totalStockInitial);
				$totalStockFinal = nullify($totalStockFinal);
				$totalTotal = nullify($totalTotal);
			  $csv .= "TOTAL ".$produitLieuLibelle.';'.$totalStockInitial.';'.$totalTotal.";".$totalStockFinal."\n";
		}

		$csv .= 'TOTAL toutes appellations;'.$tStockInitial.';'.$tTotal.";".$tStockFinal."\n";
}
echo $csv;
