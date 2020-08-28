<?php

class SubventionEngagementsForm extends acCouchdbForm {

    protected $engagements;

    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        $this->engagements = $doc->getConfiguration()->getEngagements();
        foreach ($doc->engagements as $key => $value) {
            $defaults["engagement_$key"] = 1;
        }
        parent::__construct($doc, $defaults, $options, $CSRFSecret);
    }

    public function getEngagements() {
        return $this->engagements;
    }

    public function configure() {
        foreach ($this->engagements as $key => $libelle) {
	        $this->setWidget("engagement_$key", new sfWidgetFormInputCheckbox());
	        $this->getWidget("engagement_$key")->setLabel($libelle);
	        $this->setValidator("engagement_$key", new sfValidatorBoolean(array('required' => true)));
	    }

        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
        $this->widgetSchema->setNameFormat('subvention_engagements[%s]');
    }

    public function save() {
        $values = $this->getValues();
        $this->getDocument()->remove('engagements');
        $this->getDocument()->add('engagements');
	    foreach ($this->engagements as $key => $libelle) {
	        if (isset($values["engagement_$key"]) && $values["engagement_$key"]) {
	            $this->getDocument()->engagements->add($key, true);
	        }
	    }

        $this->getDocument()->save();
    }

}
