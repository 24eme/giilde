<?php

class CompteTeledeclarantForm extends acCouchdbForm {

    public function __construct($doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        $societe = $doc->getSociete();

        $defaultEmail = null;
        if($societe->isTransaction()){
            $defaultEmail = $societe->getEtablissementPrincipal()->getEmailTeledeclaration();
        }else{
            $defaultEmail = $societe->getEmailTeledeclaration();
        }
        if(!$defaultEmail){
            $defaultEmail = $societe->email;
        }

        $defaults['email'] = $defaultEmail;

        parent::__construct($doc, $defaults, $options, $CSRFSecret);
    }

    public function configure() {
        $this->setWidgets(array(
            'email' => new sfWidgetFormInputText(),
            'mdp1' => new sfWidgetFormInputPassword(),
            'mdp2' => new sfWidgetFormInputPassword()
        ));

        $this->widgetSchema->setLabels(array(
            'email' => 'Adresse e-mail* : ',
            'mdp1' => 'Mot de passe* : ',
            'mdp2' => 'Vérification du mot de passe* : '
        ));

        $this->widgetSchema->setNameFormat('ac_vin_compte[%s]');

        $this->setValidator('email', new sfValidatorEmail(array('required' => true), array('required' => 'Champ obligatoire', 'invalid' => 'Adresse email invalide.')));
        $mdpValidator = new sfValidatorRegex(array('required' => false,
            'pattern' => "/^[^éèçùà€£µôûîêâöüïë§]+$/i",
            'min_length' => 8), array('required' => 'Le mot de passe est obligatoire',
            'invalid' => 'Le mot de passe doit être constitué de caractères non accentués',
            'min_length' => 'Le mot de passe doit être constitué de 8 caractères min.'));

        $this->setValidator('mdp1', $mdpValidator);
        $this->setValidator('mdp2', $mdpValidator);

        $this->validatorSchema->setPostValidator(new sfValidatorSchemaCompare('mdp1', sfValidatorSchemaCompare::EQUAL, 'mdp2', array(), array('invalid' => 'Les mots de passe doivent être identique.')));
    }

    public function save() {
        if (!$this->isValid())
        {
          throw $this->getErrorSchema();
        }

        if ($this->getValue('mdp1')) {
            $this->getDocument()->setMotDePasseSSHA($this->getValue('mdp1'));
        }

        $this->getDocument()->add('teledeclaration_active', true);
        $this->getDocument()->save();

        $email = $this->getValue('email');

        if(!$email) {
            return;
        }

        SocieteClient::getInstance()->clearSingleton();
        $societe = SocieteClient::getInstance()->find($this->getDocument()->id_societe);

        if ($societe->isTransaction()) {
            $allEtablissements = $societe->getEtablissementsObj();
            foreach ($allEtablissements as $etablissementObj) {
                $etb = $etablissementObj->etablissement;
                if ((!$etb->exist('email') || !$etb->email) && !$etb->isSameContactThanSociete()) {
                    $etb->email = $email;
                }
                $etb->add('teledeclaration_email', $email);
                $etb->save();
            }
        }

        if (!$societe->isTransaction()) {
            $societe->add('teledeclaration_email', $email);
            $societe->save();
        }

        if(!$societe->email) {
            $societe->email = $email;
            $societe->save();
        }
    }
}
