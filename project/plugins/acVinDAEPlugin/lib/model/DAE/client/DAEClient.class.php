<?php

class DAEClient extends acCouchdbClient {

    const ACHETEUR_TYPE_IMPORTATEUR = "IMPORTATEUR";

    public static function getInstance() {
        return acCouchdbManager::getClient("DAE");
    }

    public function createDAE($identifiant, $date,$produit,$type_acheteur,$destination,$millesime,$volume,$contenant,$prix_ht,$label) {
        $dae = new DAE();
        $dae->setIdentifiant($identifiant);
        $dae->setDate($date);
        $dae->setProduitHash($produit);
        $dae->setTypeAcheteur($type_acheteur);
        $dae->setDestination($destination);
        $dae->setMillesime($millesime);
        $dae->setVolume($volume);
        $dae->setContenance($contenant);
        $dae->setPrixHt($prix_ht);
        $dae->setLabel($label);
        $dae->storeDeclarant();
        return $dae;
    }

    public function buildId($identifiant, $date, $num) {
        return 'DAE-' . $identifiant . '-' . str_replace('-','',$date)."-".$num;
    }


    public function getNextIdentifiantForEtablissementAndDay($identifiant, $date) {
        $daes = self::getForEtablissementAtDay($identifiant, $date, acCouchdbClient::HYDRATE_ON_DEMAND)->getIds();
        $last_num = 0;
        foreach ($daes as $id) {
            if (!preg_match('/DAE-([0-9]+)-([0-9]+)-([0-9]+)/', $id, $matches)) {
                continue;
            }

            $num = $matches[3];
            if ($num > $last_num) {
                $last_num = $num;
            }
        }

        return sprintf("%03d", $last_num + 1);
    }

    public function findByIdentifiant($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
        return $this->startkey('DAE-' . $identifiant . '-00000000-000')->endkey('DAE-' . $identifiant . '-99999999-999')->execute($hydrate);
    }

    public function getForEtablissementAtDay($identifiant,$date, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $date = str_ireplace('-','',$date);
        return $this->startkey('DAE-' . $identifiant . '-'.$date.'-000')->endkey('DAE-' . $identifiant . '-'.$date.'-999')->execute($hydrate);
    }

    public function findByIdentifiantAndDate($identifiant, $date) {

        return $this->getForEtablissementAtDay($identifiant, $date);
    }
}
