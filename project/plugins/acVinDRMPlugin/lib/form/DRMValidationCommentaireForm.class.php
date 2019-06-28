<?php

class DRMValidationCommentaireForm extends acCouchdbObjectForm {

    protected static $transmission_ciel = array("1" => "Transmission");

    public function configure() {
      parent::configure();
      $this->setWidget('commentaire', new sfWidgetFormTextarea(array(), array('style' => 'width: 100%;resize:none;')));
      $this->setValidator('commentaire', new sfValidatorString(array('required' => false)));
      $this->widgetSchema->setLabel('commentaire', 'Commentaires :');

      $this->setWidget('email_transmission', new sfWidgetFormInputHidden());
      $this->setValidator('email_transmission', new sfValidatorString(array('required' => false)));
      $this->widgetSchema->setLabel('email_transmission', 'Email de transmission :');

      if (sfContext::getInstance()->getUser()->getCompte()->hasDroit(Roles::TELEDECLARATION_DOUANE) && !$this->getObject()->getDocument()->isNegoce()) {
            $this->setWidget('transmission_ciel', new sfWidgetFormInputHidden());
            $this->setValidator('transmission_ciel', new sfValidatorString(array('required' => false)));
            $this->widgetSchema->setLabel('transmission_ciel', 'Transmission pour préremplissage de votre DRM electronique sur le portail pro.douane.gouv.fr :');
       }

      $this->widgetSchema->setNameFormat('drm[%s]');
    }

}
