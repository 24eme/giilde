<?php

class MouvementDocument
{
    protected $document;
    protected $hash;

    public function __construct(acCouchdbDocument $document)
    {
        $this->document = $document;
        $this->hash = $document->getMouvements()->getHash();
    }

    public function getMouvementsCalculeByIdentifiant($identifiant) {
        $mouvements = $this->document->getMouvementsCalcule();

        return isset($mouvements[$identifiant]) ? $mouvements[$identifiant] : array();
    }

    public function generateMouvements() {
        $this->document->clearMouvements();
        $this->document->set($this->hash, $this->document->getMouvementsCalcule());
    }

    public function facturerMouvements() {
        foreach($this->document->getMouvements() as $mouvements) {
            foreach($mouvements as $mouvement) {
                $mouvement->facturer();
            }
        }
    }

    public function isFactures() {
        foreach($this->document->getMouvements() as $mouvements) {
            foreach($mouvements as $mouvement) {
                if(!$mouvement->isFacture()) {
                    return false;
                }
            }
        }

        return true;
    }

    public function isNonFactures() {
        foreach($this->document->getMouvements() as $mouvements) {
            foreach($mouvements as $mouvement) {
                if(!$mouvement->isNonFacture()) {
                    return false;
                }
            }
        }

        return true;
    }

    public function findMouvement($cle_mouvement, $part_idetablissement = null){
      $cle_mouvement = rtrim($cle_mouvement);
      foreach($this->document->getMouvements() as $identifiant => $mouvements) {
	       if ((!$part_idetablissement || preg_match('/^'.$part_idetablissement.'/', $identifiant)) && array_key_exists($cle_mouvement, $mouvements->toArray())) {
            return $mouvements[$cle_mouvement];
          }
        }
        throw new sfException(sprintf('The mouvement %s of the document %s does not exist', $cle_mouvement, $this->document->get('_id')));
    }
}
