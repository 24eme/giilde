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


    protected function initDocuments() {
        $this->declarant_document = new DeclarantDocument($this);
    }
    
    public function getConfig() {
    	$date = (!$this->date) ? date('Y-m-d') : $this->date;
    	
    	return ConfigurationClient::getConfiguration($date);
    }
    
    public function getProduitsConfig() {
    	$date = (!$this->date) ? date('Y-m-d') : $this->date;
    
    	return $this->getConfig()->formatProduits($date, "%format_libelle%", array());
    }
    
    public function getLabels() {    
    	return array('CONV' => 'Conventionnel', 'BIO' => 'Biologique', 'AUTRE' => 'Autre');
    }
    
    public function getMentions() {    
    	return array('PRIM' => 'Primeurs', 'CHDO' => 'Château ou domaine');
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
    
    /*** DECLARANT ***/

    public function getEtablissementObject() {
        return $this->getEtablissement();
    }

    public function storeDeclarant() {
        $this->declarant_document->storeDeclarant();
    }

    /*** FIN DECLARANT ***/
}
