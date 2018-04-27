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
class DRMExportCsvEdi extends DRMCsvEdi {

    public function __construct(DRM $drm = null) {
        parent::__construct(null, $drm);
    }

    public function exportEDI() {
        if (!$this->drm) {
            throw new sfException('Absence de DRM');
        }
        $header = $this->createHeaderEdi();
        $body = $this->createBodyEdi();
        return $header . $body;
    }

    private function createHeaderEdi() {
        return "#TYPE;PERIODE;IDENTIFIANT;ACCISE;CERTIFICATION / TYPE PRODUIT; GENRE / COULEUR CAPSULE; APPELLATION / CENTILITRAGE;MENTION;LIEU;COULEUR;CEPAGE;COMPLEMENT;LIBELLE;TYPE_DRM;CATEGORIE MOUVEMENT / TYPE DOCUMENT;TYPE MOUVEMENT;VOLUME / QUANTITE;PAYS EXPORT / DATE NON APUREMENT;NUMERO CONTRAT / NUMERO ACCISE DESTINATAIRE NON APUREMENT;NUMERO DOCUMENT ACCOMPAGNEMENT\n";
    }

    private function createBodyEdi() {
        $body = $this->createMouvementsEdi();
        $body.= $this->createCrdsEdi();
        $body.= $this->createAnnexesEdi();
        return $body;
    }

    public function getProduitCSV($produitDetail, $force_type_drm = null) {
        $cepageConfig = $produitDetail->getCepage()->getConfig();

        $certification = $cepageConfig->getCertification()->getLibelle();
        $genre = $cepageConfig->getGenre()->getLibelle();
        $appellation = $cepageConfig->getAppellation()->getLibelle();
        $mention = $cepageConfig->getMention()->getLibelle();
        $lieu = $cepageConfig->getLieu()->getLibelle();
        $couleur = $cepageConfig->getCouleur()->getLibelle();
        $cepage = $cepageConfig->getCepage()->getLibelle();

        $complement = "";
        if($produitDetail instanceof DSDetail){
          $libelle = "";
        }else{
          $libelle = $produitDetail->getLibelle("%format_libelle%");
        }
        $type_drm = ($produitDetail->getParent()->getKey() == 'details')? 'suspendu' : 'acquitte';
        if($force_type_drm){
          $type_drm = $force_type_drm;
        }
        return $certification . ";" .
         $genre . ";" .
          $appellation .
          ";" . $mention .
          ";" . $lieu .
          ";" . $couleur .
           ";" . $cepage.
           ";". $complement.
            ";". $libelle.
            ";". $type_drm;
    }

    private function getLibelleDetail($keyDetail) {
        if ($keyDetail == 'vrac_details') {
            return 'contrat';
        }
        return str_replace('_details', '', $keyDetail);
    }

    public function createRowStockNullProduit($produitDetail){
      $debutLigne = self::TYPE_CAVE . ";" . $this->drm->periode . ";" . $this->drm->identifiant . ";" . $this->drm->declarant->no_accises . ";";
      $lignes = $debutLigne . $this->getProduitCSV($produitDetail,'suspendu') . ";" . "stocks_debut;initial;0;\n";
      $lignes.= $debutLigne . $this->getProduitCSV($produitDetail,'suspendu') . ";" . "stocks_fin;final;0;\n";
      return $lignes;
    }

    public function createRowMouvementProduitDetail($produit, $catMouvement,$typeMouvement,$volume, $num_contrat = null){
      $debutLigne = self::TYPE_CAVE . ";" . $this->drm->periode . ";" . $this->drm->identifiant . ";" . $this->drm->declarant->no_accises . ";";
      $lignes = $debutLigne . $this->getProduitCSV($produit,'suspendu') . ";" . $catMouvement.";".$typeMouvement.";".$volume.";";
      $lignes .= ($num_contrat)? ";".str_replace("VRAC-","",$num_contrat).";" : "";
      $lignes .= "\n";
      return $lignes;
    }

