<?php
class SubventionValidationForm extends acCouchdbObjectForm
{

	public function configure() {
        // $this->setWidget('commentaire', new sfWidgetFormTextarea(array(), array('style' => 'width: 100%;resize:none;')));
        // $this->setValidator('commentaire', new sfValidatorString(array('required' => false)));
        // $this->widgetSchema->setLabel('commentaire', 'Commentaires :');

        $this->widgetSchema->setNameFormat('validation[%s]');
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $this->getObject()->validate();
    }
}
