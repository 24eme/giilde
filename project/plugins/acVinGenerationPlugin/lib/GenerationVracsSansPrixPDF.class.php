<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class GenerationFacturePDF
 * @author mathurin
 */
class GenerationVracsSansPrixPDF extends GenerationPDF {

    function __construct(Generation $g, $config = null, $options = null) {
        $this->sansprix = null;
        parent::__construct($g, $config, $options);
    }

    public function preGeneratePDF() {
        parent::preGeneratePDF();
        $arguments = $this->generation->arguments->toArray();
        $this->sansprix = new VracsSansPrixData($arguments['date_facturation']);
        $cpt = 0;
        foreach ($this->sansprix->getCSVs() as $id => $csv) {
            $this->generation->documents->add($cpt, $id);
            $cpt++;
        }
    }

    protected function generatePDFForADocumentId($id) {
      return $this->sansprix->getPDF($id);
    }

    protected function getDocumentName() {
      return "VracsSansPrix";
    }

}
