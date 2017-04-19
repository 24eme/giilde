<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DRMAnnexesForm
 *
 * @author mathurin
 */
class DRMAnnexesForm extends acCouchdbObjectForm {

    private $drm = null;
    private $detailsSortiesVrac = null;
    private $detailsSortiesExport = null;
    private $docTypesList = array();

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        $this->drm = $object;
        $this->getDocTypes();
        parent::__construct($this->drm, $options, $CSRFSecret);
    }

    public function configure() {
        foreach ($this->docTypesList as $docType) {
            $keyDebut = $docType . '_debut';
            $keyFin = $docType . '_fin';
            $keyNb = $docType . '_nb';

            $this->setWidget($keyDebut, new sfWidgetFormInputText());
            $this->setWidget($keyFin, new sfWidgetFormInputText());
            $this->setWidget($keyNb, new sfWidgetFormInputText());

            $this->setValidator($keyDebut, new sfValidatorString(array('required' => false)));
            $this->setValidator($keyFin, new sfValidatorString(array('required' => false)));
            $this->setValidator($keyNb, new sfValidatorString(array('required' => false)));

            $this->widgetSchema->setLabel($keyDebut, DRMClient::$drm_documents_daccompagnement[$docType] . ' début');
            $this->widgetSchema->setLabel($keyFin, DRMClient::$drm_documents_daccompagnement[$docType] . ' fin');
            $this->widgetSchema->setLabel($keyNb, DRMClient::$drm_documents_daccompagnement[$docType] . ' nombre de documents');
        }


        $observations = new DRMObservationsCollectionForm($this->drm);
        $this->embedForm('observationsProduits', $observations);


        $this->embedForm('releve_non_apurement', new DRMReleveNonApurementItemsForm($this->drm->getReleveNonApurement()));
        $this->widgetSchema->setNameFormat('drmAnnexesForm[%s]');


        $tavs = new DRMTavsCollectionForm($this->drm);
        $this->embedForm('tavsProduits', $tavs);

        $this->setWidget('paiement_douane_frequence', new bsWidgetFormChoice(array('expanded' => true, 'multiple' => false,'inline' => true,  'choices' => $this->getPaiementDouaneFrequence())));
        $this->setValidator('paiement_douane_frequence', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getPaiementDouaneFrequence())), array('required' => "Aucune fréquence de paiement des droits douane n'a été choisie")));
        $this->widgetSchema->setLabel('paiement_douane_frequence', 'Fréquence de paiement');


        $this->setWidget('statistiques_jus', new bsWidgetFormInputFloat());
        $this->setWidget('statistiques_mcr', new bsWidgetFormInputFloat());
        $this->setWidget('statistiques_vinaigre', new bsWidgetFormInputFloat());


        $this->widgetSchema->setLabel('statistiques_jus', 'Quantités de moûts de raisin transformées en jus de raisin');
        $this->widgetSchema->setLabel('statistiques_mcr', 'Quantités de moûts de raisin transformées en MCR');
        $this->widgetSchema->setLabel('statistiques_vinaigre', 'Quantités de moûts de raisin transformées en vinaigre');


        $this->setValidator('statistiques_jus', new sfValidatorNumber(array('required' => false)));
        $this->setValidator('statistiques_mcr', new sfValidatorNumber(array('required' => false)));
        $this->setValidator('statistiques_vinaigre', new sfValidatorNumber(array('required' => false)));

        foreach ($this->drm->getProduits() as $produit) {
            $genre = $produit->getConfig()->getGenre();
            if(!isset(DRMDroits::$correspondanceGenreKey[$genre->getKey()])) {
                continue;
            }
            $droit = $genre->getDroitDouane($this->drm->getFirstDayOfPeriode());
            $genreKey = DRMDroits::$correspondanceGenreKey[$genre->getKey()];
            $this->setWidget('cumul_' . $genreKey, new bsWidgetFormInputFloat());
            $this->setValidator('cumul_' . $genreKey, new sfValidatorNumber(array('required' => false)));

            $this->widgetSchema->setLabel('cumul_' . $genreKey, $droit->libelle . ' (' . $droit->code . ') :');
        }

    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        foreach ($this->docTypesList as $docType) {
            $this->drm->getOrAdd('documents_annexes')->getOrAdd($docType)->debut = $values[$docType . '_debut'];
            $this->drm->getOrAdd('documents_annexes')->getOrAdd($docType)->fin = $values[$docType . '_fin'];
            $this->drm->getOrAdd('documents_annexes')->getOrAdd($docType)->nb = $values[$docType . '_nb'];
        }

        foreach ($this->getEmbeddedForms() as $key => $releveNonApurementForm) {
          if($key!="observationsProduits" && $key!="tavsProduits"){
            $releveNonApurementForm->updateObject($values[$key]);
            }
        }

        $paiement_douane_frequence = $values['paiement_douane_frequence'];
        $this->drm->setPaiementDouaneFrequence($values['paiement_douane_frequence']);
        if ($paiement_douane_frequence == DRMPaiement::FREQUENCE_ANNUELLE) {
            foreach ($this->drm->getProduits() as $produit) {
                $genre = $produit->getConfig()->getGenre();
                if(!isset(DRMDroits::$correspondanceGenreKey[$genre->getKey()])) {
                    continue;
                }
                $genreKey = DRMDroits::$correspondanceGenreKey[$genre->getKey()];
                $localCumul = $values['cumul_' . $genreKey];

                if ($localCumul && $localCumul > 0) {
                    $this->drm->getOrAdd('droits')->getOrAdd('douane')->getOrAdd($genreKey)->set('report', $localCumul);
                }
            }
        }
        if ($observations = $values['observationsProduits']) {
          foreach ($observations as $hash => $observation) {
            $this->drm->addObservationProduit($hash, $observation['observations']);
            if (isset($observation['replacement'])) {
            $this->drm->addReplacementDateProduit($hash, $observation['replacement']);
            }
          }
        }
        if ($tavs = $values['tavsProduits']) {
          foreach ($tavs as $hash => $tav) {
            $this->drm->addTavProduit($hash, $tav['tav']);            
          }
        }

        $this->drm->declaratif->statistiques->jus = $values['statistiques_jus'];
        $this->drm->declaratif->statistiques->mcr = $values['statistiques_mcr'];
        $this->drm->declaratif->statistiques->vinaigre = $values['statistiques_vinaigre'];

        $this->drm->etape = DRMClient::ETAPE_VALIDATION;
        $this->drm->save();

        $societe = $this->drm->getEtablissement()->getSociete();
        $societe->add('paiement_douane_frequence', $paiement_douane_frequence);
        $societe->save();
    }

    public function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();

        if ($this->drm->exist('documents_annexes') && $this->drm->documents_annexes) {
            $annexesNode = $this->drm->documents_annexes;
            foreach ($this->docTypesList as $docType) {
                if ($annexesNode->exist($docType) && $annexesNode->{$docType}) {
                    $docNode = $annexesNode->{$docType};
                    $this->setDefault($docType . '_debut', $docNode->debut);
                    $this->setDefault($docType . '_fin', $docNode->fin);
                    $this->setDefault($docType . '_nb', $docNode->nb);
                }
            }
        }

        $societe = $this->drm->getEtablissement()->getSociete();
        if ($societe->exist('paiement_douane_frequence') && $societe->paiement_douane_frequence) {
            $this->setDefault('paiement_douane_frequence', $societe->paiement_douane_frequence);
            if ($societe->paiement_douane_frequence == DRMPaiement::FREQUENCE_ANNUELLE) {
                $droitsDouane = $this->drm->getOrAdd('droits')->getOrAdd('douane');
                foreach ($this->drm->getProduits() as $produit) {
                  $genre = $produit->getConfig()->getGenre();
                  if (isset(DRMDroits::$correspondanceGenreKey[$genre->getKey()])) {
                    $genreKey = DRMDroits::$correspondanceGenreKey[$genre->getKey()];
                    $this->setDefault('cumul_' . $genreKey, $droitsDouane->getOrAdd($genreKey)->get('report'));
                  }
                }
            }
        } else {
            $this->setDefault('paiement_douane_frequence', null);
        }

        $this->setDefault('statistiques_jus' , $this->drm->declaratif->statistiques->jus);
        $this->setDefault('statistiques_mcr' , $this->drm->declaratif->statistiques->mcr);
        $this->setDefault('statistiques_vinaigre' , $this->drm->declaratif->statistiques->vinaigre);
    }

    public function bind(array $taintedValues = null, array $taintedFiles = null) {
        foreach ($this->embeddedForms as $key => $form) {
            if ($form instanceof DRMReleveNonApurementItemsForm) {
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

    public function getFormTemplate() {
        $drm = new DRM();
        $form_embed = new DRMReleveNonApurementItemForm($drm->getOrAdd('releve_non_apurement')->add(), array('keyNonApurement' => uniqid()));
        $form = new DRMCollectionTemplateForm($this, 'releve_non_apurement', $form_embed);
        return $form->getFormTemplate();
    }

    public function getDocTypes() {


        $this->docTypesList = array();
        $this->docTypesList[] = DRMClient::DRM_DOCUMENTACCOMPAGNEMENT_DAADAC;
        $this->docTypesList[] = DRMClient::DRM_DOCUMENTACCOMPAGNEMENT_DSADSAC;


        $this->docTypesList[] = DRMClient::DRM_DOCUMENTACCOMPAGNEMENT_EMPREINTE;

        return $this->docTypesList;
    }

    public function getPaiementDouaneFrequence() {
        return DRMPaiement::$frequence_paiement_libelles;
    }

    public function getPaiementDouaneMoyen() {
        return DRMPaiement::$moyens_paiement_libelles;
    }

}
