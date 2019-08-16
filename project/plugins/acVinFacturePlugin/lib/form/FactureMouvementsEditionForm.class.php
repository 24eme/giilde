<?php

/**
 * Description of FactureMouvementsEditionForm
 *
 * @author mathurin
 */
class FactureMouvementsEditionForm extends acCouchdbObjectForm {

    protected $interpro_id;
    protected $mvtId;

    public function __construct(\acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        $this->interpro_id = "INTERPRO-declaration";
        if(isset($options['interpro_id'])) {
            $this->interpro_id = $options['interpro_id'];
        }
        parent::__construct($object, $options, $CSRFSecret);

    }

    public function configure() {

        $this->setWidget("libelle", new sfWidgetFormInput());

        $this->setValidator("libelle", new sfValidatorString(array("required" => true)));

        $this->embedForm('mouvements', new FactureMouvementEditionLignesForm($this->getObject()->mouvements, array('interpro_id' => $this->interpro_id)));

        $this->widgetSchema->setNameFormat('facture_mouvements_edition[%s]');
        $this->widgetSchema->setLabel('libelle', 'Libellé opération');
    }

    public function getFormTemplate() {
        $mouvements = $this->getObject()->getOrAdd('mouvements');
        $uniqId = uniqid();
        $this->mvtId = 'nouveau_' . $uniqId;
        $mouvements->add('nouveau')->add($uniqId);
        $form_embed = new FactureMouvementEtablissementEditionLigneForm($mouvements, array('keyMvt' => $this->mvtId, 'interpro_id' => $this->interpro_id));
        $form = new FactureMouvementsCollectionTemplateForm($this, 'mouvements', $form_embed);
        return $form->getFormTemplate();
    }

    public function getNewMvtId() {
        return $this->mvtId;
    }

    public function bind(array $taintedValues = null, array $taintedFiles = null) {
        foreach ($this->embeddedForms as $key => $form) {
            if ($form instanceof FactureMouvementEditionLignesForm) {
                if (isset($taintedValues[$key])) {
                    $form->bind($taintedValues[$key], $taintedFiles[$key]);
                    $this->updateEmbedForm($key, $form);
                }
            }
        }
        parent::bind($taintedValues, $taintedFiles);
    }

    public function updateEmbedForm($name, $form) {
        $this->widgetSchema[$name] = $form->getWidgetSchema();
        $this->validatorSchema[$name] = $form->getValidatorSchema();
    }

    protected function doUpdateObject($values) {

        $this->getObject()->set('libelle', $values['libelle']);
        if (!$this->getObject()->getDate()) {
            $date = date('Y-m-d');
            $this->getObject()->set('date', $date);
            $this->getObject()->getOrAdd('valide')->set('date_saisie', $date);
        }

        $embeddedArrayKeys = array();
        foreach ($this->getEmbeddedForms() as $mouvementsKey => $mouvementsForm) {
            foreach ($mouvementsForm->getEmbeddedForms() as $keyMvt => $mvt) {
                $mvtValues = $values[$mouvementsKey][$keyMvt];
                $embeddedArrayKeys[] = $keyMvt;
                if ($mvtValues['identifiant'] && $mvtValues['quantite']) {
                    $societe = SocieteClient::getInstance()->find($mvtValues['identifiant']);
                    $societeIdentifiant = str_replace('SOCIETE-', '', $mvtValues['identifiant']);
                    $keys = explode('_', $keyMvt);
                    $idEtb = ($keys[0] == 'nouveau') ? $societeIdentifiant . '01' : $keys[0];
                    $mvtObj = $this->getObject()->getOrAdd('mouvements')->getOrAdd($idEtb)->getOrAdd($keys[1]);

                    $mvtObj['identifiant'] = $idEtb;
                    $mvtObj->updateIdentifiantAnalytique($mvtValues['identifiant_analytique']);
                    $mvtObj['libelle'] = $mvtValues['libelle'];
                    $mvtObj['quantite'] = floatval($mvtValues['quantite']);
                    $mvtObj['prix_unitaire'] = floatval($mvtValues['prix_unitaire']);

                    if (!$mvtObj->facture) {
                        $mvtObj->facture = 0;
                    }
                    $mvtObj->facturable = 1;
                    $mvtObj->region = ($societe->getRegionViticole(false))? $societe->getRegionViticole() : $societe->type_societe;
                    $mvtObj->date = $this->getObject()->getDate();
                }
            }
        }
        $mvtsToRemove = array();
        foreach ($this->getObject()->getOrAdd('mouvements') as $etbId => $mvtsEtb) {
            foreach ($mvtsEtb as $keyMvt => $mvt) {
                if ($etbId &&
                        !$mvt->facture &&
                        !in_array($etbId . '_' . $keyMvt, $embeddedArrayKeys) &&
                        !in_array('nouveau_' . $keyMvt, $embeddedArrayKeys)) {
                    $mvtsToRemove[] = $etbId . '_' . $keyMvt;
                }
            }
        }
        foreach ($mvtsToRemove as $keyToRemove) {
            $keys = explode('_', $keyToRemove);
            $this->getObject()->getOrAdd('mouvements')->getOrAdd($keys[0])->remove($keys[1]);
        }
        foreach ($this->getObject()->getOrAdd('mouvements') as $etbId => $mvtsEtb) {
            $cpt = 0;
            foreach ($mvtsEtb as $keyMvt => $mvt) {
                if ($etbId) {
                    $mvt->vrac_numero = 'MOUVEMENTLIBRE_ORDRE_' . sprintf("%03d", $cpt);
                    $cpt++;
                }
            }
        }
        $this->getObject()->save();
    }

    public function setDefaults($defaults) {
        parent::setDefaults($defaults);
        $date = Date::francizeDate($this->getObject()->getDate());
        $this->setDefault('date', $date);
    }

}
