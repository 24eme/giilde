<?php

class exportProduitsForNewConfigurationTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      // add your own options here
    ));

    $this->namespace        = 'export';
    $this->name             = 'produits-for-new-configuration';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [exportCSVConfiguration|INFO] task does things.
Call it with:

  [php symfony exportCSVConfiguration|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    $produits = ConfigurationClient::getCurrent()->getProduits();

    echo sprintf("#interpro;categorie_libelle;categorie_code;genre_libelle;genre_code;denomination_libelle;denomination_code;mention_libelle;mention_code;lieu_libelle;lieu_code;couleur_libelle;couleur_code;cepage_libelle;cepage_code;departements;douane_code;douane_libelle;douane_taxe;douane_date;douane_noeud;cvo_taxe;cvo_date;cvo_noeud;repli_entree;repli_sorti;declassement_entree;declassement_sorti;densite;labels;code_produit;code_produit_noeud;code_analytique;code_comptable_noeud;code_douane;code_douane_noeud;alias_produit;format_libelle;format_libelle_noeud;cepages autorises\n");

    foreach($produits as $hash => $produit) {
        $master_comptable = null;
        $master_cvo = null;
        try {
            $ctaux = $produit->getDroitCVO(date('Y-m-d'));
            $droit_cvo = $ctaux->taux;
            if ($ctaux->isChapeau()) {
               $master_comptable = $ctaux->getMasterProduit()->getCodeComptable();
               $master_cvo = $ctaux->getMasterProduit()->getDroitCVO(date('Y-m-d'))->taux;
	    }
        } catch(Exception $e) {
            $droit_cvo = null;
            $master_comptable = null;
            $master_cvo = null;
        }
        $certificationLibelle = $produit->getCertification()->getLibelle();
        $certificationKey = $produit->getCertification()->getKey();

        $genreLibelle = $produit->getGenre()->getLibelle();
        $genreKey = $produit->getGenre()->getKey();
        if($genreKey == "DEFAUT"){ $genreKey = ""; }

        $appellationLibelle = $produit->getAppellation()->getLibelle();
        $appellationKey = $produit->getAppellation()->getKey();
        if($appellationKey == "DEFAUT"){ $appellationKey = ""; }

        $mentionLibelle = $produit->getMention()->getLibelle();
        $mentionKey = $produit->getMention()->getKey();
        if($mentionKey == "DEFAUT"){ $mentionKey = ""; }

        $lieuLibelle = $produit->getLieu()->getLibelle();
        $lieuKey = $produit->getLieu()->getKey();
        if($lieuKey == "DEFAUT"){ $lieuKey = ""; }

        $couleurLibelle = $produit->getCouleur()->getLibelle();
        $couleurKey = $produit->getCouleur()->getKey();
        if($couleurKey == "DEFAUT"){ $couleurKey = ""; }

        $cepageLibelle = $produit->getCepage()->getLibelle();
        $cepageKey = $produit->getCepage()->getKey();
        if($cepageKey == "DEFAUT"){ $cepageKey = ""; }

        $departements = ($produit->getDocument()->declaration->exist("departements"))? implode($produit->getDocument()->declaration->get("departements")->toArray(0,1),"|") : "";
        $code_douane = $produit->getCodeDouane();
        $libelle_douane = "Vins Tranquilles";

        $today = date("Y-m-d");
        $douane_taxe = $produit->getTauxDouane($today);
        $douane_date = "";
        try {
          $douane_node = $produit->getDroitDouane($today);
          if($douane_node->exist("date")){
            $douane_date = strstr($douane_node->getDate(),'T',true);
          }

        } catch (Exception $e) {
          echo "Le produit ".$produit->getHash()."n'a pas de date pour ses droits douanes \n";
        }
        $douane_noeud = "";

        $cvo_taxe = $produit->getTauxCVO($today);
        $cvo_date = "";
        $cvo_node = null;
        try {
          $cvo_node = $produit->getDroitCVO($today);
          if($cvo_node->exist("date")){
            $cvo_date = strstr($cvo_node->getDate(),'T',true);
            $cvo_node = $cvo_node->getNoeud();
          }

        } catch (Exception $e) {
          echo "Le produit ".$produit->getHash()."n'a pas de date pour ses droits douanes \n";
        }
        $cvo_noeud = "";

        $repli_entree = "";
        $repli_sorti = "";
        $declassement_entree	 = "";
        $declassement_sorti  = "";

        $densite  = "";
        $labels  = "";
        $code_produit  = "";
        $code_produit_noeud	= "";

        $code_analytique = "";
        $code_comptable_noeud = "";

        $code_douane = $produit->getCodeDouane();
        $code_douane_noeud = "";
        $alias_produit = "";

        $format_libelle = $produit->getFormatLibelleCalcule();
        $format_libelle_noeud = $cvo_node->getTypeNoeud();
        $cepages_autorises = "";
        if(method_exists(get_class($produit),'getCepagesAutorises') && count($produit->getCepagesAutorises())){
          $cepages_autorises = implode($produit->getCepagesAutorises(),"|") ;
        }

        echo sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n","declaration",$certificationLibelle,$certificationKey,
        $genreLibelle,$genreKey,
        $appellationLibelle,$appellationKey,
        $mentionLibelle,$mentionKey,
        $lieuLibelle,$lieuKey,
        $couleurLibelle,$couleurKey,
        $cepageLibelle,$cepageKey,
        $departements, $code_douane, $libelle_douane,
        $douane_taxe, $douane_date, $douane_noeud,
        $cvo_taxe, $cvo_date, $cvo_noeud,
        $repli_entree,$repli_sorti,$declassement_entree,$declassement_sorti,
        $densite,$labels,$code_produit,$code_produit_noeud,
        $code_analytique,$code_comptable_noeud,
        $code_douane,$code_douane_noeud,$alias_produit,
        $format_libelle,$format_libelle_noeud,$cepages_autorises
      );
    }
  }
}
