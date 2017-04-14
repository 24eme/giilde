<?php

class ProduitDefinitionForm extends acCouchdbObjectForm {

    public function configure() {
        $this->setWidgets(array(
            'libelle' => new bsWidgetFormInput(),
            'format_libelle' => new bsWidgetFormInput(),
            'code' => new bsWidgetFormInput(),
        ));
        $this->widgetSchema->setLabels(array(
            'libelle' => 'Libellé :',
            'format_libelle' => 'Format du Libellé:',
            'code' => 'Code :',
        ));
        $this->setValidators(array(
            'libelle' => new sfValidatorString(array('required' => false), array('required' => 'Champ obligatoire')),
            'format_libelle' => new sfValidatorString(array('required' => false), array('required' => 'Champ obligatoire')),
            'code' => new sfValidatorString(array('required' => false), array('required' => 'Champ obligatoire')),
        ));

        $this->widgetSchema->setHelp('code', "Ce code est pour l'interpro (il en général identique à la clé sauf pour les couleurs)");

        if ($this->getObject()->hasCodes()) {
            $this->setWidget('code_produit', new bsWidgetFormInput());
            $this->setWidget('code_douane', new bsWidgetFormInput());
            $this->setWidget('code_comptable', new bsWidgetFormInput());

            $this->getWidget('code_produit')->setLabel("Code produit :");
            $this->getWidget('code_douane')->setLabel("Code douane :");
            $this->getWidget('code_comptable')->setLabel("Code comptable :");

            $this->setValidator('code_produit', new sfValidatorString(array('required' => false), array('required' => 'Champ obligatoire')));
            $this->setValidator('code_douane', new sfValidatorString(array('required' => false), array('required' => 'Champ obligatoire')));
            $this->setValidator('code_comptable', new sfValidatorString(array('required' => false), array('required' => 'Champ obligatoire')));
        }

        if ($this->getObject()->exist('densite')) {
            $this->setWidget('densite', new bsWidgetFormInput());
            $this->getWidget('densite')->setLabel("Densité :");
            $this->setValidator('densite', new sfValidatorString(array('required' => true), array('required' => 'Champ obligatoire')));
            $this->widgetSchema->setHelp('densite', "La densité par défaut est de 1.3, celle des crémant est de 1.5");
        }

        if ($this->getObject()->hasDepartements()) {
            $this->embedForm(
                    'secteurs', new ProduitDepartementCollectionForm(null, array('departements' => $this->getNoeudDepartement(), 'nb' => $this->getOption('nbDepartement', null)))
            );
        }
        if ($this->getObject()->hasDroit(ConfigurationDroits::DROIT_DOUANE)) {
            $nbDouane = (count($this->getNoeudDroit(ConfigurationDroits::DROIT_DOUANE)) > 0) ? count($this->getNoeudDroit(ConfigurationDroits::DROIT_DOUANE)) : 1;
            $this->embedForm(
                    'droit_douane', new ProduitDroitCollectionForm(null, array('droits' => $this->getNoeudDroit('douane'), 'nb' => $this->getOption('nbDouane', null)))
            );
        }
        if ($this->getObject()->hasDroit(ConfigurationDroits::DROIT_CVO)) {
            $nbCvo = (count($this->getNoeudDroit(ConfigurationDroits::DROIT_CVO)) > 0) ? count($this->getNoeudDroit(ConfigurationDroits::DROIT_CVO)) : 1;
            $this->embedForm(
                    'droit_cvo', new ProduitDroitCollectionForm(null, array('droits' => $this->getNoeudDroit(ConfigurationDroits::DROIT_CVO), 'nb' => $this->getOption('nbCvo', null)))
            );
        }
        if ($this->getObject()->hasLabels()) {
            $this->embedForm(
                    'labels', new ProduitLabelCollectionForm(null, array('labels' => $this->getNoeudLabel(), 'nb' => $this->getOption('nbLabel', null)))
            );
        }
        if ($this->getObject()->hasDetails()) {
            $this->embedForm(
                    'detail', new ProduitDetailsForm($this->getObject()->getOrAdd('detail'))
            );
        }

        $this->setWidget('produit_non_interpro', new bsWidgetFormInputCheckbox());
        $this->widgetSchema->setLabel('produit_non_interpro', 'Produit hors Interpro : ');
        $this->setValidator('produit_non_interpro', new sfValidatorString(array('required' => false)));
        // var_dump(get_class($this->getObject())); exit;
        if ($this->getObject()->hasCepagesAutorises()) {
          $this->setWidget('cepages_autorises', new bsWidgetFormTextarea());
          $this->widgetSchema->setLabel('cepages_autorises', 'Cepages autorisés : ');
          $this->setValidator('cepages_autorises', new sfValidatorString(array('required' => false)));
        }

        $this->widgetSchema->setNameFormat('produit_definition[%s]');
        $this->mergePostValidator(new ProduitDefinitionValidatorSchema($this->getObject()));
    }

    private function getNoeudInterpro($object = null) {
        if (!$object) {
            $object = $this->getObject();
        }
        return $object->interpro->getOrAdd('INTERPRO-declaration');
    }

    private function getNoeudDepartement($object = null) {
        if (!$object) {
            $object = $this->getObject();
        }
        return $object->getOrAdd('departements');
    }

