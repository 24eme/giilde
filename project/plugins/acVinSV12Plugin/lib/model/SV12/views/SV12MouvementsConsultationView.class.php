<?php
class SV12MouvementsConsultationView extends MouvementsConsultationView
{
    public static function getInstance() {

        return acCouchdbManager::getView('mouvement', 'consultation', 'SV12', 'SV12MouvementsConsultationView');
    }

    public function findByEtablissement($id_or_identifiant) {

        return $this->findByTypeAndEtablissement('SV12', $id_or_identifiant);
    }

    public function findByEtablissementAndCampagne($id_or_identifiant, $campagne) {

        return $this->findByTypeEtablissementAndCampagne('SV12', $id_or_identifiant, $campagne);
    }

    public function findEtablissementAndPeriode($id_or_identifiant, $periode) {

        return $this->findByTypeEtablissementAndPeriode('SV12', $id_or_identifiant, SV12Client::getInstance()->buildCampagne($periode), $periode);
    }

    public function getMouvementsByEtablissement($id_or_identifiant) {

        return $this->buildMouvements($this->findByTypeAndEtablissement('SV12',$id_or_identifiant)->rows);      
    }

    public function getMouvementsByEtablissementAndCampagne($id_or_identifiant, $campagne) {

        return $this->buildMouvements($this->findByEtablissementAndCampagne($id_or_identifiant, $campagne)->rows);
    }

    public function getMouvementsByEtablissementAndPeriode($id_or_identifiant, $periode) {

        return $this->buildMouvements($this->findEtablissementAndPeriode($id_or_identifiant, $periode)->rows);
    }

}
