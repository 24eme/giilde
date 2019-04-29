<?php

class DRMTavForm extends BaseForm
{
	protected $_detail;

	public function __construct($detail, $options = array(), $CSRFSecret = null)
	{
		$this->_detail = $detail;
		parent::__construct($this->getDefaultValues(), $options, $CSRFSecret);
	}

    public function getDefaultValues() {
			$defaults = array();
			if($this->_detail->exist('tav')){
    		$defaults = array('tav' => $this->_detail->tav);
			}
    	return  $defaults;
    }

	public function configure()
	{
		$readonly = array();
		$this->is_readonly = false;
		if($this->_detail->isAlcoolPur() || $this->_detail->hasStockFinDeMoisDRMPrecedente()){
			$readonly = array('readonly' => 'readonly');
		}
        $w = array('tav' => new bsWidgetFormInputFloat(array(), $readonly));
        $v = array('tav' => new sfValidatorNumber(array('required' => false, 'min' => 0), array('min' => "La saisie d'un nombre négatif est interdite")));
        $l = array('tav' => $this->_detail->getLibelle().' ('.$this->_detail->getTypeDRMLibelle().')');
        $this->setWidgets($w);
        $this->setValidators($v);
        $this->widgetSchema->setLabels($l);
        $this->widgetSchema->setNameFormat('[%s]');
	}
}
