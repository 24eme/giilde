<?php
/**
 * Description of class EtablissementFindByCviView
 * @author mathurin
 */
class EtablissementFindByCviView extends acCouchdbView
{
	const KEY_ETABLISSEMENT_CVI = 0;
        const VALUE_ETABLISSEMENT_ID = 0;
        const VALUE_ETABLISSEMENT_NOM = 1;
        const VALUE_ETABLISSEMENT_COMMUNE = 2;

    public static function getInstance() {

        return acCouchdbManager::getView('etablissement', 'findByCvi', 'Etablissement');
    }

    public function findByCvi($cvi) {

    	return $this->client->startkey(array($cvi))
                    		->endkey(array($cvi, array()))
                    		->getView($this->design, $this->view)->rows;
    }

		public function findByAccise($accise) {

			return $this->findByCvi($accise);
		}
}
