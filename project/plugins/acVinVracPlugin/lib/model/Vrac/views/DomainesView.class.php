<?php

class VracDomainesView extends acCouchdbView
{
	const KEY_VENDEURID = 0;
    const KEY_ANNEE = 1;
	const KEY_DOMAINE = 2;

    public static function getInstance() {
        return acCouchdbManager::getView('vrac', 'domaines','Vrac');
    }

    public function findDomainesByVendeur($vendeurId, $annee_fin, $nb_annee = 1) {

        return $this->client->startkey(array($vendeurId, (string) ($annee_fin - $nb_annee)))
                    		->endkey(array($vendeurId, $annee_fin, array()))
                                ->group_level(3)
                    		->getView($this->design, $this->view);
    }

}
