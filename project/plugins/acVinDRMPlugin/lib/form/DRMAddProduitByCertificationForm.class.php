<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DRMAddProduitByCertificationForm
 *
 * @author mathurin
 */
class DRMAddProduitByCertificationForm extends acCouchdbObjectForm {

    protected $_configurationCertification = null;
    protected $_drm = null;
    protected $_choices_produits = null;

    public function __construct(DRM $drm, $options = array(), $CSRFSecret = null) {
        $this->_drm = $drm;
        $this->_configurationCertifications = array();
        $this->_produitFilter = $options['produitFilter'];
        foreach (explode(',', $this->_produitFilter) as $certifKey) {
            $this->_configurationCertifications[] = $drm->getConfig()->getDeclaration()->getCertifications()->get($certifKey);
        }
        $this->_formKey = $this->_configurationCertifications[0]->getHashWithoutInterpro();
        parent::__construct($drm, $options, $CSRFSecret);
    }

    public function configure() {
        $this->setWidgets(array(
            'produit' => new bsWidgetFormChoice(array('expanded' => false, 'multiple' => false, 'choices' => $this->getProduits())),
            'denomination_complementaire' => new bsWidgetFormInput()
        ));
        $this->widgetSchema->setLabels(array(
            'produit' => 'Produit : ',
            'denomination_complementaire' => "Libellé libre (optionnel) : "
        ));

        $this->setValidators(array(
            'produit' => new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getProduits())), array('required' => "Aucun produit n'a été saisi !")),
            'denomination_complementaire' => new sfValidatorString(array('required' => false)),
        ));
        if ($this->_produitFilter == 'ALCOOLS') {
            $this->setWidget('tav', new bsWidgetFormInputFloat());
            $this->widgetSchema->setLabel('tav', 'TAV');
            $this->setValidator('tav', new sfValidatorString(array('required' => false)));
        }

        $this->widgetSchema->setNameFormat('add_produit_' . $this->_produitFilter . '[%s]');
    }

    public function getDRM() {
        return $this->_drm;
    }

    public function getFormKey() {
        return $this->_formKey;
    }

    public function getProduitFilter() {
        return $this->_produitFilter;
    }

    public function getProduits() {
        if (is_null($this->_choices_produits)) {

            $this->_choices_produits = array("" => "");
            $produits = $this->_drm->getConfigProduits(true);
            $matchcertif = '#(';
            foreach ($this->_configurationCertifications as $certif) {
                $matchcertif .= $certif->getHash().'|';
            }
            $matchcertif = preg_replace('/\|$/', ')/#', $matchcertif);
            if (!is_null($produits)) {
                foreach ($produits as $hash => $produit) {
                    if(!preg_match($matchcertif, $hash)) {
                        continue;
                    }
                    $this->_choices_produits[$hash] = $produit;
                }
            }
        }
        return $this->_choices_produits;
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $denomination_complementaire = null;
        if(isset($values["denomination_complementaire"]) && $values["denomination_complementaire"]){
          $denomination_complementaire = $values["denomination_complementaire"];
        }
        $tav = null;
        if (isset($values['tav']) && $values['tav']) {
            $tav = $values['tav'];
        }

        $this->_drm->addProduit($values['produit'], DRM::DETAILS_KEY_SUSPENDU,$denomination_complementaire, $tav * 1);
        $this->_drm->save();
    }

}
