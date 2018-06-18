<?php

class stocksComponents extends sfComponents {

    public function executeFormEtablissementChoice() {
        if (!$this->identifiant) {
            $this->identifiant = null;
        }

        if (!$this->form) {
            $this->form = new StocksEtablissementChoiceForm('INTERPRO-declaration', array('identifiant' => $this->identifiant));
        }
    }

    public function executeMouvements() {
        $this->mouvements_viticulteur = $this->getMouvementsViticulteur();
        $this->mouvements_negociant = $this->getMouvementsNegociant();
    }

    public function executeRecapNegociant() {
        $mouvements = $this->getMouvementsNegociant();

        $this->recaps = array();

        foreach($mouvements as $mouvement) {
            if (!array_key_exists($mouvement->produit_hash, $this->recaps)) {
                $this->recaps[$mouvement->produit_hash] = $this->initLigneRecapNegociant();
                $this->recaps[$mouvement->produit_hash]['produit'] = $mouvement->produit_libelle;
            }

            $volume = $mouvement->volume * -1;

            if($mouvement->type_hash == Mouvement::TYPE_HASH_CONTRAT_RAISIN) {
                $this->recaps[$mouvement->produit_hash]['volume_stock_raisin'] += $volume;
            }

            if($mouvement->type_hash == Mouvement::TYPE_HASH_CONTRAT_MOUT) {
                $this->recaps[$mouvement->produit_hash]['volume_stock_mout'] += $volume;
            }

            if($mouvement->type_hash == Mouvement::TYPE_HASH_CONTRAT_VRAC) {
                $this->recaps[$mouvement->produit_hash]['volume_stock_vin'] += $volume;
            }

            $this->recaps[$mouvement->produit_hash]['volume_stock_total'] += $volume;
        }
    }

    protected function initLigneRecapNegociant()  {
        $ligne = array();
        $ligne['volume_stock_raisin'] = 0;
        $ligne['volume_stock_mout'] = 0;
        $ligne['volume_stock_vin'] = 0;
        $ligne['volume_stock_total'] = 0;
        $ligne['volume_stock_ds'] = null;

        return $ligne;
    }

    protected function getMouvementsViticulteur() {

        return DRMMouvementsConsultationView::getInstance()->getMouvementsByEtablissementAndCampagne($this->etablissement->identifiant, $this->campagne);
    }

    protected function getMouvementsNegociant() {
        $negoceRecapArr = SV12MouvementsConsultationView::getInstance()->getByIdentifiantAndCampagne($this->etablissement->identifiant, $this->campagne);
        if(in_array($this->etablissement->famille, DRMConfiguration::getInstance()->getFamilles())){
          $negoceRecapArr = array_merge($negoceRecapArr,DRMMouvementsConsultationView::getInstance()->getMouvementsByEtablissementAndCampagne($this->etablissement->identifiant, $this->campagne));
        }
        return $negoceRecapArr;
    }

}
