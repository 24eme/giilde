<?php
function formatNumber($number, $round = 0) {
	return ($number && $number != 0)? number_format($number, $round, ',', '') : null;
}

function getEvol($last, $current) {
	$last = intval(str_replace(',', '.', $last));
	$current = intval(str_replace(',', '.', $current));
	return ($last > 0)? formatNumber((($current - $last) / $last) * 100, 2) : null;
}


function getAppellationLibelle($key)
{
	$items = ConfigurationClient::getCurrent()->declaration->getKeys('appellation');
	if (isset($items[$key])) {
		$item = $items[$key];
		return ($item->getLibelle())? $item->getLibelle() : ' ';
	}
	return ' ';
}

function getFamilleLibelle($key)
{
	$familles = EtablissementFamilles::getFamilles();
	return (isset($familles[$key]))? $familles[$key] : null;
}

function getCouleurLibelle($key)
{
	$couleurs = array('blanc' => 'Blanc', 'rose' => 'Rosé', 'rouge' => 'Rouge');
	return (isset($couleurs[$key]))? $couleurs[$key] : null;
}

function getConditionnementLibelle($key)
{
	$conditionnements = VracClient::$types_transaction;
	return ($conditionnements[$key])? $conditionnements[$key] : null;
}
