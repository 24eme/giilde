<?php
class StatistiqueFilterForm extends BaseForm
{
	
	protected $filters;
	protected $collapseIn;
	

	public function __construct($filters = null, $defaults = array(), $options = array(), $CSRFSecret = null)
	{
		$this->filters = $filters;
		$this->collapseIn = false;
		parent::__construct($defaults, $options, $CSRFSecret);
	}
  	
	public function configure() 
	{
		$this->setWidgets(array(
				'q' => new sfWidgetFormInputText()
		));
		
		$this->widgetSchema->setLabels(array(
				'q' => 'Rechercher'
		));
		
		$this->setValidators(array(
				'q' => new sfValidatorString(array('required' => false))
		));
		$this->embedForm('advanced', new StatistiqueAdvancedFiltersForm($this->filters));
        $this->widgetSchema->setNameFormat('statistique_filter[%s]');
    }
    
    public function getDefaultQuery()
    {
    	$query_string = new acElasticaQueryQueryString('*');
    	return $query_string;
    }
    
    public function getQuery()
    {
    	$values = $this->getValues();
    	$query = $values['q'];
    	if (isset($values['advanced']) && count($values['advanced']) > 0) {
    		foreach ($values['advanced'] as $filter) {
    			if ($filter['fk']) {
    				$this->collapseIn = true;
    				$query = $query . ' ' . $filter['fk'] . ':' . $filter['fo'] . $filter['fv'];
    			}
    		}
    	}
    	$q = ($query)? $query : '*';
    	$query_string = new acElasticaQueryQueryString($q);
    	return $query_string;
    }
    
    public function getCollapseIn()
    {
    	return $this->collapseIn;
    }
    
    public function getParameters()
    {
    	$values = $this->getValues();

    	$parameters = array();
    	$parameters[$this->getName().'[q]'] = $values['q'];
    	
    	if (isset($values['advanced']) && count($values['advanced']) > 0) {
    		foreach ($values['advanced'] as $k => $v) {
    			foreach ($v as $sk => $sv) {
    				$parameters[$this->getName().'[advanced]['.$k.']['.$sk.']'] = $sv;
    			}
    		}
    		
    	}
    	return $parameters;
    }

    public function getFormTemplate() 
    {
    	$uniqId = uniqid();
    	$form_embed = new StatistiqueAdvancedFilterForm($this->filters);
    	$form = new StatistiqueAdvancedFilterTemplateForm($this, 'advanced', $form_embed);
    	return $form->getFormTemplate();
    }

    public function bind(array $taintedValues = null, array $taintedFiles = null)
    {
    	foreach ($this->embeddedForms as $key => $form) {
    		if (isset($taintedValues[$key])) {
    			$form->bind($taintedValues[$key], $taintedFiles[$key]);
    			$this->updateEmbedForm($key, $form);
    		}
    	}
    	parent::bind($taintedValues, $taintedFiles);
    }
    
    public function updateEmbedForm($name, $form) 
    {
    	$this->widgetSchema[$name] = $form->getWidgetSchema();
    	$this->validatorSchema[$name] = $form->getValidatorSchema();
    }
}