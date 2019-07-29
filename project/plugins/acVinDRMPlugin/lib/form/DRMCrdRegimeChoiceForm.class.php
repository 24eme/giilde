<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DRMCrdRegimeChoiceForm
 *
 * @author mathurin
 */
class DRMCrdRegimeChoiceForm extends acCouchdbObjectForm {

    protected $drm = null;
    protected $etablissement = null;

    public function __construct(DRM $drm, $options = array(), $CSRFSecret = null) {
        $this->drm = $drm;
        $this->etablissement = $this->drm->getEtablissement();
        parent::__construct($drm, $options, $CSRFSecret);
    }

    public function configure() {
        $this->setWidgets(array(
            'crd_regime' => new bsWidgetFormChoice(array('expanded' => true, 'multiple' => false,'inline' => true, 'choices' => $this->getCRDRegimes()))
        ));
        $this->widgetSchema->setLabels(array(
            'crd_regime' => 'Régime CRD (Compte capsules) : '
        ));

        $this->setValidators(array(
            'crd_regime' => new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getCRDRegimes())), array('required' => "Aucun régime CRD n'a été choisi")),
        ));

        $this->widgetSchema->setNameFormat('drm_regime_crd[%s]');
    }

    public function getCRDRegimes() {
        $regimes = DRMClient::getInstance()->getAllRegimesCrdsChoices(true);

        if (! sfContext::getInstance()->getUser()->isUsurpationCompte()) {
            unset(
                $regimes[EtablissementClient::REGIME_CRD_COLLECTIF_ACQUITTE_SUSPENDU],
                $regimes[EtablissementClient::REGIME_CRD_COLLECTIF_PERSONNALISE_SUSPENDU]
            );
        }

        return $regimes;
    }

    public function doUpdateObject($values) {
          parent::doUpdateObject($values);
          $crd_regime = $values['crd_regime'];
          if (strpos($crd_regime, ',') === false) {
              $this->drm->forceModified();
              $this->drm->switchCrdRegime($crd_regime);
          }
          $this->drm->save();

          $this->etablissement->add('crd_regime', $crd_regime);
          $this->etablissement->save();
      }

    public function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        $this->setDefault('crd_regime', EtablissementClient::REGIME_CRD_COLLECTIF_SUSPENDU);
    }

}
