<?php

/**
 * Model for MouvementsFacture
 *
 */
class MouvementsFacture extends BaseMouvementsFacture {

    public function constructIds($date) {
        if (!$date) {
            $date = date('Ymd');
        }

        $this->identifiant = MouvementsFactureClient::getInstance()->getNextNoMouvementsFacture($date);
        $this->periode = substr($date, 0, 6);
        $this->_id = MouvementsFactureClient::getInstance()->getId($this->identifiant);
    }

    public function getNbMvts() {
        $nb_mvt = 0;
        foreach ($this->mouvements as $etbKey => $mvtsEtb) {
            $nb_mvt += count($mvtsEtb);
        }
        return $nb_mvt;
    }

    public function getNbMvtsAFacture() {
        $nb_mvt = 0;
        foreach ($this->mouvements as $etbKey => $mvtsEtb) {
            foreach ($mvtsEtb as $mvtKey => $mvt) {
            if ($mvt->facturable && !$mvt->facture) {
                    $nb_mvt ++;
                }
            }
        }
        return $nb_mvt;
    }

    public function getNbSocietes() {
        return count($this->mouvements);
    }

    public function getTotalHt() {
        $montant = 0;
        foreach ($this->mouvements as $etbKey => $mvtsEtb) {
            foreach ($mvtsEtb as $mvtKey => $mvt) {
                if ($mvt->facturable) {
                    $montant += $mvt->getPrixHt();
                }
            }
        }
        return $montant;
    }

    public function getTotalHtAFacture() {
        $montant = 0;
        foreach ($this->mouvements as $etbKey => $mvtsEtb) {
            foreach ($mvtsEtb as $mvtKey => $mvt) {
                if ($mvt->facturable && !$mvt->facture) {
                    $montant += $mvt->getPrixHt();
                }
            }
        }
        return $montant;
    }

    public function getLibelleFromId() {
        return "Facturation libre : " . $this->getLibelle() . " (" . Date::francizeDate($this->getDate()) . ")";
    }

    public function findMouvement($mvtId, $soc) {
        return $this->mouvements->get($soc . '01')->get($mvtId);
    }

}
