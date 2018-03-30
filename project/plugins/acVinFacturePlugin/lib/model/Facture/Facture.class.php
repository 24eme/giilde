<?php

/**
 * Model for Facture
 *
 */
class Facture extends BaseFacture implements InterfaceArchivageDocument {

    private $documents_origine = array();
    protected $declarant_document = null;
    protected $archivage_document = null;
    protected $etablissements = array();
    protected $config_sortie_array = array();
    private $configuration = null;

    const MESSAGE_DEFAULT = "";

    public function __construct() {
        parent::__construct();
        $this->initDocuments();
        $this->configuration = ConfigurationClient::getCurrent();
        $config_detail_list = $this->configuration->declaration->getDetailConfiguration();
        foreach ($config_detail_list->sorties as $keyDetail => $detail) {
            $this->config_sortie_array[$keyDetail] = $detail->getLibelle();
        }
    }

    public function __clone() {
        parent::__clone();
        $this->initDocuments();
    }

    protected function initDocuments() {
        $this->declarant_document = new DeclarantDocument($this);
        $this->archivage_document = new ArchivageDocument($this);
    }

    public function getCampagne() {

        return $this->_get('campagne');
    }

    public function storeEmetteur() {
        $configs = sfConfig::get('app_configuration_facture');
        $emetteur = new stdClass();
        if (!$configs && !isset($configs['emetteur_libre']) && !isset($configs['emetteur_cvo'])) {
            throw new sfException(sprintf('Config "configuration/facture/emetteur" not found in app.yml'));
        }
        if ($this->hasArgument(FactureClient::TYPE_FACTURE_MOUVEMENT_DIVERS)) {
            $this->emetteur = $configs['emetteur_libre'];
        }
        if ($this->hasArgument(FactureClient::TYPE_FACTURE_MOUVEMENT_DRM)) {
            $this->emetteur = $configs['emetteur_cvo'];
        }
    }

    public function getCoordonneesBancaire() {
        $configs = sfConfig::get('app_configuration_facture');
        if (!$configs && !isset($configs['coordonnees_bancaire'])) {
            throw new sfException(sprintf('Config "configuration/facture/coordonnees_bancaire" not found in app.yml'));
        }
        $appCoordonneesBancaire = $configs['coordonnees_bancaire'];

        $coordonneesBancaires = new stdClass();

        $coordonneesBancaires->banque = $appCoordonneesBancaire['banque'];
        $coordonneesBancaires->bic = $appCoordonneesBancaire['bic'];
        $coordonneesBancaires->iban = $appCoordonneesBancaire['iban'];

        return $coordonneesBancaires;
    }

    public function getInformationsInterpro() {
        $configs = sfConfig::get('app_configuration_facture');
        if (!$configs && !isset($configs['infos_interpro'])) {
            throw new sfException(sprintf('Config "configuration/facture/infos_interpro" not found in app.yml'));
        }
        $appInfosInterpro = $configs['infos_interpro'];

        $infosInterpro = new stdClass();

        $infosInterpro->siret = $appInfosInterpro['siret'];
        $infosInterpro->ape = $appInfosInterpro['ape'];
        $infosInterpro->tva_intracom = $appInfosInterpro['tva_intracom'];

        return $infosInterpro;
    }

    public function storeDatesCampagne($date_facturation = null) {
        $configs = sfConfig::get('app_configuration_facture');
        $this->date_emission = date('Y-m-d');
        $this->date_facturation = $date_facturation;
        $date_facturation_object = new DateTime($this->date_facturation);
        $this->date_echeance = $date_facturation_object->modify(FactureConfiguration::getInstance()->getEcheance())->format('Y-m-d');
        if (!$this->date_facturation) {
            $this->date_facturation = date('Y-m-d');
        }

	    $date_campagne = new DateTime($this->date_facturation);

        if (FactureConfiguration::getInstance()->getExercice() == 'viticole') {
		    $date_campagne = $date_campagne->modify('+5 months');
	    }

        $this->campagne = $date_campagne->format('Y');
    }

