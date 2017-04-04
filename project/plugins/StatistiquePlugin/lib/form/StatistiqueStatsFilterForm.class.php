<?php
class StatistiqueStatsFilterForm extends BaseForm
{
	protected $config;
	protected static $rangeFields = array('doc.mouvements.date', 'doc.date_campagne');
	
	public function __construct($config, $defaults = array(), $options = array(), $CSRFSecret = null)
	{
		$this->config = $config;
		parent::__construct($defaults, $options, $CSRFSecret);
	}
  	
	public function configure() 
	{
		$this->setWidgets(array(
				'doc.mouvements.appellation' => new bsWidgetFormChoice(array('multiple' => true, 'choices' => self::getLibelles('appellation')), array('class' => 'select2 form-control')),
				'doc.declarant.famille' => new bsWidgetFormChoice(array('multiple' => true, 'expanded' => true, 'choices' => self::getFamilles())),
				'lastyear' => new bsWidgetFormInputCheckbox(),
				'pdf' => new bsWidgetFormInputCheckbox(),
				'doc.mouvements.date/from' => new bsWidgetFormInputDate(),
				'doc.mouvements.date/to' => new bsWidgetFormInputDate(),
				'statistiques' => new bsWidgetFormChoice(array('multiple' => false, 'expanded' => true, 'choices' => $this->getStatistiques())),
				'doc.appellation' => new bsWidgetFormChoice(array('multiple' => true, 'choices' => self::getLibelles('appellation')), array('class' => 'select2 form-control')),
				'doc.type_transaction' => new bsWidgetFormChoice(array('multiple' => true, 'expanded' => true, 'choices' => self::getTransactions())),
				'doc.date_campagne/from' => new bsWidgetFormInputDate(),
				'doc.date_campagne/to' => new bsWidgetFormInputDate(),
		));
		
		$this->widgetSchema->setLabels(array(
				'doc.mouvements.appellation' => 'Appellation',
				'doc.declarant.famille' => 'Catégorie',
				'lastyear' => 'Stat N/N-1',
				'pdf' => 'PDF',
				'statistiques' => 'Statistiques',
				'doc.appellation' => 'Appellation',
				'doc.type_transaction' => 'Conditionnement',
		));
		
		$this->setValidators(array(
				'doc.mouvements.appellation' => new sfValidatorChoice(array('required' => false, 'multiple' => true, 'choices' => array_keys(self::getLibelles('appellation')))),
				'doc.declarant.famille' => new sfValidatorChoice(array('required' => false, 'multiple' => true, 'choices' => array_keys(self::getFamilles()))),
				'doc.mouvements.date/from' => new sfValidatorDate(array('date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => false)),
				'doc.mouvements.date/to' => new sfValidatorDate(array('date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => false)),
				'lastyear' => new ValidatorBoolean(array('required' => false)),
				'pdf' => new ValidatorBoolean(array('required' => false)),
				'statistiques' => new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getStatistiques()))),
				'doc.appellation' => new sfValidatorChoice(array('required' => false, 'multiple' => true, 'choices' => array_keys(self::getLibelles('appellation')))),
				'doc.type_transaction' => new sfValidatorChoice(array('required' => false, 'multiple' => true, 'choices' => array_keys(self::getTransactions()))),
				'doc.date_campagne/from' => new sfValidatorDate(array('date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => false)),
				'doc.date_campagne/to' => new sfValidatorDate(array('date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => false)),
		));
		$this->setDefault('pdf', true);
        $this->widgetSchema->setNameFormat('statistique_filter[%s]');
    }
    
    public function getStatistiques() {
    	$statistiques = array();
    	foreach ($this->config['statistiques'] as $key => $value) {
    		$statistiques[$key] = $value['libelle'];
    	}
    	return $statistiques;
    }
    
    public static function getTransactions() {
    	$transactions = VracClient::$types_transaction_vins;
    	$libelles = VracClient::$types_transaction;
    	$result = array();
    	foreach ($transactions as $transaction) {
    		$result[$transaction] = $libelles[$transaction];
    	}
    	return $result;
    }
    
    public static function getFamilles() {
    	return EtablissementFamilles::getFamilles();
    }
    
    public static function getRegions() {
    	return array_merge(array(null => null), EtablissementClient::getRegions());
    }

    public static function getLibelles($noeud) {
        $libelles = array();
        $items = self::getItems($noeud);

        foreach($items as $key => $item) {
            $libelles[$key] = $item->getLibelle();
        }

        return $libelles;
    }

    public static function getItems($noeud) {

        return ConfigurationClient::getCurrent()->declaration->getKeys($noeud);
    }
    
    public function canPeriodeCompare()
    {
    	$values = $this->getValues();
    	return ($values['lastyear'] && ($values['doc.mouvements.date/from'] || $values['doc.date_campagne/from']))? true : false;
    }
    
    public function pdfFormat()
    {
    	$values = $this->getValues();
    	return ($values['pdf'])? true : false;
    }
    
    public function getAppellations()
    {
    	$values = $this->getValues();
    	$appellations = array();
    	$libelles = self::getLibelles('appellation');
    	$items = (isset($values['doc.mouvements.appellation']))? $values['doc.mouvements.appellation'] : array();
    	foreach ($items as $item) {
    		$appellations[] = $libelles[$item];
    	}
    	return $appellations;
    }
    
    public function getPeriode($format = 'd/m/Y')
    {
    	$values = $this->getValues();
    	$periode = array();
    	if ($from = $values['doc.mouvements.date/from']) {
    		$from = new DateTime($from);
    		$periode[0] = $from->format($format);
    		$periode[1] = date($format);
    	}
    	if ($to = $values['doc.mouvements.date/to']) {
    		$to = new DateTime($to);
    		$periode[1] = $to->format($format);
    	}
    	if ($from = $values['doc.date_campagne/from']) {
    		$from = new DateTime($from);
    		$periode[0] = $from->format($format);
    		$periode[1] = date($format);
    	}
    	if ($to = $values['doc.date_campagne/to']) {
    		$to = new DateTime($to);
    		$periode[1] = $to->format($format);
    	}
    	return $periode;
    }
    
    public function getValuesLastPeriode()
    {
    	$values = $this->getValues();
    	$from = ($values['doc.mouvements.date/from'])? new DateTime($values['doc.mouvements.date/from']) : new DateTime();
    	$to = ($values['doc.mouvements.date/to'])? new DateTime($values['doc.mouvements.date/to']) : new DateTime();
    	$from->modify('-1 year');
    	$to->modify('-1 year');
    	$values['doc.mouvements.date/from'] = $from->format('Y-m-d');
    	$values['doc.mouvements.date/to'] = $to->format('Y-m-d');
    	return $values;
    }
    
    public function processFilters($values = array())
    {
    	if (!$values) {
    		$values = $this->getValues();
    	}
    	if ($values['doc.mouvements.date/from'] || $values['doc.mouvements.date/to']) {
    		$values['doc.mouvements.date'] = array();
    		$values['doc.mouvements.date']['from'] = $values['doc.mouvements.date/from'];
    		$values['doc.mouvements.date']['to'] = $values['doc.mouvements.date/to'];
    	}
    	if ($values['doc.date_campagne/from'] || $values['doc.date_campagne/to']) {
    		$values['doc.date_campagne'] = array();
    		$values['doc.date_campagne']['from'] = $values['doc.date_campagne/from'];
    		$values['doc.date_campagne']['to'] = $values['doc.date_campagne/to'];
    	}
    	unset($values['statistiques'], $values['lastyear'], $values['pdf'], $values['doc.mouvements.date/from'], $values['doc.mouvements.date/to'], $values['doc.date_campagne/from'], $values['doc.date_campagne/to']);
    	$rangeFields = self::$rangeFields;
    	$nbFilters = 0;
    	$filters = array();
    	foreach ($values as $field => $value) {
    		if (in_array($field, $rangeFields)) {
    			$range = array('format' => 'yyyy-MM-dd');
    			if ($value['from']) {
    				$range['gte'] = $value['from'];
    			}
    			if ($value['to']) {
    				$range['lte'] = $value['to'];
    			}
    			$filters[] = array('range' => array($field => $range));
    			$nbFilters++;
    		} elseif ($value) {
    			$filter = (is_array($value))? 'terms' : 'term';
    			$filters[] = array($filter => array($field => $value));
    			$nbFilters++;
    		}
    	}
    	return ($nbFilters > 0)? ($nbFilters > 1)? array('filtered' => array('filter' => array('and' => $filters))) : array('filtered' => array('filter' => current($filters))) : null;
    }
    
    public function getStatistiquesConf()
    {
    	$values = $this->getValues();
    	return $this->config['statistiques'][$values['statistiques']];
    }
    
    protected function getPeriodeFromDate($date)
    {
    	if (!$date) {
    		return null;
    	}
    	$d = explode('/', $date);
    	return ($date)? sprintf('%s', date('Ym', strtotime($d[2].'-'.$d[1].'-'.$d[0]))) : null;
    }
    
   
}