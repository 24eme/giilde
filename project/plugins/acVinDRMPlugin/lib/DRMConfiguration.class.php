<?php

class DRMConfiguration {

    private static $_instance = null;
    protected $configuration;

    const ALL_KEY = "_ALL";

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new DRMConfiguration();
        }
        return self::$_instance;
    }

    public function __construct() {
        if(!sfConfig::has('drm_configuration_drm')) {
			throw new sfException("La configuration pour les drm n'a pas été défini pour cette application");
		}

        $this->configuration = sfConfig::get('drm_configuration_drm', array());
    }

    public function getAll() {

        return $this->configuration;
    }

    public function getExportDetail() {

        return $this->configuration['details']['export_detail'];
    }

    public function isVracCreation() {

        return boolval($this->configuration['details']['vrac_detail']['creation']);
    }

    public function getFamilles() {

        return $this->configuration['familles'];
    }

    public function getExportPaysDebut() {

        return $this->configuration['export_pays_debut'];
    }

    public function getExportPaysFin() {

        return $this->configuration['export_pays_fin'];
    }

    public function getRepriseDonneesUrl() {

        return $this->configuration['reprise_donnees_url'];
    }

    public function hasSansContratOption() {

        return $this->configuration['sans_contrat_option'];
    }

    public function getDelaiOuvertureTeledeclaration() {

        return $this->configuration['delai_ouverture_teledeclaration'];
    }

    public function isDRMVracMultiProduit() {

        return $this->configuration['vrac_multi_produit'];
    }

    public function isCrdOnlySuspendus() {

        return $this->configuration['crd_only_suspendus'];
    }

    public function hasAggregatedEdi() {

        return boolval($this->configuration['aggregate_edi']);
    }

    public function getAggregatedEdi() {

        return $this->configuration['aggregate_edi'];
    }

    public function hasDeclassementIgp() {

        return $this->configuration['declassement_igp'];
    }

    public function isRepriseStocksChangementCampagne() {

        return $this->configuration['reprise_stocks_changement_campagne'];
    }

    public function isPdfCvo() {

        return $this->configuration['pdf_cvo'];
    }

    public function isMouvementDivisable() {

        return $this->getMouvementDivisableSeuil() !== false;
    }

    public function getMouvementDivisableSeuil() {

        return $this->configuration['mouvement_divisable_seuil'];
    }

    public function getMouvementDivisableNbMonth() {

        return $this->configuration['mouvement_divisable_nb_month'];
    }

    public function getDefaultCrds() {

        return $this->configuration['defaults_crds_nodes'];
    }

    public function isProduitAutoChecked(){
        return boolval($this->configuration['default_auto_checked']);
    }

    public function getPdfFontSize(){
        return $this->configuration['pdf_font_size'];
    }

    public function getXmlTransfertEchec($cielResponse){
        $erreur = preg_replace('/<\/erreur-fonctionnelle>/' ,'<br/></erreur-fonctionnelle>', html_entity_decode($cielResponse));
        return "<p>".str_replace("DESCRIPTION_ERREUR",$erreur,nl2br($this->configuration['xml_transfert_echec']))."</p>";
    }

    public function isCampagneListeMinimale(){
        return $this->configuration['campagne_liste_minimale'];
    }

}
