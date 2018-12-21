<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DAEExportCsvEdi
 *
 * @author mathurin
 */
class DAEExportCsv {

    public function __construct() {

    }

    private function getHeaderEdi() {

        return "#date de la commercialisation;identifiant declarvins du déclarant;numéro d'accises du déclarant;nom du déclarant;stat famille;stat sous famille;stat département;code ou nom de la certification du vin;nom ou code du genre du vin;nom ou code du appellation du vin;nom ou code du mention du vin;nom ou code du lieu du vin;nom ou code du couleur du vin;nom ou code du cépage du vin;Le complément du vin;Le libellé personnalisé du vin;label du produit;mention de domaine ou château revendiqué;millésime;primeur;n° accise de l'acheteur;nom acheteur;type acheteur;nom du pays de destination;type de conditionnement;libellé conditionnement;contenance conditionnement en litres;quantité de conditionnement;prix unitaire;stat qtt hl;stat prix hl\n";
    }
    
    public function exportOnlyDAEByEtablissementAndCampagne($identifiant, $campagne, $header = true) {
    	$csv = array();
    	$daes = DAEClient::getInstance()->findByIdentifiantCampagne($identifiant, $campagne, acCouchdbClient::HYDRATE_JSON)->getDatas();
    	$i = 0;
    	foreach($daes as $dae) {
    		$line = $this->exportDAE($dae);
    		$csv[$line.';'.$i] = $line;
    		$i++;
    	}
       krsort($csv);
    	return ($header)? $this->getHeaderEdi().implode("", $csv) : implode("", $csv);
    }

    public function exportByEtablissementAndCampagne($identifiant, $campagne) {
    	$csv = array();
    	$daes = DAEClient::getInstance()->findByIdentifiantCampagne($identifiant, $campagne, acCouchdbClient::HYDRATE_JSON)->getDatas();
    	$i = 0;
    	foreach($daes as $dae) {
    		$line = $this->exportDAE($dae);
    		$csv[$line.';'.$i] = $line;
    		$i++;
    	}
        $mouvements = DRMMouvementsConsultationView::getInstance()->getMouvementsByEtablissementAndCampagne($identifiant, $campagne);
        foreach($mouvements as $mouvement) {
            if(!$mouvement->vrac_numero) {
                continue;
            }
            $line = $this->exportMouvementDRMContrat($mouvement);
            $csv[$line.';'.$i] = $line;
        }
        krsort($csv);
        return $this->getHeaderEdi().implode("", $csv);
    }

    public function exportDAE($dae) {
        $cp = ($dae->declarant->code_postal)? preg_replace("/([0-9]{2})[0-9]{3}/","$1",$dae->declarant->code_postal) : "";
        $produit = ConfigurationClient::getConfiguration($dae->date)->getConfigurationProduit($dae->produit_key);
        $complement = "";

        return $dae->date.";".
        ConfigurationClient::getInstance()->anonymisation($dae->identifiant).";".
        ConfigurationClient::getInstance()->anonymisation($dae->declarant->no_accises).";".
        ConfigurationClient::getInstance()->anonymisation($dae->declarant->nom).";".
        $dae->declarant->famille.";".
        $dae->declarant->sous_famille.";".
        $cp.";".
        ($produit && $produit->getCertification()->getKey() != 'DEFAUT' ? $produit->getCertification()->getKey() : null).";".
        ($produit && $produit->getGenre()->getKey() != 'DEFAUT' ? $produit->getGenre()->getKey() : null).";".
        ($produit && $produit->getAppellation()->getKey() != 'DEFAUT' ? $produit->getAppellation()->getKey() : null).";".
        ($produit && $produit->getMention()->getKey() != 'DEFAUT' ? $produit->getMention()->getKey() : null).";".
        ($produit && $produit->getLieu()->getKey() != 'DEFAUT' ? $produit->getLieu()->getKey() : null).";".
        ($produit && $produit->getCouleur()->getKey() != 'DEFAUT' ? $produit->getCouleur()->getKey() : null).";".
        ($produit && $produit->getKey() != 'DEFAUT' ? $produit->getKey() : null).";".
        $complement.";".
        $dae->produit_libelle.";".
        $dae->label_libelle.";".
        $dae->mention_libelle.";".
        $dae->millesime.";".
        ($dae->primeur ? '1' : '0').";".
        ConfigurationClient::getInstance()->anonymisation($dae->no_accises_acheteur).";".
        ConfigurationClient::getInstance()->anonymisation($dae->nom_acheteur).";".
        $dae->type_acheteur_libelle.";".
        $dae->destination_libelle.";".
        $dae->conditionnement_libelle.";".
        $dae->contenance_libelle.";".
        ($dae->contenance_hl*100).";".
        $dae->quantite.";".
        $dae->prix_unitaire.";".
        $dae->volume_hl.";".
        $dae->prix_hl.
        "\n";
    }

