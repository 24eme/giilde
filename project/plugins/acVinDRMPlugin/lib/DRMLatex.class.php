<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DRMLatex
 *
 * @author mathurin
 */
class DRMLatex extends GenericLatex {

    private $drm = null;
    private $libelles_detail_ligne = null;

    const VRAC_OUTPUT_TYPE_PDF = 'pdf';
    const VRAC_OUTPUT_TYPE_LATEX = 'latex';
    const NB_PRODUITS_PER_PAGE = 9;

    function __construct(DRM $drm, $config = null) {
        sfProjectConfiguration::getActive()->loadHelpers("Partial", "Url", "MyHelper");
        $this->drm = $drm;
        $this->libelles_detail_ligne = $drm->allLibelleDetailLigneForDRM();
    }

    public function getNbPages() {
        $nbPages = 0;
        if ($this->drm->isNeant()) {
            return 2;
        }
        foreach ($this->drm->declaration->getProduitsDetailsByCertifications(true) as $produitByCertif) {
            $nb_produits = count($produitByCertif->produits);
            if ($nb_produits == 0) {
                continue;
            }
            $nbPages+= (int) ($nb_produits / DRMLatex::NB_PRODUITS_PER_PAGE) + 1;
        }
        $cpt_crds_annexes = $this->drm->nbTotalCrdsTypes();
        if ($cpt_crds_annexes) {
            $nbPages++;
        }
        if ($this->drm->exist('releve_non_apurement') && count($this->drm->releve_non_apurement) && (count($this->drm->releve_non_apurement) >= 4)) {
            $nbPages++;
        }
        $nbPages++;
        $nbPages += DRMConfiguration::getInstance()->getNbExtraPDFPages();
        return $nbPages;
    }

    public function getLatexFileNameWithoutExtention() {
        return $this->getTEXWorkingDir() . $this->drm->_id . '_' . $this->drm->_rev;
    }

    public function getLatexFileContents() {
        return html_entity_decode(htmlspecialchars_decode(
                        get_partial('drm_pdf/generateTex', array('drm' => $this->drm,
            'nbPages' => $this->getNbPages(),
            'drmLatex' => $this))
                        , HTML_ENTITIES));
    }

    public function getPublicFileName($extention = '.pdf') {
        return 'drm_' . $this->drm->_id . '_' . $this->drm->_rev . $extention;
    }

    public function getMvtsEnteesForPdf($detailNode = 'details') {
        $entrees = array();
        foreach ($this->libelles_detail_ligne->get($detailNode)->entrees as $key => $entree) {

            $entreeObj = new stdClass();
            $entreeObj->libelle = $entree->libelle;
            $entreeObj->key = $key;
            $entrees[$entree->libelle] = $entreeObj;
        }
        ksort($entrees);
        return $entrees;
    }

    public function getMvtsSortiesForPdf($detailNode = 'details') {
        $sorties = array();
        foreach ($this->libelles_detail_ligne->get($detailNode)->sorties as $key => $sortie) {
            $sortieObj = new stdClass();
            $sortieObj->libelle = $sortie->libelle;
            $sortieObj->key = $key;
            $sorties[$sortie->libelle] = $sortieObj;
        }
        ksort($sorties);
        return $sorties;
    }

}
