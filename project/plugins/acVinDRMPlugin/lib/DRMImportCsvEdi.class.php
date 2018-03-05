<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DRMImportCsvEdi
 *
 * @author mathurin
 */
class DRMImportCsvEdi extends DRMCsvEdi {

    protected $configuration = null;
    protected $mouvements = array();
    protected $csvDoc = null;

    public function __construct($file, DRM $drm = null) {
        if(is_null($this->csvDoc)) {
            $this->csvDoc = CSVDRMClient::getInstance()->createOrFindDocFromDRM($file, $drm);
        }
        $this->initConf();
        parent::__construct($file, $drm);
    }

    public function getCsvDoc() {

        return $this->csvDoc;
    }

    protected function initConf() {
        $this->configuration = ConfigurationClient::getCurrent();
        $this->mouvements = $this->buildAllMouvements();
    }

    public function getDocRows() {
        return $this->getCsv($this->csvDoc->getFileContent());
    }

    /**
     * CHECK DU CSV
     */
    public function checkCSV() {
        $this->csvDoc->clearErreurs();
        $this->checkCSVIntegrity();
        if ($this->csvDoc->hasErreurs()) {
            $this->csvDoc->setStatut(self::STATUT_ERREUR);
            $this->csvDoc->save();
            return;
        }
        // Check annexes
        $this->checkImportAnnexesFromCSV();
        // Check mouvements
        $this->checkImportMouvementsFromCSV();
        // Check Crds
        $this->checkImportCrdsFromCSV();
        // Check Crds
        $this->checkHorsRegionFromCSV();

        if ($this->csvDoc->hasErreurs()) {
            $this->csvDoc->setStatut(self::STATUT_WARNING);
            $this->csvDoc->save();
            return;
        }
        $this->csvDoc->setStatut(self::STATUT_VALIDE);
        $this->csvDoc->save();
    }

    /**
     * IMPORT DEPUIS LE CSV
     */
    public function importCSV($withSave = true) {
        if($this->drm->isNew()) {
            $this->drm->constructId();
        }
        $this->importAnnexesFromCSV();

        $this->importMouvementsFromCSV();
        $this->importCrdsFromCSV();
        //$this->drm->teledeclare = true;
        $this->drm->etape = DRMClient::ETAPE_VALIDATION;
        $this->drm->type_creation = "IMPORT";
        $this->drm->buildFavoris();
        $this->drm->storeDeclarant();
        $this->drm->initSociete();
        $this->updateAndControlCoheranceStocks();

        if($withSave) {
            $this->drm->save();
        }
    }

    public function updateAndControlCoheranceStocks() {
        /*$stocks = array();
        foreach($this->drm->getProduitsDetails() as $detail) {
          $stocks[$detail->getHash()] = $detail->stocks_fin->final;
        }*/

        $this->drm->update();

        /*foreach($this->drm->getProduitsDetails() as $detail) {
            if(!array_key_exists($detail->getHash(), $stocks) || is_null($stocks[$detail->getHash()])) {
                continue;
            }

            if(round($stocks[$detail->getHash()], 2) == round($detail->stocks_fin->final, 2)) {
                continue;
            }
            $this->csvDoc->addErreur($this->createError(1, sprintf("%s %0.2f hl (CSV) / %0.2f hl (calculé)", $detail->produit_libelle, $stocks[$detail->getHash()], $detail->stocks_fin->final), "Le stock fin de mois du CSV différent du calculé"));
        }*/

        if ($this->csvDoc->hasErreurs()) {
            $this->csvDoc->setStatut(self::STATUT_WARNING);
            $this->csvDoc->save();
        }
    }

