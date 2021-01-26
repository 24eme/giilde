<?php

class GenerationFacturePapier extends GenerationPDF
{
    protected $factures = [];

    public function preGeneratePDF()
    {
        foreach ($this->generation->getMasterGeneration()->documents as $id) {
            $facture = FactureClient::getInstance()->find($id);

            if (! $facture) {
                throw new sfException("Facture $id n'existe pas");
            }

            if ($facture->exist('telechargee') && $facture->telechargee) {
                continue;
            }

            $this->generation->documents->add(null, $id);
        }
    }

    public static function getActionLibelle() {

        return "Générer le PDF des factures non téléchargées";
    }

    public function generatePDFForADocumentID($docid)
    {
        $facture = FactureClient::getInstance()->find($docid);
        return new FactureLatex($facture, $this->config);
    }

    public function getDocumentName()
    {
        return 'Factures à envoyer par courrier';
    }
}
