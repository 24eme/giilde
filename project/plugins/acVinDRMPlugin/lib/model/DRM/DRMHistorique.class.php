<?php

class DRMHistorique {

    protected $identifiant = null;
    protected $drms = null;
    protected $drm_process = null;
    protected $campagne = null;

    const VIEW_INDEX_ETABLISSEMENT = 0;
    const VIEW_CAMPAGNE = 1;
    const VIEW_PERIODE = 2;
    const VIEW_VERSION = 3;
    const VIEW_TYPE_CREATION = 4;
    const VIEW_STATUT = 5;
    const VIEW_STATUT_DOUANE_ENVOI = 6;
    const VIEW_STATUT_DOUANE_ACCUSE = 7;

    public function __construct($identifiant, $campagne)
    {
        $this->identifiant = $identifiant;
        $this->campagne = $campagne;

        $this->load();
    }

    public function hasInProcess() {

        return $this->drm_process;
    }

    public function getLast() {
        foreach($this->drms as $drm) {

            return DRMClient::getInstance()->find($drm->_id);
        }
    }

    public function getPrevious($periode) {
        foreach($this->drms as $drm) {
            if ($drm->periode < $periode) {

                return DRMClient::getInstance()->find($drm->_id);
            }
        }
    }

    public function getNext($periode) {
        $next_drm = new stdClass();
        $next_drm->_id = null;
        $next_drm->periode = '9999-99';
        foreach($this->drms as $drm) {
            if ($drm->periode < $next_drm->periode) {
                $next_drm = $drm;
            }

            if($drm->periode <= $periode) {
                break;
            }
        }

        if(!$next_drm->_id) {
            return null;
        }

        return DRMClient::getInstance()->find($next_drm->_id);
    }

    public function reload() {
        $this->load();
    }

    protected function load() {
        $this->drms = array();

        $drms = DRMClient::getInstance()->viewByIdentifiantAndCampagne($this->identifiant, $this->campagne);

        $this->drm_process = false;

        foreach($drms as $drm) {
            $key = $drm[self::VIEW_PERIODE].$drm[self::VIEW_VERSION];

            if (array_key_exists($key, $this->drms)) {

                continue;
            }

            $this->drms[$key] = $this->build($drm);

            if (!$this->drms[$key]->valide->date_saisie) {
                $this->drm_process = true;
            }
        }
    }

    protected function build($ligne) {
        $drm = new stdClass();
        $drm->identifiant = $ligne[self::VIEW_INDEX_ETABLISSEMENT];
        $drm->campagne = $ligne[self::VIEW_CAMPAGNE];
        $drm->periode = $ligne[self::VIEW_PERIODE];
        $drm->version = $ligne[self::VIEW_VERSION];
        $drm->type_creation = $ligne[self::VIEW_TYPE_CREATION];
        $drm->valide = new stdClass();
        $drm->valide->date_saisie = $ligne[self::VIEW_STATUT];
        $drm->douane = new stdClass();
        $drm->douane->envoi = $ligne[self::VIEW_STATUT_DOUANE_ENVOI];
        $drm->douane->accuse = $ligne[self::VIEW_STATUT_DOUANE_ACCUSE];
        $drm->_id = DRMClient::getInstance()->buildId($drm->identifiant, $drm->periode, $drm->version);

        return $drm;
    }

    public function getDRMs() {

        return $this->drms;
    }

    public function getIdentifiant() {

        return $this->identifiant;
    }

    public function getPeriodes() {

        return $this->periodes;
    }

}