    public function constructIds($doc) {
        if (!$doc)
            throw new sfException('Pas de document attribué');

        $this->region = $doc->getRegionViticole();
        $this->identifiant = $doc->identifiant;
        $this->numero_facture = FactureClient::getInstance()->getNextNoFacture($this->identifiant, date('Ymd'));
        $this->_id = FactureClient::getInstance()->getId($this->identifiant, $this->numero_facture);
    }

    public function getNumeroInterpro() {

        return $this->getNumeroPieceComptable();
    }

    public function getNumeroPieceComptable() {
        if ($this->_get('numero_piece_comptable')) {

            return $this->_get('numero_piece_comptable');
        }
        $prefix = FactureConfiguration::getInstance()->getPrefixId($this);

        return $prefix . preg_replace('/^\d{2}(\d{2})/', '$1', $this->campagne) . sprintf('%05d', $this->numero_archive);
    }

    public function getTaxe() {
        return $this->total_ttc - $this->total_ht;
    }

    public function facturerMouvements() {
        foreach ($this->getLignes() as $l) {
            $l->facturerMouvements();
        }
    }

    public function defacturer() {
        if (!$this->isRedressable())
            return;
        foreach ($this->getLignes() as $ligne) {
            $ligne->defacturerMouvements();
        }
        $this->statut = FactureClient::STATUT_REDRESSEE;
    }

    public function isRedressee() {
        return ($this->statut == FactureClient::STATUT_REDRESSEE);
    }

    public function isRedressable() {
        return ($this->statut != FactureClient::STATUT_REDRESSEE && $this->statut != FactureClient::STATUT_NONREDRESSABLE);
    }

    public function getEcheancesArray() {
        $e = $this->_get('echeances')->toArray();
        usort($e, 'Facture::triEcheanceDate');
        return $e;
    }

    public function getLignesArray() {
        $l = $this->_get('lignes')->toArray();
        usort($l, 'Facture::triOrigineDate');
        return $l;
    }

    public function getNbLignesAndDetails() {
      $nb = 0;
      foreach($this->lignes as $k => $l) {
        $nb++;
        $nb += count($l->details);
      }
      return $nb;
    }

    static function triOrigineDate($ligne_0, $ligne_1) {
        return self::triDate("origine_date", $ligne_0, $ligne_1);
    }

    static function triEcheanceDate($ligne_0, $ligne_1) {
        return self::triDate("echeance_date", $ligne_0, $ligne_1);
    }

    static function triDate($champ, $ligne_0, $ligne_1) {
        if ($ligne_0->{$champ} == $ligne_1->{$champ}) {

            return 0;
        }
        return ($ligne_0->{$champ} > $ligne_1->{$champ}) ? -1 : +1;
    }

// PLUS UTILISE => TEMPLATES
    public function storeLignesFromTemplate($cotisations) {
        foreach ($cotisations as $key => $cotisation) {
            $ligne = $this->lignes->add($key);
            $ligne->libelle = $cotisation["libelle"];
            $ligne->produit_identifiant_analytique = $cotisation["code_comptable"];
            $ligne->origine_mouvements = $cotisation["origines"];
            $total = 0;
            $totalTva = 0;
            foreach ($cotisation["details"] as $detail) {
                $d = $ligne->details->add();
                $d->libelle = $detail["libelle"];
                $d->quantite = $detail["quantite"];
                $d->prix_unitaire = $detail["prix"];
                $d->taux_tva = $detail["taux"];
                $d->montant_tva = $detail["tva"];
                $d->montant_ht = $detail["total"];
                $total += $detail["total"];
                $totalTva += $detail["tva"];
            }

            $totalTva = round($totalTva, 2);
            $total = round($total, 2);

            $ligne->updateTotaux();

            if ($totalTva != $ligne->montant_tva) {
                throw new sfException("Incohérence");
            }

            if ($total != $ligne->montant_ht) {
                throw new sfException("Incohérence");
            }
        }
    }