    private function checkCSVIntegrity() {
        $ligne_num = 1;
        foreach ($this->getDocRows() as $csvRow) {
            if(count($csvRow) < 17){
              $this->csvDoc->addErreur($this->createWrongFormatFieldCountError($ligne_num, $csvRow));
              $ligne_num++;
              continue;
            }
            if ($ligne_num == 1 && KeyInflector::slugify($csvRow[self::CSV_TYPE]) == 'TYPE') {
                $ligne_num++;
                continue;
            }
            if (!in_array(KeyInflector::slugify($csvRow[self::CSV_TYPE]), self::$permitted_types)) {
                $this->csvDoc->addErreur($this->createWrongFormatTypeError($ligne_num, $csvRow));
            }
            if (!preg_match('/^[0-9]{6}$/', KeyInflector::slugify($csvRow[self::CSV_PERIODE]))) {
                $this->csvDoc->addErreur($this->createWrongFormatPeriodeError($ligne_num, $csvRow));
            }
            if (!preg_match('/^FR0[0-9]{10}$/', KeyInflector::slugify($csvRow[self::CSV_NUMACCISE]))) {
                //$this->csvDoc->addErreur($this->createWrongFormatNumAcciseError($ligne_num, $csvRow));
            }
            $ligne_num++;
        }
    }

    private function checkImportMouvementsFromCSV() {
        return $this->importMouvementsFromCSV(true);
    }

    private function checkImportCrdsFromCSV() {
        return $this->importCrdsFromCSV(true);
    }

    private function checkHorsRegionFromCSV() {
    	$etablissementObj = $this->drm->getEtablissementObject();
    	if ($etablissementObj->region == EtablissementClient::REGION_HORS_CVO) {
    		$this->csvDoc->addErreur($this->importHorsRegionError());
    	}
    }

    private function checkImportAnnexesFromCSV() {
        return $this->importAnnexesFromCSV(true);
    }

