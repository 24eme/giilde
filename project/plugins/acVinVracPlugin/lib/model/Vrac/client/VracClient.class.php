<?php

class VracClient extends acCouchdbClient {

    const VRAC_VIEW_CAMPAGNE = 0;
    const VRAC_VIEW_STATUT = 1;
    const VRAC_VIEW_ID = 2;
    const VRAC_VIEW_NUMCONTRAT = 3;
    const VRAC_VIEW_NUMARCHIVE = 4;
    const VRAC_VIEW_ACHETEUR_ID = 5;
    const VRAC_VIEW_ACHETEUR_NOM = 6;
    const VRAC_VIEW_VENDEUR_ID = 7;
    const VRAC_VIEW_VENDEUR_NOM = 8;
    const VRAC_VIEW_MANDATAIRE_ID = 9;
    const VRAC_VIEW_MANDATAIRE_NOM = 10;
    const VRAC_VIEW_TYPEPRODUIT = 11;
    const VRAC_VIEW_PRODUIT_ID = 12;
    const VRAC_VIEW_PRODUIT_LIBELLE = 13;
    const VRAC_VIEW_VOLPROP = 14;
    const VRAC_VIEW_VOLENLEVE = 15;
    const VRAC_VIEW_CREATEURIDENTIFANT = 16;
    const VRAC_VIEW_SIGNATUREVENDEUR = 17;
    const VRAC_VIEW_SIGNATUREACHETEUR = 18;
    const VRAC_VIEW_SIGNATURECOURTIER = 19;
    const VRAC_VIEW_BOUTEILLE_QUANTITE = 20;
    const VRAC_VIEW_JUS_QUANTITE = 21;
    const VRAC_VIEW_RAISIN_QUANTITE = 22;
    const VRAC_VIEW_PRIX_UNITAIRE = 23;
    const VRAC_VIEW_DATE_SIGNATURE = 24;
    const VRAC_VIEW_DATE_CAMPAGNE = 25;
    const VRAC_VIEW_DATE_SAISIE = 26;
    const VRAC_VIEW_MILLESIME = 27;

    const VRAC_SIMILAIRE_KEY_VENDEURID = 0;
    const VRAC_SIMILAIRE_KEY_ACHETEURID = 1;
    const VRAC_SIMILAIRE_KEY_MANDATAIREID = 3;
    const VRAC_SIMILAIRE_KEY_TYPE = 4;
    const VRAC_SIMILAIRE_KEY_PRODUIT = 5;
    const VRAC_SIMILAIRE_KEY_VOLPROP = 6;
    const VRAC_SIMILAIRE_VALUE_NUMCONTRAT = 0;
    const VRAC_SIMILAIRE_VALUE_STATUT = 1;
    const VRAC_SIMILAIRE_VALUE_MILLESIME = 2;
    const VRAC_SIMILAIRE_VALUE_VOLPROP = 3;
    const VRAC_SIMILAIRE_VALUE_NUMARCHIVE = 4;
    const TYPE_TRANSACTION_RAISINS = 'RAISINS';
    const TYPE_TRANSACTION_MOUTS = 'MOUTS';
    const TYPE_TRANSACTION_VIN_VRAC = 'VIN_VRAC';
    const TYPE_TRANSACTION_VIN_BOUTEILLE = 'VIN_BOUTEILLE';
    const TYPE_CONTRAT_SPOT = 'SPOT';
    const TYPE_CONTRAT_PLURIANNUEL = 'PLURIANNUEL';
    const CVO_NATURE_MARCHE_DEFINITIF = 'MARCHE_DEFINITIF';
    const CVO_NATURE_COMPENSATION = 'COMPENSATION';
    const CVO_NATURE_NON_FINANCIERE = 'NON_FINANCIERE';
    const CVO_NATURE_VINAIGRERIE = 'VINAIGRERIE';
    const CATEGORIE_VIN_GENERIQUE = 'GENERIQUE';
    const CATEGORIE_VIN_DOMAINE = 'DOMAINE';
    const CATEGORIE_VIN_CHATEAU = 'CHATEAU';
    const CATEGORIE_VIN_AGE = 'AGE';
    const CATEGORIE_VIN_MARQUE = 'MARQUE';
    const CVO_REPARTITION_50_50 = '50';
    const CVO_REPARTITION_100_VITI = '100_VENDEUR';
    const CVO_REPARTITION_100_NEGO = '100_ACHETEUR';
    const CVO_REPARTITION_0_VINAIGRERIE = '0';
    const RESULTAT_LIMIT = 700;
    const STATUS_CONTRAT_BROUILLON = 'BROUILLON';
    const STATUS_CONTRAT_ATTENTE_SIGNATURE = 'ATTENTE_SIGNATURE';
    const STATUS_CONTRAT_VISE = 'VISE';
    const STATUS_CONTRAT_VALIDE = 'VALIDE';
    const STATUS_CONTRAT_SOLDE = 'SOLDE';
    const STATUS_CONTRAT_ANNULE = 'ANNULE';
    const STATUS_CONTRAT_NONSOLDE = 'NONSOLDE';
    const STATUS_SOUSSIGNECONTRAT_ATTENTE_SIGNATURE_MOI = "ATTENTE_SIGNATURE_MOI";
    const STATUS_SOUSSIGNECONTRAT_ATTENTE_SIGNATURE_AUTRES = "ATTENTE_SIGNATURE_AUTRES";

