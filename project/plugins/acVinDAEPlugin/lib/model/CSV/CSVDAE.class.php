<?php

/**
 * Model for CSV
 *
 */
class CSVDAE extends BaseCSVDAE {

    public function __construct() {
        parent::__construct();
    }

    public function hasCsvAttachement(){
        return $this->exist("_attachments") && count($this->get("_attachments"));
    }

    public function getFileContent() {
        return file_get_contents($this->getAttachmentUri($this->getFileName()));
    }

    public function getFileName() {
        return 'import_edi_' . $this->identifiant . '_' . $this->periode . '.csv';
    }

    public function hasErreurs() {
        return count($this->erreurs);
    }

    public function addErreur($erreur) {
        $erreurNode = $this->erreurs->getOrAdd($erreur->num_ligne);
        $erreurNode->num_ligne = $erreur->num_ligne;
        $erreurNode->csv_erreur = $erreur->erreur_csv;
        $erreurNode->diagnostic = $erreur->raison;
        return $erreurNode;
    }

    public function clearErreurs() {
        $this->remove('erreurs');
        $this->add('erreurs');
        $this->statut = null;
    }


}
