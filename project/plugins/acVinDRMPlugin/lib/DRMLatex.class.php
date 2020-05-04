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
    const NB_PRODUITS_PER_PAGE = 8;

    function __construct(DRM $drm, $config = null) {
        sfProjectConfiguration::getActive()->loadHelpers("Partial", "Url", "MyHelper");
        $this->drm = $drm;
        $this->libelles_detail_ligne = $drm->allLibelleDetailLigneForDRM();
    }

    private function makeDivision($nb_produits){
        $nb = 0;
        if($nb_produits <= DRMLatex::NB_PRODUITS_PER_PAGE){
           $nb++; 
        }else if($nb_produits%DRMLatex::NB_PRODUITS_PER_PAGE == 0){
            $nb+= $nb_produits / DRMLatex::NB_PRODUITS_PER_PAGE;
        }else{
            $nb+= (int) ($nb_produits / DRMLatex::NB_PRODUITS_PER_PAGE) + 1; 
        }
        return $nb;
    }

    public function getNbPages() {
        $nbPages = 0;
        foreach (DRMClient::$types_libelles as $typeDetailsNodes => $libelle){
            
            foreach ($this->drm->declaration->getProduitsDetailsByCertifications(true, $typeDetailsNodes) as $key => $produitByCertif) {
                $nb_produits = count($produitByCertif->produits);
                if ($nb_produits == 0) {
                   continue;
                }
                $nbPages += $this->makeDivision($nb_produits);
            }
        }
        $recap = $this->drm->declaration->getProduitsDetailsAggregateByAppellation(true, 'details', '/genres/VCI/');
        if(isset($recap['/declaration/certifications/AOC_ALSACE'])) {
            $nb_recap = count(array_keys($recap['/declaration/certifications/AOC_ALSACE']->produits));
            $nbPages += $this->makeDivision($nb_recap);
        }

        $dataExport = $this->drm->declaration->getMouvementsAggregateByAppellation('export.*_details', '/declaration/certifications/AOC_ALSACE');
        $nb = 0;
        foreach ($dataExport as $pays => $produits) {
            if($nb < count($produits)){
                $nb = count($produits);
            }
        }
        if($nb){
            $nbPages += $this->makeDivision($nb);
        }
        $cpt_crds_annexes = $this->drm->nbTotalCrdsTypes();

        if($cpt_crds_annexes || count($this->drm->documents_annexes)){
            $nbPages++;
        }        
        
        if ($this->drm->exist('releve_non_apurement') && count($this->drm->releve_non_apurement) && (count($this->drm->releve_non_apurement) >= 4)) {
            $nbPages++;
        }
        $nbPages += $this->makeDivision(count($this->drm->droits->douane));
        
        return $nbPages;
    }

    public function deleteAccents ($string) {
        $table = array(
            'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj', 'd'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'C'=>'C', 'c'=>'c', 'C'=>'C', 'c'=>'c',
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
            'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
            'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
            'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
            'ÿ'=>'y', 'R'=>'R', 'r'=>'r',
        );
       
        return strtr($string, $table);
    }

    public function sortDataExport($dataExport){
        $all_keys = array_keys($dataExport);
        $data_new = array();
        $countries = array();
        foreach ($all_keys as $key) {
            $countries[$key] = DRMLatex::deleteAccents(ConfigurationClient::getInstance()->getCountry($key));
        }
        asort($countries);
        foreach ($countries as $iso => $p) {
            $data_new[$iso] = $dataExport[$iso];            
        }
        return $data_new;
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
