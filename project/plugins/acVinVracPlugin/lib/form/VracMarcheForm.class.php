<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class VracSoussigneForm
 * @author mathurin
 */
class VracMarcheForm extends VracForm {

    protected $_choices_produits;
    protected $_choices_cepages;
    protected $actual_campagne;
    protected $next_campagne;
    protected $isTeledeclarationMode;
    protected $defaultDomaine;

    const NONMILLESIMELABEL = "Non millésimé";

    public function __construct(Vrac $vrac, $isTeledeclarationMode = false, $defaultDomaine = null, $options = array(), $CSRFSecret = null) {
        $this->isTeledeclarationMode = $isTeledeclarationMode;
        $this->defaultDomaine = $defaultDomaine;
        parent::__construct($vrac, $options, $CSRFSecret);
    }

    public function configure() {
        $this->actual_campagne = ConfigurationClient::getInstance()->getCurrentCampagne();
        $this->next_campagne = (date('Y') > substr($this->actual_campagne, 0, 4) && date('m') > 3) ? ConfigurationClient::getInstance()->getNextCampagne($this->actual_campagne) : $this->actual_campagne;

        $originalArray = array('0' => 'Non', '1' => 'Oui');

        $this->getDomaines();
        $this->getMillesimes();
        $contenances = $this->getContenances();

        $this->setWidget('label', new bsWidgetFormChoice(array('choices' => $this->getLabels(), 'multiple' => true, 'expanded' => true)));
        $this->setWidget('produit', new bsWidgetFormChoice(array('choices' => $this->getProduits()), array('class' => 'autocomplete')));
        $this->setWidget('cepage', new bsWidgetFormChoice(array('choices' => $this->getCepages()), array('class' => 'autocomplete')));
        $this->setWidget('millesime', new bsWidgetFormChoice(array('choices' => $this->millesimes), array('class' => 'autocomplete permissif')));
        $this->setWidget('categorie_vin', new bsWidgetFormChoice(array('choices' => $this->getCategoriesVin(), 'expanded' => true)));
        $this->setWidget('domaine', new bsWidgetFormInput());
        $this->setWidget('raisin_quantite', new bsWidgetFormInputFloat());
        $this->setWidget('lot', new bsWidgetFormInput());
        $this->setWidget('jus_quantite', new bsWidgetFormInputFloat());
        $this->setWidget('bouteilles_contenance_libelle', new sfWidgetFormChoice(array('choices' => $contenances), array('class' => 'select2')));
        $this->setWidget('prix_initial_unitaire', new bsWidgetFormInputFloat());
        $this->setWidget('degre', new bsWidgetFormInputFloat());
        $this->setWidget('surface', new bsWidgetFormInputFloat());
        $this->setWidget('selection', new bsWidgetFormInputCheckbox());
        $this->setWidget('millesime_85_15', new bsWidgetFormInputCheckbox());
        $this->setWidget('cepage_85_15', new bsWidgetFormInputCheckbox());

        $this->widgetSchema->setLabels(array(
            'produit' => 'produit',
            'cepage' => 'cepage',
            'millesime' => $this->getMillesimeLabel(),
            'categorie_vin' => 'Type',
            'domaine' => 'Nom du domaine',
            'raisin_quantite' => 'Quantité',
            'jus_quantite' => 'Volume',
            'bouteilles_contenance_libelle' => 'Contenance',
            'label' => 'Label',
            'prix_initial_unitaire' => 'Prix',
            'degre' => 'Degré',
            'surface' => 'Surface',
            'millesime_85_15' => 'Millésime 85/15',
            'cepage_85_15' => 'Cépage 85/15'
        ));
        $validatorForNumbers = new sfValidatorRegex(array('required' => false, 'pattern' => "/^[0-9]*.?,?[0-9]+$/"));

        $this->setValidator('produit', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getProduits()))));
        $this->setValidator('cepage', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getCepages()))));
        $this->setValidator('millesime', new sfValidatorInteger(array('required' => true)));
        $this->setValidator('categorie_vin', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getCategoriesVin()))));
        $this->setValidator('domaine', new sfValidatorString(array('required' => false)));
        $this->setValidator('raisin_quantite', new sfValidatorNumber(array('required' => false)));
        $this->setValidator('jus_quantite', new sfValidatorNumber(array('required' => true)));
        $this->setValidator('bouteilles_contenance_libelle', new sfValidatorString(array('required' => true)));
        $this->setValidator('prix_initial_unitaire', new sfValidatorNumber(array('required' => true)));
        $this->setValidator('label', new sfValidatorChoice(array('required' => false, 'multiple' => true, 'choices' => array_keys($this->getLabels()))));
        $this->setValidator('degre', new sfValidatorNumber(array('required' => false, 'min' => 7, 'max' => 15)));
        $this->setValidator('surface', new sfValidatorNumber(array('required' => false)));
        $this->setValidator('selection', new sfValidatorBoolean(array('required' => false)));
        $this->setValidator('millesime_85_15', new sfValidatorBoolean(array('required' => false)));
        $this->setValidator('cepage_85_15', new sfValidatorBoolean(array('required' => false)));
        $this->setValidator('lot', new sfValidatorString(array('required' => false)));

        if (in_array($this->getObject()->type_transaction, array(VracClient::TYPE_TRANSACTION_VIN_VRAC, VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE))) {
            $this->getValidator('cepage')->setOption('required', false);
        } else {
            $this->getValidator('produit')->setOption('required', false);
        }

        $this->validatorSchema['cepage']->setMessage('required', 'La cepage doit être renseigné.');
        $this->validatorSchema['jus_quantite']->setMessage('required', 'La quantité doit être renseignée.');
        $this->validatorSchema['jus_quantite']->setMessage('invalid', 'La quantité "%value%" n\'est pas un nombre.');
        $this->validatorSchema['raisin_quantite']->setMessage('invalid', 'La quantité "%value%" n\'est pas un nombre.');
        $this->validatorSchema['prix_initial_unitaire']->setMessage('invalid', 'Le prix "%value%" n\'est pas un nombre.');
        $this->validatorSchema['produit']->setMessage('required', 'Le choix d\'un produit est obligatoire');
        $this->validatorSchema['prix_initial_unitaire']->setMessage('required', 'Le prix doit être renseigné');
        $this->validatorSchema['millesime']->setMessage('required', 'Le millésime doit être renseigné');
        $this->validatorSchema['degre']->setMessage('min', '7° minimum');
        $this->validatorSchema['degre']->setMessage('max', '15° maximum');

        $this->unsetFields(VracConfiguration::getInstance()->getChampsSupprimes('marche', $this->getObject()->type_transaction));

        if (in_array($this->getObject()->type_transaction, array(VracClient::TYPE_TRANSACTION_RAISINS, VracClient::TYPE_TRANSACTION_MOUTS))) {

            $this->setWidget('millesime', new sfWidgetFormInputHidden());
        }

        $this->widgetSchema->setNameFormat('vrac[%s]');
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        if ($this->getObject()->hasPrixVariable()) {
            $this->setDefault('prix_unitaire', $this->getObject()->_get('prix_unitaire'));
        }
        if (!$this->getObject()->bouteilles_contenance_libelle) {
            $this->setDefault('bouteilles_contenance_libelle', 'Bouteille 75 cl');
        }
        if (!$this->getObject()->millesime) {
            $this->setDefault('millesime', substr($this->next_campagne, 0, 4));
        } else {
            $this->setDefault('millesime', $this->getObject()->millesime);
        }

        if (
                (in_array($this->getObject()->type_transaction, array(VracClient::TYPE_TRANSACTION_VIN_VRAC, VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE)) && $this->getObject()->cepage) ||
                (in_array($this->getObject()->type_transaction, array(VracClient::TYPE_TRANSACTION_RAISINS, VracClient::TYPE_TRANSACTION_MOUTS)) && $this->getObject()->produit)
        ) {
            $this->setDefault('selection', true);
        }
        if (!$this->getObject()->cepage && !$this->getObject()->produit) {
        	$this->setDefault('selection', false);
        }
        if (!$this->getObject()->categorie_vin) {
            $this->setDefault('categorie_vin', VracClient::CATEGORIE_VIN_GENERIQUE);
        }
        if ($this->getObject()->label) {
            $this->setDefault('label', array_keys($this->getObject()->label->toArray(true, false)));
        } else {
            $this->setDefault('label', array());
        }
    }

    public function getProduits() {
        if (is_null($this->_choices_produits)) {
            $this->_choices_produits = array_merge(array("" => ""), $this->getObject()->getProduitsConfig());
        }
        return $this->_choices_produits;
    }

    public function getCepages() {
        if (is_null($this->_choices_cepages)) {
            $this->_choices_cepages = array_merge(array("" => ""), $this->getObject()->getCepagesConfig());
        }
        return $this->_choices_cepages;
    }

    public function getContenances() {
        $contenances = array();
        foreach (array_keys(VracConfiguration::getInstance()->getContenances()) as $c) {
            $contenances[$c] = $c;
        }
        return $contenances;
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $this->getObject()->update();
        $this->getObject()->domaine = strtoupper(KeyInflector::unaccent($values['domaine']));
        if ($this->getObject()->categorie_vin == 'GENERIQUE') {
        	$this->getObject()->domaine = null;
        }
        if ($values['millesime'] === 0) {
            $this->getObject()->millesime = null;
        }
        if ($this->getObject()->type_transaction == VracClient::TYPE_TRANSACTION_RAISINS) {
            $this->getObject()->jus_quantite = null;
        } else {
            $this->getObject()->raisin_quantite = null;
            $this->getObject()->surface = null;
        }
        if ($this->getObject()->type_transaction != VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE) {
            $this->getObject()->bouteilles_contenance_libelle = null;
        }
        if ($this->getObject()->exist('unites')) {
            $this->getObject()->remove('unites');
        }
        if (!isset($values['selection']) || !$values['selection']) {
            if (in_array($this->getObject()->type_transaction, array(VracClient::TYPE_TRANSACTION_VIN_VRAC, VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE))) {
                $this->getObject()->cepage = null;
            } else {
                $this->getObject()->produit = null;
            }
        }
        $configuration = VracConfiguration::getInstance();
        $unites = $this->getObject()->add('unites');
        if (isset($configuration->getUnites()[$this->getObject()->type_transaction]['surface']['cle'])) {
            $unites->surface->add($configuration->getUnites()[$this->getObject()->type_transaction]['surface']['cle'], $configuration->getUnites()[$this->getObject()->type_transaction]['surface']['libelle']);
        }
        if (isset($configuration->getUnites()[$this->getObject()->type_transaction]['jus_quantite'])) {
            $unites->jus_quantite->add($configuration->getUnites()[$this->getObject()->type_transaction]['jus_quantite']['cle'], $configuration->getUnites()[$this->getObject()->type_transaction]['jus_quantite']['libelle']);
            $this->getObject()->volume_initial = $this->getObject()->jus_quantite;
        }
        if (isset($configuration->getUnites()[$this->getObject()->type_transaction]['raisin_quantite'])) {
            $unites->raisin_quantite->add($configuration->getUnites()[$this->getObject()->type_transaction]['raisin_quantite']['cle'], $configuration->getUnites()[$this->getObject()->type_transaction]['raisin_quantite']['libelle']);
            $this->getObject()->volume_initial = $this->getObject()->raisin_quantite;
        }
        $unites->prix_initial_unitaire->add($configuration->getUnites()[$this->getObject()->type_transaction]['prix_initial_unitaire']['cle'], $configuration->getUnites()[$this->getObject()->type_transaction]['prix_initial_unitaire']['libelle']);
        $unites->volume_initial->add($configuration->getUnites()[$this->getObject()->type_transaction]['volume_initial']['cle'], $configuration->getUnites()[$this->getObject()->type_transaction]['volume_initial']['libelle']);

        $this->getObject()->remove('label');
        $this->getObject()->add('label');
        if (isset($values['label'])) {
            foreach ($values['label'] as $label_key) {
                $this->getObject()->label->add($label_key, $this->getLabels()[$label_key]);
            }
        }
    }

    public function getDomaines()
    {
        $domaines = VracDomainesView::getInstance()->findDomainesByVendeur($this->getObject()->vendeur_identifiant);
        $this->domaines = array('' => '');
        foreach ($domaines->rows as $resultDomaine) {
            $d = $resultDomaine->key[VracDomainesView::KEY_DOMAINE];
            $this->domaines[$d] = $d;
        }
        if ($this->defaultDomaine) {
            $this->domaines[$this->defaultDomaine] = $this->defaultDomaine;
        }
    }

    public function getMillesimes() {
        if ($this->getObject()->type_transaction != VracClient::TYPE_TRANSACTION_RAISINS) {
            $this->millesimes = array('0' => self::NONMILLESIMELABEL);
        }

        $date = new DateTime();
        $annee = $date->format('Y');
        if ($date->format('m') < 8) {
            $annee--;
        }
        $stop = $annee - 15;
        while ($annee >= $stop) {
            $this->millesimes[$annee] = '' . $annee;
            $annee--;
        }
    }

    public function getCategoriesVin() {

        return VracConfiguration::getInstance()->getCategories();
    }

    protected function getConfig() {

        return ConfigurationClient::getCurrent();
    }

    protected function getLabels() {

        return $this->getConfig()->labels->toArray();
    }

    private function getCurrentYear() {
        $year = date('Y');
        return (int) $year + 1;
    }

    private function getMillesimeLabel() {
        return $this->getObject()->getMillesimeLabel();
    }

    public function getNextMillesime() {
        return substr($this->next_campagne, 0, 4);
    }

    public function getActuelMillesime() {
        return substr($this->actual_campagne, 0, 4);
    }

    public function getDomainesForAutocomplete() {
        $domainesView = VracDomainesView::getInstance()->findDomainesByVendeur($this->getObject()->vendeur_identifiant);
        $domaines = array();
        foreach ($domainesView->rows as $resultDomaine) {
            $d = $resultDomaine->key[VracDomainesView::KEY_DOMAINE];
            $domaines[$d] = $d;
        }
        if ($this->getObject()->domaine) {
            $domaines[$this->getObject()->domaine] = $this->getObject()->domaine;
        }
        $entries = array();
        foreach ($domaines as $domaine) {
            $entry = new stdClass();
            $entry->id = trim($domaine);
            $entry->text = trim($domaine);
            $entries[] = $entry;
        }
        return $entries;
    }

}
