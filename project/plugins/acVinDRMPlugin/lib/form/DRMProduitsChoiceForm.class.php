<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DRMProduitsChoiceForm
 *
 * @author mathurin
 */
class DRMProduitsChoiceForm extends acCouchdbObjectForm {

    private $_drm = null;
    private $_produits = null;
    private $all_checked = true;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        $this->_drm = $object;
        $this->_produits = $this->_drm->declaration->getProduitsDetails(true);
        parent::__construct($this->_drm, $options, $CSRFSecret);
    }

    public function configure() {
        $produitAutoChecked = DRMConfiguration::getInstance()->isProduitAutoChecked();
        foreach ($this->_produits as $produit) {
          $disabled=array();
            $checkedAuto = $produitAutoChecked || (!preg_match("/(DPLC|LIES)/",$produit->getHash()));
            $this->setWidget('produit' . $produit->getHashForKey(), new sfWidgetFormInputCheckbox(array('value_attribute_value' => '1', 'default' => $checkedAuto)));

            $this->setWidget('acquitte' . $produit->getHashForKey(), new sfWidgetFormInputCheckbox(array('value_attribute_value' => '1', 'default' => $checkedAuto)));
            if(preg_match("/USAGESINDUSTRIELS/",$produit->getHashForKey())){
                $this->getWidget('acquitte' . $produit->getHashForKey())->setAttribute('disabled', 'disabled');
              }


            $this->widgetSchema->setLabel('produit' . $produit->getHashForKey(), '');
            $this->widgetSchema->setLabel('acquitte' . $produit->getHashForKey(), '');

            $this->setValidator('produit' . $produit->getHashForKey(), new sfValidatorString(array('required' => false)));
            $this->setValidator('acquitte' . $produit->getHashForKey(), new sfValidatorString(array('required' => false)));
        }

        $this->widgetSchema->setNameFormat('produitsChoice[%s]');
    }

    protected function doUpdateObject($values) {
        foreach ($values as $key => $value) {
            $matches = array();
            if (preg_match('/^produit(.*)/', $key, $matches)) {
                $key = str_replace('-', '/', $matches[1]);
                $this->_drm->get($key)->add('no_movements', ! $value);
                $this->_drm->etape = DRMClient::ETAPE_SAISIE;

            }
            if (preg_match('/^acquitte(.*)/', $key, $matches)) {
                $key = str_replace('-', '/', $matches[1]);

                if ($value) {
                  $denomination_complementaire = null;
                  if($this->_drm->get($key)->exist("denomination_complementaire") && $this->_drm->get($key)->get("denomination_complementaire")){
                    $denomination_complementaire = $this->_drm->get($key)->get("denomination_complementaire");
                  }
                  $p =	$this->_drm->addProduit($this->_drm->get($key)->getCepage()->getHash(), DRM::DETAILS_KEY_ACQUITTE, $denomination_complementaire);
                }

                $hashAcquitte = str_replace(DRM::DETAILS_KEY_SUSPENDU, DRM::DETAILS_KEY_ACQUITTE, $key);
                if($this->_drm->exist($hashAcquitte)) {
                    $this->_drm->get($hashAcquitte)->add('no_movements', ! $value);
                }
            }
        }
        $this->_drm->save();
    }

    public function updateDefaultsFromObject() {
        $this->all_checked = true;
        parent::updateDefaultsFromObject();
        foreach ($this->_produits as $produit) {
              if($produit->exist('no_movements') && $produit->get('no_movements')){
                  $this->setDefault('produit' . $produit->getHashForKey(), false);
              }
              $hashAcquitte = (!preg_match('/'.DRM::DETAILS_KEY_ACQUITTE.'/', $produit->getHash()))? str_replace(DRM::DETAILS_KEY_SUSPENDU, DRM::DETAILS_KEY_ACQUITTE, $produit->getHash()) : $produit->getHash();
              if (!$this->_drm->exist($hashAcquitte) || ($this->_drm->get($hashAcquitte)->exist('no_movements') && $this->_drm->get($hashAcquitte)->get('no_movements'))) {
                  $this->setDefault('acquitte' . $produit->getHashForKey(), false);
              }
          }
    }

    public function isAllChecked() {
        return $this->all_checked;
    }

}