    private function importMouvementsFromCSV($just_check = false) {
            $aggregatedEdiList = null;
            if(DRMConfiguration::getInstance()->hasAggregatedEdi()){
              $aggregatedEdiList = DRMConfiguration::getInstance()->getAggregatedEdi();
            }
            $all_produits = $this->configuration->declaration->getProduitsAll();

        $num_ligne = 1;
        foreach ($this->getDocRows() as $csvRow) {
            if (KeyInflector::slugify($csvRow[self::CSV_TYPE] != self::TYPE_CAVE)) {
                $num_ligne++;
                continue;
            }

            $csvLibelleProductArray = $this->buildLibellesArrayWithRow($csvRow, true);

            $founded_produit = false;

            if(!$founded_produit) {
                $founded_produit = $this->configuration->identifyProductByLibelle(preg_replace("/[ ]+/", " ", sprintf("%s %s %s %s %s %s %s", $csvRow[self::CSV_CAVE_CERTIFICATION], $csvRow[self::CSV_CAVE_GENRE], $csvRow[self::CSV_CAVE_APPELLATION], $csvRow[self::CSV_CAVE_MENTION], $csvRow[self::CSV_CAVE_LIEU], $csvRow[self::CSV_CAVE_COULEUR], $csvRow[self::CSV_CAVE_CEPAGE])));
            }

            if(!$founded_produit) {
                $founded_produit = $this->configuration->identifyProductByLibelle(trim(preg_replace("/[ ]+/", " ", $csvRow[self::CSV_CAVE_LIBELLE_PRODUIT])));
            }

            if (!$founded_produit) {
                $this->csvDoc->addErreur($this->productNotFoundError($num_ligne, $csvRow));
                $num_ligne++;
                continue;
            }

            if (!$founded_produit) {
                foreach ($all_produits as $produit) {
                    if ($founded_produit) {
                        break;
                    }
                    $produitConfLibelle = $this->slugifyProduitConf($produit);
                    if (count(array_diff($csvLibelleProductArray, $produitConfLibelle)) < count(array_diff($produitConfLibelle, $csvLibelleProductArray))) {
                        continue;
                    }elseif((count(array_diff($csvLibelleProductArray, $produitConfLibelleAOC))) && (count(array_diff($csvLibelleProductArray, $produitConfLibelleAOP)))
                        && ($libelleCompletConfAOC != $csvLibelleProductComplet) && ($libelleCompletConfAOP != $csvLibelleProductComplet)
                        && ($libelleCompletConfAOC != $libelleCompletEnCsv) && ($libelleCompletConfAOP != $libelleCompletEnCsv)
                        && ($this->slugifyProduitArrayOrString($produit->getLibelleFormat()) != $libelleCompletEnCsv)) {
                        continue;
                    }
                    $founded_produit = $produit;
                    $date = new DateTime($this->drm->getDate());
                    if($founded_produit->getTauxCVO($date) == "-1" && $founded_produit->getTauxDouane($date) == "-1"){
                      if($aggregatedEdiList && count($aggregatedEdiList) && count($aggregatedEdiList[0])
                      && isset($aggregatedEdiList[0][$founded_produit->getHash()])){
                        $founded_produit = $all_produits[$aggregatedEdiList[0][$founded_produit->getHash()]];
                      }else{
                        $founded_produit = $produit->getProduitSiblingWithTaux($date);
                      }
                    }
                }
            }

            $type_douane_drm = KeyInflector::slugify($csvRow[self::CSV_CAVE_TYPE_DRM]);
            $type_douane_drm_key = $this->getDetailsKeyFromDRMType($type_douane_drm);
            $type_drm = KeyInflector::slugify($csvRow[self::CSV_CAVE_TYPE_MOUVEMENT]);
            $cat_mouvement = KeyInflector::slugify($csvRow[self::CSV_CAVE_CATEGORIE_MOUVEMENT]);
            $type_mouvement = KeyInflector::slugify($csvRow[self::CSV_CAVE_TYPE_MOUVEMENT]);

            if (!array_key_exists($cat_mouvement, $this->mouvements[$type_douane_drm_key])) {
                $this->csvDoc->addErreur($this->categorieMouvementNotFoundError($num_ligne, $csvRow));
                $num_ligne++;
                continue;
            }
            if (!array_key_exists($type_mouvement, $this->mouvements[$type_douane_drm_key][$cat_mouvement])) {
                $this->csvDoc->addErreur($this->typeMouvementNotFoundError($num_ligne, $csvRow));
                $num_ligne++;
                continue;
            }
            $confDetailMvt = $this->mouvements[$type_douane_drm_key][$cat_mouvement][$type_mouvement];

            if (!$just_check) {
                $drmDetails = $this->drm->addProduit($founded_produit->getHash(), $type_douane_drm_key);

                $detailTotalVol = round(floatval($csvRow[self::CSV_CAVE_VOLUME]), 2);
                $volume = round(floatval($csvRow[self::CSV_CAVE_VOLUME]), 2);

                $cat_key = $confDetailMvt->getParent()->getKey();
                $type_key = $confDetailMvt->getKey();
                if($cat_key == "stocks_debut" && !$drmDetails->canSetStockDebutMois()) {

                    continue;
                }
                if ($confDetailMvt->hasDetails()) {
                    $detailTotalVol += floatval($drmDetails->getOrAdd($cat_key)->getOrAdd($type_key));

                    if (preg_match("/^export/", $type_key)) {
                        $pays = ConfigurationClient::getInstance()->findCountry($csvRow[self::CSV_CAVE_EXPORTPAYS]);
                        $export = DRMESDetailExport::freeInstance($this->drm);
                        $export->volume = $volume;
                        $export->identifiant = $pays;
                        $drmDetails->getOrAdd($cat_key)->getOrAdd($type_key . '_details')->addDetail($export);
                    }

                    if ($type_key == 'vrac' || $type_key == 'contrat') {
                        $vrac_id = $this->findContratDocId($csvRow);

                        $detailNode = $drmDetails->getOrAdd($cat_key)->getOrAdd($type_key . '_details')->add($vrac_id);
                        if ($detailNode->volume) {
                            $volume+=$detailNode->volume;
                        }
                        $date = new DateTime($this->drm->getDate());
                        $detailNode->volume = $volume;
                        $detailNode->identifiant = $vrac_id;
                        $detailNode->date_enlevement = $date->format('Y-m-d');
                    }
                    if($type_key == 'creationvrac' || $type_key == 'creationvractirebouche'){
                        $creationvrac = DRMESDetailCreationVrac::freeInstance($this->drm);
                        $creationvrac->volume = $volume;
                        $creationvrac->prixhl = floatval($csvRow[18]);
                        $creationvrac->acheteur = $csvRow[17];
                        $creationvrac->type_contrat = ($type_key == 'creationvrac')? VracClient::TYPE_TRANSACTION_VIN_VRAC : VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE;
                        $creationvrac->date_enlevement = DateTime::createFromFormat('Ymd',$csvRow[19])->format('Y-m-d');

                        $drmDetails->getOrAdd($cat_key)->getOrAdd($type_key . '_details')->addDetail($creationvrac);
                    }
                } else {
                    $oldVolume = $drmDetails->getOrAdd($cat_key)->getOrAdd($type_key);
                    if($cat_key == "stocks_debut" && !is_null($oldVolume) && $oldVolume != "") {
                        $this->drm->commentaire .= sprintf("IMPORT de %s le stock_debut %s de %s hl n'a pas été pris en compte\n", $drmDetails->getLibelle(), $type_key, $detailTotalVol);
                    } else {
                        $drmDetails->getOrAdd($cat_key)->add($type_key, $oldVolume + $detailTotalVol);
                    }
                }

                if(isset($csvRow[self::CSV_CAVE_COMMENTAIRE]) && $csvRow[self::CSV_CAVE_COMMENTAIRE] && trim($csvRow[self::CSV_CAVE_COMMENTAIRE])) {
                    $this->drm->commentaire .= str_replace("\\n", "\n", trim($csvRow[self::CSV_CAVE_COMMENTAIRE]));
                    if(!preg_match("/\n$/", $this->drm->commentaire)) {
                        $this->drm->commentaire .= "\n";
                    }
                }

            } else {
                if ($confDetailMvt->hasDetails()) {
                    if ($confDetailMvt->getKey() == 'export') {
                        $pays = ConfigurationClient::getInstance()->findCountry($csvRow[self::CSV_CAVE_EXPORTPAYS]);
                        if (!$pays) {
                            $this->csvDoc->addErreur($this->exportPaysNotFoundError($num_ligne, $csvRow));
                            $num_ligne++;
                            continue;
                        }
                    }
                    if ($confDetailMvt->getKey() == 'vrac' || $confDetailMvt->getKey() == 'contrat') {
                        if (!$csvRow[self::CSV_CAVE_CONTRATID]) {
                            $this->csvDoc->addErreur($this->contratIDEmptyError($num_ligne, $csvRow));
                            $num_ligne++;
                            continue;
                        }

                        $vrac_id = $this->findContratDocId($csvRow);

                        if(!$vrac_id) {
                            $this->csvDoc->addErreur($this->contratIDNotFoundError($num_ligne, $csvRow));
                            $num_ligne++;
                            continue;
                        }
                    }
                }
            }

            $num_ligne++;
        }
    }

