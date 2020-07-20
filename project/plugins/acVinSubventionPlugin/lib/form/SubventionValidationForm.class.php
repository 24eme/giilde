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

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $this->getObject()->validate();
    }
}
