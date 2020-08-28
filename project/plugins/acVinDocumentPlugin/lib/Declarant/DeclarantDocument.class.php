<?php

class DeclarantDocument
{
    protected $document;
    protected $etablissement = null;

    public function __construct(acCouchdbDocument $document)
    {
        $this->document = $document;
    }

    public function getIdentifiant()
    {
        return $this->document->identifiant;
    }

    public function getDeclarant()
    {
        return $this->document->declarant;
    }

   public function getDeclarantObject() {
       if(is_null($this->etablissement)) {
            $class = sfConfig::get('app_declarant_class', 'Etablissement');
            $this->etablissement = acCouchdbManager::getClient($class)->findByIdentifiant($this->getIdentifiant());
        }

        return $this->etablissement;
    }

    public function getEtablissementObject() {
        return $this->getDeclarantObject();
    }

    public function storeDeclarant()
    {
        $etablissement = $this->getEtablissementObject();
        if (!$etablissement) {
            throw new sfException(sprintf("L'etablissement %s n'existe pas", $this->getIdentifiant()));
            return;
        }
        $declarant = $this->getDeclarant();

        $declarant->nom = null;
        if ($etablissement->exist("intitule") && $etablissement->get("intitule")) {
            $declarant->nom = $etablissement->intitule . " ";
        }
        $declarant->nom .= $etablissement->nom;
        $declarant->raison_sociale = $etablissement->getRaisonSociale();
        $declarant->cvi = $etablissement->cvi;
        $declarant->no_accises = $etablissement->getNoAccises();
        $declarant->adresse = $etablissement->siege->adresse;
        if ($etablissement->siege->exist("adresse_complementaire")) {
            $declarant->adresse .= ' ; '.$etablissement->siege->adresse_complementaire;
        }
        $declarant->commune = $etablissement->siege->commune;
        $declarant->code_postal = $etablissement->siege->code_postal;
        $declarant->region = $etablissement->getRegion();
        if ($declarant->exist('famille')) {
          $declarant->famille = $etablissement->famille;
        }
        if($declarant->getDefinition()->exist('siret')) {
             $declarant->add('siret', $etablissement->siret);
        }
        if ($etablissement->exist("telephone")) {
            if($declarant->getDefinition()->exist('telephone'))
                 $declarant->add('telephone', $etablissement->telephone);
        }
        if ($etablissement->exist("email")) {
            if($declarant->getDefinition()->exist('email'))
               $declarant->add('email', $etablissement->email);
        }
        if ($etablissement->exist("fax")) {
             if($declarant->getDefinition()->exist('fax'))
                $declarant->add('fax', $etablissement->fax);
        }
    }
}
