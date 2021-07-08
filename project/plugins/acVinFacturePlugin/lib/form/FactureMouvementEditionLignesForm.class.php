<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FactureMouvementEditionLignesForm
 *
 * @author mathurin
 */
class FactureMouvementEditionLignesForm extends acCouchdbObjectForm {

    protected $interpro_id;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        $this->interpro_id = $options['interpro_id'];
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
        foreach ($this->getObject() as $identifiantKey => $mvts) {
            foreach ($mvts as $uniqKey => $mvt) {
                $mvtId = $identifiantKey . '_' . $uniqKey;
                $this->embedForm($mvtId, new FactureMouvementEtablissementEditionLigneForm($mvt, array('interpro_id' => $this->interpro_id, 'keyMvt' => $mvtId)));
            }
        }
        $this->widgetSchema->setNameFormat('facture_mouvement_edition_ligne[%s]');
        $this->validatorSchema->setOption('allow_extra_fields', true);
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);

    }

    public function bind(array $taintedValues = null, array $taintedFiles = null) {
        foreach ($this->embeddedForms as $key => $form) {
            if (!array_key_exists($key, $taintedValues)) {
                $this->unEmbedForm($key);
                unset($taintedValues[$key]);
            }
        }
        foreach ($taintedValues as $key => $values) {

            if (!is_array($values) || array_key_exists($key, $this->embeddedForms)) {
                continue;
            }

            if (preg_match('/^nouveau_/', $key)) {
                $identifiant = false;
                foreach ($values as $keyValue => $value) {
                    if ($keyValue == 'identifiant') {
                        if ($value && SocieteClient::getInstance()->find($value) && $values['quantite']) {
                            $identifiant = str_replace("SOCIETE-", "", $values['identifiant']) . Societe::get01PostfixEtablissementIfExist();
                        }
                    }
                }

                $keyMvt = str_replace("nouveau_", "", $key);
                $mouvement = $this->getObject()->getOrAdd($identifiant)->getOrAdd($keyMvt);
                $this->embedForm($key, new FactureMouvementEtablissementEditionLigneForm($mouvement, array('interpro_id' => $this->interpro_id, 'keyMvt' => $key)));
            }
        }
        $nodes_to_remove = array();
        foreach ($taintedValues as $key => $values) {
            if (array_key_exists($key, $this->embeddedForms)) {
                foreach ($values as $keyValue => $value) {
                    if ($keyValue == 'identifiant') {
                        $keyEmbedded = explode('_', $key);
                        if (($keyEmbedded[0] != str_replace('SOCIETE-', '', $value) . Societe::get01PostfixEtablissementIfExist()) && $keyEmbedded[0] != "nouveau") {

                            if ($value && SocieteClient::getInstance()->find($value) && $values['quantite']) {
                                $identifiant = str_replace("SOCIETE-", "", $values['identifiant']) . Societe::get01PostfixEtablissementIfExist();

                                $keyMvt = $keyEmbedded[1];
                                $newKey = $identifiant . '_' . $keyMvt;

                                $mouvementCloned = clone $this->getObject()->getOrAdd($keyEmbedded[0])->get($keyEmbedded[1]);
                                $mouvementCloned->identifiant = str_replace("SOCIETE-", "", $values['identifiant']) . Societe::get01PostfixEtablissementIfExist();

                                $mouvement = $this->getObject()->getOrAdd($mouvementCloned->identifiant)->add($keyMvt, $mouvementCloned);

                                $this->embedForm($newKey, new FactureMouvementEtablissementEditionLigneForm($mouvement, array('interpro_id' => $this->interpro_id, 'keyMvt' => $newKey)));
                                $taintedValues[$newKey] = $taintedValues[$key];
                                $this->validatorSchema[$newKey] = $this->validatorSchema[$key];
                                $this->widgetSchema[$newKey] = $this->widgetSchema[$key];

                                $nodes_to_remove[] = $key;
                            }
                        }
                    }
                }
            }
        }

        foreach ($nodes_to_remove as $nodeToRemoveKey) {
            $keyEmbedded = explode('_', $nodeToRemoveKey);
            $this->unEmbedFormAndRemoveNode($keyEmbedded[0], $keyEmbedded[1], $taintedValues);
        }
        return parent::bind($taintedValues, $taintedFiles);
    }

    public function unEmbedForm($key) {
        unset($this->widgetSchema[$key]);
        unset($this->validatorSchema[$key]);
        unset($this->embeddedForms[$key]);
    }

    public function unEmbedFormAndRemoveNode($socId, $uniqkey, &$taintedValues) {
        $this->getObject()->getOrAdd($socId)->remove($uniqkey);
        if (!count($this->getObject()->getOrAdd($socId))) {
            $this->getObject()->remove($socId);
        }
        $key = $socId . '_' . $uniqkey;
        unset($this->widgetSchema[$key]);
        unset($this->validatorSchema[$key]);
        unset($this->embeddedForms[$key]);
        unset($taintedValues[$key]);
    }

}
