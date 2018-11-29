<?php
use_helper('Statistique');
use_helper('IvbdStatistique');
if ($lastPeriode) {
	$csv = "Produit;Catégorie;Stock initial;Stock initial N-1;Somme mouvements;Somme mouvements N-1;Stock final;Stock final N-1\n";
	$result = $result->getRawValue();
	$lastPeriode = $lastPeriode->getRawValue();
	foreach ($result as $key => $values) {
		$key = sfOutputEscaper::unescape($key);
		$tabKey = explode('/', $key);
		$csv .= $tabKey[0].';'.$tabKey[1].";";
			if (isset($lastPeriode[$key])) {
					if($tabKey[1] == 'TOTAL'){
						$lastPeriode[$key] = array_values($lastPeriode[$key]);
					}
					foreach ($lastPeriode[$key] as $lastPeriodeCaseKey => $lastPeriodeCaseValue) {
						$csv .= $values[$lastPeriodeCaseKey].";".$lastPeriode[$key][$lastPeriodeCaseKey];
						$csv .=  ($lastPeriodeCaseKey == count($lastPeriode[$key]) - 1)? "\n" : ';';
					}
					unset($lastPeriode[$key]);
			} else {
					foreach ($values as $k => $v) {
						$csv .= $values[$k];
						$csv .=  ($k == count($values) - 1)? "\n" : ';;';
					}
			}
	}
	if(count($lastPeriode)){
		foreach ($lastPeriode as $lastPeriodeKey => $lastPeriodeCaseValue) {
			$key = sfOutputEscaper::unescape($lastPeriodeKey);
			$tabKey = explode('/', $lastPeriodeKey);
			$csv .= $tabKey[0].';'.$tabKey[1].";";			
			foreach ($lastPeriode[$key] as $lastPeriodeKeyKey => $lastPeriodeKeyValue) {
				$csv .= ";".$lastPeriodeKeyValue;
				$csv .=  ($lastPeriodeKeyKey == count($lastPeriode[$key]) - 1)? "\n" : ';';
			}
		}
	}
}else{
	$csv =  "Produit;Catégorie;Stock initial;Somme mouvements;Stock final\n";
	foreach ($result['agg_page']['buckets'] as $produitCepage) {
			if(preg_match("/details(Acquitte)?\/DEFAUT$/",$produitCepage['key'])){
				$produitCepageKey = str_replace(array('\\', '/details/DEFAUT','/detailsACQUITTE/DEFAUT'), array('','',''), $produitCepage['key']);
				$produitCepageLibelle = preg_replace('/AOC /', '', ConfigurationClient::getCurrent()->get($produitCepageKey)->getLibelleFormat());
				$totalStockInitial = (formatNumber($produitCepage['total_stock_initial']['value']) != 0)? formatNumber($produitCepage['total_stock_initial']['value']) : null;
				$totalStockFinal = (formatNumber($produitCepage['total_stock_final']['value']) != 0)? formatNumber($produitCepage['total_stock_final']['value']) : null;
				$totalTotal = (formatNumber($produitCepage['total_total']['value']) != 0)? formatNumber($produitCepage['total_total']['value']) : null;
				foreach ($produitCepage['agg_line']['buckets'] as $categorie) {
					$categorieLibelle = getFamilleLibelle($categorie['key']);
					$stockInitial = (formatNumber($categorie['stock_initial']['agg_column']['value']) != 0)? formatNumber($categorie['stock_initial']['agg_column']['value']) : null;
					$stockFinal = (formatNumber($categorie['stock_final']['agg_column']['value']) != 0)? formatNumber($categorie['stock_final']['agg_column']['value']) : null;
					$totalMvt = (formatNumber($categorie['total']['value']) != 0)? formatNumber($categorie['total']['value']) : null;
					$csv .= $produitCepageLibelle.';'.$categorieLibelle.';'.$stockInitial.';'.$totalMvt.";".$stockFinal."\n";
				}
			}
			$csv .= $produitCepageLibelle.';TOTAL;'.$totalStockInitial.';'.$totalTotal.";".$totalStockFinal."\n";
		}
}
echo $csv;
