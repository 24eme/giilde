<?php

class DRMDetailEntreesForm extends acCouchdbObjectForm {

    public function configure() {
        $configurationDetail = $this->getObject()->getParent()->getConfig();
        $certif = $this->getObject()->getParent()->getCertification()->getkey();
        $appellation = $this->getObject()->getParent()->getAppellation()->getkey();
        $drm = $this->getObject()->getDocument();
        $declassementIgp = (DRMConfiguration::getInstance()->hasDeclassementIgp())?  '/AOC/' :'/AOC|IGP/';
        foreach ($configurationDetail->getEntreesSorted() as $key => $value) {
            if ($value->readable) {
                if (!$value->writable || (preg_match($declassementIgp, $certif) && ($key == 'declassement'))
                    || (($certif == 'AUTRES') && ($key != 'recolte') && ($key != 'revendication') && ($key != 'transfertsrecolte'))
                    || (preg_match('/USAGESINDUSTRIELS/', $appellation) && (!$value->restriction_lies))
		                || (preg_match('/VINSSIG/', $certif) && $key == 'repli' && !preg_match("/déclassement/i", $value->getLibelle())))
                {
                    $this->setWidget($key, new bsWidgetFormInputFloat(array(), array('readonly' => 'readonly')));
                } else {
                    $this->setWidget($key, new bsWidgetFormInputFloat());
                }
                $this->setValidator($key, new sfValidatorNumber(array('required' => false, 'min' => 0), array('min' => "La saisie d'un nombre négatif est interdite")));
            }
        }
        $this->widgetSchema->setNameFormat('drm_detail_entrees[%s]');
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
    }

}
