<?php

class DSNegoceProduitDetailStocksForm extends acCouchdbObjectForm {

    public function configure() {
	    	$this->setWidgets(array(
	    			'stock_initial_millesime_courant' => new bsWidgetFormInputFloat(),
    			  'stock_declare_millesime_courant' => new bsWidgetFormInputFloat(),
	    			'dont_vraclibre_millesime_courant' => new bsWidgetFormInputFloat(),
	    			'stock_declare_millesime_anterieur' => new bsWidgetFormInputFloat(),
	    			'dont_vraclibre_millesime_anterieur' => new bsWidgetFormInputFloat(),
	    	));

    	$this->setValidators(array(
          'stock_initial_millesime_courant' => new sfValidatorNumber(array('required' => false)),
          'stock_declare_millesime_courant' => new sfValidatorNumber(array('required' => false)),
          'dont_vraclibre_millesime_courant' => new sfValidatorNumber(array('required' => false)),
          'stock_declare_millesime_anterieur' => new sfValidatorNumber(array('required' => false)),
          'dont_vraclibre_millesime_anterieur' => new sfValidatorNumber(array('required' => false)),
    	));
        $this->widgetSchema->setNameFormat('stocks[%s]');
    }

}
