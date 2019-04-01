<?php
use_helper('IvbdStatistique');

if ($lastPeriode) {
	$csv = "Pays;Blanc;Blanc N-1;Blanc %;Blanc Sec;Blanc Sec N-1;Blanc Sec %;Blanc Moelleux;Blanc Moelleux N-1;Blanc Moelleux %;Blanc Doux;Blanc Doux N-1;Blanc Doux %;Rosé;Rosé N-1;Rosé %;Rouge;Rouge N-1;Rouge %;TOTAL;TOTAL N-1;TOTAL %\n";
	$result = $result->getRawValue();
	$lastPeriode = $lastPeriode->getRawValue();
	$resultKeys = array_keys($result);
	$resultPartKeys = array();
	foreach ($result as $key => $values) {
		$key = sfOutputEscaper::unescape($key);
		$tabKey = explode('/', $key);
		if (!in_array($tabKey[0], $resultPartKeys)) {
			$resultPartKeys[] = $tabKey[0];
		}
		if ($tabKey[0] == 'TOTAL') {
			foreach ($lastPeriode as $subkey => $subvalues) {
				$subtabKey = explode('/', $subkey);
				if (!in_array($subtabKey[0], $resultPartKeys)) {
					$csv .= $subtabKey[0].';';
					foreach ($subvalues as $subvalueskey => $subvaluesVal) {
						$csv .= null.';'.$subvalues[$subvalueskey].";".getEvol($subvalues[$subvalueskey], 0);
						$csv .= ($subvalueskey == count($subvalues) - 1)? "\n" : ';';
					}
				}
			}
		}
		if (isset($lastPeriode[$key])) {
			if ($lastPeriode[$key][6] || $values[6]) {
				$csv .= $tabKey[0].';';
				foreach ($lastPeriode[$key] as $lastPeriodeCaseKey => $lastPeriodeCaseValue) {
					$csv .= $values[$lastPeriodeCaseKey].';'.$lastPeriode[$key][$lastPeriodeCaseKey].";".getEvol($lastPeriode[$key][$lastPeriodeCaseKey], $values[$lastPeriodeCaseKey]);
					$csv .=  ($lastPeriodeCaseKey == count($lastPeriode[$key]) - 1)? "\n" : ';'; //.';'.$lastPeriode[$key][1].';'.$values[1].';'.getEvol($lastPeriode[$key][1], $values[1]).';'.$lastPeriode[$key][2].';'.$values[2].';'.getEvol($lastPeriode[$key][2], $values[2]).';'.$lastPeriode[$key][3].';'.$values[3].';'.getEvol($lastPeriode[$key][3], $values[3])."\n";
				}
			}
		} else {
			if ($values[6]) {
				$csv .= $tabKey[0].';';
				foreach ($values as $k => $v) {
					$csv .= $values[$k].';'.null.';'.getEvol(0, $values[$k]);
					$csv .=  ($k == count($values) - 1)? "\n" : ";";
				}
			}
		}
	}
} else {
	$csv = "Pays;Blanc;Blanc Sec;Blanc Moelleux;Blanc Doux;Rosé;Rouge;TOTAL\n";
	$totalBlanc = formatNumber($result['totaux_blanc']['value'],2);
	$totalBlancSec = formatNumber($result['totaux_blanc_sec']['value'],2);
	$totalBlancMoelleux = formatNumber($result['totaux_blanc_moelleux']['value'],2);
	$totalBlancDoux = formatNumber($result['totaux_blanc_doux']['value'],2);
	$totalRose = formatNumber($result['totaux_rose']['value'],2);
	$totalRouge = formatNumber($result['totaux_rouge']['value'],2);
	$totalTotal = formatNumber($result['totaux_total']['value'],2);
	foreach ($result['agg_line']['buckets'] as $pays) {
		$paysLibelle = $pays['key'];
		$blanc = formatNumber($pays['blanc']['agg_column']['value'],2);
		$blancSec = formatNumber($pays['blanc_sec']['agg_column']['value'],2);
		$blancMoelleux = formatNumber($pays['blanc_moelleux']['agg_column']['value'],2);
		$blancDoux = formatNumber($pays['blanc_doux']['agg_column']['value'],2);
		$rose = formatNumber($pays['rose']['agg_column']['value'],2);
		$rouge = formatNumber($pays['rouge']['agg_column']['value'],2);
		$total = formatNumber($pays['total']['agg_column']['value'],2);
		$csv .= $paysLibelle.';'.$blanc.';'.$blancSec.";".$blancMoelleux.";".$blancDoux.";".$rose.';'.$rouge.';'.$total."\n";
	}
	$csv .= 'TOTAL;'.$totalBlanc.';'.$totalBlancSec.';'.$totalBlancMoelleux.';'.$totalBlancDoux.';'.$totalRose.';'.$totalRouge.';'.$totalTotal."\n";
}
echo $csv;
