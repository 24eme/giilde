<?php
/**
 * Model for SV12Contrat
 *
 */

class SV12Contrat extends BaseSV12Contrat {

    protected $vrac = null;

    public function getMouvementVendeur() {
        $mouvement = $this->getMouvement();
        if (!$mouvement) {

            return null;
        }
        $mouvement->vrac_destinataire = $this->getDocument()->declarant->nom;
        $mouvement->region = $this->getVendeur()->region;
        $mouvement->cvo = 0.0;

        if ($this->getVrac()) {
        	$mouvement->cvo = $this->getTauxCvo() * $this->getVrac()->getRepartitionCVOCoef($this->vendeur_identifiant);
        }

        if($mouvement->cvo <= 0) {
            $mouvement->facturable = 0;
        }

        if(VracConfiguration::getInstance()->getRepartitionCvo() == "50"){
          $coeff = ($this->isVendeurRegion())? 0.5 : 0.0;
          $mouvement->cvo = $this->getTauxCvo() * $coeff;
          $mouvement->facturable = 1;
        }

        return $mouvement;
    }

    public function getVendeur() {
        return EtablissementClient::getInstance()->find($this->vendeur_identifiant);
    }

    public function isVendeurRegion() {
      return EtablissementClient::getInstance()->find($this->vendeur_identifiant)->region == EtablissementClient::REGION_CVO;
    }

    public function getAcheteur() {
        return $this->getDocument()->getEtablissementObject();
    }

    public function getMouvementAcheteur() {
        $mouvement = $this->getMouvement();
        if (!$mouvement) {

            return null;
        }

        $mouvement->vrac_destinataire = $this->vendeur_nom;
        $mouvement->region = $this->getAcheteur()->region;

        $mouvement->cvo = $this->getTauxCvo() * 1.0;

        if($mouvement->cvo <= 0) {
            $mouvement->facturable = 0;
        }

        if(VracConfiguration::getInstance()->getRepartitionCvo() == "50"){
          $coeff = ($this->isVendeurRegion())? 0.5 : 1.0;
          $mouvement->cvo = $this->getTauxCvo() * $coeff;
        }

        return $mouvement;
    }

    public function isCleanable() {

        return !$this->volume && !($this->getDocument()->hasVersion() && $this->getDocument()->isModifiedMother($this, 'volume'));
    }

    protected function getVolumeVersion() {
        if ($this->getDocument()->hasVersion() && !$this->getDocument()->isModifiedMother($this, 'volume')) {

            return 0;
        }

        $volume = $this->volume;

        if ($this->getDocument()->hasVersion() && $this->getDocument()->motherExist($this->getHash() . '/volume')) {
            $volume = $volume - $this->getDocument()->motherGet($this->getHash() . '/volume');
        }

        return $volume;
    }

    protected function getMouvement() {

        $volume = $this->getVolumeVersion();

        if ($volume == 0) {
            return null;
        }

        $mouvement = DRMMouvement::freeInstance($this->getDocument());
        $mouvement->produit_hash = $this->produit_hash;
        $mouvement->facture = 0;
        $mouvement->version = $this->getDocument()->version;
        $mouvement->date_version = ($this->getDocument()->valide->date_saisie) ? ($this->getDocument()->valide->date_saisie) : date('Y-m-d');
        if ($this->contrat_type == VracClient::TYPE_TRANSACTION_RAISINS) {
            $mouvement->categorie = FactureClient::FACTURE_LIGNE_PRODUIT_TYPE_RAISINS;
        } elseif ($this->contrat_type == VracClient::TYPE_TRANSACTION_MOUTS) {
            $mouvement->categorie = FactureClient::FACTURE_LIGNE_PRODUIT_TYPE_MOUTS;
        }
        if (!$this->getVrac())
            $mouvement->categorie = FactureClient::FACTURE_LIGNE_PRODUIT_TYPE_ECART;
        $mouvement->type_hash = $this->contrat_type;
        $mouvement->type_libelle = sprintf("Contrat %s", strtolower($this->getContratTypeLibelle()));
        $mouvement->volume = -1 * $volume;
        $mouvement->facturable = 1;
        $mouvement->date = $this->getDocument()->getDate();
        $mouvement->vrac_numero = $this->contrat_numero;
        if ($this->getVrac()) {
            $mouvement->detail_identifiant = $this->getVracIdentifiant();
            $mouvement->detail_libelle = $this->vrac->numero_archive;
        } else {
            $mouvement->detail_identifiant = null;
            $mouvement->detail_libelle = $this->contrat_numero;
        }

        return $mouvement;
    }