    public function storeLignesFromMouvements($mvts, $famille, $modele) {
        foreach ($mvts as $ligneByType) {
            if (isset($this->config_sortie_array['vrac']) && $ligneByType->value[MouvementfactureFacturationView::VALUE_TYPE_LIBELLE] == $this->config_sortie_array['vrac']) {
                continue;
            }
            $this->storeLigneFromMouvements($ligneByType, $famille, $modele);
        }
        foreach ($mvts as $ligneByType) {
            if (!isset($this->config_sortie_array['vrac'])) {
                continue;
            }
            if ($ligneByType->value[MouvementfactureFacturationView::VALUE_TYPE_LIBELLE] != $this->config_sortie_array['vrac']) {
                continue;
            }

            $this->storeLigneFromMouvements($ligneByType, $famille, $modele);
        }
    }

    public function storeLigneFromMouvements($ligneByType, $famille, $modele) {

        $etablissements = $this->getEtablissements();
        $keysOrigin = array();
        if (($modele == FactureClient::FACTURE_LIGNE_ORIGINE_TYPE_DRM) || ($modele == FactureClient::FACTURE_LIGNE_ORIGINE_TYPE_SV12)) {
            foreach ($ligneByType->value[MouvementfactureFacturationView::VALUE_ID_ORIGINE] as $origine) {
                $keyOrigin = explode(':', $origine);
                $keyOriginWithoutModificatrice = preg_replace('/(.*)-M[0-9]+$/', '$1', $keyOrigin[0]);
                if (!array_key_exists($keyOriginWithoutModificatrice, $keysOrigin)) {
                    $keysOrigin[$keyOriginWithoutModificatrice] = array();
                }
                $keysOrigin[$keyOriginWithoutModificatrice][$origine] = $keyOrigin;
            }
        } elseif ($modele == FactureClient::FACTURE_LIGNE_ORIGINE_TYPE_MOUVEMENTSFACTURE) {
            foreach ($ligneByType->value[MouvementfactureFacturationView::VALUE_ID_ORIGINE] as $origine) {
                $keyOrigin = explode(':', $origine);
                $keysOrigin[$keyOrigin[0]][$keyOrigin[1]] = $keyOrigin;
            }
        }

        $ligne = null;
        foreach ($keysOrigin as $docId => $originesArr) {
            $ligne = $this->lignes->add($docId);

            foreach ($originesArr as $origineKey => $mvtKeyArray) {
                $ligne->origine_mouvements->getOrAdd($mvtKeyArray[0])->add(null, $mvtKeyArray[1]);
            }
            $origin_mouvement = $ligneByType->key[MouvementfactureFacturationView::KEYS_ORIGIN];
            if ($origin_mouvement == FactureClient::FACTURE_LIGNE_ORIGINE_TYPE_DRM){
                $ligne->libelle = DRMClient::getInstance()->getLibelleFromId($docId);
                if (count($etablissements) > 1) {
                    $idEtb = $ligneByType->key[MouvementfactureFacturationView::KEYS_ETB_ID];
                    $etb = $etablissements["ETABLISSEMENT-" . $idEtb];
                    $ligne->libelle .= ' (' . $etb->etablissement->nom . ')';
                }
            }elseif($origin_mouvement == FactureClient::FACTURE_LIGNE_ORIGINE_TYPE_SV12){
                $ligne->libelle = SV12Client::getInstance()->getLibelleFromId($docId);
                if (count($etablissements) > 1) {
                    $idEtb = $ligneByType->key[MouvementfactureFacturationView::KEYS_ETB_ID];
                    $etb = $etablissements["ETABLISSEMENT-" . $idEtb];
                    $ligne->libelle .= ' (' . $etb->etablissement->nom . ')';
                  }
            }elseif ($origin_mouvement == FactureClient::FACTURE_LIGNE_ORIGINE_TYPE_MOUVEMENTSFACTURE) {
                $ligne->libelle = $ligneByType->key[MouvementfactureFacturationView::KEYS_MATIERE];
                $ligne->add("produit_identifiant_analytique", $ligneByType->key[MouvementfactureFacturationView::KEYS_PRODUIT_ID]);
            }

            if (($origin_mouvement == FactureClient::FACTURE_LIGNE_ORIGINE_TYPE_DRM) || ($origin_mouvement == FactureClient::FACTURE_LIGNE_ORIGINE_TYPE_SV12)) {
                $produit_libelle = $ligneByType->value[MouvementfactureFacturationView::VALUE_PRODUIT_LIBELLE];
                $transacteur = $ligneByType->key[MouvementfactureFacturationView::KEYS_VRAC_DEST];
                $detail = null;
                if ($transacteur) {
                    $detail = $ligne->getOrAdd('details')->add();
                    $detail->libelle = $produit_libelle;
                    $detail->prix_unitaire = $ligneByType->value[MouvementfactureFacturationView::VALUE_CVO];
                    $detail->quantite = ($ligneByType->value[MouvementfactureFacturationView::VALUE_VOLUME] * -1);
                    $detail->taux_tva = 0.2;
                    $detail->origine_type = $this->createOrigine($transacteur, $famille, $ligneByType);
                } else {
                    foreach ($ligne->get('details') as $present_detail) {
                        if (!$present_detail->origine_type && !is_null($detail) && ($produit_libelle == $detail->libelle)) {
                            $detail = $present_detail;
                        }
                    }
                    if (!$detail) {
                        $detail = $ligne->getOrAdd('details')->add();
                        $detail->quantite = 0;
                        $detail->libelle = $produit_libelle;
                        $detail->prix_unitaire = $ligneByType->value[MouvementfactureFacturationView::VALUE_CVO];
                        $detail->taux_tva = 0.2;
                    }
                    $detail->quantite += ($ligneByType->value[MouvementfactureFacturationView::VALUE_VOLUME] * -1);
                }
                $produit_configuration = null;
                if($this->configuration->exist($ligneByType->key[MouvementfactureFacturationView::KEYS_PRODUIT_ID])){
                  $produit_configuration = $this->configuration->get($ligneByType->key[MouvementfactureFacturationView::KEYS_PRODUIT_ID]);
                }else{
                  $hashTransformed = preg_replace('/(.*)\/([a-zA-Z0-9]+)\/([a-zA-Z0-9]+)/',"$1",$ligneByType->key[MouvementfactureFacturationView::KEYS_PRODUIT_ID]);
                  $produit_configuration = $this->configuration->get($hashTransformed);
                }
                $codeProduit = $produit_configuration->getCodeComptable();

                $detail->add(FactureConfiguration::getInstance()->getStockageCodeProduit(), $codeProduit);
            }
            elseif ($origin_mouvement == FactureClient::FACTURE_LIGNE_ORIGINE_TYPE_MOUVEMENTSFACTURE) {
                $detail = $ligne->getOrAdd('details')->add();
                $detail->quantite = $ligneByType->value[MouvementfactureFacturationView::VALUE_VOLUME] * -1;
                $detail->libelle = $ligneByType->value[MouvementfactureFacturationView::VALUE_TYPE_LIBELLE];
                $detail->prix_unitaire = $ligneByType->value[MouvementfactureFacturationView::VALUE_CVO];
                if(!preg_match('/^([0-9]+)_([a-z0-9]*)$/', $ligneByType->key[MouvementfactureFacturationView::KEYS_PRODUIT_ID])){
                    throw new sfException(sprintf("L'identifiant analytique (composé) %s n'a pas le bon format !",$ligneByType->key[MouvementfactureFacturationView::KEYS_PRODUIT_ID]));
                }
                $identifiants_compte_analytique = explode('_',$ligneByType->key[MouvementfactureFacturationView::KEYS_PRODUIT_ID]);
                $detail->add('identifiant_analytique',$identifiants_compte_analytique[1]);
                $detail->add('code_compte',$identifiants_compte_analytique[0]);
                $detail->taux_tva = 0.2;
            }
        }
    }