    private function importCrdsFromCSV($just_check = false) {
        $num_ligne = 1;
        $etablissementObj = $this->drm->getEtablissementObject();

        $crd_regime = ($etablissementObj->exist('crd_regime'))? $etablissementObj->get('crd_regime') : EtablissementClient::REGIME_CRD_COLLECTIF_SUSPENDU;
        $all_contenances = VracConfiguration::getInstance()->getContenances();
        foreach ($this->getDocRows() as $csvRow) {
            if (KeyInflector::slugify($csvRow[self::CSV_TYPE] != self::TYPE_CRD)) {
                $num_ligne++;
                continue;
            }
            $genre = KeyInflector::slugify($csvRow[self::CSV_CRD_GENRE]);
            $couleur = KeyInflector::slugify($csvRow[self::CSV_CRD_COULEUR]);
            $litrageLibelle = $csvRow[self::CSV_CRD_CENTILITRAGE];
            $categorie_key = $csvRow[self::CSV_CRD_CATEGORIE_KEY];
            $type_key = $csvRow[self::CSV_CRD_TYPE_KEY];
            $quantite = KeyInflector::slugify($csvRow[self::CSV_CRD_QUANTITE]);
            $fieldNameCrd = $categorie_key;
            if ($categorie_key != "stock_debut" && $categorie_key != "stock_fin") {
                $fieldNameCrd.="_" . $type_key;
            }
            if (!$just_check) {

                $centilitrage = $all_contenances[$litrageLibelle] * 100000;
                $regimeNode = $this->drm->getOrAdd('crds')->getOrAdd($crd_regime);
                $keyNode = $regimeNode->constructKey($genre, $couleur, $centilitrage, $litrageLibelle);
                if (!$regimeNode->exist($keyNode)) {
                    $regimeNode->getOrAddCrdNode($genre, $couleur, $centilitrage, $litrageLibelle);
                }
                $regimeNode->getOrAdd($keyNode)->$fieldNameCrd = intval($quantite);
                $num_ligne++;
            }
        }
    }

