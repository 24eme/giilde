<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class DRMMatierePremiereValidator
 * @author mathurin
 */
class DRMMatierePremiereValidator extends sfValidatorSchema {

    private $drm;

    public function __construct($fields = null, $options = array(), $messages = array())
    {
          $this->addRequiredOption('drm');

          $this->drm = $options['drm'];

          parent::__construct($fields,$options, $messages);
          $this->addOption('throw_global_error', false);
    }

        public function configure($options = array(), $messages = array())
        {
        }

        protected function doClean($values) {

            if (!($doublonHash = $this->validateNoDoublon($values))) {

                return $values;
            }

            if ($this->getOption('throw_global_error')) {
                throw new sfValidatorError($this, 'invalid');
            }
            $produitLibelle = $this->drm->get($doublonHash)->getLibelle();
            throw new sfValidatorErrorSchema($this, array($doublonHash => new sfValidatorError($this, "$produitLibelle : Double transfert impossible")));
        }

        private function validateNoDoublon($values) {
            $volumeOrTavExist = array();
            foreach ($values as $keyField => $valueSubForm) {
              if(preg_match("/^sorties_/",$keyField)){
                foreach ($valueSubForm as $k_volOrTav => $volOrTav) {
                  $subFieldsValue = floatval($volOrTav['volume']);
                  if($subFieldsValue){
                    $keySplitted = explode('-',$k_volOrTav);
                    if(array_key_exists($keySplitted[1],$volumeOrTavExist)){
                      return $keySplitted[1];
                    }else{
                      $volumeOrTavExist[$keySplitted[1]] = $keySplitted[1];
                    }
                  }
                }
              }
            }
            return false;
        }

}