    protected function verifLigneAndVolumeOrigines($ligne) {
        $volume = 0;
        foreach ($ligne->origine_mouvements as $doc_id => $keys) {
            $doc = acCouchdbManager::getClient()->find($doc_id, acCouchdbClient::HYDRATE_JSON);
            foreach ($keys as $key) {
                foreach ($doc->mouvements as $identifiant => $mouvements) {
                    if (!preg_match("/^" . $this->identifiant . "/", $identifiant)) {
                        continue;
                    }

                    $mouvement = $mouvements->$key;
                    $volume += $mouvement->volume;
                }
            }
        }
        if (round($ligne->volume, 2) != round($volume, 2)) {

            throw new sfException(sprintf("Le volume de la ligne %s de %s hl ne correspond pas à la somme des volumes des mouvements %s hl", $ligne->getKey(), round($ligne->volume, 2), round($volume, 2)));
        }
    }

    private function createLigneOriginesMouvements($ligne, $originesTable) {
        $origines = array();
        foreach ($originesTable as $origineFormatted) {
            $origineKeyValue = explode(':', $origineFormatted);
            if (count($origineKeyValue) != 2)
                throw new sfException('Le mouvement est mal formé : %s', print_r($origineKeyValue));
            $key = $origineKeyValue[0];
            $value = $origineKeyValue[1];
            if (!array_key_exists($key, $origines)) {
                $origines[$key] = array();
            }
            $origines[$key][] = $value;
        }

        return $origines;
    }

