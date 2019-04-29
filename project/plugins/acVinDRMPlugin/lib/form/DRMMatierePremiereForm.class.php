<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DRMAnnexesForm
 *
 * @author mathurin
 */
class DRMMatierePremiereForm extends acCouchdbForm {

    private $drm = null;
    private $detailsMp = null;


    public function __construct(acCouchdbJson $drm, $options = array(), $CSRFSecret = null) {
        $this->drm = $drm;
        $this->detailsMp = array();
        $defaults = array();
        foreach($this->drm->getProduitsDetails() as $detailsMp) {
            if($detailsMp->isMatierePremiere()){
              $this->detailsMp[str_replace('/', '-', $detailsMp->getHash())] = $detailsMp;
              $defaults['stocks_debut_'.str_replace('/', '-', $detailsMp->getHash())] = $detailsMp->stocks_debut->initial;
            }
        }

        parent::__construct($this->drm, $defaults, $options, $CSRFSecret);
    }

    public function configure() {


        foreach ($this->detailsMp as $detailsMpKey => $detailsMp) {
          $this->setWidget('stocks_debut_'.$detailsMpKey, new bsWidgetFormInputFloat());
          $this->setValidator('stocks_debut_'.$detailsMpKey, new sfValidatorNumber(array('required' => false)));

          $formProduits = new BaseForm();
          foreach($this->getDetailsAlcool() as $detail) {
                  $volume = null;
                  $keyDetail = str_replace('/', '-', $detail->getHash());
                  if($detailsMp->sorties->transfertsrecolte_details->exist($keyDetail)) {
                      $volume = $detailsMp->sorties->transfertsrecolte_details->get($keyDetail)->volume;
                  }
                  $formProduit = new BaseForm(array("volume" => $volume, "tav" => $detail->tav));
                  $formProduit->setWidget('volume', new bsWidgetFormInputFloat());
                  $formProduit->setValidator('volume', new sfValidatorNumber(array('required' => false)));
                  $formProduit->setWidget('tav', new bsWidgetFormInputFloat());
                  $formProduit->setValidator('tav', new sfValidatorNumber(array('required' => false)));
                  $formProduits->embedForm($detailsMp->getHash()."-".$detail->getHash(), $formProduit);

              }
              $this->embedForm('sorties_'.$detailsMpKey, $formProduits);

        }
        $matierePremiereValidator = new DRMMatierePremiereValidator(null, array('drm' => $this->drm));
        $this->mergePostValidator($matierePremiereValidator);
        $this->widgetSchema->setNameFormat('drm_matiere_premiere[%s]');
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
    }

    public function getDetailsAlcool() {
        $produits = array();

        foreach($this->getDocument()->getProduitsDetails() as $detail) {
            if(!$detail->exist('tav')) {
                continue;
            }
            $produits[] = $detail;
        }

        return $produits;
    }

    public function save() {
        if(!$this->isBound()) {
            return;
        }

        foreach ($this->detailsMp as $detailsMpKey => $detailsMp) {
        $detailsMp->stocks_debut->initial = $this->getValue('stocks_debut_'.$detailsMpKey);
        $detailsMp->add('edited',true);
        $sortiesValues = $this->getValue('sorties_'.$detailsMpKey);
        foreach($sortiesValues as $hash => $sortie) {
          $detailSplittedKey = explode("-",$hash);
              $detailAlcool = DRMESDetailAlcoolPur::freeInstance($this->getDocument());
              if($sortie['volume']){
                $detailAlcool->setProduit($this->getDocument()->get($detailSplittedKey[1]));
                $detailAlcool->tav = $sortie['tav'];
                $detailAlcool->volume = $sortie['volume'];
                $detailsMp->sorties->transfertsrecolte_details->addDetail($detailAlcool);
            }else{
              $k = str_replace("/","-",$detailSplittedKey[1]);
              $exist = $detailsMp->sorties->transfertsrecolte_details->exist($k);
              if($exist){
                $detailsMp->sorties->transfertsrecolte_details->remove($k);
              }
            }
          }
        }

        $this->getDocument()->update();
        $this->getDocument()->save();
    }

    public function getDetailsMp(){
      return $this->detailsMp;
    }

}