    const VERSEMENT_FA_NOUVEAU = 'NC';
    const VERSEMENT_FA_MODIFICATION = 'MC';
    const VERSEMENT_FA_ANNULATION = 'SC';
    const VERSEMENT_FA_TRANSMIS = 'TRANSMIS';

    public static $types_transaction = array('' => '', VracClient::TYPE_TRANSACTION_RAISINS => 'Raisins',
        VracClient::TYPE_TRANSACTION_MOUTS => 'Moûts',
        VracClient::TYPE_TRANSACTION_VIN_VRAC => 'Vin en vrac',
        VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE => 'Vin conditionné');

    public static $categories_vin = array(self::CATEGORIE_VIN_GENERIQUE => 'Générique', self::CATEGORIE_VIN_DOMAINE => 'Domaine', self::CATEGORIE_VIN_CHATEAU => 'Château', self::CATEGORIE_VIN_AGE => 'Age', self::CATEGORIE_VIN_MARQUE => 'Marque');

    public static $types_transaction_vins = array(self::TYPE_TRANSACTION_VIN_VRAC, self::TYPE_TRANSACTION_VIN_BOUTEILLE);

    public static $types_transaction_non_vins = array(self::TYPE_TRANSACTION_RAISINS, self::TYPE_TRANSACTION_MOUTS);

    public static $cvo_repartition = array(self::CVO_REPARTITION_50_50 => '50/50',
        self::CVO_REPARTITION_100_VITI => '100% Vendeur',
        self::CVO_REPARTITION_100_NEGO => '100% Acheteur',
        self::CVO_REPARTITION_0_VINAIGRERIE => 'Vinaigrerie');

    public static $statuts_vise = array(self::STATUS_CONTRAT_NONSOLDE, self::STATUS_CONTRAT_SOLDE, self::STATUS_CONTRAT_VISE);

    public static $statuts_labels = array(self::STATUS_CONTRAT_BROUILLON => 'Brouillon',
        self::STATUS_CONTRAT_ATTENTE_SIGNATURE => 'En attente de signature',
        self::STATUS_CONTRAT_VISE => 'En attente de traitement',
        self::STATUS_CONTRAT_VALIDE => 'En attente de traitement',
        self::STATUS_CONTRAT_SOLDE => 'Soldé',
        self::STATUS_CONTRAT_ANNULE => 'Annulé',
        self::STATUS_CONTRAT_NONSOLDE => 'Non Soldé');

    public static $statuts_labels_teledeclaration = array(self::STATUS_CONTRAT_BROUILLON => 'Brouillon',
        self::STATUS_CONTRAT_ATTENTE_SIGNATURE => 'En attente de signature',
        self::STATUS_CONTRAT_VISE => 'En attente de traitement',
        self::STATUS_CONTRAT_VALIDE => 'En attente de traitement',
        self::STATUS_CONTRAT_SOLDE => 'Validé',
        self::STATUS_CONTRAT_ANNULE => 'Annulé',
        self::STATUS_CONTRAT_NONSOLDE => 'Validé');

    public static $statuts_teledeclaration_sorted = array(self::STATUS_CONTRAT_VISE,
        self::STATUS_CONTRAT_VALIDE,
        self::STATUS_CONTRAT_BROUILLON,
        self::STATUS_CONTRAT_ATTENTE_SIGNATURE,
        self::STATUS_CONTRAT_NONSOLDE,
        self::STATUS_CONTRAT_SOLDE);

    /**
     *
     * @return DRMClient
     */
    public static function getInstance() {
        return acCouchdbManager::getClient("Vrac");
    }

    public function getContenances() {
        $contenances = VracConfiguration::getInstance()->getContenances();
        if (!$contenances)
            throw new sfException("Les contenances n'ont pas été renseignée dans le fichier de configuration app.yml");
        return $contenances;
    }

    public function getContenance($k) {
        $contenances = $this->getContenances();

        return $contenances[$k];
    }

    public function getId($id_or_numerocontrat) {
        $id = $id_or_numerocontrat;
        if (strpos($id_or_numerocontrat, 'VRAC-') === false) {
            $id = 'VRAC-' . $id_or_numerocontrat;
        }

        return $id;
    }

