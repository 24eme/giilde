<?php

class DRMLibellesForm extends acCouchdbForm {

	public function configure() {
		$details = $this->getObject()->getProduitsDetails(true);
		foreach ($details as $hash => $detail) {
			$this->setWidget($hash.'-libelle', new sfWidgetFormInput());
			$this->widgetSchema->setLabel($hash.'-libelle', $detail->getLibelle());
			$this->setValidator($hash.'-libelle', new sfValidatorString(array('required' => false)));
			if ($detail->hasLibelleModified()) {
				$this->setDefault($hash.'-libelle', $detail->produit_libelle);
			}
			$this->setWidget($hash.'-code', new sfWidgetFormInput());
			$this->widgetSchema->setLabel($hash.'-code', $detail->getCodeDouane());
			$this->setValidator($hash.'-code', new sfValidatorString(array('required' => false)));
			if ($detail->code_inao) {
				$this->setDefault($hash.'-code', $detail->code_inao);
			}
		}
		$this->widgetSchema->setNameFormat('drm_libelles[%s]');
	}
	
    public function save() {
        $values = $this->getValues();
        $drm = $this->getObject();
        foreach ($values as $hash_field => $value) {
			if (($hash = preg_replace('/\-libelle/', '', $hash_field)) && ($hash != $hash_field)) {
	        	if ($drm->exist($hash) && preg_match('/^\/declaration\/certifications\//', $hash) && $value) {
	        		$detail = $drm->get($hash);
	        		$detail->produit_libelle = $value;
	        	}
			}elseif (($hash = preg_replace('/\-code/', '', $hash_field)) && ($hash != $hash_field)) {
	        	if ($drm->exist($hash) && preg_match('/^\/declaration\/certifications\//', $hash) && $value) {
	        		$detail = $drm->get($hash);
	        		$detail->code_inao = $value;
	        	}
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
    	return count($this->getObject()->getProduitsDetails(true, $detailsKey)) > 0;
    }
	
	
}