    private function importAnnexesFromCSV($just_check = false) {
        $num_ligne = 1;
        $typesAnnexes = array_keys($this->type_annexes);
        foreach ($this->getDocRows() as $csvRow) {
            if (KeyInflector::slugify($csvRow[self::CSV_TYPE] != self::TYPE_ANNEXE)) {
                $num_ligne++;
                continue;
            }
            switch (KeyInflector::slugify($csvRow[self::CSV_ANNEXE_TYPEANNEXE])) {
                case self::TYPE_ANNEXE_NONAPUREMENT:
                    $numero_document = KeyInflector::slugify($csvRow[self::CSV_ANNEXE_NUMERODOCUMENT]);
                    $date_emission = KeyInflector::slugify($csvRow[self::CSV_ANNEXE_NONAPUREMENTDATEEMISSION]);
                    $dt = DateTime::createFromFormat("d-m-Y", $date_emission);

                    $numero_accise = KeyInflector::slugify($csvRow[self::CSV_ANNEXE_NONAPUREMENTACCISEDEST]);
                    if (!$numero_document) {
                        if ($just_check) {
                            $this->csvDoc->addErreur($this->annexesNumeroDocumentError($num_ligne, $csvRow));
                        }
                        $num_ligne++;
                        break;
                    }
                    if (!$date_emission || $dt == false || array_sum($dt->getLastErrors())) {
                        if ($just_check) {
                            $this->csvDoc->addErreur($this->annexesNonApurementWrongDateError($num_ligne, $csvRow));
                        }
                        $num_ligne++;
                        break;
                    }
                    if (!preg_match('/^FR0[0-9]{10}$/', $numero_accise)) {
                        if ($just_check) {
                            $this->csvDoc->addErreur($this->annexesNonApurementWrongNumAcciseError($num_ligne, $csvRow));
                        }
                        $num_ligne++;
                        break;
                    }
                    if (!$just_check) {
                        $nonAppurementNode = $this->drm->getOrAdd('releve_non_apurement')->getOrAdd($numero_document);
                        $nonAppurementNode->numero_document = $numero_document;
                        $nonAppurementNode->date_emission = $dt->format('Y-m-d');
                        $nonAppurementNode->numero_accise = $numero_accise;
                    }
                    $num_ligne++;
                    break;

                case DRMClient::DRM_DOCUMENTACCOMPAGNEMENT_DAADAC:
                case DRMClient::DRM_DOCUMENTACCOMPAGNEMENT_DSADSAC:
                case DRMClient::DRM_DOCUMENTACCOMPAGNEMENT_EMPREINTE:
                    $docTypeAnnexe = $this->drm->getOrAdd('documents_annexes')->getOrAdd(KeyInflector::slugify($csvRow[self::CSV_ANNEXE_TYPEANNEXE]));
                    $annexeTypeMvt = KeyInflector::slugify($csvRow[self::CSV_ANNEXE_TYPEMVT]);
                    $numDocument = KeyInflector::slugify($csvRow[self::CSV_ANNEXE_QUANTITE]);
                    if (!in_array($annexeTypeMvt, self::$permitted_annexes_type_mouvements)) {
                        if ($just_check) {
                            $this->csvDoc->addErreur($this->annexesTypeMvtWrongFormatError($num_ligne, $csvRow));
                        } $num_ligne++;
                        break;
                    }
                    if (!$numDocument) {
                        if ($just_check) {
                            $this->csvDoc->addErreur($this->annexesNumeroDocumentError($num_ligne, $csvRow));
                        } $num_ligne++;
                        break;
                    }
                    if (!$just_check) {
                        $docTypeAnnexe->add(strtolower($annexeTypeMvt), $numDocument);
                    }
                    $num_ligne++;
                    break;
                case self::TYPE_ANNEXE_STATS_EUROPEENES :
                    $this->drm->getOrAdd('declaratif')->getOrAdd('statistiques')->add(strtolower($csvRow[self::CSV_ANNEXE_TYPEMVT]),round(floatval($csvRow[self::CSV_ANNEXE_QUANTITE]), 2));
                    $num_ligne++;
                    break;
                default:
                    if ($just_check) {
                        $this->csvDoc->addErreur($this->typeDocumentWrongFormatError($num_ligne, $csvRow));
                    }
                    $num_ligne++;
                    break;
            }
        }
    }