    public function hasArgument($arg) {
        foreach ($this->arguments as $argumentKey => $argumentValue) {
            if ($arg == $argumentValue) {
                return true;
            }
        }
        return false;
    }

    public function getEcheancesPapillon() {
        $echeance = new stdClass();
        $echeance->echeance_date = $this->date_echeance;

        $echeance->montant_ttc = 0;
        foreach ($this->lignes as $ligne) {
            $echeance->montant_ttc += $ligne->montant_tva + $ligne->montant_ht;
        }
        return array($echeance);
    }

    private function createOrigine($transacteur, $famille, $view) {
        $isProduitFirst = FactureConfiguration::getInstance()->isPdfProduitFirst();

        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Date'));
        if (($view->key[MouvementfactureFacturationView::KEYS_ORIGIN] == FactureClient::FACTURE_LIGNE_ORIGINE_TYPE_DRM)
          || ($view->key[MouvementfactureFacturationView::KEYS_ORIGIN] == FactureClient::FACTURE_LIGNE_ORIGINE_TYPE_SV12)) {

            $type_contrat = "";
            if($view->key[MouvementfactureFacturationView::KEYS_ORIGIN] == FactureClient::FACTURE_LIGNE_ORIGINE_TYPE_SV12){
              $type_contrat =  'Achat ';
            }else{
              $type_contrat = 'Contrat ';
            }

            if ($famille == SocieteClient::TYPE_OPERATEUR) {
                if ( sfConfig::get('app_configuration_facture_idcontrat') == 'ID' ) {
                    $idContrat = intval(substr($view->key[MouvementfactureFacturationView::KEYS_CONTRAT_ID], -6));
                }else{
                    $idContrat = $view->value[MouvementfactureFacturationView::VALUE_DETAIL_LIBELLE];
                }
                $origine_libelle = 'Contrat n° ' . $idContrat;
            }
            $origine_libelle .= ' (' . $transacteur . ') ';

            if($isProduitFirst){
              $origine_libelle = $type_contrat . $transacteur;
            }
            return $origine_libelle;
        }
    }

