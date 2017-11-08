<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class SocieteEtablissementChoiceForm extends baseForm {

    protected $societe;

    public function __construct(Societe $societe, $defaults = array(), $options = array(), $CSRFSecret = null)
    {
        $this->societe = $societe;
        $this->etablissements = $this->societe->getEtablissementsObj(false);
        parent::__construct($defaults, $options, $CSRFSecret);
    }

    public function configure()
    {
            $this->setWidget('etablissementChoice', new bsWidgetFormChoice(array('choices' => $this->getEtablissements(true))));
            $this->setValidator('etablissementChoice', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getEtablissements()))));
            $this->widgetSchema->setLabel('etablissementChoice', 'Etablissement*:');
            $this->validatorSchema['etablissementChoice']->setMessage('invalid', 'Le choix d\'un établissement est obligatoire.');
            $this->widgetSchema->setNameFormat('vrac[%s]');
    }

    public function getEtablissements($include_libelle = false) {
        $etablissements = array();
        if($include_libelle){
            $etablissements['0'] = 'Choisir un établissement';
        }
        foreach ($this->etablissements as $key => $etablissementObj) {
            $etablissements[$etablissementObj->etablissement->identifiant] = $etablissementObj->etablissement->nom . ' - ' . $etablissementObj->etablissement->famille;
        }
        return $etablissements;
    }

}
