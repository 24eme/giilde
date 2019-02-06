<?php
class configurationExportEdiCatalogueProduitTask extends sfBaseTask
{
	CONST EXPORT_WITH_KEY = false;
	CONST FCT_CONF_PRODUITS = 'getProduits';
	
	protected function configure()
	{
		$this->addOptions(array(
      		new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
      		new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      		new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default')
    	));
    	$this->namespace        = 'configuration';
    	$this->name             = 'export-edi-catalogue-produit';
    	$this->briefDescription = '';
    	$this->detailedDescription = '';
	}
	
	private function formatLibelle($libelle)
	{
		return str_replace(',', '.', trim($libelle));
	}
    	
	protected function execute($arguments = array(), $options = array())
	{
	    $databaseManager = new sfDatabaseManager($this->configuration);
    	$connection = $databaseManager->getDatabase($options['connection'])->getConnection();
    	
    	$produits = ConfigurationClient::getCurrent()->{self::FCT_CONF_PRODUITS}();
    	
    	echo (self::EXPORT_WITH_KEY)? 
    		sprintf("CERTIFICATION_KEY,CERTIFICATION,GENRE_KEY,GENRE,APPELLATION_KEY,APPELLATION,MENTION_KEY,MENTION,LIEU_KEY,LIEU,COULEUR_KEY,COULEUR,CEPAGE_KEY,CEPAGE,LIBELLE_PRODUIT,IDENTIFIANT INAO\n") :
    		sprintf("CERTIFICATION,GENRE,APPELLATION,MENTION,LIEU,COULEUR,CEPAGE,LIBELLE_PRODUIT,IDENTIFIANT INAO\n");
    	
    	foreach($produits as $hash => $produit) {
				if (!$produit->isActif(date('Y-m-d'))) {
					continue;
				}
    		$certificationKey = $produit->getCertification()->getKey();
    		if ($certificationKey == "DEFAUT")
    			$certificationKey = "";
    		$certification = $this->formatLibelle($produit->getCertification()->getLibelle());
    		
    		$genreKey = $produit->getGenre()->getKey();
    		if ($genreKey == "DEFAUT")
    			$genreKey = "";
    		$genre = $this->formatLibelle($produit->getGenre()->getLibelle());

    		$appellationKey = $produit->getAppellation()->getKey();
    		if($appellationKey == "DEFAUT")
    			$appellationKey = "";
    		$appellation = str_replace(',', '.', $this->formatLibelle($produit->getAppellation()->getLibelle()));

    		$mentionKey = $produit->getMention()->getKey();
    		if($mentionKey == "DEFAUT")
    			$mentionKey = "";
    		$mention = $this->formatLibelle($produit->getMention()->getLibelle());

    		$lieuKey = $produit->getLieu()->getKey();
    		if($lieuKey == "DEFAUT")
    			$lieuKey = "";
    		$lieu = $this->formatLibelle($produit->getLieu()->getLibelle());

    		$couleurKey = $produit->getCouleur()->getKey();
    		if($couleurKey == "DEFAUT")
    			$couleurKey = "";
    		$couleur = $this->formatLibelle($produit->getCouleur()->getLibelle());

    		$cepageKey = $produit->getCepage()->getKey();
    		if($cepageKey == "DEFAUT")
    			$cepageKey = "";    		
    		$cepage = $this->formatLibelle($produit->getCepage()->getLibelle());
    		
    		$libelleProduit = $this->formatLibelle($produit->getLibelleFormat());
    		$douaneId = $produit->getCodeDouane();
    		
    		echo (self::EXPORT_WITH_KEY)?
    			sprintf("%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\n", $certificationKey, $certification, $genreKey, $genre, $appellationKey, $appellation, $mentionKey, $mention, $lieuKey, $lieu, $couleurKey, $couleur, $cepageKey, $cepage, $libelleProduit, $douaneId) :
    			sprintf("%s,%s,%s,%s,%s,%s,%s,%s,%s\n", $certification, $genre, $appellation, $mention, $lieu, $couleur, $cepage, $libelleProduit, $douaneId);
    	}
	}
}
