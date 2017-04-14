<?php

class EditionRevendicationForm extends sfForm {

    protected $_choices_produits;
    protected $revendication;
    protected $identifiant;
    protected $row;
    protected $produit_hash;
    protected $volume;
    protected $num_ligne;
    protected $code_douane;
    protected $date;

    public function __construct(stdClass $revendication, $identifiant, $produit, $row, $defaults = array(), $options = array(), $CSRFSecret = null) {
        parent::__construct($defaults, $options, $CSRFSecret);
        $this->revendication = $revendication;
        $this->date = ConfigurationClient::getInstance()->getDateDebutCampagne($revendication->campagne);
        if (isset($identifiant) && isset($produit) && isset($row)) {
            $this->initFields($identifiant, $produit, $row);
            $this->setDefaults($defaults);
        }
    }

    public function configure() {
        parent::configure();
        $this->setWidget('produit_hash', new sfWidgetFormChoice(array('choices' => $this->getProduits()), array('class' => 'autocomplete')));
        $this->setWidget('volume', new sfWidgetFormInput());
        $this->setValidator('produit_hash', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getProduits()))));
        $this->setValidator('volume', new sfValidatorNumber(array('required' => true)));
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
        $this->widgetSchema->setNameFormat('revendication_edition_row[%s]');
    }

    public function getVolumeProduitObj($revendication, $identifiant, $produit, $row) {
        $result = new stdClass();
        if (!isset($revendication->datas->$identifiant))
            throw new sfException("Le noeud d'identifiant $identifiant n'existe pas dans la revendication");
        $etbNode = $revendication->datas->$identifiant;
        //$produitNode = RevendicationClient::getInstance()->getProduitNode($revendication, $identifiant, $row);

        if (!isset($etbNode->produits->$produit))
            throw new sfException("Le noeud produit ($produit) d'identifiant $identifiant n'existe pas dans la revendication");

        $prodNode = $etbNode->produits->$produit;

        if (!isset($prodNode->volumes->$row))
            throw new sfException("La ligne $row n'existe pas dans la revendication");

        $result->produit = $prodNode;
        $result->volume = $prodNode->volumes->$row;
        return $result;
    }

    public function getProduits() {
        if (is_null($this->_choices_produits)) {
            $this->_choices_produits = array_merge(array("" => ""), $this->getConfig()->formatProduits($this->date));
        }
        return $this->_choices_produits;
    }

    protected function getConfig() {

        return ConfigurationClient::getCurrent();
    }

    public function doUpdate() {
        $newProduitDouane = $this->getConfig()->get($this->values['produit_hash'])->getCodeDouane();
        $oldNode = RevendicationClient::getInstance()->getProduitNode($this->revendication, $this->identifiant, $this->produit);
        $row = $this->row;
        if ($this->code_douane != $newProduitDouane) {
            $this->createAndGetProduitNewNode($newProduitDouane, $this->values['produit_hash'], $row, $oldNode);
            $oldNode->volumes->$row->statut = RevendicationProduits::STATUT_SUPPRIME;
        }
        $this->updateVolume($newProduitDouane, $row, $this->values['volume']);
        return $this->revendication;
    }

    public function getDefaults() {
        return $this->defaults;
    }

    public function createAndGetProduitNewNode($codeDouane, $hash, $row, $oldNode) {
        $identifiant = $this->identifiant;
        if (!isset($this->revendication->datas->$identifiant))
            throw new sfException("Le noeud d'identifiant $identifiant n'existe pas dans la revendication");
        $produits = $this->revendication->datas->$identifiant->produits;
        if (!isset($produits->$codeDouane)) {
            $produits->$codeDouane = new stdClass();
        }
        $libelle = $this->getConfig()->get($this->values['produit_hash'])->getLibelleFormat( null, "%format_libelle% %la%");
        $produits->$codeDouane->libelle_produit_csv = $libelle;
        $produits->$codeDouane->produit_hash = $hash;
        $produits->$codeDouane->produit_libelle = $libelle;
        $produits->$codeDouane->date_certification = $oldNode->date_certification;
        if (!isset($produits->$codeDouane->volumes))
            $produits->$codeDouane->volumes = new stdClass();
        $produits->$codeDouane->volumes->$row = new stdClass();
        $new_ligne = $produits->$codeDouane->volumes->$row;
        $new_ligne->num_ligne = $oldNode->volumes->$row->num_ligne;
        $new_ligne->volume = $oldNode->volumes->$row->volume;
        $new_ligne->bailleur_identifiant = $oldNode->volumes->$row->bailleur_identifiant;
        $new_ligne->bailleur_nom = $oldNode->volumes->$row->bailleur_nom;
        $new_ligne->date_certification = $oldNode->volumes->$row->date_certification;
        $new_ligne->ligne = $oldNode->volumes->$row->ligne;
        $new_ligne->statut = RevendicationProduits::STATUT_MODIFIE;
        return $produits->$codeDouane;
    }

    public function updateVolume($codeDouane, $row, $volume) {
        $identifiant = $this->identifiant;
        if (!isset($this->revendication->datas->$identifiant))
            throw new sfException("Le noeud d'identifiant $identifiant n'existe pas dans la revendication");
        $produits = $this->revendication->datas->$identifiant->produits;
        if (!isset($produits->$codeDouane))
            throw new sfException("Le noeud produit d'identifiant $identifiant et de code douane $codeDouane n'existe pas dans la revendication");
        $produits->$codeDouane->volumes->$row->volume = $volume;
        $produits->$codeDouane->volumes->$row->statut = RevendicationProduits::STATUT_MODIFIE;
    }

    public function initFields($identifiant, $produit, $row) {
        $this->identifiant = $identifiant;
        $this->produit = $produit;
        $this->row = $row;
        $volumeProduitObj = $this->getVolumeProduitObj($this->revendication, $this->identifiant, $this->produit, $this->row);

        $this->produit_hash = $volumeProduitObj->produit->produit_hash;
        $this->code_douane = $produit;
        $this->volume = sprintf("%01.02f", round($volumeProduitObj->volume->volume, 2));
        $this->num_ligne = $volumeProduitObj->volume->num_ligne;
    }

    public function setDefaults($defaults) {
        parent::setDefaults($defaults);
        $this->defaults['produit_hash'] = $this->produit_hash;
        $this->defaults['volume'] = $this->volume;
    }

}