    public function storePapillons() {
        foreach ($this->lignes as $typeLignes) {
            foreach ($typeLignes as $ligne) {
                switch ($ligne->produit_type) {
                    case FactureClient::FACTURE_LIGNE_PRODUIT_TYPE_MOUTS:
                    case FactureClient::FACTURE_LIGNE_PRODUIT_TYPE_RAISINS:
                        if (strstr($ligne->produit_hash, 'mentions/LIE/')) {
                            $this->createOrUpdateEcheanceD($ligne);
                            break;
                        }
                        if ($this->isContratPluriannuel($ligne))
                            $this->createOrUpdateEcheanceC($ligne);
                        else
                            $this->createOrUpdateEcheanceB($ligne);
                        break;
                    case FactureClient::FACTURE_LIGNE_PRODUIT_TYPE_ECART:
                        $this->createOrUpdateEcheanceB($ligne);
                        break;
                    case FactureClient::FACTURE_LIGNE_PRODUIT_TYPE_VINS:
                    default :
                        $this->createOrUpdateEcheanceA($ligne);
                        break;
                }
            }
        }
    }

    public function updateAvoir() {
        if ($this->total_ht > 0) {
            $this->storePapillons();
        }
    }

    private function isContratPluriannuel($l) {
        $contrat = VracClient::getInstance()->findByNumContrat($l->contrat_identifiant, acCouchdbClient::HYDRATE_JSON);
        if (!$contrat->type_contrat)
            throw new sfException("Le contrat de numéro $l->contrat_identifiant n'est pas valide.");
        return ($contrat->type_contrat == VracClient::TYPE_CONTRAT_PLURIANNUEL);
    }

    public function createOrUpdateEcheanceC($ligne) {
        $ligne->echeance_code = 'C';
        $date = str_replace('-', '', $this->date_facturation);

        $d1 = date('Y') . '0331'; // 31/03/N
        $d2 = date('Y') . '0630'; // 30/06/N
        $d3 = date('Y') . '0930'; // 30/09/N
//if( date < 31/03/N) { 33% 31/03/N 33% 30/06/N et 33% 30/09/N }
        if ($date < $d1) {
            $this->updateEcheance('C', date('Y') . '-03-31', $ligne->montant_ht * (1 / 3));
            $this->updateEcheance('C', date('Y') . '-06-30', $ligne->montant_ht * (1 / 3));
            $this->updateEcheance('C', date('Y') . '-09-30', $ligne->montant_ht * (1 / 3));
            return;
        }

//if(01/04/N < date < 31/05/N)   { 50% au 30/06/N et 50% 30/09/N}
        if ($date < $d2) {
            $this->updateEcheance('C', date('Y') . '-06-30', $ligne->montant_ht * 0.5);
            $this->updateEcheance('C', date('Y') . '-09-30', $ligne->montant_ht * 0.5);
            return;
        }

//if(30/06/N < date < 30/09/N) { 100% 30/09/N }
        if ($date < $d3) {
            $this->updateEcheance('C', date('Y') . '-09-30', $ligne->montant_ht);
            return;
        }

//Dépassement de délais -> 100% comptant
        $this->createOrUpdateEcheanceE($ligne);
    }

    public function createOrUpdateEcheanceB($ligne) {
        $ligne->echeance_code = 'B';
        $date = str_replace('-', '', $this->date_facturation);

        $d1 = date('Y') . '0331'; // 31/03/N
        $d2 = date('Y') . '0630'; // 30/06/N
//if( date < 31/03/N) { 50% 31/03/N 50% 30/06/N}
        if ($date < $d1) {
            $this->updateEcheance('B', date('Y') . '-03-31', $ligne->montant_ht * 0.5);
            $this->updateEcheance('B', date('Y') . '-06-30', $ligne->montant_ht * 0.5);
            return;
        }
//if(01/04/N <= date < 30/06/N)   { 100% au 30/06 }
        if ($date < $d2) {
            $this->updateEcheance('B', date('Y') . '-06-30', $ligne->montant_ht);
            return;
        }

//Dépassement de délais -> 100% comptant
        $this->createOrUpdateEcheanceE($ligne);
    }

    public function createOrUpdateEcheanceA($ligne) {
        $ligne->echeance_code = 'A';
        $this->updateEcheance('A', Date::getIsoDateFinDeMoisISO($this->date_facturation, 2), $ligne->montant_ht);
    }

