<?php
use_helper('IvbdStatistique');

	$options = $options->getRawValue();
	$fromDate = (isset($options['fromDate']))? $options['fromDate'] : date('Y-m-d');

	$appellationByCouleurCsv = array();
	$appellationByCouleurCsv['ztotal']['ztotal'] = array("TOTAL",0.0,0.0,0.0,0.0,0.0,0.0,0.0,0.0,0.0,0.0);
	foreach ($result['agg_page']['buckets'] as $produitLine) {
		$produitKey = $produitLine['key'];
		$produit = ConfigurationClient::getCurrent()->get($produitKey);
		$couleur = $produit->getLibelle();
		$produitLibelle = $produit->getLibelleFormat();

		$cvoTaux = $produit->getDroitCVO($fromDate)->taux;

		$vrac_contrat = $produitLine['total_vrac']['total_vrac_cvo']['agg_column']['value'];
		$vrac_prix = $vrac_contrat*$cvoTaux;

		$facturant_hors_contrat = $produitLine['total_facturation_hors_contrat']['value'];
		$facturant_hors_contrat_prix = $facturant_hors_contrat*$cvoTaux;

		$facturant = $produitLine['total_facturation']['value'];
		$facturant_prix = $facturant*$cvoTaux;

		if(!array_key_exists($couleur,$appellationByCouleurCsv)){
			$appellationByCouleurCsv[$couleur] = array();
			$appellationByCouleurCsv[$couleur]['ztotal'] = array("TOTAL ".$couleur, 0.0,null,0.0,0.0,null,0.0,0.0,null,0.0);
		}

		$appellationByCouleurCsv[$couleur][$produitLibelle][] = $produitLibelle;
		$appellationByCouleurCsv[$couleur][$produitLibelle][] = $vrac_contrat;
		$appellationByCouleurCsv[$couleur][$produitLibelle][] = $cvoTaux;
		$appellationByCouleurCsv[$couleur][$produitLibelle][] = $vrac_prix;

		$appellationByCouleurCsv[$couleur][$produitLibelle][] = $facturant_hors_contrat;
		$appellationByCouleurCsv[$couleur][$produitLibelle][] = $cvoTaux;
		$appellationByCouleurCsv[$couleur][$produitLibelle][] = $facturant_hors_contrat_prix;

		$appellationByCouleurCsv[$couleur][$produitLibelle][] = $facturant;
		$appellationByCouleurCsv[$couleur][$produitLibelle][] = $cvoTaux;
		$appellationByCouleurCsv[$couleur][$produitLibelle][] = $facturant_prix;

		// remplissage totaux pour couleurs
		$appellationByCouleurCsv[$couleur]['ztotal'][0] = "TOTAL ".$couleur;
		$appellationByCouleurCsv[$couleur]['ztotal'][1] += $vrac_contrat;
		$appellationByCouleurCsv[$couleur]['ztotal'][2] = null;
		$appellationByCouleurCsv[$couleur]['ztotal'][3] += $vrac_prix;
		$appellationByCouleurCsv[$couleur]['ztotal'][4] += $facturant_hors_contrat;
		$appellationByCouleurCsv[$couleur]['ztotal'][5] = null;
		$appellationByCouleurCsv[$couleur]['ztotal'][6] += $facturant_hors_contrat_prix;
		$appellationByCouleurCsv[$couleur]['ztotal'][7] += $facturant;
		$appellationByCouleurCsv[$couleur]['ztotal'][8] = null;
		$appellationByCouleurCsv[$couleur]['ztotal'][9] += $facturant_prix;

		// remplissage totaux
		$appellationByCouleurCsv['ztotal']['ztotal'][0] = "TOTAL";
		$appellationByCouleurCsv['ztotal']['ztotal'][1] += $vrac_contrat;
		$appellationByCouleurCsv['ztotal']['ztotal'][2] = null;
		$appellationByCouleurCsv['ztotal']['ztotal'][3] += $vrac_prix;
		$appellationByCouleurCsv['ztotal']['ztotal'][4] += $facturant_hors_contrat;
		$appellationByCouleurCsv['ztotal']['ztotal'][5] = null;
		$appellationByCouleurCsv['ztotal']['ztotal'][6] += $facturant_hors_contrat_prix;
		$appellationByCouleurCsv['ztotal']['ztotal'][7] += $facturant;
		$appellationByCouleurCsv['ztotal']['ztotal'][8] = null;
		$appellationByCouleurCsv['ztotal']['ztotal'][9] += $facturant_prix;
	}

	ksort($appellationByCouleurCsv);

	$csv = "Appellation;Sorties sous contrats (vrac) hl;CVO €/hl;Facturation attendue €;Sorties hors contrats (bouteilles) hl;CVO €/hl;Facturation attendue €;total sorties réelles à facturer hl;CVO €/hl;Facturation attendue €\n";

	foreach ($appellationByCouleurCsv as $couleur => $couleurLines) {
		sort($couleurLines);
			foreach ($couleurLines as $produitKey => $line) {
				if(is_array($line)){
					foreach ($line as $key => $value) {
							if(!$key){
								$csv .= $value;
							}else{
								$csv .= ';'.formatNumber($value,2);
							}
						}
						if(($couleur != "ztotal") || ($produitKey != "ztotal")){
							$csv .= "\n";
						}
					}
				}
			}
echo $csv;