    public function buildNumeroContrat($annee = null, $type = null, $teledeclare = 0, $bordereau = null) {
        if ($teledeclare && $bordereau) {
            throw new sfException('options de generation d\'identifiant vrac non coherentes');
        }

        if (!$annee) {
          $annee = date('Y');
        }

        if (is_null($type)) {
          $type = date('md');
        }

        $numero = $annee;
        $numero .= str_pad($type, 4, "0");
        $numero .= $teledeclare;
        if ($bordereau) {
            $numero .= sprintf("%04d", $bordereau);
        } else {

            $numero .= sprintf("%04d", $this->getNextNoContrat($annee.$type,$teledeclare));
        }
        return $numero;
    }

    public function getNumeroContrat($id_or_numerocontrat) {

        return str_replace('VRAC-', '', $id_or_numerocontrat);
    }

    public function buildCampagne($date) {

        return ConfigurationClient::getInstance()->buildCampagne($date);
    }

    private function getNextNoContrat($date = null, $teledeclare = 0) {
        $date = ($date) ? $date : date('Ymd');
        $contrats = self::getAtDate($date,$teledeclare, acCouchdbClient::HYDRATE_ON_DEMAND)->getIds();
        if (count($contrats) > 0) {
            return substr(str_replace('VRAC-', '', max($contrats)), -4) + 1;
        } else {
             return 1;
        }
    }