    public function createOrUpdateEcheanceD($ligne) {
        $ligne->echeance_code = 'D';
        $date = date('Y') . '0930';
        $dateEcheance = date('Y') . '-09-30';
        if (str_replace('-', '', $this->date_facturation) < $date) {
            $this->updateEcheance('D', $dateEcheance, $ligne->montant_ht);
            return;
        }
//Dépassement de délais -> 100% comptant
        $this->createOrUpdateEcheanceE($ligne);
    }

    public function createOrUpdateEcheanceE($ligne) {
        $ligne->echeance_code = 'E';
        $this->updateEcheance('E', $this->date_facturation, $ligne->montant_ht);
    }

    public function updateEcheance($echeance_code, $date, $montant_ht) {
//Vérifie qu'il n'y a pas d'échéance à la même date avant de ajouter une nouvelle
        foreach ($this->echeances as $echeance) {
            if ($echeance->echeance_date == $date) {
                $echeance->montant_ttc += $this->ttc($montant_ht);
                if (strstr($echeance->echeance_code, $echeance_code) === FALSE)
                    $echeance->echeance_code.=' + ' . $echeance_code;
                return;
            }
        }
//Ici on est sur qu'il n'y a pas d'échéance à cette date, alors on l'ajoute
        $echeance = new stdClass();
        $echeance->echeance_code = $echeance_code;
        $echeance->montant_ttc = $this->ttc($montant_ht);
        $echeance->echeance_date = $this->date_echeance;
        $this->add("echeances")->add(count($this->echeances), $echeance);
    }

    public function storeOrigines() {
        foreach ($this->getLignes() as $ligne) {
            foreach ($ligne->origine_mouvements as $idorigine => $null) {
                if (!array_key_exists($idorigine, $this->origines))
                    $this->origines->add($idorigine, $idorigine);
            }
        }
    }

    public function storeTemplates() {
        foreach ($this->getLignes() as $ligne) {
            foreach ($ligne->origine_mouvements as $templates) {
                foreach ($templates as $template) {
                    if (!array_key_exists($template, $this->templates)) {
                        $this->templates->add($template, $template);
                    }
                }
            }
        }
    }

    public function updateTotaux() {
        $this->lignes->updateTotaux();
        $this->updateTotalHT();
        $this->updateTotalTaxe();
        $this->updateTotalTTC();
    }

    public function updateTotalHT() {
        $this->total_ht = 0;
        foreach ($this->lignes as $ligne) {
            $this->total_ht += $ligne->montant_ht;
        }
        $this->total_ht = round($this->total_ht, 2);
    }

    public function updateTotalTTC() {
        $this->total_ttc = round($this->total_ht + $this->total_taxe, 2);
    }

    public function updateTotalTaxe() {
        $this->total_taxe = 0;
        foreach ($this->lignes as $ligne) {
            $this->total_taxe += $ligne->montant_tva;
        }
        $this->total_taxe = round($this->total_taxe, 2);
    }

    public function getNbLignesMouvements() {
        $nbLigne = 0;
        foreach ($this->lignes as $lignesType) {
            $nbLigne += count($lignesType->details) + 1;
        }
        return $nbLigne;
    }

    protected function ttc($p) {
        $taux_tva = $this->getTauxTva() / 100;
        return round($p + $p * $taux_tva, 2);
    }

    public function getTauxTva() {
        if ($this->exist('taux_tva') && $this->_get('taux_tva')) {
            return round($this->_get('taux_tva'), 2);
        }
        $config_tva = FactureConfiguration::getInstance()->getTauxTva();
        $date_facturation = str_replace('-', '', $this->date_facturation);
        $taux_f = 0.0;
        foreach ($config_tva as $date => $taux) {
            if ($date_facturation >= $date) {
                $taux_f = round($taux, 2);
            }
        }
        return $taux_f;
    }

    public function save() {
        parent::save();
        $this->saveDocumentsOrigine();
    }

