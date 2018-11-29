<?php
class EdiDAEView extends acCouchdbView
{
	const KEY_DATE = 0;

	const VALUE_DATE = 0;
	const VALUE_IDENTIFIANT_DECLARANT = 1;
	const VALUE_ACCISES_DECLARANT = 2;
	const VALUE_NOM_DECLARANT = 3;
	const VALUE_FAMILLE_DECLARANT = 4;
	const VALUE_SOUS_FAMILLE_DECLARANT = 5;
	const VALUE_CP_DECLARANT = 6;
	const VALUE_CERTIFICATION_CODE = 7;
	const VALUE_GENRE_CODE = 8;
	const VALUE_APPELLATION_CODE = 9;
	const VALUE_MENTION_CODE = 10;
	const VALUE_LIEU_CODE = 11;
	const VALUE_COULEUR_CODE = 12;
	const VALUE_CEPAGE_CODE = 13;
	const VALUE_COMPLEMENT_PRODUIT = 14;
	const VALUE_LIBELLE_PRODUIT = 15;	
	const VALUE_LABEL = 16;
	const VALUE_MENTION = 17;
	const VALUE_MILLESIME = 18;
	const VALUE_PRIMEUR = 19;
	const VALUE_ACCISES_ACHETEUR = 20;
	const VALUE_NOM_ACHETEUR = 21;
	const VALUE_TYPE_ACHETEUR = 22;
	const VALUE_DESTINATION = 23;
	const VALUE_CONDITIONNEMENT = 24;
	const VALUE_CONTENANCE = 25;
	const VALUE_HL = 26;
	const VALUE_QUANTITE = 27;
	const VALUE_PU = 28;
	const VALUE_VOL_HL = 29;
	const VALUE_PRIX_HL = 30;

	public static function getInstance() 
	{
        return acCouchdbManager::getView('edi', 'dae', 'DAE');
    }

    public function findByDate($date) 
    {
      	return $this->client->startkey(array($date))
                    		->endkey(array($this->getEndISODateForView(), array()))
                    		->getView($this->design, $this->view);
    }
    
    public function getEndISODateForView() 
    {
    	return '9999-99-99T99:99:99'.date('P');
    }

}  