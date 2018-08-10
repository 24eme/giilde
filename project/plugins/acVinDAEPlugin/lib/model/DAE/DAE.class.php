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
    	return array('CONV' => 'Conventionnel', 'BIO' => 'Biologique', 'AUTRE' => 'Autre');
    }
    
    public function getMentions() {    
    	return array('PRIM' => 'Primeurs', 'CHDO' => 'Château ou domaine');
    }
    
    public function getTypes() {

        return DAEClient::$types;
    }
    
    public function getContenances() {
    	return array_merge(array('HL' => 'HL'), VracConfiguration::getInstance()->getContenances());
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
