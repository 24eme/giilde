<?php

class DRMDetailVracItemForm extends DRMESDetailsItemForm {

    protected $details = null;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        $this->details = $options['details'];
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function getFormName() {

        return "drm_detail_vrac_item";
    }

    public function configure() {
        parent::configure();

        if(!$this->getProduitDetail()->getCVOTaux()) {
            $this->setWidget('identifiant', new bsWidgetFormInput(array(), array("placeholder" => "Saisissez le numéro de votre contrat")));
            $this->setValidator('identifiant', new sfValidatorRegex(array('required' => true, 'pattern' => '/^[0-9]+$/')));
        }
    }

    public function getIdentifiantChoices() {
        $vracs = $this->getProduitDetail()->getContratsVrac();
        if ($this->getObject()->identifiant && !array_key_exists($this->getObject()->identifiant, $vracs) && $this->getObject()->getVrac()) {
            $vrac = $this->getObject()->getVrac();
            if ($vrac->valide->statut != VracClient::STATUS_CONTRAT_ANNULE && $vrac->valide->statut != VracClient::STATUS_CONTRAT_BROUILLON) {
                $vracs[$this->getObject()->identifiant] = sprintf("%s - %s (%s) - %s hl [%s/%s]", $vrac->acheteur->nom, $vrac->numero_contrat, $vrac->numero_archive, round($vrac->volume_propose - $vrac->volume_enleve, 2), $vrac->volume_enleve, $vrac->volume_propose);
            }
        }
        return array_merge(array("" => ""), $vracs);
    }

    public function getPostValidatorClass() {

        return 'DRMDetailVracItemValidator';
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

    public function getProduitDetail() {
        return $this->details->getProduitDetail();
    }

}
