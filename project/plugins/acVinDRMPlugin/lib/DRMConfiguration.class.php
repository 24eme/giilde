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
    public function isDRMNegoce() {

        return boolval($this->configuration['negoce']);
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


}
