<?php

class FactureMouvementEtablissementEditionLigneForm extends acCouchdbObjectForm {

    protected $interpro_id;
    protected $keyMvt = null;
    protected $isreadonly = array();

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        $this->interpro_id = $options['interpro_id'];
        $this->keyMvt = $options['keyMvt'];
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {

        if ($this->getObject() && ($this->getObject() instanceof FactureMouvement)
                && $this->getObject()->exist('facture') && $this->getObject()->facture) {
            $this->isreadonly = array('readonly' => 'readonly');
        }

        $this->setWidget('identifiant', new WidgetSociete(array('interpro_id' => $this->interpro_id, 'type_societe' => array(SocieteClient::TYPE_OPERATEUR,SocieteClient::TYPE_AUTRE)), $this->isreadonly));
        $this->setWidget("libelle", new bsWidgetFormInput(array(), $this->isreadonly));
        $this->setWidget("quantite", new bsWidgetFormInputFloat(array(), $this->isreadonly));
        $this->setWidget("prix_unitaire", new bsWidgetFormInputFloat(array(), $this->isreadonly));

        if ($this->getObject() && ($this->getObject() instanceof FactureMouvement)
                && $this->getObject()->exist('facture') && $this->getObject()->facture) {
            $this->setWidget("identifiant_analytique", new sfWidgetFormInputHidden());
        } else {
            $this->setWidget("identifiant_analytique", new sfWidgetFormChoice(array('choices' => $this->getIdentifiantsAnalytiques())));
        }


        $this->setValidator('identifiant', new ValidatorSociete(array('required' => false)));
        $this->setValidator("identifiant_analytique", new sfValidatorChoice(array('choices' => array_keys($this->getIdentifiantsAnalytiques()), 'required' => false)));
        $this->setValidator("libelle", new sfValidatorString(array('required' => false)));
        $this->setValidator("quantite", new sfValidatorNumber(array('required' => false)));
        $this->setValidator("prix_unitaire", new sfValidatorNumber(array('required' => false)));


        $this->configureTypeSociete(array(SocieteClient::TYPE_OPERATEUR));
        $this->widgetSchema->setNameFormat('facture_mouvement_etablissement_edition_ligne[%s]');
    }

    public function setDefaults($defaults) {
        parent::setDefaults($defaults);
        if ($this->getObject() && $this->getObject() instanceof FactureMouvement) {
            if ($this->getObject()->getIdentifiant()) {

                $identifiantSociete = preg_replace('/([0-9]{6})([0-9]{2})/', '\1', $this->getObject()->getIdentifiant());
                $societe = SocieteClient::getInstance()->findByIdentifiantSociete($identifiantSociete);

                $this->setDefault('identifiant', "SOCIETE-" . $societe->identifiant); // "SOCIETE-" . $societe->identifiant . ',' . $societe->raison_sociale . ' ' . $societe->identifiant . ' / ' . $societe->siege->commune . ' ' . $societe->siege->code_postal . ' (Société)');
            }
        }
        if (array_key_exists('quantite', $defaults) && $defaults['quantite']) {
            $this->setDefault('quantite', $defaults['quantite']);
        }
    }

    public function configureTypeSociete($types) {

        $this->getWidget('identifiant')->setOption('type_societe', $types);
        $this->getValidator('identifiant')->setOption('type_societe', $types);
    }

    public function getSociete() {
        return $this->getValidator('identifiant')->getDocument();
    }

    public function getIdentifiantsAnalytiques() {
        return ComptabiliteClient::getInstance()->findCompta()->getAllIdentifiantsAnalytiquesArrayForCompta();
    }

    public function isReadonly() {
        return $this->isreadonly;
    }

}
