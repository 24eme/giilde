<?php

/**
 * Model for DAE
 *
 */
class DAE extends BaseDAE implements InterfaceDeclarantDocument {

    protected $declarant_document = null;

    public function __construct() {
        parent::__construct();
        $this->initDocuments();
    }

    public function constructId() {
        $numero = DAEClient::getInstance()->getNextIdentifiantForEtablissementAndDay($this->identifiant, $this->date);
        $this->set('_id', DAEClient::getInstance()->buildId($this->identifiant, $this->date,$numero));
    }


    public function getProduitObject() {
    	return $this->getConfig()->getProduit($this->produit_key);
    }

    protected function initDocuments() {
        $this->declarant_document = new DeclarantDocument($this);
    }
    
    public function getConfig() {
    	$date = (!$this->date) ? date('Y-m-d') : $this->date;
    	
    	return ConfigurationClient::getConfiguration($date);
    }
    
    public function getProduitsConfig($date = null) {
    	if (!$date || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date)) {
    		$date = (!$this->date) ? date('Y-m-d') : $this->date;
    	}
    
    	return $this->getConfig()->formatProduits($date, "%format_libelle%", array());
    }
    
    public function getLabels() {    
    	return array('CONV' => 'Conventionnel', 'BIO' => 'Biologique', 'HVE' => 'Haute Valeur Envrionnementale (HVE - niveau 3)', 'DEMETER' => 'Demeter', 'NATURE_PROGRES' => 'Nature et Progrès', 'BIODYVIN' => 'Biodyvin', 'BIO_COHERENCE' => 'Bio Cohérence', 'TERRA_VITIS' => 'Terra Vitis', 'AUTRE' => 'Autre');
    }
    
    public function getMentions() {    
    	return array('DOMAINE' => 'Domaine', 'CHATEAU' => 'Château', 'CLOS' => 'Clos', 'MAS' => 'Mas', 'AUTRE' => 'Autre');
    }
    
    public function getTypes() {

        return DAEClient::$types;
    }
    
    public function getContenances() {
    	return array(
    			'HL' => 'VRAC',
    			'CL_10' => 'Bouteille 10 cL',
         		'CL_12_5' => 'Bouteille 12.5 cL',
    			'CL_18_7' => 'Bouteille 18.7 cL',
    			'CL_20' => 'Bouteille 20 cL',
    			'CL_25' => 'Bouteille 25 cL',
    			'CL_35' => 'Bouteille 35 cL',
    			'CL_37_5' => 'Bouteille 37.5 cL',
    			'CL_60' => 'Bouteille 60 cL',
    			'CL_62' => 'Bouteille 62 cL',
    			'CL_70' => 'Bouteille 70 cL',
    			'CL_75' => 'Bouteille 75 cL',
    			'CL_100' => 'Bouteille 1 L',
    			'CL_150' => 'Bouteille 1.5 L',
    			'CL_175' => 'Bouteille 1.75 L',
    			'CL_200' => 'Bouteille 2 L',
    			'CL_225' => 'Bouteille 2.25 L',
    			'CL_300' => 'Bouteille 3 L',
    			'CL_400' => 'Bouteille 4 L',
    			'CL_450' => 'Bouteille 4.5 L',
    			'CL_500' => 'Bouteille 5 L',
    			'CL_525' => 'Bouteille 5.25 L',
    			'CL_600' => 'Bouteille 6 L',
    			'CL_800' => 'Bouteille 8 L',
    			'CL_900' => 'Bouteille 9 L',
    			'CL_1000' => 'Bouteille 10 L',
    			'CL_1200' => 'Bouteille 12 L',
    			'CL_1500' => 'Bouteille 15 L',
    			'CL_1800' => 'Bouteille 18 L',
    			'BIB_100' => 'Bag In Box 1 L',
    			'BIB_150' => 'Bag In Box 1.5 L',
    			'BIB_200' => 'Bag In Box 2 L',
    			'BIB_300' => 'Bag In Box 3 L',
    			'BIB_400' => 'Bag In Box 4 L',
    			'BIB_500' => 'Bag In Box 5 L',
    			'BIB_600' => 'Bag In Box 6 L',
    			'BIB_800' => 'Bag In Box 8 L',
    			'BIB_900' => 'Bag In Box 9 L',
    			'BIB_1000' => 'Bag In Box 10 L',
    			'BIB_2000' => 'Bag In Box 20 L',
    			'POCHE_100' => 'Poche 1 L',
    			'POCHE_150' => 'Poche 1.5 L',
    			'POCHE_200' => 'Poche 2 L',
    			'POCHE_300' => 'Poche 3 L',
    			'POCHE_400' => 'Poche 4 L',
    			'POCHE_500' => 'Poche 5 L',
    			'POCHE_600' => 'Poche 6 L',
    			'POCHE_800' => 'Poche 8 L',
    			'POCHE_900' => 'Poche 9 L',
    			'POCHE_1000' => 'Poche 10 L',
    			'POCHE_2000' => 'Poche 20 L',
    			'CUBI_100' => 'Cubi 1 L',
    			'CUBI_150' => 'Cubi 1.5 L',
    			'CUBI_200' => 'Cubi 2 L',
    			'CUBI_300' => 'Cubi 3 L',
    			'CUBI_400' => 'Cubi 4 L',
    			'CUBI_500' => 'Cubi 5 L',
    			'CUBI_600' => 'Cubi 6 L',
    			'CUBI_800' => 'Cubi 8 L',
    			'CUBI_900' => 'Cubi 9 L',
    			'CUBI_1000' => 'Cubi 10 L',
    			'CUBI_2000' => 'Cubi 20 L',
    			'TETRABRICK_100' => 'Tetrabrick 1 L',
    			'TETRABRICK_150' => 'Tetrabrick 1.5 L',
    			'TETRABRICK_200' => 'Tetrabrick 2 L',
    			'TETRABRICK_300' => 'Tetrabrick 3 L',
    			'TETRABRICK_400' => 'Tetrabrick 4 L',
    			'TETRABRICK_500' => 'Tetrabrick 5 L',
    			'AUTRE_10' => 'Autre 10 cL',
    			'AUTRE_12_5' => 'Autre 12.5 cL',
    			'AUTRE_18_7' => 'Autre 18.7 cL',
    			'AUTRE_20' => 'Autre 20 cL',
    			'AUTRE_25' => 'Autre 25 cL',
    			'AUTRE_35' => 'Autre 35 cL',
    			'AUTRE_37_5' => 'Autre 37.5 cL',
    			'AUTRE_60' => 'Autre 60 cL',
    			'AUTRE_62' => 'Autre 62 cL',
    			'AUTRE_70' => 'Autre 70 cL',
    			'AUTRE_75' => 'Autre 75 cL',
    			'AUTRE_100' => 'Autre 1 L',
    			'AUTRE_150' => 'Autre 1.5 L',
    			'AUTRE_175' => 'Autre 1.75 L',
    			'AUTRE_200' => 'Autre 2 L',
    			'AUTRE_225' => 'Autre 2.25 L',
    			'AUTRE_300' => 'Autre 3 L',
    			'AUTRE_400' => 'Autre 4 L',
    			'AUTRE_450' => 'Autre 4.5 L',
    			'AUTRE_500' => 'Autre 5 L',
    			'AUTRE_525' => 'Autre 5.25 L',
    			'AUTRE_600' => 'Autre 6 L',
    			'AUTRE_800' => 'Autre 8 L',
    			'AUTRE_900' => 'Autre 9 L',
    			'AUTRE_1000' => 'Autre 10 L',
    			'AUTRE_1200' => 'Autre 12 L',
    			'AUTRE_1500' => 'Autre 15 L',
    			'AUTRE_1800' => 'Autre 18 L'
    	);
    }
    
    public function getDateObject()
    {
    	return ($this->date)? new DateTime($this->date) : null;
    }
    
    public function getLiteralPeriode()
    {
    	sfApplicationConfiguration::getActive()->loadHelpers(array('Date'));
    	return ($this->date)? ucfirst(format_date($this->date, 'MMMM yyyy', 'fr_FR')) : null;
    }
    
    public function calculateDatas()
    {
    	if (preg_match('/CL_/', $this->contenance_key)) {
    		$this->conditionnement_key = 'BOUTEILLE';
    		$this->conditionnement_libelle = 'Bouteille';
    	} elseif (preg_match('/BIB_/', $this->contenance_key)) {
    		$this->conditionnement_key = 'BIB';
    		$this->conditionnement_libelle = 'Bib';
    	} else {
    		$this->conditionnement_key = 'HL';
    		$this->conditionnement_libelle = 'Hectolitre';
    	}
    	$this->contenance_hl = $this->getContenanceHl();
    	$this->volume_hl = round($this->contenance_hl * $this->quantite, 2);
    	$this->prix_hl = round($this->prix_unitaire / $this->contenance_hl, 2);
    }

    public function getContenanceHl()
    {
    	if (!$this->contenance_key || (!preg_match('/CL_/', $this->contenance_key) && !preg_match('/BIB_/', $this->contenance_key))) {
    		return 1;
    	}
    	return (str_replace('_', '.', str_replace(array('CL_','BIB_'), '', $this->contenance_key)) * 1) / 10000;
    }
    
    public function initByDae($dae)
    {
    	$this->destination_key = $dae->destination_key;
    	$this->destination_libelle = $dae->destination_libelle;
    	$this->type_acheteur_key = $dae->type_acheteur_key;
    	$this->type_acheteur_libelle = $dae->type_acheteur_libelle;
    	$this->no_accises_acheteur = $dae->no_accises_acheteur;
    	$this->nom_acheteur = $dae->nom_acheteur;
    }
    
    /*** DECLARANT ***/

    public function getEtablissementObject() {
    	return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    }

    public function storeDeclarant() {
        $this->declarant_document->storeDeclarant();
        $etablissement = $this->getEtablissementObject();
        $declarant = $this->getDeclarant();
        $declarant->famille = $etablissement->famille;
        $declarant->sous_famille = $etablissement->sous_famille;
    }

    /*** FIN DECLARANT ***/
}