    public function canBeSoldable() {

        return $this->isSaisi();
    }

    public function isSaisi() {
        return !is_null($this->volume);
    }

    public function isSansContrat() {

        return is_null($this->contrat_numero);
    }

    public function enleverVolume() {
        if ($this->isSansContrat()) {

            return false;
        }

        $volume = $this->getVolumeVersion();

        if (!$this->getVrac()) {

            throw new sfException(sprintf("Le contrat %s est introuvable", $this->getVracIdentifiant()));
        }

        if ($this->isSaisi() && $volume == 0 && $this->getVrac()->isSolde()) {

            return false;
        }

        $this->getVrac()->enleverVolume($this->getVolumeVersion());

        if ($this->canBeSoldable()) {
            $this->getVrac()->solder();
        } else {
            $this->getVrac()->desolder();
        }

        return true;
    }

    public function getVrac() {
        if (is_null($this->vrac)) {
            $this->vrac = VracClient::getInstance()->find($this->getVracIdentifiant());
        }

        return $this->vrac;
    }

    public function getVracIdentifiant() {

        return 'VRAC-' . $this->contrat_numero;
    }

    public function getDroitCVO() {

        return $this->getProduitObject()->getDroitCVO($this->getDocument()->getDate());
    }

    public function getTauxCvo() {
        if (is_null($this->cvo)) {
            $this->cvo = $this->getDroitCVO()->taux*1.0;
        }

        return $this->cvo;
    }

    public function storeDroits() {
        $this->cvo = null;
        $this->getTauxCvo();
    }

    public function getProduitObject() {

        return ConfigurationClient::getCurrent()->get($this->produit_hash);
    }

    public function getContratTypeLibelle() {
        $contratTypeLibelles = array_merge(VracClient::$types_transaction, array(SV12Client::SV12_TYPEKEY_VENDANGE => 'de vendanges'));
        return ($this->contrat_type) ? $contratTypeLibelles[$this->contrat_type] : null;
    }

    function getNumeroArchive() {
        return VracClient::getInstance()->findByNumContrat($this->contrat_numero)->numero_archive;
    }

    function updateFromView($viewinfo) {
        if ($viewinfo[VracClient::VRAC_VIEW_PRODUIT_ID] != $this->produit_hash ||
                $this->vendeur_identifiant != $viewinfo[VracClient::VRAC_VIEW_VENDEUR_ID] ||
                $this->contrat_type != $viewinfo[VracClient::VRAC_VIEW_TYPEPRODUIT]) {
            $produit = ConfigurationClient::getCurrent()->get($viewinfo[VracClient::VRAC_VIEW_PRODUIT_ID]);
            return $this->updateNoContrat($produit, array('contrat_type' => $viewinfo[VracClient::VRAC_VIEW_TYPEPRODUIT], 'vendeur_identifiant' => $viewinfo[VracClient::VRAC_VIEW_VENDEUR_ID], 'vendeur_nom' => $viewinfo[VracClient::VRAC_VIEW_VENDEUR_NOM], 'contrat_numero' => $this->contrat_numero, 'volume' => $this->volume, 'volume_prop' => $this->volume_prop));
        }
        return;
    }

    function updateNoContrat($produit, $contratinfo = array('contrat_type' => null, 'vendeur_identifiant' => null, 'vendeur_nom' => null, 'contrat_numero' => null, 'volume' => null, 'volume_prop' => null)) {
        if ($this->volume && (!isset($contratinfo['volume']) || !$contratinfo['volume']))
            return;
        $this->contrat_numero = (isset($contratinfo['contrat_numero'])) ? $contratinfo['contrat_numero'] : null;
        $this->contrat_type = $contratinfo['contrat_type'];
        $this->produit_libelle = $produit->getLibelleFormat( null, "%format_libelle% %la%");
        $this->produit_hash = $produit->getHash();
        $this->vendeur_identifiant = $contratinfo['vendeur_identifiant'];
        $this->vendeur_nom = $contratinfo['vendeur_nom'];
        $this->volume_prop = (isset($contratinfo['volume_prop'])) ? $contratinfo['volume_prop'] : null;
        $this->volume = (isset($contratinfo['volume'])) ? $contratinfo['volume'] : null;
    }

}
