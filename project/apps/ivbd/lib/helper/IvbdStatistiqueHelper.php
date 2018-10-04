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
