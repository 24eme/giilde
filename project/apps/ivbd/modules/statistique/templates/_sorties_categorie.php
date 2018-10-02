<?php 
use_helper('Statistique');
use_helper('IvbdStatistique');

if ($lastPeriode) {
	$csv = "Appellation;Couleur;France N-1;France;France %;Export N-1;Export;Export %;Négoce N-1;Négoce;Négoce %;TOTAL N-1;TOTAL;TOTAL %\n";
	$result = $result->getRawValue();
	$lastPeriode = $lastPeriode->getRawValue();
	$resultKeys = array_keys($result);
	$resultPartKeys = array();
	$resultFirstKeys = array();
	foreach ($result as $key => $values) {
		$key = sfOutputEscaper::unescape($key);
		if (strpos($key, '/') === false) {
			$key = '/'.$key;
		}
		$tabKey = explode('/', $key);
		if (!in_array($tabKey[0], $resultFirstKeys)) {
			$resultFirstKeys[] = $tabKey[0];
		}
		if (!in_array($tabKey[0].'/'.$tabKey[1], $resultPartKeys)) {
			$resultPartKeys[] = $tabKey[0].'/'.$tabKey[1];
		}
		if ($tabKey[0] == 'TOTAL') {
			foreach ($lastPeriode as $subkey => $subvalues) {
				$subtabKey = explode('/', $subkey);
				if (!in_array($subtabKey[0], $resultFirstKeys)) {
					$csv .= $subtabKey[0].';'.$subtabKey[1].';'.$subvalues[0].';'.null.';'.getEvol($subvalues[0], 0).';'.$subvalues[1].';'.null.';'.getEvol($subvalues[1], 0).';'.$subvalues[2].';'.null.';'.getEvol($subvalues[2], 0).';'.$subvalues[3].';'.null.';'.getEvol($subvalues[3], 0)."\n";
				}
			}
		}
		if ($tabKey[1] == 'TOTAL') {
			foreach ($lastPeriode as $subkey => $subvalues) {
				$subtabKey = explode('/', $subkey);
				if ($tabKey[0] == $subtabKey[0] && !in_array($subkey, $resultKeys)) {
					$csv .= $subtabKey[0].';'.$subtabKey[1].';'.$subvalues[0].';'.null.';'.getEvol($subvalues[0], 0).';'.$subvalues[1].';'.null.';'.getEvol($subvalues[1], 0).';'.$subvalues[2].';'.null.';'.getEvol($subvalues[2], 0).';'.$subvalues[3].';'.null.';'.getEvol($subvalues[3], 0)."\n";
				}
			}
		}
		if (isset($lastPeriode[$key])) {
			$csv .= $tabKey[0].';'.$tabKey[1].';'.$lastPeriode[$key][0].';'.$values[0].';'.getEvol($lastPeriode[$key][0], $values[0]).';'.$lastPeriode[$key][1].';'.$values[1].';'.getEvol($lastPeriode[$key][1], $values[1]).';'.$lastPeriode[$key][2].';'.$values[2].';'.getEvol($lastPeriode[$key][2], $values[2]).';'.$lastPeriode[$key][3].';'.$values[3].';'.getEvol($lastPeriode[$key][3], $values[3])."\n";
		} else {
			$csv .= $tabKey[0].';'.$tabKey[1].';'.null.';'.$values[0].';'.getEvol(0, $values[0]).';'.null.';'.$values[1].';'.getEvol(0, $values[1]).';'.null.';'.$values[2].';'.getEvol(0, $values[2]).';'.null.';'.$values[3].';'.getEvol(0, $values[3])."\n";
		}
	}
} else {
	$csv = "Appellation;Couleur;France;Export;Négoce;TOTAL\n";
	foreach ($result['agg_page']['buckets'] as $appellation) {
		$appellationLibelle = getAppellationLibelle(strtoupper($appellation['key']));
		$totalFrance = (formatNumber($appellation['total_france']['value']) != 0)? formatNumber($appellation['total_france']['value']) : null;
		$totalExport = (formatNumber($appellation['total_export']['value']) != 0)? formatNumber($appellation['total_export']['value']) : null;
		$totalNegoce = (formatNumber($appellation['total_negoce']['value']) != 0)? formatNumber($appellation['total_negoce']['value']) : null;
		$totalTotal = (formatNumber($appellation['total_total']['value']) != 0)? formatNumber($appellation['total_total']['value']) : null;
		foreach ($appellation['agg_line']['buckets'] as $couleur) {
			$couleurLibelle = getCouleurLibelle($couleur['key']);
			$france = (($couleur['france']['agg_column']['value']) != 0)? formatNumber($couleur['france']['agg_column']['value']) : null;
			$export = (($couleur['export']['agg_column']['value']) != 0)? formatNumber($couleur['export']['agg_column']['value']) : null;
			$negoce = (($couleur['negoce']['agg_column']['value']) != 0)? formatNumber($couleur['negoce']['agg_column']['value']) : null;
			$total = (($couleur['total']['value']) != 0)? formatNumber($couleur['total']['value']) : null;
			if ($france === null && $export === null && $negoce === null) {
				continue;
			}
			$csv .= $appellationLibelle.';'.$couleurLibelle.';'.$france.';'.$export.';'.$negoce.';'.$total."\n";
		}
		$csv .= $appellationLibelle.';TOTAL;'.$totalFrance.';'.$totalExport.';'.$totalNegoce.';'.$totalTotal."\n";
	}
	$csv .= 'TOTAL;TOTAL;'.formatNumber($result['totaux_france']['value']).';'.formatNumber($result['totaux_export']['value']).';'.formatNumber($result['totaux_negoce']['value']).';'.formatNumber($result['totaux_total']['value'])."\n";
}
echo $csv;