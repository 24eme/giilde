<?php
class VracConfiguration
{
	private static $_instance = null;
	protected $configuration;

	const ALL_KEY = "_ALL";

	public static function getInstance()
	{
		if(is_null(self::$_instance)) {
			self::$_instance = new VracConfiguration();
		}
		return self::$_instance;
	}

	public function __construct()
	{
		if(!sfConfig::has('vrac_configuration_vrac')) {
			throw new sfException("La configuration pour les contrats n'a pas été défini pour cette application");
		}

		$this->configuration = sfConfig::get('vrac_configuration_vrac', array());
	}

	public function getAll() {

		return $this->configuration;
	}

	public function getTransactions()
	{
		$transactions = array();
		foreach($this->configuration['transactions'] as $key => $transaction) {
			if($transaction === null) {
				continue;
			}

			$transactions[$key] = $transaction;
		}

		return $transactions;
	}

	public function getContenances()
	{
		return $this->configuration['contenances'];
	}

	public static function slugifyContenances($s) {
		return strtoupper(str_replace(" ","",str_replace(",",".", $s)));
	}

	public function getContenanceLibelle($value) {
		$contenances = $this->getContenancesSlugifiedLibelle();

		return $contenances[self::slugifyContenances($value)];
	}

	public function getContenancesSlugified()
	{
		$contenances = $this->configuration['contenances'];
		$contenaces_retour = array();
		foreach ($contenances as $l => $c) {
			$contenances_retour[self::slugifyContenances($l)] = $c;
			$contenances_retour[self::slugifyContenances(preg_replace('/(Bouteilles? *|BIB)/i', '', $l))] = $c;
		}
		return $contenances_retour;
	}

	public function getContenancesSlugifiedLibelle()
	{
		$contenances = $this->configuration['contenances'];
		$contenaces_retour = array();
		foreach ($contenances as $l => $c) {
			$contenances_retour[self::slugifyContenances($l)] = $l;
			$contenances_retour[self::slugifyContenances(preg_replace('/(Bouteilles? *|BIB)/i', '', $l))] = $l;
		}
		return $contenances_retour;
	}

	public function getDelaisPaiement()
	{
		return $this->configuration['delais_paiement'];
	}

	public function getMoyensPaiement()
	{
		return $this->configuration['moyens_paiement'];
	}

	public function getRepartitionCourtage()
	{
		return $this->configuration['repartition_courtage'];
	}

	public function getTeledeclarationVisaAutomatique()
	{
		return $this->configuration['teledeclaration_visa_automatique'];
	}

	public function getTva()
	{
		return $this->configuration['tva'];
	}

	public function getCategories()
	{
		return $this->configuration['categories'];
	}

	public function getEtapes()
	{
		return $this->configuration['etapes'];
	}

	public function getChampsSupprimes($etape, $type_transaction)
	{
		$all = (isset($this->configuration['champs_supprimes'][$etape]) && isset($this->configuration['champs_supprimes'][$etape][self::ALL_KEY]))? $this->configuration['champs_supprimes'][$etape][self::ALL_KEY] : array();
		$champs = (isset($this->configuration['champs_supprimes'][$etape]) && isset($this->configuration['champs_supprimes'][$etape][$type_transaction]))? $this->configuration['champs_supprimes'][$etape][$type_transaction] : array();
		return array_merge($all, $champs);
	}
	public function getUnites()
	{
		return $this->configuration['unites'];
	}
	public function getActeursPreparationVin()
	{
		return $this->configuration['acteurs_preparation_vin'];
	}
	public function getActeursEmbouteillage()
	{
		return $this->configuration['acteurs_embouteillage'];
	}
	public function getConditionnementsCRD()
	{
		return $this->configuration['conditionnements_crd'];
	}

    public function getSoldeSeuil()
	{
		return $this->configuration['solde_seuil'];
	}

	public function getRegionDepartement() {

		return $this->configuration['region_departement'];
	}

	public function getRepartitionCvo()
	{
		return $this->configuration['repartition_cvo'];
	}

	public function isVisaUnique() {

		return $this->configuration['visa_unique'];
	}
}
