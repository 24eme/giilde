<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class VracSoussigneForm
 * @author mathurin
 */
class VracSoussigneForm extends VracForm {

    private $vendeurs = null;
    private $acheteurs = null;
    private $mandataires = null;
    private $representant = null;
    protected $fromAnnuaire;
    protected $isAcheteurResponsable;
    protected $isVendeurResponsable;
    protected $isMandataireResponsable;
    protected $isRepresentantResponsable;
    protected $isTeledeclarationMode;
    private $types_contrat = array('1' => 'Oui', '0' => 'Non');
    private $types_responsable = array('vendeur' => 'Vendeur', 'acheteur' => 'Acheteur', 'mandataire' => 'Mandataire / Courtier', 'representant' => "Représentant");

    public function __construct(Vrac $object, $fromAnnuaire = false, $ajaxSearch = false, $options = array(), $CSRFSecret = null) {
        $this->fromAnnuaire = $fromAnnuaire;
        $this->isAcheteurResponsable = $object->isAcheteurResponsable();
        $this->isVendeurResponsable = $object->isVendeurResponsable();
        $this->isMandataireResponsable = $object->isMandataireResponsable();
        $this->isRepresentantResponsable = $object->isRepresentantResponsable();
        $this->isTeledeclarationMode = ($object->responsable);
        $this->ajaxSearch = $ajaxSearch;
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function getAnnuaire() {
        $createur_identifiant = $this->getObject()->createur_identifiant;
        $identifiantEtb = EtablissementClient::getInstance()->findByIdentifiant($createur_identifiant)->getEtablissementPrincipal()->identifiant;
        return AnnuaireClient::getInstance()->findOrCreateAnnuaireWithSuspendu($identifiantEtb);
    }

    public function configure() {
        $originalArray = array('0' => 'Non', '1' => 'Oui');
        $type = array(EtablissementFamilles::FAMILLE_PRODUCTEUR => 'Producteur', EtablissementFamilles::FAMILLE_NEGOCIANT => 'Négociant');
        if ($this->fromAnnuaire && $this->getObject()->createur_identifiant) {
            $vendeurs = $this->getRecoltants();
            $acheteurs = $this->getNegociants();
            $commerciaux = $this->getCommerciaux();
            $representants = $this->getRepresentants();

            if (!$this->isVendeurResponsable) {
                $this->setWidget('vendeur_identifiant', new bsWidgetFormChoice(array('choices' => $vendeurs), array('class' => 'autocomplete')));
                $this->setValidator('vendeur_identifiant', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($vendeurs))));
                $this->validatorSchema['vendeur_identifiant']->setMessage('required', 'Le choix d\'un vendeur est obligatoire');
            }

            if ($this->isAcheteurResponsable) {
                $acheteursChoiceValides[] = 'ETABLISSEMENT-' . $this->getObject()->createur_identifiant;
            } else {
                $acheteursChoiceValides = array_keys($acheteurs);
                $this->setValidator('acheteur_producteur', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($acheteurs))));
                $this->setValidator('acheteur_negociant', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($acheteurs))));
                $this->setWidget('acheteur_producteur', new bsWidgetFormChoice(array('choices' => $acheteurs), array('class' => 'autocomplete')));
                $this->setWidget('acheteur_negociant', new bsWidgetFormChoice(array('choices' => $acheteurs), array('class' => 'autocomplete')));
                $this->setWidget('acheteur_type', new bsWidgetFormChoice(array('expanded' => true, 'choices' => $type)));
                $this->setValidator('acheteur_type', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($type))));
                $this->setWidget('acheteur_identifiant', new bsWidgetFormChoice(array('choices' => $acheteurs), array('class' => 'autocomplete')));
                $this->setValidator('acheteur_identifiant', new sfValidatorChoice(array('required' => false, 'choices' => $acheteursChoiceValides)));
                $this->validatorSchema['acheteur_identifiant']->setMessage('required', 'Le choix d\'un acheteur est obligatoire');
                $this->validatorSchema->setPostValidator(new ValidatorVracSoussigne());
            }

            if(!$this->isRepresentantResponsable){
                $this->setWidget('representant_identifiant', new bsWidgetFormChoice(array('choices' => $representants), array('class' => 'autocomplete')));
                $this->setValidator('representant_identifiant', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($representants))));
            }

            $this->setWidget('commercial', new bsWidgetFormChoice(array('choices' => $commerciaux), array('class' => 'autocomplete')));
            $this->setValidator('commercial', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($commerciaux))));
            $this->widgetSchema->setLabel('commercial', 'Sélectionner un interlocuteur commercial :');
        } else {
            $this->setWidget('vendeur_identifiant', new WidgetEtablissement(array('interpro_id' => 'INTERPRO-declaration', 'familles' => array(EtablissementFamilles::FAMILLE_PRODUCTEUR, EtablissementFamilles::FAMILLE_NEGOCIANT))));
            $this->setValidator('vendeur_identifiant', new ValidatorEtablissement(array('required' => true)));
            $this->validatorSchema['vendeur_identifiant']->setMessage('required', 'Le choix d\'un vendeur est obligatoire');

            $this->setWidget('acheteur_producteur', new WidgetEtablissement(array('interpro_id' => 'INTERPRO-declaration', 'familles' => EtablissementFamilles::FAMILLE_PRODUCTEUR)));
            $this->setValidator('acheteur_producteur', new ValidatorEtablissement(array('required' => false, 'familles' => EtablissementFamilles::FAMILLE_PRODUCTEUR)));
            $this->setWidget('acheteur_negociant', new WidgetEtablissement(array('interpro_id' => 'INTERPRO-declaration', 'familles' => EtablissementFamilles::FAMILLE_NEGOCIANT)));
            $this->setValidator('acheteur_negociant', new ValidatorEtablissement(array('required' => false, 'familles' => EtablissementFamilles::FAMILLE_NEGOCIANT)));
            $this->setWidget('acheteur_type', new bsWidgetFormChoice(array('choices' => $type, 'expanded' => true)));
            $this->setValidator('acheteur_type', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($type))));

            $this->setWidget('representant_identifiant', new WidgetEtablissement(array('interpro_id' => 'INTERPRO-declaration', 'familles' => EtablissementFamilles::FAMILLE_REPRESENTANT)));
            $this->setValidator('representant_identifiant', new ValidatorEtablissement(array('required' => false, 'familles' => EtablissementFamilles::FAMILLE_REPRESENTANT)));

            $this->setWidget('mandataire_exist', new bsWidgetFormInputCheckbox());
            $this->setValidator('mandataire_exist', new sfValidatorBoolean(array('required' => false)));
            $this->setWidget('mandataire_identifiant', new WidgetEtablissement(array('interpro_id' => 'INTERPRO-declaration', 'familles' => EtablissementFamilles::FAMILLE_COURTIER)));
            $this->setValidator('mandataire_identifiant', new ValidatorEtablissement(array('required' => false, 'familles' => EtablissementFamilles::FAMILLE_COURTIER)));

            $this->validatorSchema->setPostValidator(new ValidatorVracSoussigne());
        }
        $this->setWidget('responsable', new bsWidgetFormChoice(array('choices' => $this->getTypesResponsable(), 'expanded' => true)));
        $this->setValidator('responsable', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getTypesResponsable()))));

        $this->setWidget('type_contrat', new bsWidgetFormChoice(array('choices' => $this->getTypesContrat(), 'expanded' => true)));

        $this->setWidget('type_transaction', new bsWidgetFormChoice(array('choices' => $this->getTypesTransaction(), 'expanded' => true)));
        $this->setWidget('interne', new bsWidgetFormInputCheckbox());
        $this->setWidget('vendeur_intermediaire', new bsWidgetFormInputCheckbox());
        $this->setWidget('logement_exist', new bsWidgetFormInputCheckbox());
        $this->setWidget('logement', new bsWidgetFormInput());
        $this->setWidget('vendeur_tva', new bsWidgetFormInputCheckbox());

        $this->setWidget('isVendeur', new sfWidgetFormInputHidden());
        $this->setValidator('isVendeur', new sfValidatorPass());

        $this->widgetSchema->setLabels(array(
            'type_transaction' => 'Type de transaction',
            'responsable' => 'Responsable du contrat :',
            'vendeur_famille' => '',
            'vendeur_identifiant' => 'Sélectionner un vendeur :',
            'representant_identifiant' => 'Sélectionner un representant :',
            'acheteur_famille' => '',
            'acheteur_identifiant' => 'Sélectionner un acheteur :',
            'interne' => 'Cocher si le contrat est interne',
            'mandataire_identifiant' => 'Sélectionner un courtier :',
            'mandataire_exist' => "Décocher s'il n'y a pas de courtier",
            'logement_exist' => "Vin logé à une autre adresse",
            'vendeur_intermediaire' => "Vendeur via intermedaire",
            'mandatant' => 'Mandaté par : ',
            'logement' => 'Ville : ',
            'type_contrat' => 'Contrat pluriannuel',
        ));
        $this->setValidator('type_transaction', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getTypesTransaction()))));
        $this->setValidator('interne', new sfValidatorBoolean(array('required' => false)));
        $this->setValidator('logement_exist', new sfValidatorBoolean(array('required' => false)));
        $this->setValidator('vendeur_intermediaire', new sfValidatorBoolean(array('required' => false)));
        $this->setValidator('logement', new sfValidatorString(array('required' => false)));
        $this->setValidator('type_contrat', new sfValidatorInteger(array('required' => true)));
        $this->setValidator('vendeur_tva', new sfValidatorBoolean(array('required' => false)));

      //  $this->validatorSchema['acheteur_producteur']->setMessage('required', 'Le choix d\'un acheteur est obligatoire');
      //  $this->validatorSchema['acheteur_negociant']->setMessage('required', 'Le choix d\'un acheteur est obligatoire');

        $this->unsetFields(VracConfiguration::getInstance()->getChampsSupprimes('soussigne', $this->getObject()->type_transaction));
        $this->widgetSchema->setNameFormat('vrac[%s]');
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        $defaults = $this->getDefaults();
        if (!$this->getObject()->type_transaction) {
            $defaults['type_transaction'] = 'VIN_VRAC';
        }
        if ($this->getObject()->vendeur_identifiant) {
            $defaults['vendeur_identifiant'] = 'ETABLISSEMENT-' . $this->getObject()->vendeur_identifiant;
        }
        if ($this->getObject()->representant_identifiant) {
            $defaults['representant_identifiant'] = 'ETABLISSEMENT-' . $this->getObject()->representant_identifiant;
        }
        $defaults['acheteur_type'] = EtablissementFamilles::FAMILLE_NEGOCIANT;

        if ($this->getObject()->acheteur_identifiant) {
            if ($this->getObject()->getAcheteurObject()->famille == EtablissementFamilles::FAMILLE_PRODUCTEUR) {
                $defaults['acheteur_producteur'] = 'ETABLISSEMENT-' . $this->getObject()->acheteur_identifiant;
                $defaults['acheteur_type'] = EtablissementFamilles::FAMILLE_PRODUCTEUR;
            } else {
                $defaults['acheteur_negociant'] = 'ETABLISSEMENT-' . $this->getObject()->acheteur_identifiant;
            }
        }
        if ($this->getObject()->mandataire_identifiant) {
            $defaults['mandataire_identifiant'] = 'ETABLISSEMENT-' . $this->getObject()->mandataire_identifiant;
        }
        if ($this->getObject()->interlocuteur_commercial->nom) {
            $defaults['commercial'] = $this->getObject()->interlocuteur_commercial->nom;
        }
        if ($this->getObject()->isNew()) {
            $defaults['mandataire_exist'] = false;
        }
        if (!$this->getObject()->isNew() && $this->getObject()->representant_identifiant && $this->getObject()->representant_identifiant != $this->getObject()->vendeur_identifiant) {
            $defaults['vendeur_intermediaire'] = true;
        } else {
            $defaults['vendeur_intermediaire'] = false;
            $defaults['representant_identifiant'] = null;
        }
        if($this->isRepresentantResponsable){
            $defaults['vendeur_intermediaire'] = true;
        }

        if (!$this->getObject()->isNew() && !$this->getObject()->mandataire_identifiant) {
            $defaults['mandataire_exist'] = false;
        }
        $defaults['logement_exist'] = false;
        if ($this->getObject()->logement) {
            $defaults['logement_exist'] = true;
        }
        if (!$this->getObject()->isNew() && $this->getObject()->type_contrat === VracClient::TYPE_CONTRAT_PLURIANNUEL) {
            $defaults['type_contrat'] = 1;
        } else {
            $defaults['type_contrat'] = 0;
        }

        if ($this->getObject()->vendeur_tva || is_null($this->getObject()->vendeur_tva)) {
            $defaults['vendeur_tva'] = true;
        } else {
            $defaults['vendeur_tva'] = false;
        }

        $defaults['isVendeur'] = $this->getObject()->isVendeurResponsable();

        $this->setDefaults($defaults);
    }

    public function doUpdateObject($values) {

        if (!isset($values['vendeur_intermediaire']) || !$values['vendeur_intermediaire']) {
            $values['representant_identifiant'] = null;
        }
        if (isset($values['vendeur_identifiant']) && !$values['representant_identifiant']) {
            $values['representant_identifiant'] = $values['vendeur_identifiant'];
        }
        if(!$this->isMandataireResponsable){
          if (!isset($values['mandataire_exist']) || !$values['mandataire_exist']) {
              $values['mandataire_identifiant'] = null;
              $values['mandatant'] = null;
          }
          if (!isset($values['mandataire_identifiant']) || !$values['mandataire_identifiant']) {
              $values['mandatant'] = null;
              $values['mandataire_exist'] = false;
          }
        }
        if (!isset($values['logement_exist']) || !$values['logement_exist']) {
            $values['logement'] = null;
        }
        if (isset($values['commercial']) && $values['commercial']) {
            $this->getObject()->storeInterlocuteurCommercialInformations($values['commercial'], $this->getAnnuaire()->commerciaux->get($values['commercial']));
        } else {
            $this->getObject()->remove('interlocuteur_commercial');
            $this->getObject()->add('interlocuteur_commercial');
        }
        parent::doUpdateObject($values);
        if (isset($values['acheteur_producteur']) || isset($values['acheteur_negociant'])){
          if($values['acheteur_type'] == EtablissementFamilles::FAMILLE_PRODUCTEUR) {
              $this->getObject()->acheteur_identifiant = $values['acheteur_producteur'];
          } else {
              $this->getObject()->acheteur_identifiant = $values['acheteur_negociant'];
          }
        }
        if (isset($values['type_contrat']) && $values['type_contrat']) {
            $this->getObject()->type_contrat = VracClient::TYPE_CONTRAT_PLURIANNUEL;
        } else {
            $this->getObject()->type_contrat = null;
        }
        if (!isset($values['vendeur_tva']) || !$values['vendeur_tva']) {
            $this->getObject()->vendeur_tva = 0;
        }
        if (isset($values['vendeur_tva']) && $values['vendeur_tva']) {
            $this->getObject()->vendeur_tva = 1;
        }
        if (isset($values['isVendeur']) && $values['isVendeur']) {
            $this->getObject()->responsable = Vrac::VRAC_RESPONSABLE_VENDEUR;
        }
        $this->getObject()->setInformations();
    }

    public function getUrlAutocomplete($famille) {

        return sfContext::getInstance()->getRouting()->generate('etablissement_autocomplete_byfamilles', array('familles' => $famille));
    }

    public function getTypesTransaction() {

        return VracConfiguration::getInstance()->getTransactions();
    }

    public function getRecoltants() {
        $annuaire = $this->getAnnuaire();
        if (!$annuaire) {
            return array();
        }
        $result = array();
        foreach ($annuaire->recoltants as $key => $value) {
            if ($value->isActif) {
                $num = explode('-', $key);
                $result[$key] = $value->name . " (" . $num[1] . ")";
            }
        }
        return array_merge(array('' => ''),array('AJOUT' => 'Ajouter un récoltant'), $result);
    }

    public function getNegociants() {
        $annuaire = $this->getAnnuaire();
        if (!$annuaire) {
            return array();
        }
        $result = array();
        foreach ($annuaire->negociants as $key => $value) {
            if ($value->isActif) {
                $num = explode('-', $key);
                $result[$key] = $value->name . " (" . $num[1] . ")";
            }
        }
        return array_merge(array('' => ''),array('AJOUT' => 'Ajouter un négociant'), $result);
    }

    public function getRepresentants() {
        $annuaire = $this->getAnnuaire();
        if (!$annuaire) {
            return array();
        }
        $result = array();
        foreach ($annuaire->representants as $key => $value) {
            if ($value->isActif) {
                $num = explode('-', $key);
                $result[$key] = $value->name . " (" . $num[1] . ")";
            }
        }
        return array_merge(array('' => ''),array('AJOUT' => 'Ajouter un représentant'), $result);
    }

    public function getCommerciaux() {
        $annuaire = $this->getAnnuaire();
        if (!$annuaire) {
            return array();
        }
        $commerciaux = $annuaire->commerciaux->toArray();
        $choices = array();
        foreach ($commerciaux as $key => $commercial) {
            $choices[$key] = $key;
        }
        return array_merge(array('' => ''),array('AJOUT' => 'Ajouter un courtier'), $choices);
    }

    public function getTypesContrat() {
        return $this->types_contrat;
    }

    public function getTypesResponsable() {
        return $this->types_responsable;
    }

}