    private function getDetailsKeyFromDRMType($drmType ) {
        if(KeyInflector::slugify($drmType) == "SUSPENDU") {

            return DRM::DETAILS_KEY_SUSPENDU;
        }

        if(KeyInflector::slugify($drmType) == "ACQUITTE") {

            return DRM::DETAILS_KEY_ACQUITTE;
        }

        throw new sfException(sprintf("Le type de DRM \"%s\" n'est pas connu", $drmType));
    }

    private function findContratDocId($csvRow) {
        if($vrac = VracClient::getInstance()->findByNumContrat($csvRow[self::CSV_CAVE_CONTRATID], acCouchdbClient::HYDRATE_JSON)) {

            return $vrac->_id;
        }

        return VracClient::getInstance()->findDocIdByNumArchive($this->drm->campagne, $csvRow[self::CSV_CAVE_CONTRATID], 2);
    }

    /**
     * Functions de création d'erreurs
     */

     private function createWrongFormatFieldCountError($num_ligne, $csvRow) {
         return $this->createError($num_ligne, KeyInflector::slugify($csvRow[self::CSV_TYPE]), "La ligne possède trop peu de colonnes.");
     }

    private function createWrongFormatTypeError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, KeyInflector::slugify($csvRow[self::CSV_TYPE]), "Choix possible type : " . implode(', ', self::$permitted_types));
    }

    private function createWrongFormatPeriodeError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, KeyInflector::slugify($csvRow[self::CSV_PERIODE]), "Format période : AAAAMM");
    }

    private function createWrongFormatNumAcciseError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, KeyInflector::slugify($csvRow[self::CSV_NUMACCISE]), "Format numéro d'accise : FR0XXXXXXXXXX");
    }

    private function productNotFoundError($num_ligne, $csvRow) {
        $libellesArray = $this->buildLibellesArrayWithRow($csvRow);
        return $this->createError($num_ligne, implode(' ', $libellesArray), "Le produit n'a pas été trouvé");
    }

    private function categorieMouvementNotFoundError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, $csvRow[self::CSV_CAVE_CATEGORIE_MOUVEMENT], "La catégorie de mouvement n'a pas été trouvée");
    }

    private function typeMouvementNotFoundError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, $csvRow[self::CSV_CAVE_TYPE_MOUVEMENT], "Le type de mouvement n'a pas été trouvé");
    }

    private function exportPaysNotFoundError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, $csvRow[self::CSV_CAVE_EXPORTPAYS], "Le pays d'export n'a pas été trouvé");
    }

    private function contratIDEmptyError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, $csvRow[self::CSV_CAVE_CONTRATID], "L'id du contrat ne peut pas être vide");
    }

    private function contratIDNotFoundError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, $csvRow[self::CSV_CAVE_CONTRATID], "Le contrat n'a pas été trouvé");
    }

    private function observationsEmptyError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, "Observations", "Les observations sont vides.");
    }

    private function sucreWrongFormatError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, $csvRow[self::CSV_ANNEXE_QUANTITE], "La quantité de sucre est nulle ou possède un mauvais format.");
    }

    private function typeDocumentWrongFormatError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, $csvRow[self::CSV_ANNEXE_TYPEANNEXE], "Le type de document d'annexe n'est pas connu.");
    }

    private function annexesTypeMvtWrongFormatError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, $csvRow[self::CSV_ANNEXE_TYPEMVT], "Le type d'enregistrement des " . $csvRow[self::CSV_ANNEXE_TYPEANNEXE] . " doit être 'début' ou 'fin' .");
    }

    private function annexesNumeroDocumentError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, $csvRow[self::CSV_ANNEXE_TYPEANNEXE], "Le numéro de document ne peut pas être vide.");
    }

    private function importHorsRegionError() {
    	return $this->createError(0, "Etablissemment", "Import DRM non permis pour les établissements hors région.");
    }

    private function annexesNonApurementWrongDateError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, $csvRow[self::CSV_ANNEXE_NONAPUREMENTDATEEMISSION], "La date est vide ou mal formattée.");
    }

    private function annexesNonApurementWrongNumAcciseError($num_ligne, $csvRow) {
        return $this->createError($num_ligne, $csvRow[self::CSV_ANNEXE_NONAPUREMENTACCISEDEST], "La numéro d'accise du destinataire est vide ou mal formatté.");
    }

    private function createError($num_ligne, $erreur_csv, $raison) {
        $error = new stdClass();
        $error->num_ligne = $num_ligne;
        $error->erreur_csv = $erreur_csv;
        $error->raison = $raison;
        return $error;
    }

    /**
     * Fin des functions de création d'erreurs
     */
    private function buildLibellesArrayWithRow($csvRow, $with_slugify = false) {
        $certification = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_CAVE_CERTIFICATION]) : $csvRow[self::CSV_CAVE_CERTIFICATION];
        $genre = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_CAVE_GENRE]) : $csvRow[self::CSV_CAVE_GENRE];
        $appellation = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_CAVE_APPELLATION]) : $csvRow[self::CSV_CAVE_APPELLATION];
        $mention = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_CAVE_APPELLATION]) : $csvRow[self::CSV_CAVE_APPELLATION];
        $lieu = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_CAVE_LIEU]) : $csvRow[self::CSV_CAVE_LIEU];
        $couleur = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_CAVE_COULEUR]) : $csvRow[self::CSV_CAVE_COULEUR];
        $cepage = ($with_slugify) ? KeyInflector::slugify($csvRow[self::CSV_CAVE_CEPAGE]) : $csvRow[self::CSV_CAVE_CEPAGE];
        $libelles = array($certification,
            $genre,
            $appellation,
            $mention,
            $lieu,
            $couleur,
            $cepage);
        foreach ($libelles as $key => $libelle) {
            if (!$libelle) {
                $libelles[$key] = null;
            }
        }
        return $libelles;
    }

    private function slugifyProduitConf($produit) {
        $libellesSlugified = array();
        foreach ($produit->getLibelles() as $libelle) {
            $libellesSlugified[] = KeyInflector::slugify($libelle);
        }
        foreach ($libellesSlugified as $key => $libelle) {
            if (!$libelle) {
                $libellesSlugified[$key] = null;
            }
        }
        return $libellesSlugified;
    }

    private function buildAllMouvements() {
        $all_conf_details_slugified = array("details" => array(), "detailsACQUITTE" => array());
        foreach($this->configuration->declaration->filter('details') as $keyType => $all_conf_details) {
            foreach ($all_conf_details as $all_conf_detail_cat_Key => $all_conf_detail_cat) {
                foreach ($all_conf_detail_cat as $key_type => $type_detail) {
                    if (!array_key_exists(KeyInflector::slugify($all_conf_detail_cat_Key), $all_conf_details_slugified[$keyType])) {
                        $all_conf_details_slugified[$keyType][KeyInflector::slugify($all_conf_detail_cat_Key)] = array();
                    }
                    if (KeyInflector::slugify($key_type) == 'VRAC') {
                        $all_conf_details_slugified[$keyType][KeyInflector::slugify($all_conf_detail_cat_Key)]['CONTRAT'] = $type_detail;
                    }
                    $all_conf_details_slugified[$keyType][KeyInflector::slugify($all_conf_detail_cat_Key)][KeyInflector::slugify($key_type)] = $type_detail;
                }
            }
        }

        return $all_conf_details_slugified;
    }

}
