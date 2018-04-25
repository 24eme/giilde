<?php

class DRMLibellesForm extends acCouchdbForm {

	public function configure() {
		$details = $this->getObject()->getProduitsDetails(true);
		foreach ($details as $hash => $detail) {
			$this->setWidget($hash, new sfWidgetFormInput());
			$this->widgetSchema->setLabel($hash, $detail->getLibelle());
			$this->setValidator($hash, new sfValidatorString(array('required' => false)));
			if ($detail->hasLibelleModified()) {
				$this->setDefault($hash, $detail->produit_libelle);
			}
		}
		$this->widgetSchema->setNameFormat('drm_libelles[%s]');
	}
	
    public function save() {
        $values = $this->getValues();
        $drm = $this->getObject();
        foreach ($values as $hash => $value) {
        	if ($drm->exist($hash) && preg_match('/^\/declaration\/certifications\//', $hash) && $value) {
        		$detail = $drm->get($hash);
        		$detail->produit_libelle = $value;
        	}
        }
        $drm->save();
        return $drm;
    }
    
    public function getObject() {
    	return $this->getDocument();
    }
    
    public function hasDetailsKey($detailsKey)
    {
    	return count($this->getObject()->getProduitsDetails(true, $detailsKey) > 0);
    }
	
	
}