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

    public function __construct(acCouchdbJson $object, $certificationsProduits,  $options = array(), $CSRFSecret = null) {
        $this->_drm = $object;
        $this->_produits = array();
        foreach($certificationsProduits as $certifProduits) {
            foreach($certifProduits->produits as $hash => $produit) {
                $this->_produits[$hash] = $produit;
            }
        }
        parent::__construct($this->_drm, $options, $CSRFSecret);
    }

    public function configure() {
        foreach ($this->_produits as $produit) {

            $this->setWidget('produit' . $produit->getHashForKey(), new sfWidgetFormInputCheckbox(array('value_attribute_value' => '1')));
            $this->setWidget('acquitte' . $produit->getHashForKey(), new sfWidgetFormInputCheckbox(array('value_attribute_value' => '1')));

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
                $this->updateDetail(str_replace("/detailsACQUITTE/", "/details/", str_replace('-', '/', $matches[1])), $value);
            } elseif (preg_match('/^acquitte(.*)/', $key, $matches)) {
                $this->updateDetail(str_replace("/details/", "/detailsACQUITTE/", str_replace('-', '/', $matches[1])), $value);
            }
        }

        $this->_drm->etape = DRMClient::ETAPE_SAISIE;
        $this->_drm->save();
    }

    public function updateDetail($hash, $checked) {
        if(!$this->_drm->exist($hash) && !$checked) {
            return;
        }

        if($this->_drm->exist($hash)) {
            $produit = $this->_drm->get($hash);
        }

        if (!$produit) {
            if(preg_match("|/details/|", $hash)) {
                $produitExistant = $this->_drm->get(str_replace("/details/", "/detailsACQUITTE/", $hash));
                $detailKey = "details";
            } elseif(preg_match("|/detailsACQUITTE/|", $hash)) {
                $produitExistant = $this->_drm->get(str_replace("/detailsACQUITTE/", "/details/", $hash));
                $detailKey = "detailsACQUITTE";
            }

            $denomination_complementaire = null;
            if($produitExistant->exist("denomination_complementaire") && $produitExistant->get("denomination_complementaire")){
              $denomination_complementaire = $produitExistant->get("denomination_complementaire");
            }

            $produit = $this->_drm->addProduit($produitExistant->getCepage()->getHash(), $detailKey, $denomination_complementaire);
        }

        $produit->add('no_movements', !$checked);
    }

    public function calculChecked($hash) {
        $noMouvement = $this->_drm->exist($hash) && $this->_drm->get($hash)->exist('no_movements') && $this->_drm->get($hash)->get('no_movements');

        $checked = false;

        if($this->_drm->exist($hash) && preg_match("/(DPLC|LIES)/", $hash) && DRMConfiguration::getInstance()->isProduitAutoChecked()) {
            $checked = true;
        }
        if($this->_drm->exist($hash) && !preg_match("/(DPLC|LIES)/", $hash)) {
            $checked = true;
        }
        if($this->_drm->exist($hash) && $this->_drm->get($hash)->getTotalDebutMois() > 0) {
            $checked = true;
        }

        return $checked && !$noMouvement;
    }

    public function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();

        foreach ($this->_produits as $hash => $produit) {
            $this->setDefault('produit' . $produit->getHashForKey(), $this->calculChecked(str_replace("/detailsACQUITTE/", "/details/", $hash)));
            $this->setDefault('acquitte' . $produit->getHashForKey(), $this->calculChecked(str_replace("/details/", "/detailsACQUITTE/", $hash)));
        }
    }

}
