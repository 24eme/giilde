<?php
class SubventionValidationForm extends acCouchdbObjectForm
{
    protected $engagements;
    protected $engagementsPrecisions;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
    }

	public function configure() {
        $this->setWidget('commentaire', new sfWidgetFormTextarea(array(), array('style' => 'width: 100%;resize:none;')));
        $this->setValidator('commentaire', new sfValidatorString(array('required' => false)));
        $this->widgetSchema->setLabel('commentaire', 'Commentaires :');

        $this->widgetSchema->setNameFormat('validation[%s]');
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        $defaults = $this->getDefaults();
        foreach ($this->getObject()->engagements as $key => $value) {
            $defaults["engagement_$key"] = 1;
        }
        foreach ($this->getObject()->engagements_precisions as $key => $value) {
            $defaults["precision_engagement_$key"] = 1;
        }
        $this->setDefaults($defaults);
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $this->getObject()->validate();
	    foreach ($this->engagementsPrecisions as $eng => $precisions) {
	        foreach ($precisions as $k => $libelle) {
	            $key = "$eng/$k";
    	        if (isset($values["precision_engagement_$key"]) && $values["precision_engagement_$key"]) {
    	            $this->getObject()->engagements_precisions->add($key, 1);
    	        }
	        }
	    }
    }
}