    public function saveDocumentsOrigine() {
        foreach ($this->origines as $docid) {
            $doc = FactureClient::getInstance()->getDocumentOrigine($docid);
            if ($doc) {
                $doc->save();
            }
        }
    }

    public function getTemplate() {
        foreach ($this->templates as $template_id) {

            return TemplateFactureClient::getInstance()->find($template_id);
        }

        return null;
    }

    protected function preSave() {
        if ($this->isNew() && $this->statut != FactureClient::STATUT_REDRESSEE) {
            $this->facturerMouvements();
            $this->storeOrigines();
        }

        if (!$this->versement_comptable) {
            $this->versement_comptable = 0;
        }

        if (!$this->versement_comptable_paiement) {
            $this->versement_comptable_paiement = 0;
        }

        $this->archivage_document->preSave();
        $this->numero_piece_comptable = $this->getNumeroPieceComptable();
    }

    public function storeDeclarant($doc) {
        $this->numero_adherent = $doc->identifiant;
        $declarant = $this->declarant;
        $declarant->nom = $doc->raison_sociale;
//$declarant->num_tva_intracomm = $this->societe->no_tva_intracommunautaire;
        $declarant->adresse = $doc->siege->adresse;
        $declarant->adresse_complementaire = $doc->siege->adresse_complementaire;
        $declarant->commune = $doc->siege->commune;
        $declarant->code_postal = $doc->siege->code_postal;
        $declarant->raison_sociale = $doc->raison_sociale;
        $this->code_comptable_client = $doc->code_comptable_client;
    }

    public function isPayee() {

        return $this->date_paiement;
    }

    public function getMontantPaiement() {
        if (!is_null($this->_get('montant_paiement'))) {

            return $this->_get('montant_paiement');
        }

        if ($this->isPayee() && !$this->isAvoir()) {

            return $this->_get('total_ttc');
        }

        return $this->_get('montant_paiement');
    }

    public function getCodeComptableClient() {
        return $this->_get('code_comptable_client');
    }

    public function getSociete() {
        return SocieteClient::getInstance()->find($this->identifiant);
    }

    public function getEtablissements() {
        if (!$this->etablissements) {

            $this->etablissements = $this->getSociete()->getEtablissementsObj();
        }
        return $this->etablissements;
    }

    public function getCompte() {

        return CompteClient::getInstance()->findByIdentifiant($this->identifiant);
    }

    public function getPrefixForRegion() {
        return EtablissementClient::getPrefixForRegion($this->region);
    }

    public function hasAvoir() {
        return ($this->exist('avoir') && !is_null($this->get('avoir')));
    }

    public function isAvoir() {

        return $this->total_ht < 0.0;
    }

    /*     * * ARCHIVAGE ** */

    public function getNumeroArchive() {

        return $this->_get('numero_archive');
    }

    public function isArchivageCanBeSet() {

        return true;
    }

    /*     * * FIN ARCHIVAGE ** */

    /*     * * VERSEMENT COMPTABLE ** */

    public function setVerseEnCompta() {
        return $this->_set('versement_comptable', 1);
    }

    public function setDeVerseEnCompta() {
        return $this->_set('versement_comptable', 0);
    }

    /*     * * VERSEMENT COMPTABLE ** */

    public function addOneMessageCommunication($message_communication = null) {
        $this->add('message_communication', $message_communication);
    }

    public function hasMessageCommunication() {
        return $this->exist('message_communication');
    }

    public function getMessageCommunicationWithDefault() {
        if ($this->exist('message_communication')) {
            return $this->_get('message_communication');
        }
        return self::MESSAGE_DEFAULT;
    }

    public function isFactureDRM(){
      return $this->hasArgument(FactureClient::TYPE_FACTURE_MOUVEMENT_DRM);
    }

    public function isFactureSV12(){
      return $this->hasArgument(FactureClient::TYPE_FACTURE_MOUVEMENT_SV12);
    }

    public function isFactureDivers(){
      return $this->hasArgument(FactureClient::TYPE_FACTURE_MOUVEMENT_DIVERS);
    }

}