    public function exportMouvementDRMContrat($mouvement) {
        $vrac = VracClient::getInstance()->find("VRAC-".$mouvement->vrac_numero, acCouchdbClient::HYDRATE_JSON);
        if(!$vrac) {
            return;
        }

        $date = DRMClient::getInstance()->buildDate(preg_replace("/DRM-[0-9]+-([0-9]{6}).*/", '\1', $mouvement->doc_id));
        $produit = ConfigurationClient::getConfiguration($date)->getConfigurationProduit($vrac->produit);
        $cp = ($vrac->vendeur->code_postal)? preg_replace("/([0-9]{2})[0-9]{3}/","$1",$vrac->vendeur->code_postal) : "";
        $complement = "";

        return $date.";".
               ConfigurationClient::getInstance()->anonymisation($vrac->vendeur_identifiant).";".
               ConfigurationClient::getInstance()->anonymisation((isset($vrac->vendeur->no_accises) ? $vrac->vendeur->no_accises : (isset($vrac->vendeur->num_accise) ? $vrac->vendeur->num_accise : null))).";".
               ConfigurationClient::getInstance()->anonymisation($vrac->vendeur->nom).";".
               $vrac->vendeur->famille.";".
               (isset($vrac->vendeur->sous_famille) ? $vrac->vendeur->sous_famille : null).";".
               $cp.";".
               $produit->getCertification()->getLibelle().";".
               $produit->getGenre()->getLibelle().";".
               $produit->getAppellation()->getLibelle().";".
               $produit->getMention()->getLibelle().";".
               $produit->getLieu()->getLibelle().";".
               $produit->getCouleur()->getLibelle().";".
               $produit->getCepage()->getLibelle().";".
               $complement.";".
               $vrac->produit_libelle.";".
               (isset($vrac->labels_libelle) ? $vrac->labels_libelle : null).";".
               ((isset($vrac->mentions_libelle) && $vrac->mentions_libelle != 'Primeurs') ? $vrac->mentions_libelle : null).";".
               $vrac->millesime.";".
               ((isset($vrac->mentions_libelle) && $vrac->mentions_libelle == 'Primeurs') ? 1 : 0).";".
               ConfigurationClient::getInstance()->anonymisation((isset($vrac->acheteur->no_accises) ? $vrac->acheteur->no_accises : (isset($vrac->acheteur->num_accise) ? $vrac->acheteur->num_accise : null))).";".
               ConfigurationClient::getInstance()->anonymisation($vrac->acheteur->nom).";".
               DAEClient::$types['NEGOCIANT_REGION'].";".
               "France".";".
               "Hectolitre".";".
               "Hectolitre".";".
               "100".";".
               ($mouvement->volume*-1).";".
               (isset($vrac->prix_unitaire_hl) ? $vrac->prix_unitaire_hl : $vrac->prix_unitaire).";".
               ($mouvement->volume*-1).";".
               (isset($vrac->prix_unitaire_hl) ? $vrac->prix_unitaire_hl : $vrac->prix_unitaire).
               "\n";
    }

}