    private function getNoeudDroit($type, $object = null) {
        if (!$object) {
            $object = $this->getObject();
        }
        return $this->getNoeudInterpro($object)->getOrAdd('droits')->getOrAdd($type);
    }

    private function getNoeudLabel($object = null) {
        if (!$object) {
            $object = $this->getObject();
        }
        return $this->getNoeudInterpro($object)->getOrAdd('labels');
    }

    private function getDatesCirculation($object = null) {
        if (!$object) {
            $object = $this->getObject();
        }
        return $this->getNoeudInterpro($object)->getOrAdd('dates_circulation');
    }

    private function setLabel($code, $libelle) {
        $labels = $this->getObject()->getDocument()->labels;
        if (!$labels->exist($code)) {
            $label = $labels->add($code, $libelle);
        }
    }

    private function setDroit($code, $libelle) {
        $droits = $this->getObject()->getDocument()->droits;
        if (!$droits->exist($code)) {
            $droit = $droits->add($code, $libelle);
            if (preg_match('/^([^_]+)_/', $code, $m)) {
                if (!$droits->exist($m[1])) {
                    $droit = $droits->add($m[1], null);
                }
            }
        }
    }

    private function setNodeDroit($droit, $date, $taux, $code, $libelle) {
        $droit->date = $date;
        $droit->taux = $taux;
        $droit->code = $code;
        $droit->libelle = $libelle;
    }

    private function addNodeDatesCirculation($circulationNode, $campagne, $date_debut, $date_fin) {
        $circulationNode->campagne = $campagne;
        $circulationNode->date_debut = $date_debut;
        $circulationNode->date_fin = $date_fin;
    }

    public function save($con = null) {
        $object = $this->getObject();
        $values = $this->getValues();
        $produit_non_interpro = isset($values['produit_non_interpro']) && $values['produit_non_interpro'];
        $this->getNoeudInterpro($object)->add('produit_non_interpro', $produit_non_interpro);
        $droit_cvo = $values['droit_cvo'];
        $droit_douane = $values['droit_douane'];
        if($this->getObject()->hasCepagesAutorises()){
          $this->getObject()->setCepagesAutorises($this->values['cepages_autorises']);
        }
        unset($this->values['produit_non_interpro']);
        unset($this->values['droit_cvo']);
        unset($this->values['droit_douane']);
        unset($this->values['cepages_autorises']);

        $object = parent::save($con);

        if ($object->hasDepartements()) {
            $object->remove('departements');
            $departements = $this->getNoeudDepartement($object);
            foreach ($values['secteurs'] as $value) {
                $departements->add(null, $value['departement']);
            }
        }
        if ($object->hasDroit(ConfigurationDroits::DROIT_DOUANE)) {
            $this->getNoeudInterpro($object)->droits->remove('douane');
            $this->getNoeudInterpro($object)->droits->add('douane',array());
            foreach ($droit_douane as $value) {

                $this->setDroit('DOUANE', 'Douane');
                $date = new DateTime($value['date']);

                $this->setNodeDroit($this->getNoeudDroit('douane', $object)->add(), $date->format('c'), $value['taux'], 'DOUANE', 'Douane');
            }
        }
        if ($object->hasDroit(ConfigurationDroits::DROIT_CVO)) {
            $this->getNoeudInterpro()->droits->remove('cvo');
            $this->getNoeudInterpro()->droits->add('cvo',array());
            foreach ($droit_cvo as $value) {

                $this->setDroit('CVO', 'Cvo');
                $date = new DateTime($value['date']);
                $this->setNodeDroit($this->getNoeudDroit('cvo', $object)->add(), $date->format('c'), $value['taux'], 'CVO', 'Cvo');
            }
        }
        if (array_key_exists('dates_circulation', $values)) {
            $this->getNoeudInterpro($object)->remove('dates_circulation');
            foreach ($values['dates_circulation'] as $campagneNode => $date_circulation) {
                $campagne = $date_circulation['campagne'];
                $date_debut = $date_circulation['date_debut'];
                $date_fin = $date_circulation['date_fin'];
                if ($campagne && $date_debut) {
                    $date_debut_arr = explode('/', $date_debut);
                    $date_debut = $date_debut_arr[2] . '-' . $date_debut_arr[1] . '-' . $date_debut_arr[0];

                    if ($date_fin) {
                        $date_fin_arr = explode('/', $date_fin);
                        $date_fin = $date_fin_arr[2] . '-' . $date_fin_arr[1] . '-' . $date_fin_arr[0];
                    }
                    $this->addNodeDatesCirculation($this->getDatesCirculation($object)->add($campagne, null), $campagne, $date_debut, $date_fin);
                }
            }
        }
        if ($object->hasLabels()) {
            $this->getNoeudInterpro($object)->remove('labels');
            $labels = $this->getNoeudLabel($object);
            foreach ($values['labels'] as $value) {
                $this->setLabel($value['code'], $value['label']);
                $labels->add(null, $value['code']);
            }
        }

        $object->getDocument()->save();
        return $object;
    }

    public function updateDefaultsFromObject() {
      parent::updateDefaultsFromObject();
      if($this->getObject()->hasCepagesAutorises()){
        $this->setDefault('cepages_autorises',implode(', ',$this->getObject()->cepages_autorises->toArray(true,false)));
      }

    }

}
