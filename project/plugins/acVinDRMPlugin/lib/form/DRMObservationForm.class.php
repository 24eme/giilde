<?php

class DRMObservationForm extends BaseForm
{
	protected $_detail;

	public function __construct($detail, $options = array(), $CSRFSecret = null)
	{
		$this->_detail = $detail;
		parent::__construct($this->getDefaultValues(), $options, $CSRFSecret);
	}

    public function getDefaultValues() {
		$defaults = array();

		if($this->_detail->exist('observations')){
			$defaults['observations'] = $this->_detail->observations;
		}

		if($this->_detail->exist('replacement_date')){
			$defaults['replacement_date'] = $this->_detail->replacement_date;
		}

    	return  $defaults;
    }

		public function configure()
		{
			$w = array('observations' => new bsWidgetFormTextarea());
			$v = array('observations' => new sfValidatorString(array('required' => false)));
			$l = array('observations' => $this->_detail->getLibelle());

			if ($this->_detail->exist('replacement_date')) {
				$w['replacement_date'] = new bsWidgetFormInputDate();
				$v['replacement_date'] = new sfValidatorString(array('required' => false));
				$l['replacement_date'] = "Date de la sortie du volume correspondant à ce replacement";
			}
			$this->setWidgets($w);
			$this->setValidators($v);
			$this->widgetSchema->setLabels($l);
			$this->widgetSchema->setNameFormat('[%s]');
		}
}
