<?php
class SubventionValidationForm extends acCouchdbObjectForm
{
	public function configure() {
	    
	    $engagements = sfConfig::get('subvention_configuration_engagements');
	    foreach ($engagements as $key => $libelle) {
	        $this->setWidget("engagement_$key", new sfWidgetFormInputCheckbox());
	        $this->getWidget("engagement_$key")->setLabel($libelle);
	        $this->setValidator("engagement_$key", new sfValidatorBoolean(array('required' => true)));
	    }
        $this->setWidget('commentaire', new sfWidgetFormTextarea(array(), array('style' => 'width: 100%;resize:none;')));
        $this->setValidator('commentaire', new sfValidatorString(array('required' => false)));
        $this->widgetSchema->setLabel('commentaire', 'Commentaires :');
        
        $this->widgetSchema->setNameFormat('validation[%s]');
    }
}