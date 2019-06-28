<?php

class DRMValidationCoordonneesEtablissementForm extends acCouchdbObjectForm {

    protected $coordonneesEtablissement = null;
    protected $drm = null;

    public function __construct(DRM $drm, $options = array(), $CSRFSecret = null) {
        $this->drm = $drm;
        parent::__construct($drm, $options, $CSRFSecret);
    }

    public function configure() {
        parent::configure();
        
        $cviRequired = true;
        if ($this->drm->isNegoce()) {
            $cviRequired = false;
        }
        $this->setWidget('cvi', new bsWidgetFormInput());
        $this->setValidator('cvi', new sfValidatorString(array('required' => $cviRequired)));
        $this->widgetSchema->setLabel('cvi', 'CVI :');

        $this->setWidget('adresse', new bsWidgetFormInput());
        $this->setValidator('adresse', new sfValidatorString(array('required' => true)));
        $this->widgetSchema->setLabel('adresse', 'Adresse :');

        $this->setWidget('code_postal', new bsWidgetFormInput());
        $this->setValidator('code_postal', new sfValidatorString(array('required' => true)));
        $this->widgetSchema->setLabel('code_postal', 'Code postal :');

        $this->setWidget('commune', new bsWidgetFormInput());
        $this->setValidator('commune', new sfValidatorString(array('required' => true)));
        $this->widgetSchema->setLabel('commune', 'Commune :');

        $this->setWidget('no_accises', new bsWidgetFormInput());
        $this->setValidator('no_accises', new sfValidatorString(array('required' => false)));
        $this->widgetSchema->setLabel('no_accises', "N° d'accise :");

        if ($this->drm->declarant->exist('adresse_compta')) {
            $this->setWidget('adresse_compta', new bsWidgetFormInput());
            $this->setValidator('adresse_compta', new sfValidatorString(array('required' => false)));
            $this->widgetSchema->setLabel('adresse_compta', 'Lieu de la comptabilité matière :');
        }

        $this->widgetSchema->setNameFormat('drm_validation_coordonnees_etablissement[%s]');
    }

    private function getCoordonneesEtablissement() {
        if (!$this->coordonneesEtablissement) {
            $this->coordonneesEtablissement = $this->drm->getDeclarant();
        }
        return $this->coordonneesEtablissement;
    }

    public function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        $this->getCoordonneesEtablissement();
        $this->setDefault('cvi', $this->coordonneesEtablissement->cvi);
        $this->setDefault('adresse', $this->coordonneesEtablissement->adresse);
        $this->setDefault('code_postal', $this->coordonneesEtablissement->code_postal);
        $this->setDefault('commune', $this->coordonneesEtablissement->commune);
        $this->setDefault('no_accises', $this->coordonneesEtablissement->no_accises);

        if ($this->drm->declarant->exist('adresse_compta')) {
            $this->setDefault('adresse_compta', $this->coordonneesEtablissement->adresse_compta);
        }

    }

    public function getDiff() {
        $diff = array();
        $this->getCoordonneesEtablissement();
        foreach ($this->getValues() as $key => $new_value) {
            if (preg_match('/^_revision$/', $key)) {
                continue;
            }
            if ($this->coordonneesEtablissement->$key != $new_value) {
                $diff[$key] = $new_value;
            }
        }
        return $diff;
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $this->drm->declarant->cvi = $values['cvi'];
        $this->drm->declarant->adresse = $values['adresse'];
        $this->drm->declarant->code_postal = $values['code_postal'];
        $this->drm->declarant->commune = $values['commune'];
        $this->drm->declarant->no_accises = $values['no_accises'];
        if ($this->drm->declarant->exist('adresse_compta')) {
            $this->drm->declarant->adresse_compta = $values['adresse_compta'];
        }

    }

}
