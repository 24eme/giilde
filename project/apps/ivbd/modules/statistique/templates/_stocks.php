<?php
use_helper('IvbdStatistique');
if ($lastPeriode) {
	$csv = "Produit;Stock initial;Stock initial N-1;Ecart Hl;Ecart %;Somme mouvements;Somme mouvements N-1;Ecart Hl;Ecart %;Stock final;Stock final N-1;Ecart Hl;Ecart %\n";
	$result = $result->getRawValue();
	$lastPeriode = $lastPeriode->getRawValue();
	foreach ($result as $key => $values) {
		$key = sfOutputEscaper::unescape($key);
		$csv .= $key.';';
		if (isset($lastPeriode[$key])) {
				foreach ($lastPeriode[$key] as $k => $v) {
					$csv .= floatval($values[$k]).";".floatval($v).";".(floatval($values[$k]) - floatval($v)).";".getEvol(floatval($v),floatval($values[$k]));
					$csv .=  ($k == count($lastPeriode[$key]) - 1)? "\n" : ';';
				}
				unset($lastPeriode[$key]);
			} else {
				foreach ($values as $k => $v) {
			 			$csv .= $values[$k].";;;";
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
	$tStockInitial = $totalStockRRInitial = $totalStockBInitial = 0;
	$tStockFinal = $totalStockRRFinal = $totalStockBFinal = 0;
	$tTotal = $totalRR = $totalB = 0;
	$results = mergeBlancBlancMoelleux($result['agg_page']['buckets']);
	foreach ($results as $produitsCouleur) {
		$couleurLibelle = getCouleurLibelle($produitsCouleur['key']);

				$totalStockInitial  = 0;
				$totalStockFinal = 0;
				$totalTotal = 0;

				foreach ($produitsCouleur['agg_line']['buckets'] as $produit) {
					$produitHash = str_replace("/declaration/certifications/AOC/genres/TRANQ/appellations/CDB/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc_moelleux",
					"/declaration/certifications/AOC/genres/TRANQ/appellations/CDB/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc",$produit['key']."/couleurs/".$produitsCouleur['key']);

					$produitCouleurLibelle = preg_replace('/AOC /', '',ConfigurationClient::getCurrent()->get($produitHash)->getLibelleFormat());
					
					$stockInitial = (formatNumber($produit['stock_initial']['agg_column']['value']) != 0)? formatNumber($produit['stock_initial']['agg_column']['value']) : null;
					$stockFinal = (formatNumber($produit['stock_final']['agg_column']['value']) != 0)? formatNumber($produit['stock_final']['agg_column']['value']) : null;
					$totalMvt = (formatNumber($produit['total']['value']) != 0)? formatNumber($produit['total']['value']) : null;
				  $csv .= $produitCouleurLibelle.';'.$stockInitial.';'.$totalMvt.";".$stockFinal."\n";
					$totalStockInitial += ($stockInitial)? $stockInitial : 0;
					$totalTotal += ($totalMvt)? $totalMvt : 0;
					$totalStockFinal += ($stockFinal)? $stockFinal : 0;
				}
				$tStockInitial += $totalStockInitial;
				$tStockFinal += $totalStockFinal;
				$tTotal += $totalTotal;

				if(in_array($produitsCouleur['key'],array("rouge","rose"))){
					$totalStockRRInitial += $totalStockInitial;
					$totalRR += $totalTotal;
					$totalStockRRFinal += $totalStockFinal;
				}else{
					$totalStockBInitial += $totalStockInitial;
					$totalB += $totalTotal;
					$totalStockBFinal += $totalStockFinal;
				}

				$totalStockInitial = nullify($totalStockInitial);
				$totalStockFinal = nullify($totalStockFinal);
				$totalTotal = nullify($totalTotal);
			  $csv .= "TOTAL ".$couleurLibelle.';'.$totalStockInitial.';'.$totalTotal.";".$totalStockFinal."\n";
		}
		$csv .= "TOTAL ROUGES ET ROSES;".$totalStockRRInitial.';'.$totalRR.";".$totalStockRRFinal."\n";
		$csv .= "TOTAL BLANC;".$totalStockBInitial.';'.$totalB.";".$totalStockBFinal."\n";
		$csv .= 'TOTAL TOUTES APPELLATIONS;'.$tStockInitial.';'.$tTotal.";".$tStockFinal."\n";
}
echo $csv;
