<?php

class FactureMouvementEditionLigneForm extends acCouchdbObjectForm {

    protected $interpro_id;
    protected $uniqkeyMvt;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        $this->interpro_id = $options['interpro_id'];
        $this->uniqkeyMvt = $options['uniqkeyMvt'];
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
        parent::configure();

        $this->widgetSchema->setNameFormat('facture_mouvement_edition_ligne[%s]');
        //  $this->validatorSchema->setPreValidator(new FactureMouvementsEditionValidator());
    }

    public function setDefaults($defaults) {
        parent::setDefaults($defaults);
        if (array_key_exists('identifiant', $defaults) && $defaults['identifiant']) {
            $societe = SocieteClient::getInstance()->find($defaults['identifiant']);
            $this->setDefault('identifiant', $defaults['identifiant'] . ',' . $societe->raison_sociale . ' ' . $societe->identifiant . ' / ' . $societe->siege->commune . ' ' . $societe->siege->code_postal . ' (Société)');
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
        return  ComptabiliteClient::getInstance()->findCompta()->getAllIdentifiantsAnalytiquesArrayForCompta();
    }

    public function doUpdateObject($values) {
    }

}
