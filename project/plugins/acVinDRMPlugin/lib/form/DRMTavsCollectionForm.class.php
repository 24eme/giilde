<?php

class DRMTavsCollectionForm extends BaseForm
{
        protected $drm;

    	public function __construct(DRM $drm, $options = array(), $CSRFSecret = null) {
        	$this->drm = $drm;
        	parent::__construct(array(), $options, $CSRFSecret);
    	}

        public function configure()
        {
			  foreach ($this->drm->getProduitsDetails() as $hash => $detail) {
        if($detail->exist('tav')){
          $this->embedForm($hash, new DRMTavForm($detail));
          }
			}
    }
}
