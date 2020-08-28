<?php
use_helper('IvbdStatistique');



	$csv = "Produit;01. Vrac sous contrat;Total sorties hors contrat;06. Retour logement ext;08. Retour de vin CRD;09. Retour de vin hors CRD;Facturation cvo hors contrat;Sorties réelles pour facturation (hl);Mouvements exonérées de cvo (hl);Total mouvements (hl)\n";
	foreach ($result['agg_page']['buckets'] as $produitLine) {
		$produit = ConfigurationClient::getCurrent()->get($produitLine['key'])->getLibelleFormat();

		$vrac_contrat = $produitLine['total_vrac']['total_vrac_cvo']['agg_column']['value'];

		$total_sortie_hors_contrat = $produitLine['total_sorties_hors_contrat']['value'];

		// 06. Retour logement ext. = transfertcomptamatierecession
		$retour_logement_ext = $produitLine['total_entrees']['total_entrees_transfertcomptamatierecession']['agg_column']['value'];

		// 08. Retour de vin CRD acq. retourmarchandiseacquitte + susp
		$retour_vin_crd = $produitLine['total_entrees']['total_entrees_retourmarchandise']['agg_column']['value'];

		// 09. Retour de vin hors CRD  = retourmarchandisenontaxees
		$retour_vin_hors_crd = $produitLine['total_entrees']['total_entrees_retourmarchandisenontaxees']['agg_column']['value'];

		$total_facturation_hors_contrat = $produitLine['total_facturation_hors_contrat']['value'];

		// total cvo
		$sorties_reelles_pour_facturation = $total_facturation_hors_contrat+$vrac_contrat;

		$mouvements_exoneres_cvo = $produitLine['total_mouvements_hors_cvo']['value'];

		$total_mouvements =  $produitLine['total_sorties']['agg_column']['value'] - $produitLine['total_entrees']['agg_column']['value'];

		$csv .= $produit.';'.formatNumber($vrac_contrat,2).';'.
												formatNumber($total_sortie_hors_contrat,2).";".
												formatNumber($retour_logement_ext,2).";".
												formatNumber($retour_vin_crd,2).";".
												formatNumber($retour_vin_hors_crd,2).';'.
												formatNumber($total_facturation_hors_contrat,2).';'.
												formatNumber($sorties_reelles_pour_facturation,2).';'.
												formatNumber($mouvements_exoneres_cvo,2).';'.
												formatNumber($total_mouvements,2)."\n";

	}
		 $totaux_vrac_cvo = formatNumber($result["totaux_vrac_cvo"]["value"],2);
		 $totaux_sortie_hors_contrat = formatNumber($result["totaux_sorties_hors_contrat"]["value"],2);
	 	 $totaux_retour_logement_ext = formatNumber($result['totaux_entrees_transfertcomptamatierecession']['value'],2);
	 	 $totaux_retour_vin_crd = formatNumber($result['totaux_entrees_retourmarchandise']['value'],2);
	 	 $totaux_retour_vin_hors_crd = formatNumber($result['totaux_entrees_retourmarchandisenontaxees']['value'],2);
		 $totaux_facturation_hors_contrat = formatNumber($result['totaux_facturation_hors_contrat']['value'],2);
		 $totaux_sorties_reelles_pour_facturation = formatNumber($result["totaux_sorties_hors_contrat"]["value"]+$result["totaux_vrac_cvo"]["value"],2);
		 $totaux_mouvements_exoneres_cvo = formatNumber($result['totaux_mouvements_hors_cvo']['value'],2);
		 $totaux_mouvements = formatNumber($result['totaux_mouvements']['value'],2);

	 $csv .= 'TOTAL;'.$totaux_vrac_cvo.';'.
	 $totaux_sortie_hors_contrat.';'.
	 $totaux_retour_logement_ext.';'.
	 $totaux_retour_vin_crd.';'.
	 $totaux_retour_vin_hors_crd.';'.
	 $totaux_facturation_hors_contrat.';'.
	 $totaux_sorties_reelles_pour_facturation.';'.
	 $totaux_mouvements_exoneres_cvo.';'.
	 $totaux_mouvements;
echo $csv;
