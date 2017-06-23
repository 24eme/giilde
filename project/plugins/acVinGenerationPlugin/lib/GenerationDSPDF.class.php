<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class GenerationDSPDF
 * @author mathurin
 */
class GenerationDSPDF extends GenerationPDF {

    function __construct(Generation $g, $config = null, $options = null) {
        parent::__construct($g, $config, $options);
    }

    public function preGeneratePDF() {
        parent::preGeneratePDF();

        $etablissementsViews = array();
        $operateur_types = array(EtablissementFamilles::FAMILLE_PRODUCTEUR, EtablissementFamilles::FAMILLE_NEGOCIANT);
        $regions = EtablissementClient::getRegionsWithoutHorsInterpro();

        if ($this->generation->arguments->exist('operateur_types')) {
            $operateur_types = explode(',', $this->generation->arguments->operateur_types);
        }

        if ($this->generation->arguments->exist('regions')) {
            $regions = explode(',', $this->generation->arguments->regions);
        }

        $etablissementsViews = EtablissementClient::getInstance()->findByFamillesAndRegions($operateur_types, $regions, null);
        $dsClient = DSClient::getInstance();
        $cpt = 0;
	$this->generation->documents = array();
        foreach ($etablissementsViews as $etablissement) {
	  $ds = $dsClient->findOrCreateDsByEtbId($etablissement->key[EtablissementRegionView::KEY_IDENTIFIANT], $this->generation->arguments->date_declaration);
	  $ds->save();
	  $this->generation->documents->add($cpt, $ds->_id);
	  $cpt++;
        }
    }

    protected function getDocumentName() {
        return "Declarations de stock";
    }

    protected function generatePDFForADocumentID($docid) {
        $ds = DSClient::getInstance()->find($docid);
        if (!$ds) {
            throw new sfException("DS $dsid doesn't exist\n");
        }
        return new DSLatex($ds, $this->config);
    }

}
