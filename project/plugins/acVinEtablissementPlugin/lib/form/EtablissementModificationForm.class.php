<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class SocieteModificationForm
 * @author mathurin
 */
class EtablissementModificationForm extends CompteGeneriqueForm {

    private $etablissement;
    private $liaisons_operateurs = null;

    public function __construct(Etablissement $etablissement, $options = array(), $CSRFSecret = null) {
        $this->etablissement = $etablissement;
        parent::__construct($this->etablissement, $options, $CSRFSecret);
    }

    public function configure() {
        parent::configure();

        $this->setWidget('famille', new bsWidgetFormChoice(array('choices' => $this->getFamilles())));
        $this->setWidget('nom', new bsWidgetFormInput());
        $this->setWidget('region', new bsWidgetFormChoice(array('choices' => self::getRegions())));
        $this->setWidget('nature_inao', new bsWidgetFormChoice(array('choices' => self::getNaturesInao())));
        $this->setWidget('no_accises', new bsWidgetFormInput());
        $this->setWidget('commentaire', new bsWidgetFormTextarea(array(), array('style' => 'width: 100%;resize:none;')));
        $this->setWidget('site_fiche', new bsWidgetFormInput());
        $this->setWidget('mois_stock_debut', new bsWidgetFormChoice(array('choices' => $this->getMonths())));


        $this->widgetSchema->setLabel('famille', 'Famille *');
        $this->widgetSchema->setLabel('nom', 'Nom du chai *');
        $this->widgetSchema->setLabel('nature_inao', 'Nature INAO *');
        $this->widgetSchema->setLabel('region', 'Région viticole *');
        $this->widgetSchema->setLabel('no_accises', "N° d'Accise");
        $this->widgetSchema->setLabel('commentaire', 'Commentaire');
        $this->widgetSchema->setLabel('site_fiche', 'Site Fiche Publique');
        $this->widgetSchema->setLabel('mois_stock_debut', 'Mois de début de declaration DRM');


        $this->setValidator('famille', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getFamilles()))));
        $this->setValidator('nom', new sfValidatorString(array('required' => true)));
        $this->setValidator('nature_inao', new sfValidatorChoice(array('required' => false, 'choices' => array_keys(self::getNaturesInao()))));
        $this->setValidator('region', new sfValidatorChoice(array('required' => true, 'choices' => array_keys(self::getRegions()))));
        $this->setValidator('site_fiche', new sfValidatorString(array('required' => false)));
        $this->setValidator('no_accises', new sfValidatorString(array('required' => false)));
        $this->setValidator('commentaire', new sfValidatorString(array('required' => false)));
        $this->setValidator('mois_stock_debut', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getMonths()))));

        if (!$this->etablissement->isCourtier()) {
            $this->setWidget('cvi', new bsWidgetFormInput());
            $this->widgetSchema->setLabel('cvi', 'CVI');
            $cviMsg = 'Le CVI doit impérativement être constitué de 10 chiffres';
            $this->setValidator('cvi', new sfValidatorString(array('required' => false, 'min_length' => 10, 'max_length' => 10),array('min_length' => $cviMsg, 'max_length' => $cviMsg)));
         } else {
            $this->setWidget('carte_pro', new bsWidgetFormInput());
            $this->widgetSchema->setLabel('carte_pro', 'N° Carte professionnelle');
            $this->setValidator('carte_pro', new sfValidatorString(array('required' => false)));
        }

        $this->widgetSchema->setNameFormat('etablissement_modification[%s]');
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
    }

    public function getFamilles()
    {
        return EtablissementFamilles::getFamillesByTypeSociete($this->getObject()->getSociete()->getTypeSociete());
    }

    public static function getRegions() {
        return EtablissementClient::getRegions();
    }

    public static function getNaturesInao() {
        return EtablissementClient::getNaturesInao();
    }

    public function getTypeDR() {
        return EtablissementClient::getTypeDR();
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);

        if (!$this->etablissement->isCourtier()) {
            $this->etablissement->setCvi($values['cvi']);
        } else {
            $this->etablissement->setCartePro($values['carte_pro']);
        }
        if (!$this->etablissement->exist('mois_stock_debut')) {
            $this->etablissement->add('mois_stock_debut', $values['mois_stock_debut']);
        } else {
            $this->etablissement->mois_stock_debut = $values['mois_stock_debut'];
        }
    }

    public function getMonths()
    {
      $dateFormat = new sfDateFormat('fr_FR');
      $results = array('' => '');
      for ($i = 1; $i <= 12; $i++) {
            $month = $dateFormat->format(date('Y').'-'.$i.'-01', 'MMMM');
            $results[$i] = $month;
      }
      return $results;
    }

    public function updateEmbedForm($name, $form) {
        $this->widgetSchema[$name] = $form->getWidgetSchema();
        $this->validatorSchema[$name] = $form->getValidatorSchema();
    }

    public function getFormTemplate() {
        $etablissement = new Etablissement();
        $form_embed = new LiaisonItemForm($etablissement->liaisons_operateurs->add());
        $form = new EtablissementCollectionTemplateForm($this, 'liaisons_operateurs', $form_embed);
        return $form->getFormTemplate();
    }

    protected function unembedForm($key) {
        unset($this->widgetSchema[$key]);
        unset($this->validatorSchema[$key]);
        unset($this->embeddedForms[$key]);
        $this->liaisons_operateurs->remove($key);
    }

}
