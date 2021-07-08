<?php
/**
 * Model for DSNegoce
 *
 */

class DSNegoce extends BaseDSNegoce implements InterfaceDeclarantDocument {

	protected $declarant_document = null;

	public function __construct() {
			parent::__construct();
			$this->initDocuments();
	}

	protected function initDocuments() {
			$this->declarant_document = new DeclarantDocument($this);
	}

	public function constructId()
	{
		$this->set('_id', DSNegoceClient::makeId($this->identifiant, $this->getDateStock()));
	}

	public function getPeriode()
	{
		return substr($this->date_stock, 0, -3);
	}

	public function initDoc($etablissement, $date, $teledeclare = false)
	{
			if (!preg_match('/^[0-9]{4}-07-31$/', $date)) {
				throw new Exception('Date de stock invalide : '.$date);
			}
			$this->identifiant = (is_object($etablissement))? $etablissement->identifiant : $etablissement;
			$cm = new CampagneManager('08-01');
			$this->date_stock = $date;
			$tabDateStock = explode('-', $date);
			$this->campagne = $cm->getCampagneByDate($date);
			$this->millesime = $tabDateStock[0]-1;
			$this->teledeclare = (int)$teledeclare;
			$this->constructId();
			$this->storeDeclarant();
			$this->storeProduits();
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

	public function storeProduits() {
		$doc = $this->getDocumentRepriseProduits();
		if ($doc) {
			$this->docid_origine_reprise_produits = $doc->_id;
			foreach($doc->getProduitsCepages() as $produitCepage) {
				$hashCepage = str_replace(['/declaration/','declaration/'], '', $produitCepage->getHash());
				$produit = $this->declaration->add($hashCepage);
				$produit->libelle = $produitCepage->getLibelle();
				foreach($produitCepage->getProduits() as $detail) {
					$produitDetail = $produit->detail->add($detail->getKey());
					$produitDetail->denomination_complementaire = trim(str_replace($produitCepage->getLibelle(), '', $detail->getLibelle()));
					$produitDetail->stock_initial_millesime_courant = $detail->total;
				}
			}
		}
	}

	public function getDocumentRepriseProduits()
	{
			return DSNegoceClient::getDocumentRepriseProduits($this->identifiant, $this->date_stock);
	}

	public function devalidate()
	{
		$this->valide->date_saisie = null;
		$this->valide->date_signee = null;
		$this->valide->identifiant = null;
	}

	public function validate()
	{
		$this->valide->date_saisie = date('Y-m-d');
		$this->valide->date_signee = $this->valide->date_saisie;
		$this->valide->identifiant = $this->identifiant;
	}

	public function isValidee()
	{
		return preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $this->valide->date_saisie);
	}

}