    private function createMouvementsEdi() {
        $mouvementsEdi = "";
        $produitsDetails = $this->drm->declaration->getProduitsDetailsSorted(true);
        $debutLigne = self::TYPE_CAVE . ";" . $this->drm->periode . ";" . $this->drm->identifiant . ";" . $this->drm->declarant->no_accises . ";";

        foreach ($produitsDetails as $hashProduit => $produitDetail) {

            foreach ($produitDetail->stocks_debut as $stockdebut_key => $stockdebutValue) {
                if (!$stockdebutValue && !$produitDetail->stocks_fin->get(str_replace('initial', 'final', $stockdebut_key))) {
                    continue;
                }
                $mouvementsEdi.= $debutLigne . $this->getProduitCSV($produitDetail) . ";" . "stocks_debut;" . $stockdebut_key . ";" . $stockdebutValue*1.0 . ";;;\n";
            }

            foreach ($produitDetail->entrees as $entreekey => $entreeValue) {
                if (!$entreeValue) {
                    continue;
                }
                $mouvementsEdi.= $debutLigne . $this->getProduitCSV($produitDetail) . ";" . "entrees;" . $entreekey . ";" . $entreeValue . ";;;\n";
            }

            foreach ($produitDetail->sorties as $sortiekey => $sortieValue) {

                if ($sortieValue) {
                    if ($sortieValue instanceof DRMESDetails) {
                        foreach ($sortieValue as $sortieDetailKey => $sortieDetailValue) {
                            if ($sortieDetailValue->getVolume()) {
                                $complement = $sortieDetailValue->getIdentifiant();

                                $numero_doc = ($sortieDetailValue->numero_document) ? $sortieDetailValue->numero_document : '';
                                if ($sortiekey == 'export_details') {
                                    $pays = $this->countryList[$sortieDetailValue->getIdentifiant()];
                                    $mouvementsEdi.= $debutLigne . $this->getProduitCSV($produitDetail) . ";" . "sorties;" . $this->getLibelleDetail($sortiekey) . ";" . $sortieDetailValue->getVolume() . ";" . $pays . ";;" . $numero_doc . "\n";
                                }
                                if ($sortiekey == 'vrac_details') {
                                    $numero_vrac = str_replace('VRAC-', '', $sortieDetailValue->getIdentifiant());
                                    $mouvementsEdi.= $debutLigne . $this->getProduitCSV($produitDetail) . ";" . "sorties;" . $this->getLibelleDetail($sortiekey) . ";" . $sortieDetailValue->getVolume() . ";;" . $numero_vrac . ";" . $numero_doc . "\n";
                                }
                            }
                        }
                    } else {
                        if (!$produitDetail->getConfig()->get('sorties')->get($sortiekey)->hasDetails()) {
                            $mouvementsEdi.= $debutLigne . $this->getProduitCSV($produitDetail) . ";" . "sorties;" . $sortiekey . ";" . $sortieValue . ";;;\n";
                        }
                    }
                }
            }

            foreach ($produitDetail->stocks_fin as $stockfin_key => $stockfinValue) {
                if (!$stockfinValue && !$produitDetail->stocks_debut->get(str_replace('final', 'initial', $stockfin_key))) {
                    continue;
                }
                $mouvementsEdi.= $debutLigne . $this->getProduitCSV($produitDetail) . ";" . "stocks_fin;" . $stockfin_key . ";" . $stockfinValue*1.0 . ";;;\n";
            }
        }
        return $mouvementsEdi;
    }

