<?php

function getAppellationLibelle($key)
{
	$items = ConfigurationClient::getCurrent()->declaration->getKeys('appellation');
	if (isset($items[$key])) {
		$item = $items[$key];
		return ($item->getLibelle())? $item->getLibelle() : $item->getLibelleFormat();
	}
	return ' ';
}

function getProduitLibelle($key)
{
	$item = ConfigurationClient::getCurrent()->get($key);
	return $item->getLibelleFormat();
}

function getFamilleLibelle($key)
{
	$familles = EtablissementFamilles::getFamilles();
	return (isset($familles[$key]))? $familles[$key] : null;
}

function getCouleurLibelle($key)
{
	$couleurs = array('blanc' => 'Blanc','blanc_sec' => 'Blanc Sec','blanc_moelleux' => 'Blanc Moelleux','blanc_doux' => 'Blanc Doux', 'rose' => 'Rosé', 'rouge' => 'Rouge');
	return (isset($couleurs[$key]))? $couleurs[$key] : null;
}

function getConditionnementLibelle($key)
{
	$conditionnements = VracClient::$types_transaction;
	return ($conditionnements[$key])? $conditionnements[$key] : null;
}

function nullify($numberOrNullValue){
	return ($numberOrNullValue)? $numberOrNullValue : null;
}

function formatNumber($number, $round = 0) {
	return ($number && $number != 0)? number_format($number, $round, ',', '') : null;
}

function formatNumberPourcent($number, $round = 0){
	$n = formatNumber($number, $round);
	if(floatval($n) > 0 ){
		return "+".$n;
	}
	return $n;
}

function getEvol($last, $current) {
	if(!$last && !$current){
		return "";
	}
	if(!$current){
		return "- infini";
	}
	if(!$last){
		return "+ infini";
	}

	$last = str_replace(',', '.', $last);
	$current = str_replace(',', '.', $current);
	return formatNumberPourcent((($current - $last) / $last) * 100, 2);
}