    public function getAtDate($date, $teledeclare = 0, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {

        return $this->startkey('VRAC-' . $date .$teledeclare. '0000')->endkey('VRAC-' . $date .$teledeclare. '9999')->execute($hydrate);
    }

    public function findByNumContrat($num_contrat, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {

        return $this->find($this->getId($num_contrat), $hydrate);
    }

    public function findDocIdByNumArchive($campagne, $num_contrat, $recursive = 0) {

        $doc_id = ArchivageAllView::getInstance()->findDocId("Vrac", $campagne, $num_contrat);

        if ($doc_id) {

            return $doc_id;
        }

        $recursive = $recursive - 1;

        if($recursive < 0) {

            return null;
        }

        return $this->findDocIdByNumArchive(ConfigurationClient::getInstance()->getPreviousCampagne($campagne), $num_contrat, $recursive);
    }

    public function retrieveLastDocs($limit = self::RESULTAT_LIMIT) {

        return $this->descending(true)
                        ->startkey(array(0, array()))
                        ->endkey(array(0))
                        ->limit($limit)
                        ->getView('vrac', 'history');
    }

    public function getBySoussigne($campagne, $identifiant) {

        return $this->descending(true)
                        ->startkey(array($identifiant, $campagne, array()))
                        ->endkey(array($identifiant, $campagne))
                        ->reduce(false)
                        ->getView('vrac', 'soussigneidentifiant');
    }

    public function retrieveAllVracsTeledeclares() {

        return $this->descending(true)
                        ->startkey(array(1, array()))
                        ->endkey(array(1))
                        ->getView('vrac', 'history');
    }

    public function retrieveAllVracs() {

        return $this->descending(true)
                        ->getView('vrac', 'history');
    }

    public function retrieveByCampagneSocieteAndStatut($campagne, $societe, $statut, $limit = self::RESULTAT_LIMIT) {
        if (!preg_match('/[0-9]*-[0-9]*/', $campagne)){
          throw new sfException("wrong campagne format ($campagne)");
        }
        if(!$societe){
          return array();
        }
        $allEtablissements = $societe->getEtablissementsObj();
        $bySoussigne = array();
        foreach ($allEtablissements as $etablissementObj) {
            $etbId = $etablissementObj->etablissement->identifiant;
            $bySoussigneQuery = $this->startkey(array($etbId,$campagne,  array()))
                            ->endkey(array($etbId,$campagne))->descending(true);

            $local_result = $bySoussigneQuery->reduce(false)->getView('vrac', 'soussigneidentifiant');
            $bySoussigne = array_merge($bySoussigne, $local_result->rows);
        }
        return $bySoussigne;
    }

    public function retrieveByCampagneEtablissementAndStatut($societe, $campagne, $etablissement = 'tous', $statut = 'tous') {
      $result = new stdClass();
      $result->rows = array();
      $local_result_view = array();
      $local_result = array();
      $statuts = ($statut == 'tous')? array($statut) : self::$statuts_teledeclaration_sorted;
      foreach ($statuts as $statut) {
            $local_result_view = array_merge($this->retrieveByCampagneSocieteAndStatut($campagne, $societe, $statut, self::RESULTAT_LIMIT), $local_result_view);
      }
      foreach ($local_result_view as $local_r) {
        $result->rows[] = $local_r;
      }
      return $result;
    }

    public function retrieveBySocieteWithInfosLimit($societe, $etbId, $limit = self::RESULTAT_LIMIT) {

        $result = new stdClass();
        $result->rows = array();
        $this->buildInfosObj($result);
        $campagnes = array();

        $campagnes['current'] = ConfigurationClient::getInstance()->getCurrentCampagne();
        $campagnes['previous'] = ConfigurationClient::getInstance()->getPreviousCampagne($campagnes['current']);
        $statuts = self::$statuts_teledeclaration_sorted;

        $cpt = 0;
        $local_result_view = array();
        $local_result = array();

        foreach ($statuts as $statut) {
            foreach ($campagnes as $campagne) {
                $local_result_view = array_merge($this->retrieveByCampagneSocieteAndStatut($campagne, $societe, $statut, $limit), $local_result_view);
              }
            }

            foreach ($local_result_view as $local_r) {
              $local_result[$local_r->id] = $local_r;
              if ($cpt > $limit) {
                  break;
              }
              $cpt++;
            }
            foreach ($local_result as $idContrat => $contrat) {
              if ($contrat->key[VracSoussigneIdentifiantView::VRAC_VIEW_KEY_STATUT] == VracClient::STATUS_CONTRAT_BROUILLON) {
                $result->rows[] = $contrat;
                $result->infos->brouillon++;
              }
            }

            foreach ($local_result as $idContrat => $contrat) {
              if ($contrat->key[VracSoussigneIdentifiantView::VRAC_VIEW_KEY_STATUT] != VracClient::STATUS_CONTRAT_BROUILLON) {
                    $result->rows[] = $contrat;
                    $v = VracClient::getInstance()->find($contrat->id);//, acCouchdbClient::HYDRATE_JSON);
                    if($contrat->key[VracSoussigneIdentifiantView::VRAC_VIEW_KEY_STATUT] == VracClient::STATUS_CONTRAT_ATTENTE_SIGNATURE){
                      $signature_vendeur =
                      $tobeSignedByMe = $this->toBeSignedBySociete(VracClient::STATUS_CONTRAT_ATTENTE_SIGNATURE, $societe, $v->valide->date_signature_vendeur, $v->valide->date_signature_acheteur, $v->valide->date_signature_courtier);
                      $result->infos->a_signer += (int) $tobeSignedByMe;
                      $result->infos->en_attente += (int) !$tobeSignedByMe;
                    }
                }
              }
        return $result;
    }

    private function countBrouillons($societe, $viewResult) {
        $nb_brouillon = 0;
        foreach ($viewResult as $brouillon_contrat) {
            if ($brouillon_contrat->key[VracSoussigneIdentifiantView::VRAC_VIEW_KEY_TELEDECLARE] && $societe->identifiant == substr($brouillon_contrat->value[VracSoussigneIdentifiantView::VRAC_VIEW_VALUE_ACHETEUR_IDENTIFIANT], 0, 6)) {
                $nb_brouillon++;
            }
        }
        return $nb_brouillon;
    }

    public function listCampagneBySocieteId($societeId) {

        $societe = SocieteClient::getInstance()->findByIdentifiantSociete($societeId);
        $result = array();
        foreach ($societe->getEtablissementsObj() as $etbObj) {
            $result = array_merge($this->listCampagneByEtablissementId($etbObj->etablissement->identifiant));
        }
        return $result;
    }

    private function buildInfosObj(&$result) {
        $result->infos = new stdClass();
        $result->infos->a_signer = 0;
        $result->infos->brouillon = 0;
        $result->infos->en_attente = 0;
    }

    public function retrieveBySoussigne($soussigneId, $campagne = null, $limit = self::RESULTAT_LIMIT) {
        $soussigneId = EtablissementClient::getInstance()->getIdentifiant($soussigneId);
        if ($campagne && !preg_match('/[0-9]*-[0-9]*/', $campagne))
            throw new sfException("wrong campagne format ($campagne)");
        if ($campagne) {
          $bySoussigneQuery = $this->startkey(array($soussigneId, $campagne))
                ->endkey(array($soussigneId, $campagne, array()));
        }else{
          $bySoussigneQuery = $this->startkey(array($soussigneId))
                ->endkey(array($soussigneId, array()));
        }
        if ($limit) {
            $bySoussigneQuery = $bySoussigneQuery->limit($limit);
        }

        $bySoussigne = $bySoussigneQuery->reduce(false)->getView('vrac', 'soussigneidentifiant');
        return $bySoussigne;
    }

    public function retrieveBySoussigneAndStatut($soussigneId, $campagne, $statut, $limit = self::RESULTAT_LIMIT) {
        $soussigneId = EtablissementClient::getInstance()->getIdentifiant($soussigneId);
        if (!preg_match('/[0-9]*-[0-9]*/', $campagne))
            throw new sfException("wrong campagne format ($campagne)");
        $bySoussigneStatutQuery = $this->startkey(array($soussigneId, $campagne, $statut))
                ->endkey(array($soussigneId, $campagne, $statut, array()));

        if ($limit) {
            $bySoussigneStatutQuery = $bySoussigneStatutQuery->limit($limit);
        }

        $bySoussigneStatut = $bySoussigneStatutQuery->reduce(false)->getView('vrac', 'soussigneidentifiant');
        return $bySoussigneStatut;
    }

    public function retrieveBySoussigneAndType($soussigneId, $campagne, $type, $limit = self::RESULTAT_LIMIT) {
        if (!preg_match('/[0-9]*-[0-9]*/', $campagne))
            throw new sfException("wrong campagne format ($campagne)");
        if (is_array($type)) {
            $typestart = $type['start'];
            $typeend = $type['end'];
        } else {
            $typestart = $type;
            $typeend = $type;
        }
        $soussigneId = EtablissementClient::getInstance()->getIdentifiant($soussigneId);
        $bySoussigneTypeQuery = $this->startkey(array('TYPE', $soussigneId, $campagne, $typestart))
                ->endkey(array('TYPE', $soussigneId, $campagne, $typeend, array()));

        if ($limit) {
            $bySoussigneTypeQuery = $bySoussigneTypeQuery->limit($limit);
        }
        $bySoussigneType = $bySoussigneTypeQuery->reduce(false)->getView('vrac', 'soussigneidentifiant');
        return $bySoussigneType;
    }

    public function retrieveBySoussigneStatutAndType($soussigneId, $campagne, $statut, $type, $limit = self::RESULTAT_LIMIT) {
        if (!preg_match('/[0-9]*-[0-9]*/', $campagne))
            throw new sfException("wrong campagne format ($campagne)");
        $soussigneId = EtablissementClient::getInstance()->getIdentifiant($soussigneId);
        $bySoussigneTypeQuery = $this->startkey(array('STATUT', $soussigneId, $campagne, $statut, $type, true))
                ->endkey(array('STATUT', $soussigneId, $campagne, $statut, $type, true, array()));

        if ($limit) {
            $bySoussigneTypeQuery = $bySoussigneTypeQuery->limit($limit);
        }
        $bySoussigneType = $bySoussigneTypeQuery->reduce(false)->getView('vrac', 'soussigneidentifiant');
        return $bySoussigneType;
    }

    public function listCampagneByEtablissementId($identifiant) {
        $rows = $this->startkey(array($identifiant))
                        ->endkey(array($identifiant, array()))
                        ->group_level(2)
                        ->getView('vrac', 'soussigneidentifiant')->rows;

        $current = ConfigurationClient::getInstance()->getCurrentCampagne();
        $list = array();
        foreach ($rows as $r) {
            $c = $r->key[1];
            if (!$c) {

                continue;
            }
            $list[$c] = $c;
        }
        krsort($list);
        return ConfigurationClient::getInstance()->getCampagneVinicole()->consoliderCampagnesList($list);
    }

    public static function getCsvForEtiquettes($date_debut, $date_fin) {
        if (is_null($date_debut) || is_null($date_fin)) {
            throw new sfException('La date de début et la date de fin sont obligatoires.');
        }
        $date_debut_iso = Date::getIsoDateFromFrenchDate($date_debut);
        $date_fin_iso = Date::getIsoDateFromFrenchDate($date_fin);

        if (str_replace('-', '', $date_fin_iso) < str_replace('-', '', $date_debut_iso)) {
            throw new sfException('La date de fin ne peut etre supérieur à la date de fin.');
        }

        $vracs = VracStatutAndTypeView::getInstance()->findContatsByStatutsAndTypesAndDates(self::$statuts_vise, array_keys(self::$types_transaction), $date_debut_iso, $date_fin_iso . " 99:99:99");

        $result = "\xef\xbb\xbf";
        $result .="RAISON SOCIALE SOCIETE;ADRESSE SOCIETE ;ADRESSE COMPLEMENTAIRE SOCIETE;CODE POSTAL SOCIETE;VILLE SOCIETE\n";
        $adress_tab = array();
        foreach ($vracs as $key => $vrac_row) {
            $vrac = VracClient::getInstance()->find($vrac_row->id, acCouchdbClient::HYDRATE_JSON);

            if (isset($vrac->teledeclare) && $vrac->teledeclare) {
                continue;
            }

            $row_vendeur = self::constructRowForEtiquettes($vrac->vendeur, $vrac->vendeur_identifiant);
            if (!in_array($row_vendeur, $adress_tab)) {
                $result.=$row_vendeur;
                $adress_tab[] = $row_vendeur;
            }

            $row_acheteur = self::constructRowForEtiquettes($vrac->acheteur, $vrac->acheteur_identifiant);
            if (!in_array($row_acheteur, $adress_tab)) {
                $result.=$row_acheteur;
                $adress_tab[] = $row_acheteur;
            }

            $row_mandataire = self::constructRowForEtiquettes($vrac->mandataire, $vrac->mandataire_identifiant);
            if ($row_mandataire != "" && $vrac->mandataire_exist && !in_array($row_mandataire, $adress_tab)) {
                $result.=$row_mandataire;
                $adress_tab[] = $row_mandataire;
            }
        }

        $result = substr($result, 0, strlen($result) - 1);
        return $result;
    }

    protected static function constructRowForEtiquettes($soussigne, $identifiant) {
        if (!$identifiant)
            return "";
        $compte = CompteClient::getInstance()->findByIdentifiant($identifiant);
        if (!$compte)
            return "";
        $societe = $compte->getSociete();
        if (!$societe)
            return "";

        $result = ($societe->exist('raison_sociale')) ? str_replace(";", "", $societe->raison_sociale) . ";" : ";";
        if (!$societe->exist('siege'))
            return $result . ";;;\n";

        $result.= ($societe->siege->exist('adresse')) ? str_replace(";", "", $societe->siege->adresse) . ";" : ";";
        $result.= ($societe->siege->exist('adresse_complementaire')) ? str_replace(";", "", $societe->siege->adresse_complementaire) . ";" : ";";
        $result.= ($societe->siege->exist('code_postal')) ? str_replace(";", "", $societe->siege->code_postal) . ";" : ";";
        $result.= ($societe->siege->exist('commune')) ? str_replace(";", "", $societe->siege->commune) . "\n" : "\n";
        return $result;
    }

    public static function getCsvBySoussigne($vracs) {
        $result = "\xef\xbb\xbf";
        $statuts_libelles = self::getStatuts();
        foreach ($vracs->rows as $value) {
            $cpt = 0;
            $elt = $value->getRawValue()->value;

            foreach ($elt as $key => $champs) {
                $cpt++;
                if ($key == self::VRAC_VIEW_STATUT)
                    $champs = (array_key_exists($champs, $statuts_libelles)) ? $statuts_libelles[$champs] : $champs;
                if ($key == self::VRAC_VIEW_NUMARCHIVE)
                    $champs = "" . $champs;
                if ($key == self::VRAC_VIEW_TYPEPRODUIT)
                    $champs = self::$types_transaction[$champs];
                if ($key == self::VRAC_VIEW_VOLPROP || $key == self::VRAC_VIEW_VOLENLEVE)
                    $champs = sprintf("%01.02f", round($champs, 2));
                $result.='"' . $champs . '"';
                if ($cpt < count($elt))
                    $result.=';';
            }
            $result.="\n";
        }
        return $result;
    }

    public static function getCsvBySociete($vracs) {

        $result = "\xef\xbb\xbf";
        $result.= "numero_contrat;numero_archive;produit_libelle;quantite;prix_unitaire;millesime;statut;type_transaction;vendeur_identifiant;vendeur_nom;vendeur_signature;";
        $result.= "acheteur_identifiant;acheteur_nom;acheteur_signature;courtier_identifiant;courtier_nom;courtier_signature\n";

        foreach ($vracs as $vracsRows) {
          foreach ($vracsRows as $contrat) {
            $cpt = 0;
              $vrac = VracClient::getInstance()->find($contrat->id);
              $quantite = "";
              switch ($vrac->type_transaction) {
                case self::TYPE_TRANSACTION_MOUTS:
                case self::TYPE_TRANSACTION_VIN_VRAC:
                    $quantite = $vrac->jus_quantite;
                    break;
                case self::TYPE_TRANSACTION_RAISINS:
                    $quantite = $vrac->raisin_quantite;
                    break;
                case self::TYPE_TRANSACTION_VIN_BOUTEILLE:
                    $quantite = $vrac->bouteilles_quantite;
                    break;
            }

            $result.= $contrat->value[self::VRAC_VIEW_NUMCONTRAT] . ';';
            $result.= $contrat->value[self::VRAC_VIEW_NUMARCHIVE] . ';';

            $result.= $vrac->produit_libelle . ';';
            $result.= str_replace('.', ',', $quantite) . ';';
            $result.= str_replace('.', ',', $vrac->prix_unitaire) . ';';
            $result.= $vrac->millesime . ';';

            $result.= $vrac->valide->statut . ';';
            $result.= $vrac->type_transaction . ';';

            $result.= $contrat->value[self::VRAC_VIEW_VENDEUR_ID] . ';';
            $result.= $contrat->value[self::VRAC_VIEW_VENDEUR_NOM] . ';';
            $result.= ($vrac->teledeclare)? (new DateTime($vrac->valide->date_signature_vendeur))->format("Y-m-d") : (new DateTime($vrac->valide->date_saisie))->format("Y-m-d"). ';';

            $result.= $contrat->value[self::VRAC_VIEW_ACHETEUR_ID] . ';';
            $result.= $contrat->value[self::VRAC_VIEW_ACHETEUR_NOM] . ';';
            $result.= ($vrac->teledeclare)? (new DateTime($vrac->valide->date_signature_acheteur))->format("Y-m-d") : (new DateTime($vrac->valide->date_saisie))->format("Y-m-d") . ';';

            $result.= $contrat->value[self::VRAC_VIEW_MANDATAIRE_ID] . ';';
            $result.= $contrat->value[self::VRAC_VIEW_MANDATAIRE_NOM] . ';';
            $result.= ($vrac->teledeclare)? (new DateTime($vrac->valide->date_signature_courtier))->format("Y-m-d") : (new DateTime($vrac->valide->date_saisie))->format("Y-m-d") . ';';

            $result.="\n";
        }
}
        return $result;
    }

    public function retrieveSimilaryContracts($vrac) {
        if (isset($vrac->vendeur_identifiant) || isset($vrac->acheteur_identifiant)) {
            return false;
        }
        $args = array();
        if ($vrac->mandataire_exist) {
            $args = array($vrac->vendeur_identifiant, $vrac->acheteur_identifiant, $vrac->mandataire_identifiant, $vrac->type_transaction);
        } else {
            $args = array($vrac->vendeur_identifiant, $vrac->acheteur_identifiant, '', $vrac->type_transaction);
        }
        if ($vrac->produit) {
            $args[] = $vrac->produit;

            if ($vrac->volume_propose) {
                $args[] = $vrac->volume_propose;
            }
        }
        $start = $this->startkey($args);
        $args[] = array();
        $view = $start->endkey($args)->limit(10)->getView('vrac', 'vracSimilaire');
        if ($vrac->_id)
            $this->filterSimilaryContracts($view, $vrac->_id);
        return $view->rows;
    }

    public function retrieveSimilaryContractsWithProdTypeVol($params) {
        if ((empty($params['vendeur'])) || (empty($params['acheteur'])) || (empty($params['type']))) {

            return false;
        }

        if (empty($params['produit']) && !empty($params['volume'])) {

            return false;
        }

        if (empty($params['volume']) && empty($params['produit'])) {

            return $this->startkey(array($params['vendeur'], $params['acheteur'], $params['mandataire'], $params['type']))
                            ->endkey(array($params['vendeur'], $params['acheteur'], $params['mandataire'], $params['type'], array()))->limit(10)->getView('vrac', 'vracSimilaire');
        }

        if (empty($params['volume'])) {

            return $this->startkey(array($params['vendeur'], $params['acheteur'], $params['mandataire'], $params['type'], $params['produit']))
                            ->endkey(array($params['vendeur'], $params['acheteur'], $params['mandataire'], $params['type'], $params['produit'], array()))->limit(10)->getView('vrac', 'vracSimilaire');
        }

        $volumeBas = ((float) $params['volume']) * 0.95;
        $volumeHaut = ((float) $params['volume']) * 1.05;

        return $this->startkey(array($params['vendeur'], $params['acheteur'], $params['mandataire'], $params['type'], $params['produit'], $volumeBas))
                        ->endkey(array($params['vendeur'], $params['acheteur'], $params['mandataire'], $params['type'], $params['produit'], $volumeHaut, array()))->limit(10)->getView('vrac', 'vracSimilaire');
    }

    public function filterSimilaryContracts($similaryContracts, $vracid) {

        foreach ($similaryContracts->rows as $key => $value) {
            if ($value->id === $vracid) {
                unset($similaryContracts->rows[$key]);
                return;
            }
        }
    }

    public function retrieveByNumeroAndEtablissementAndHashOrCreateIt($id, $etablissement, $hash, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $vrac = $this->retrieveById($id);
        if (!$vrac) {
            $vrac = new Vrac();
            $vrac->vendeur_identifiant = $etablissement;
            $vrac->numero_contrat = $id;
            $vrac->produit = $hash;
            $vrac->type_transaction = self::TYPE_TRANSACTION_VIN_VRAC;
        }
        if ($etablissement != $vrac->vendeur_identifiant)
            throw new sfException('le vendeur ne correpond pas à l\'établissement initial');
        if (!preg_match("|^$hash|", $vrac->produit))
            throw new sfException('Le hash du produit ne correpond pas au hash initial (' . $vrac->produit . '<->' . $hash . ')');
        return $vrac;
    }

    public function createContratFromDrmDetails($details) {
      $idContrat = $details->getKey();
      $vrac = $this->retrieveById($idContrat);
      if ($vrac) {
            if ($vrac->acheteur_identifiant != $details->acheteur) {
              $vrac->acheteur_identifiant = $details->acheteur;
              $vrac->setInformations();
            }
            return $vrac;
      }

      $vendeurId = $details->getDocument()->getEtablissement()->identifiant;
      $vendeur = EtablissementClient::getInstance()->find($vendeurId);
      if(!$vendeur){
          throw new sfException("Le vendeur d'id $vendeurId n'existe pas");
      }
      $acheteurId = $details->acheteur;
      $acheteur = EtablissementClient::getInstance()->find($acheteurId);
      if(!$acheteur){
          throw new sfException("L'acheteur d'id $acheteurId n'existe pas");
      }
      $hash = $details->getDetail()->getCepage()->getHash();

        $vrac = new Vrac();
        $vrac->vendeur_identifiant = $vendeurId;
        $vrac->campagne = $details->getDocument()->campagne;
        $vrac->numero_contrat = $idContrat;
        $vrac->numero_archive = $idContrat;
        $vrac->acheteur_identifiant = $acheteurId;
        $vrac->produit = $hash;
        $vrac->type_transaction = $details->type_contrat;
        $vrac->jus_quantite = $details->volume;
        $vrac->volume_propose = $details->volume;
        $vrac->prix_initial_unitaire_hl = $details->prixhl;
        $vrac->prix_initial_unitaire = $details->prixhl;
        $vrac->prix_unitaire = $details->prixhl;
        $vrac->numero_archive = ($details->exist('numero_archive') && $details->numero_archive)? $details->numero_archive : null;
        if ($details->date_enlevement) {
          $vrac->enlevement_date = $details->date_enlevement;
          $vrac->date_visa = $details->date_enlevement;
          $vrac->date_campagne =  $details->date_enlevement;
        }else{
          $vrac->enlevement_date = $details->getDocument()->getDate();
          $vrac->date_visa = $details->getDocument()->getDate();
          $vrac->valide->date_campagne = $details->getDocument()->getDate();
        }
        if ($details->getDocument()->exist('validation')) {
          $vrac->valide->date_saisie = $details->getDocument()->validation->date_saisie;
          $vrac->date_signature = $details->getDocument()->validation->date_signee;
        }

        $vrac->setInformations();
        $vrac->update();

        return $vrac;
    }

    /**
     *
     * @param string $id
     * @param integer $hydrate
     * @return Vrac
     */
    public function retrieveById($id, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {

        return parent::retrieveDocumentById('VRAC-' . $id, $hydrate);
    }

    public static function getTypes() {
        return array(self::TYPE_TRANSACTION_MOUTS => "Moûts",
            self::TYPE_TRANSACTION_RAISINS => "Raisins",
            self::TYPE_TRANSACTION_VIN_BOUTEILLE => "Conditionné",
            self::TYPE_TRANSACTION_VIN_VRAC => "Vrac");
    }

    public static function getStatuts() {
        return array(self::STATUS_CONTRAT_ANNULE => "Annulé",
            self::STATUS_CONTRAT_NONSOLDE => "Non soldé",
            self::STATUS_CONTRAT_SOLDE => "Soldé");
    }

    public function getLibelleFromId($id, $separation = " ") {
        $id = str_replace('VRAC-', '', $id);
        return sprintf('%s%s%s', substr($id, 0, 8), $separation, substr($id, 8, strlen($id) - 1));
    }

    public function getLibelleContratNum($id) {
        // if(strlen($id)!=13) throw new sfException(sprintf ('Le numéro de contrat %s ne possède pas un bon format.',$id));
        $annee = substr($id, 0, 4);
        $mois = substr($id, 4, 2);
        $jour = substr($id, 6, 2);
        $num = substr($id, 8);
        return $jour . '/' . $mois . '/' . $annee . ' n° ' . $num;
    }

    public function getNumeroArchiveEtDate($id) {
        $c = $this->findByNumContrat($id);
        return $c->numero_archive . ' du ' . $c->date_signature;
    }

    public function retreiveByStatutsTypes($statuts, $types) {
        return VracStatutAndTypeView::getInstance()->findContatsByStatutsAndTypes($statuts, $types);
    }

    public function retreiveByStatutsTypesAndDate($statuts, $types, $date) {
        return VracStatutAndTypeView::getInstance()->findContatsByStatutsAndTypesAndDate($statuts, $types, $date);
    }

    public function getMandatants() {

        return array('acheteur' => 'acheteur', 'vendeur' => 'vendeur');
    }

    public function getMaster($id) {
        return $this->find($id);
    }

    public function toBeSignedBySociete($statut, $societe, $signature_vendeur, $signature_acheteur, $signature_courtier) {
        return ($statut === self::STATUS_CONTRAT_ATTENTE_SIGNATURE) &&
                (($societe->isCourtier() && !$signature_courtier) || ($societe->isNegociant() && !$signature_acheteur) || ($societe->isViticulteur() && !$signature_vendeur));
    }

    public function buildEnlevements($vrac) {
        $all_mouvements_vendeur = DRMMouvementsConsultationView::getInstance()->findByEtablissement($vrac->vendeur_identifiant);
        $enlevements = array();
        foreach ($all_mouvements_vendeur->rows as $rowView) {
            $vrac_view_id = "VRAC-".$rowView->key[DRMMouvementsConsultationView::KEY_VRAC_NUMERO];
            if ($vrac_view_id && $vrac_view_id == $vrac->_id) {

                $index = $rowView->value[DRMMouvementsConsultationView::VALUE_MOUVEMENT_ID];
                $enlevements[$index] = new stdClass();
                $enlevements[$index]->drm_id = $rowView->id;
                $enlevements[$index]->periode = $rowView->key[DRMMouvementsConsultationView::KEY_PERIODE];
                $enlevements[$index]->volume = $rowView->value[DRMMouvementsConsultationView::VALUE_VOLUME] * -1;

            }
        }

        return $enlevements;
    }

    public function calculCvoRepartition($vrac) {
        if($vrac->acheteur->region != EtablissementClient::REGION_CVO) {

            return self::CVO_REPARTITION_100_VITI;
        }

        return VracConfiguration::getInstance()->getRepartitionCvo();
    }

}