    private function createCrdsEdi() {
        $crdsEdi = "";
        $debutLigne = self::TYPE_CRD . ";" . $this->drm->periode . ";" . $this->drm->identifiant . ";" . $this->drm->declarant->no_accises . ";";
        foreach ($this->drm->getAllCrdsByRegimeAndByGenre() as $regimeKey => $crdByGenre) {
            foreach ($crdByGenre as $genreKey => $crds) {
                foreach ($crds as $crdKey => $crdDetail) {
                    $this->createCrdRowEdi($debutLigne, $regimeKey, $crdDetail, 'stock_debut', $crdsEdi);
                    $this->createCrdRowEdi($debutLigne, $regimeKey,$crdDetail, 'entrees_achats', $crdsEdi);
                    $this->createCrdRowEdi($debutLigne, $regimeKey,$crdDetail, 'entrees_retours', $crdsEdi);
                    $this->createCrdRowEdi($debutLigne, $regimeKey,$crdDetail, 'entrees_excedents', $crdsEdi);
                    $this->createCrdRowEdi($debutLigne, $regimeKey,$crdDetail, 'sorties_utilisations', $crdsEdi);
                    $this->createCrdRowEdi($debutLigne, $regimeKey,$crdDetail, 'sorties_destructions', $crdsEdi);
                    $this->createCrdRowEdi($debutLigne, $regimeKey,$crdDetail, 'sorties_manquants', $crdsEdi);
                    $this->createCrdRowEdi($debutLigne, $regimeKey,$crdDetail, 'stock_fin', $crdsEdi);
                }
            }
        }
        return $crdsEdi;
    }

    private function createCrdRowEdi($debutLigne, $regimeKey, $crdDetail, $type_mvt, &$crdsEdi) {
        if ($crdDetail->$type_mvt) {
            $type_mvt_csv = "";
            switch ($type_mvt) {
                case 'stock_debut':
                    $type_mvt_csv = $type_mvt . ";debut";
                    break;
                case 'stock_fin':
                    $type_mvt_csv = $type_mvt . ";fin";
                    break;
                default :
                    $type_mvt_csv = str_replace('_', ';', $type_mvt);
                    break;
            }
            $crdsEdi.= $debutLigne .  DRMClient::$drm_crds_couleurs[$crdDetail->couleur] . ";" . DRMClient::$drm_crds_genre[$crdDetail->genre] . ";" . $crdDetail->detail_libelle . ";;;;;;;".
            EtablissementClient::$regimes_crds_libelles[$regimeKey].";" . $type_mvt_csv . ";" . $crdDetail->$type_mvt . ";;;\n";
        }
    }

    private function createAnnexesEdi() {
        $annexesEdi = "";
        $debutLigneAnnexe = self::TYPE_ANNEXE . ";" . $this->drm->periode . ";" . $this->drm->identifiant . ";" . $this->drm->declarant->no_accises . ";;;;;;;;";

        foreach ($this->drm->documents_annexes as $typeDoc => $numsDoc) {
            $annexesEdi.=$debutLigneAnnexe .";;;". $typeDoc . ";debut;" . $numsDoc->debut . "\n";
            $annexesEdi.=$debutLigneAnnexe .";;;". $typeDoc . ";fin;" . $numsDoc->fin . "\n";
        }
        if($this->drm->exist('releve_non_apurement')){
          foreach ($this->drm->releve_non_apurement as $non_apurement) {
              $annexesEdi.=$debutLigneAnnexe .";;;". self::TYPE_ANNEXE_NONAPUREMENT . ";;;".$non_apurement->date_emission.";" . $non_apurement->numero_accise . ";" . $non_apurement->numero_document . "\n";
          }
        }

        if($this->drm->exist('declaratif') && $this->drm->declaratif->exist('statistiques')){
            if($this->drm->declaratif->statistiques->exist('jus') && $this->drm->declaratif->statistiques->jus){
              $annexesEdi.=$debutLigneAnnexe .";;;STATS EUROPEENNES;JUS;" . $this->drm->declaratif->statistiques->jus . ";;;\n";
            }
            if($this->drm->declaratif->statistiques->exist('mcr') && $this->drm->declaratif->statistiques->mcr){
              $annexesEdi.=$debutLigneAnnexe .";;;STATS EUROPEENNES;MCR;" . $this->drm->declaratif->statistiques->mcr . ";;;\n";
            }
            if($this->drm->declaratif->statistiques->exist('vinaigre') && $this->drm->declaratif->statistiques->vinaigre){
              $annexesEdi.=$debutLigneAnnexe .";;;STATS EUROPEENNES;VINAIGRE;" . $this->drm->declaratif->statistiques->vinaigre . ";;;\n";
            }
        }

        return $annexesEdi;
    }

}
