<?php

class importDRMTask extends importAbstractTask
{

  const CSV_LIGNE_ETABLISSEMENT = 0;
  const CSV_LIGNE_PERIODE = 1;
  const CSV_LIGNE_CODE_APPELLATION = 2;
  const CSV_LIGNE_TYPE = 3;
  const CSV_LIGNE_CAMPAGNE = 4;

  const CSV_LIGNE_TYPE_INFO = '01.INFO';
  const CSV_LIGNE_TYPE_DS = '02.DS';
  const CSV_LIGNE_TYPE_CONTRAT = '03.CONTRAT';
  const CSV_LIGNE_TYPE_ACHAT = '04.ACHAT';
  const CSV_LIGNE_TYPE_VENTE = '05.VENTE';
  const CSV_LIGNE_TYPE_DIVERS = '06.DIVERS';
  const CSV_LIGNE_TYPE_CAVE_VITI = '07.CAVE-VITI';
  const CSV_LIGNE_TYPE_CAVE_COOP = '08.CAVE-COOP';
  const CSV_LIGNE_TYPE_TRANSFERT_ENTREE = '09.TRANSFERT-ENTREE';
  const CSV_LIGNE_TYPE_TRANSFERT_SORTIE = '10.TRANSFERT-SORTIE';
  const CSV_LIGNE_TYPE_REGULARISATION = '11.REGULARISATION';
  const CSV_LIGNE_TYPE_REVENDICATION = '12.REVENDICATION';
  const CSV_LIGNE_TYPE_MOUVEMENT = '13.MOUVEMENT';
  const CSV_LIGNE_TYPE_STOCK = '14.STOCK';

  const CSV_DS_DOSSIER = 5;
  const CSV_DS_CAMPAGNE = 6;
  const CSV_DS_NUMERO_DECLARATION = 7;
  const CSV_DS_CODE_PARTENAIRE = 8;
  const CSV_DS_CODE_CHAI = 9;
  const CSV_DS_DATE_CREATION = 10;
  const CSV_DS_DATE_MODIFICATION = 11;
  const CSV_DS_CODE_SAISIS = 12;
  const CSV_DS_NUMERO_LIGNE = 16;
  const CSV_DS_CODE_APPELLATION = 17;
  const CSV_DS_VOLUME_LIBRE = 18;
  const CSV_DS_VOLUME_BLOQUE = 19;
  const CSV_DS_RECOLTE = 20;

  const CSV_CONTRAT_DOSSIER = 5;
  const CSV_CONTRAT_CAMPAGNE = 6;
  const CSV_CONTRAT_NUMERO_CONTRAT = 7;
  const CSV_CONTRAT_NUMERO_ENLEVEMENT = 8;
  const CSV_CONTRAT_PERIODE_ENLEVEMENT = 9;
  const CSV_CONTRAT_VOLUME_ENLEVE_UNITE_ACHAT = 10;
  const CSV_CONTRAT_UNITE_ACHAT = 11;
  const CSV_CONTRAT_COEF_CONVERSION_VOLUME = 12;
  const CSV_CONTRAT_MODE_CONVERSION_VOLUME = 13;
  const CSV_CONTRAT_VOLUME_ENLEVE_HL = 14;
  const CSV_CONTRAT_COTISATION_CVO_NEGOCIANT = 15;
  const CSV_CONTRAT_COTISATION_CVO_VITICULTEUR = 16;
  const CSV_CONTRAT_DATE_ENLEVEMENT = 19;
  const CSV_CONTRAT_CAMPAGNE_FACTURATION_VITICULTEUR = 20;
  const CSV_CONTRAT_SITE_VITICULTEUR = 21;
  const CSV_CONTRAT_NUMERO_FACTURE_VITICULTEUR = 22;
  const CSV_CONTRAT_CAMPAGNE_FACTURATION_NEGOCIANT = 23;
  const CSV_CONTRAT_SITE_NEGOCIANT = 24;
  const CSV_CONTRAT_NUMERO_FACTURE_NEGOCIANT = 25;
  const CSV_CONTRAT_DATE_ENREGISTREMENT = 29;
  const CSV_CONTRAT_CODE_RECETTE_LOCALE = 30;
  const CSV_CONTRAT_CODE_VITICULTEUR = 31;
  const CSV_CONTRAT_CODE_CHAI_CAVE = 32;
  const CSV_CONTRAT_CODE_NEGOCIANT = 33;
  const CSV_CONTRAT_CODE_COURTIER = 34;
  const CSV_CONTRAT_CODE_APPELLATION = 35;
  const CSV_CONTRAT_TYPE_PRODUIT = 36;
  const CSV_CONTRAT_MILLESIME = 37;
  const CSV_CONTRAT_COTISATION_CVO_NEGOCIANT_TOTAL = 38;
  const CSV_CONTRAT_COTISATION_CVO_VITICULTEUR_TOTAL = 39;
  const CSV_CONTRAT_VOLUME = 40;
  const CSV_CONTRAT_UNITE_VOLUME = 41;
  const CSV_CONTRAT_COEF_CONVERSION_VOLUME_TOTAL = 42;
  const CSV_CONTRAT_MODE_CONVERSION_VOLUME_TOTAL = 43;
  const CSV_CONTRAT_VOLUME_PROPOSE_HL = 44;
  const CSV_CONTRAT_VOLUME_ENLEVE_TOTAl_HL = 45;
  const CSV_CONTRAT_PRIX_VENTE = 46;
  const CSV_CONTRAT_CODE_DEVISE = 47;
  const CSV_CONTRAT_UNITE_PRIX_VENTE = 48;
  const CSV_CONTRAT_COEF_CONVERSION_PRIX = 49;
  const CSV_CONTRAT_MODE_CONVERSION_PRIX = 50;
  const CSV_CONTRAT_PRIX_AU_LITRE = 51;
  const CSV_CONTRAT_CONTRAT_SOLDE = 52;
  const CSV_CONTRAT_DATE_SIGNATURE_OU_CREATION = 53;
  const CSV_CONTRAT_DATE_DERNIERE_MODIFICATION = 54;
  const CSV_CONTRAT_CODE_SAISIE = 55;
  const CSV_CONTRAT_DATE_LIVRAISON = 56;
  const CSV_CONTRAT_CODE_MODE_PAIEMENT = 57;
  const CSV_CONTRAT_COMPOSTAGE = 58;
  const CSV_CONTRAT_TYPE_CONTRAT = 59;
  const CSV_CONTRAT_ATTENTE_ORIGINAL = 60;
  const CSV_CONTRAT_CATEGORIE_VIN = 61;
  const CSV_CONTRAT_CEPAGE = 62;
  const CSV_CONTRAT_MILLESIME_ANNEE = 63;
  const CSV_CONTRAT_PRIX_HORS_CVO = 64;
  const CSV_CONTRAT_PRIX_CVO_INCLUSE = 65;
  const CSV_CONTRAT_TAUX_CVO_GLOBAL = 66;

  const CSV_VENTE_DOSSIER = 5;
  const CSV_VENTE_CAMPAGNE = 6;
  const CSV_VENTE_NUMERO_SORTIE = 7;
  const CSV_VENTE_MOIS_SORTIE = 8;
  const CSV_VENTE_CODE_PARTENAIRE = 9;
  const CSV_VENTE_CODE_CHAI = 10;
  const CSV_VENTE_CODE_RECETTE_LOCALE = 11;
  const CSV_VENTE_DATE_CREATION = 12;
  const CSV_VENTE_DATE_MODIFICATION = 13;
  const CSV_VENTE_CODE_SAISIS = 14;
  const CSV_VENTE_DATE_SORTIE = 15;
  const CSV_VENTE_DATE_SAISIE_SORTIE = 16;
  const CSV_VENTE_NUMERO_LIGNE = 20;
  const CSV_VENTE_CODE_APPELLATION = 21;
  const CSV_VENTE_TYPE_VIN = 22;
  const CSV_VENTE_COTISATION_VITICULEUR_VENTE_DIRECTE = 23;
  const CSV_VENTE_VOLUME_EXPORT = 24;
  const CSV_VENTE_VOLUME_CONGE = 25;
  const CSV_VENTE_VOLUME_CRD = 26;
  const CSV_VENTE_FACTURE_INDICATEUR = 27;
  const CSV_VENTE_MILLESIME_INDICATEUR = 28;
  const CSV_VENTE_CODE_PAYS = 29;
  const CSV_VENTE_CAMPAGNE_FACTURE = 30;
  const CSV_VENTE_CODE_SITE = 31;
  const CSV_VENTE_NUMERO_FACTURE = 32;

  /*const CSV_ACHAT_DOSSIER = 5;
  const CSV_ACHAT_CAMPAGNE = 6;
  const CSV_ACHAT_NUMERO = 7;
  const CSV_ACHAT_CODE_PARTENAIRE = 8;
  const CSV_ACHAT_CODE_CHAI = 9;
  const CSV_ACHAT_DATE_CREATION = 10;
  const CSV_ACHAT_DATE_MODIFICATION = 11;
  const CSV_ACHAT_CODE_SAISIS = 12;
  const CSV_ACHAT_DATE_SORTIE = 13;
  const CSV_ACHAT_NUMERO_LIGNE = 16;
  const CSV_ACHAT_CODE_APPELLATION = 17;
  const CSV_ACHAT_VOLUME_LIBRE = 18;
  const CSV_ACHAT_VOLUME_BLOQUE = 19;
  const CSV_ACHAT_CERTIFICAT = 20;
  const CSV_ACHAT_VOLUME = 21;
  const CSV_ACHAT_VOLUME_RECOLTE = 22;
  const CSV_ACHAT_SUPERFICIE = 22;
  const CSV_ACHAT_CODE_APPELLATION_INAO = 24;
  const CSV_ACHAT_MILLESIME = 25;
  const CSV_ACHAT_DATE_CERTIFICAT = 26;
  const CSV_ACHAT_LABEL_AGREMENT = 27;*/

  const CSV_DIVERS_DOSSIER = 5;
  const CSV_DIVERS_CAMPAGNE = 6;
  const CSV_DIVERS_NUMERO_MOUVEMENT = 7;
  const CSV_DIVERS_ANNULATION = 8;
  const CSV_DIVERS_CODE_PARTENAIRE = 9;
  const CSV_DIVERS_CODE_CHAI = 10;
  const CSV_DIVERS_CODE_APPELLATION_1 = 11;
  const CSV_DIVERS_CODE_APPELLATION_2 = 12;
  const CSV_DIVERS_TEXTE_MOUVEMENT = 13;
  const CSV_DIVERS_CODE_MOUVEMENT = 14;
  const CSV_DIVERS_DATE_MOUVEMENT = 15;
  const CSV_DIVERS_MOIS_MOUVEMENT = 16;
  const CSV_DIVERS_DATE_HEURE_SAISIE = 17;
  const CSV_DIVERS_VOLUME_HL = 18;
  const CSV_DIVERS_CODE_UTILISATEUR = 19;
  const CSV_DIVERS_STOCK_DISPO_AVANT = 20;
  const CSV_DIVERS_STOCK_DISPO_APRES = 21;
  const CSV_DIVERS_STOCK_DISPO_PRECEDENT_AVANT = 22;
  const CSV_DIVERS_STOCK_DISPO_PRECEDENT_APRES = 23;
  const CSV_DIVERS_CODE_UTILISATEUR_SUPPRESSION = 24;
  const CSV_DIVERS_DATE_SUPPRESSION = 25;

  const CSV_TRANSFERT_DOSSIER = 5;
  const CSV_TRANSFERT_CAMPAGNE = 6;
  const CSV_TRANSFERT_NUMERO_MOUVEMENT = 7;
  const CSV_TRANSFERT_ANNULATION = 8;
  const CSV_TRANSFERT_CODE_PARTENAIRE = 9;
  const CSV_TRANSFERT_CODE_CHAI = 10;
  const CSV_TRANSFERT_CODE_PARTENAIRE_DESTINATAIRE = 11;
  const CSV_TRANSFERT_CODE_CHAI_DESTINATAIRE = 12;
  const CSV_TRANSFERT_CODE_APPELLATION = 13;
  const CSV_TRANSFERT_DATE_MOUVEMENT = 14;
  const CSV_TRANSFERT_MOIS_MOUVEMENT = 15;
  const CSV_TRANSFERT_DATE_HEURE_SAISIE = 16;
  const CSV_TRANSFERT_VOLUME_HL = 17;
  const CSV_TRANSFERT_CODE_UTILISATEUR = 18;
  const CSV_TRANSFERT_STOCK_DISPO_AVANT = 19;
  const CSV_TRANSFERT_STOCK_DISPO_APRES = 20;
  const CSV_TRANSFERT_CODE_UTILISATEUR_SUPPRESSION = 21;
  const CSV_TRANSFERT_DATE_SUPPRESSION = 22;

  const CSV_CAVE_DOSSIER = 5;
  const CSV_CAVE_CAMPAGNE = 6;
  const CSV_CAVE_NUMERO_MOUVEMENT = 7;
  const CSV_CAVE_ANNULATION = 8;
  const CSV_CAVE_CODE_COOPERATEUR = 9;
  const CSV_CAVE_CODE_COOPERATEUR_CHAI = 10;
  const CSV_CAVE_CODE_VITICULTEUR = 11;
  const CSV_CAVE_CODE_VITICULTEUR_CHAI = 12;
  const CSV_CAVE_CODE_APPELLATION = 13;
  const CSV_CAVE_DATE_MOUVEMENT = 14;
  const CSV_CAVE_DATE_HEURE_SAISIE = 15;
  const CSV_CAVE_VOLUME_ENTREE = 16;
  const CSV_CAVE_VOLUME_SORTIE = 17;
  const CSV_CAVE_CODE_UTILISATEUR = 18;
  const CSV_CAVE_STOCK_DISPO_AVANT = 19;
  const CSV_CAVE_STOCK_DISPO_APRES = 20;
  const CSV_CAVE_CODE_UTILISATEUR_SUPPRESSION = 21;
  const CSV_CAVE_DATE_SUPPRESSION = 22;
  const CSV_CAVE_MOIS_MOUVEMENT = 23;
  const CSV_CAVE_NUMERO_DOCUMENT = 24;

  const CSV_MOUVEMENT_DOSSIER = 5;
  const CSV_MOUVEMENT_CAMPAGNE = 6;
  const CSV_MOUVEMENT_CODE_PARTENAIRE = 7;
  const CSV_MOUVEMENT_CODE_CHAI = 8;
  const CSV_MOUVEMENT_CODE_APPELLATION = 9;
  const CSV_MOUVEMENT_CODE_MOUVEMENT = 10;
  const CSV_MOUVEMENT_DATE_MOUVEMENT = 11;
  const CSV_MOUVEMENT_DATE_HEURE_SAISIE = 12;
  const CSV_MOUVEMENT_STOCK_FIN_CAMPAGNE = 13;
  const CSV_MOUVEMENT_VOLUME_CONTRAT = 14;
  const CSV_MOUVEMENT_VOLUME_SORTIE = 15;
  const CSV_MOUVEMENT_VOLUME_ENLEVE = 16;
  const CSV_MOUVEMENT_VOLUME_CONTRAT_NOUVELLE_RECOLTE = 17;
  const CSV_MOUVEMENT_VOLUME_AGREE_COMMERCIALISABLE = 18;
  const CSV_MOUVEMENT_VOLUME_AGREE_BLOQUE = 19;
  const CSV_MOUVEMENT_VOLUME_SUSCEPTIBLE_RECLASSEMENT = 20;
  const CSV_MOUVEMENT_VOLUME_REGULARISATION = 21;
  const CSV_MOUVEMENT_VOLUME_AGR2 = 22;
  const CSV_MOUVEMENT_VOLUME_VOLUME_BLOQUE_CAMPAGNE_PRECEDENTE = 23;
  const CSV_MOUVEMENT_STOCK_COURANT = 24;
  const CSV_MOUVEMENT_TYPE_DOCUMENT = 25;
  const CSV_MOUVEMENT_NUMERO_DOCUMENT = 26;
  const CSV_MOUVEMENT_COMMENTAIRE = 27;
  const CSV_MOUVEMENT_VOLUME_RECOLTE = 28;
  const CSV_MOUVEMENT_SUPERFICIE_RECOLTE = 29;

  const CSV_STOCK_DOSSIER = 5;
  const CSV_STOCK_CAMPAGNE = 6;
  const CSV_STOCK_CODE_PARTENAIRE = 7;
  const CSV_STOCK_CODE_CHAI = 8;
  const CSV_STOCK_CODE_APPELLATION = 9;
  const CSV_STOCK_STOCK_FIN_CAMPAGNE = 10;
  const CSV_STOCK_VOLUME_CONTRAT = 11;
  const CSV_STOCK_VOLUME_SORTIE = 12;
  const CSV_STOCK_VOLUME_ENLEVEMENT = 13;
  const CSV_STOCK_VOLUME_CONTRAT_NOUVELLE_RECOLTE = 14;
  const CSV_STOCK_VOLUME_AGREE_COMMERCIALISABLE = 15;
  const CSV_STOCK_VOLUME_AGREE = 16;
  const CSV_STOCK_VOLUME_AGREE_BLOQUE = 17;
  const CSV_STOCK_VOLUME_SUSCEPTIBLE_RECLASSEMENT = 18;
  const CSV_STOCK_VOLUME_RECOLTE = 19;
  const CSV_STOCK_SUPERFICIE_RECOLTE = 20;
  const CSV_STOCK_VOLUME_REGULARISATION = 21;
  const CSV_STOCK_NON_UTILISE_1 = 22;
  const CSV_STOCK_VOLUME_VOLUME_BLOQUE_CAMPAGNE_PRECEDENTE = 23;
  const CSV_STOCK_STOCK = 24;
  const CSV_STOCK_NON_UTILISE_2 = 25;

  const CSV_CODE_MOUVEMENT_DS = 00;
  const CSV_CODE_MOUVEMENT_DS_MODIF = 01;
  const CSV_CODE_MOUVEMENT_DS_ANNULATION = 02;
  const CSV_CODE_MOUVEMENT_ENLEVEMENT = 20;
  const CSV_CODE_MOUVEMENT_ENLEVEMENT_ANNULATION = 21;
  const CSV_CODE_MOUVEMENT_ENLEVEMENT_REGUL = 22;
  const CSV_CODE_MOUVEMENT_SAISIE_DMVDP = 30;
  const CSV_CODE_MOUVEMENT_MODIF_DMVDP = 31;
  const CSV_CODE_MOUVEMENT_ANNUL_DMVDP = 32;
  const CSV_CODE_MOUVEMENT_REPLI_SORTIE = 51;
  const CSV_CODE_MOUVEMENT_REPLI_ENTREE = 52;
  const CSV_CODE_MOUVEMENT_CAVE_DEPOT = 56;
  const CSV_CODE_MOUVEMENT_CAVE_RETROCESSION = 57;
  const CSV_CODE_MOUVEMENT_CAVE_DEPOT_ANNULATION = 58;
  const CSV_CODE_MOUVEMENT_CAVE_RETROCESSION_ANNULATION = 59;
  const CSV_CODE_MOUVEMENT_AGREMENT = 60;
  const CSV_CODE_MOUVEMENT_AGREMENT_REGUL = 40;
  const CSV_CODE_MOUVEMENT_AUTRES = 80; //FERMAGE METAYAGE
  const CSV_CODE_MOUVEMENT_DECLASSEMENT = 81;
  const CSV_CODE_MOUVEMENT_DISTILLATION = 82;
  const CSV_CODE_MOUVEMENT_83 = 83;
  const CSV_CODE_MOUVEMENT_CONSO_PERTES = 86;
  const CSV_CODE_MOUVEMENT_DIVERS = 89;
  const CSV_CODE_MOUVEMENT_CESSION_AU_VITI = 71;
  const CSV_CODE_MOUVEMENT_CESSION_DU_VITI = 72;

  const CSV_ANNULATION_OUI = "OUI";

  protected function configure()
  {
    // // add your own arguments here
    $this->addArguments(array(
       new sfCommandArgument('file', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      // add your own options here
    ));

    $this->namespace        = 'import';
    $this->name             = 'drm';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [importVrac|INFO] task does things.
Call it with:

  [php symfony import:drm|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
    set_time_limit(0);
    $i = 1;
    $id = null;
    $lines = array();
    foreach(file($arguments['file']) as $line) {
      $data = str_getcsv($line, ';');
      if($id && $id != $this->getId($data)) {
        $this->importDRM($lines);
        $lines = array();
      }

      $id = $this->getId($data);
      $lines[$i] = $data;
      $i++;
    }

    if(count($lines) > 0) {
      $this->importDRM($lines);
    }
  }

  protected function getCodeProduit($line) {

    return $line[self::CSV_LIGNE_CODE_APPELLATION];
  }

  protected function getId($line) {

    return DRMClient::getInstance()->buildId($this->getIdentifiant($line), $this->getPeriode($line));
  }

  protected function getPeriode($line) {
    $annee = substr($line[self::CSV_LIGNE_PERIODE], 0, 4);
    $mois = substr($line[self::CSV_LIGNE_PERIODE], 4, 2);

    $periode = DRMClient::getInstance()->buildPeriode($annee, $mois);

    return DRMClient::getInstance()->buildPeriode($annee, $mois);
  }

  protected function getIdentifiant($line) {

    return $line[self::CSV_LIGNE_ETABLISSEMENT];
  }

  public function importDRM($lines) {
    $drm = null;
    $coherence_mouv = $this->initCoheranceWithMouvement();

    foreach($lines as $i => $line) {
      try{
        if(!$this->verifyLine($line)) {
          $coherence_mouv = $this->postVerifLine($drm, $line, $coherence_mouv);
		      continue;
	      }

	      $drm = $this->importLigne($drm, $line);
      } catch (Exception $e) {
        $this->logLigne('ERROR', $e->getMessage(), $line, $i);
        return;
      }
    }

    if(is_null($drm)) {
	    $this->logLignes('WARNING', sprintf("La DRM n'a pas été créée alors qu'il existe des lignes"), $lines, $i);
	    return;
    }

    $drm->update();

    try{
      $this->verifCoherenceWithMouvement($coherence_mouv, $drm, $lines);
    } catch (Exception $e) {
      $this->logLignes('WARNING', $e->getMessage(), $lines, $i);
    }

    if(!$drm->numero_archive) {
      $drm->numero_archive = sprintf("%05d", ArchivageAllView::getInstance()->getLastNumeroArchiveByTypeAndCampagne('DRM', $drm->campagne, '60000', '89999') + 1);
    }

    $drm->valide->date_saisie = date('Y-m-d', strtotime($drm->getDate()));
    $drm->valide->date_signee = date('Y-m-d', strtotime($drm->getDate()));

    $drm->validate();
    $drm->facturerMouvements();

    $drm->save();

    $drm->updateVracs();
  }

  public function importLigne($drm, $line) {
    if (is_null($drm)) {
      $drm = DRMClient::getInstance()->findOrCreateByIdentifiantAndPeriode($this->getIdentifiant($line), $this->getPeriode($line), true);

      if (!$drm->isNew()) {
        throw new sfException(sprintf("La DRM de %s pour la période %s existe déjà", $this->getIdentifiant($line), $this->getPeriode($line)));
      }

      if(!$drm->getEtablissement()) {
        throw new sfException(sprintf("L'etablissement %s n'existe pas", $this->getIdentifiant($line)));
      }

      if($drm->getEtablissement()->famille != EtablissementFamilles::FAMILLE_PRODUCTEUR) {
        throw new sfException(sprintf("L'etablissement %s n'est pas un producteur", $this->getIdentifiant($line)));
      }
    }

    switch($line[self::CSV_LIGNE_TYPE]) {
      case self::CSV_LIGNE_TYPE_INFO:
        $this->importLigneInfo($drm, $line);
        break;
      case self::CSV_LIGNE_TYPE_DS:
        $this->importLigneDS($drm, $line);
        break;
      case self::CSV_LIGNE_TYPE_CONTRAT:
        $this->importLigneContrat($drm, $line);
        break;
      case self::CSV_LIGNE_TYPE_VENTE:
        $this->importLigneVente($drm, $line);
        break;
      case self::CSV_LIGNE_TYPE_ACHAT:
        break;
      case self::CSV_LIGNE_TYPE_DIVERS:
        $this->importLigneDivers($drm, $line);
        break;
      case self::CSV_LIGNE_TYPE_CAVE_VITI:
      case self::CSV_LIGNE_TYPE_CAVE_COOP:
        $this->importLigneCave($drm, $line);
        break;
      case self::CSV_LIGNE_TYPE_TRANSFERT_ENTREE:
      case self::CSV_LIGNE_TYPE_TRANSFERT_SORTIE:
        $this->importLigneTransfert($drm, $line);
        break;
      case self::CSV_LIGNE_TYPE_REGULARISATION;
        $this->importLigneRegularisation($drm, $line);
        break;
      case self::CSV_LIGNE_TYPE_REVENDICATION;
        $this->importLigneRevendication($drm, $line);
        break;
      default:
        throw new sfException(sprintf("Le type de ligne '%s' n'est pas pris en compte", $line[self::CSV_LIGNE_TYPE]));
    }

    return $drm;
  }

  public function postVerifLine($drm, $line, $coherence_mouv) {
    if(!$drm) {

      return;
    }

    $drm->update();

    switch($line[self::CSV_LIGNE_TYPE]) {
      case self::CSV_LIGNE_TYPE_MOUVEMENT:
        $coherence_mouv = $this->buildCoheranceWithMouvement($coherence_mouv, $line);
        break;
      case self::CSV_LIGNE_TYPE_STOCK:
        $this->verifCoherenceWithStock($drm, $line);
        break;
    }

    return $coherence_mouv;
  }

  public function importLigneInfo($drm, $line) {
    $drm->numero_archive = sprintf("%05d", $line[self::CSV_VENTE_NUMERO_SORTIE]);
  }

  public function importLigneDS($drm, $line) {
    $produit = $drm->addProduit($this->getHash($this->getCodeProduit($line)));

    if (is_null($produit->stocks_debut->dont_revendique)) {
      $produit->stocks_debut->dont_revendique = 0;
    }

    $produit->stocks_debut->dont_revendique = $this->convertToFloat($line[self::CSV_DS_VOLUME_LIBRE]);
  }

  public function importLigneContrat($drm, $line) {
    $produit = $drm->addProduit($this->getHash($this->getCodeProduit($line)));

    $cvo = $this->convertToFloat($line[self::CSV_CONTRAT_COTISATION_CVO_VITICULTEUR] + $line[self::CSV_CONTRAT_COTISATION_CVO_NEGOCIANT]);

    if($produit->cvo->taux && $produit->cvo->taux != $cvo) {
      $this->logLigne('WARNING', sprintf("Deux taux de cvo différent ont été défini pour un produit d'une même DRM %s / %s", $produit->cvo->taux, $cvo), $line);
    }

    if(!$produit->cvo->taux) {
      $produit->cvo->taux = $cvo;
    }

    $detail = $produit->sorties->vrac_details->addDetail('VRAC-'.$this->constructNumeroContrat($line),
                                               $this->convertToFloat($line[self::CSV_CONTRAT_VOLUME_ENLEVE_HL]),
                                               $this->convertToDateObject($line[self::CSV_CONTRAT_DATE_ENLEVEMENT])->format('Y-m-d'));

    if ($detail->volume < 0) {
      $produit->entrees->reintegration += abs($detail->volume);
      $detail->volume = 0;
    }
  }

  public function importLigneVente($drm, $line) {
    $produit = $drm->addProduit($this->getHash($this->getCodeProduit($line)));

    $cvo = $this->convertToFloat($line[self::CSV_VENTE_COTISATION_VITICULEUR_VENTE_DIRECTE]);

    if($produit->cvo->taux && $produit->cvo->taux != $cvo) {
      $this->logLigne('WARNING', sprintf("Deux taux de cvo différent ont été défini pour un produit d'une même DRM %s / %s", $produit->cvo->taux, $cvo), $line);
    }

    if(!$produit->cvo->taux) {
      $produit->cvo->taux = $cvo;
    }

    if ($this->convertToFloat($line[self::CSV_VENTE_VOLUME_EXPORT]) > 0) {
      $code_pays = $this->convertCountry($line[self::CSV_VENTE_CODE_PAYS]);
      $produit->sorties->export_details->addDetail($code_pays,
                                                   $this->convertToFloat($line[self::CSV_VENTE_VOLUME_EXPORT]),
                                                   $this->convertToDateObject($line[self::CSV_VENTE_DATE_SORTIE])->format('Y-m-d'));
    } elseif($this->convertToFloat($line[self::CSV_VENTE_VOLUME_EXPORT]) < 0) {
      $produit->entrees->reintegration += abs($this->convertToFloat($line[self::CSV_VENTE_VOLUME_EXPORT]));
    }

    if ($this->convertToFloat($line[self::CSV_VENTE_VOLUME_CONGE]) > 0) {
      $produit->sorties->vracsanscontrat += $this->convertToFloat($line[self::CSV_VENTE_VOLUME_CONGE]);
    } elseif($this->convertToFloat($line[self::CSV_VENTE_VOLUME_CONGE]) < 0) {
      $produit->entrees->reintegration += abs($this->convertToFloat($line[self::CSV_VENTE_VOLUME_CONGE]));
    }

    if ($this->convertToFloat($line[self::CSV_VENTE_VOLUME_CRD]) > 0) {
      $produit->sorties->bouteille += $this->convertToFloat($line[self::CSV_VENTE_VOLUME_CRD]);
    } elseif($this->convertToFloat($line[self::CSV_VENTE_VOLUME_CRD]) < 0) {
      $produit->entrees->reintegration += abs($this->convertToFloat($line[self::CSV_VENTE_VOLUME_CRD]));
    }
  }

  public function importLigneDivers($drm, $line) {

    $produit = $drm->addProduit($this->getHash($this->getCodeProduit($line)));

    if($line[self::CSV_DIVERS_CODE_MOUVEMENT] == 86) {
      $produit->sorties->consommation += $this->convertToFloat($line[self::CSV_DIVERS_VOLUME_HL]);
      return;
    }

    if($line[self::CSV_DIVERS_CODE_MOUVEMENT] == 82) {
      $produit->sorties->distillation += $this->convertToFloat($line[self::CSV_DIVERS_VOLUME_HL]);
      return;
    }

    if($line[self::CSV_DIVERS_CODE_MOUVEMENT] == 81) {
      $produit->sorties->declassement += $this->convertToFloat($line[self::CSV_DIVERS_VOLUME_HL]);
      return;
    }

    if($line[self::CSV_DIVERS_CODE_MOUVEMENT] == 80) {
      $produit->sorties->fermagemetayage += $this->convertToFloat($line[self::CSV_DIVERS_VOLUME_HL]);
      return;
    }

    if($line[self::CSV_DIVERS_CODE_MOUVEMENT] == 89) {
      $produit->sorties->regularisation += $this->convertToFloat($line[self::CSV_DIVERS_VOLUME_HL]);
      return;
    }

    if($line[self::CSV_DIVERS_TEXTE_MOUVEMENT] == "REPLI") {
      $produit->sorties->repli += $this->convertToFloat($line[self::CSV_DIVERS_VOLUME_HL]);
      $produit_repli = $drm->addProduit($this->getHash($line[self::CSV_DIVERS_CODE_APPELLATION_2]));
      $produit_repli->entrees->repli += $this->convertToFloat($line[self::CSV_DIVERS_VOLUME_HL]);
      return;
    }

    throw new sfException(sprintf("Ce mouvement n'est pas prit en compte '%s;%s'", $line[self::CSV_DIVERS_TEXTE_MOUVEMENT], $line[self::CSV_DIVERS_CODE_MOUVEMENT]));
  }

  public function importLigneCave($drm, $line) {

    $produit = $drm->addProduit($this->getHash($this->getCodeProduit($line)));

    if($line[self::CSV_LIGNE_TYPE] == self::CSV_LIGNE_TYPE_CAVE_VITI) {
      $etablissement = EtablissementClient::getInstance()->find(sprintf("%06d%02d", $line[self::CSV_CAVE_CODE_COOPERATEUR], $line[self::CSV_CAVE_CODE_COOPERATEUR_CHAI]), acCouchdbClient::HYDRATE_JSON);
      if(!$etablissement) {

	      throw new sfException(sprintf("L'établissement cave coop '%s' n'existe pas", sprintf("%06d%02d", $line[self::CSV_CAVE_CODE_COOPERATEUR], $line[self::CSV_CAVE_CODE_COOPERATEUR_CHAI])));
      }

      if($this->convertToFloat($line[self::CSV_CAVE_VOLUME_ENTREE]) > 0) {

        $detail = $produit->sorties->cooperative_details->addDetail($etablissement->_id,
                                                        $this->convertToFloat($line[self::CSV_CAVE_VOLUME_ENTREE]),
                                                        $this->convertToDateObject($line[self::CSV_CAVE_DATE_MOUVEMENT])->format('Y-m-d'));
      }

      if($this->convertToFloat($line[self::CSV_CAVE_VOLUME_SORTIE]) > 0) {
        $produit->entrees->cooperative += $this->convertToFloat($line[self::CSV_CAVE_VOLUME_SORTIE]);
      }

    }

    if($line[self::CSV_LIGNE_TYPE] == self::CSV_LIGNE_TYPE_CAVE_COOP) {

      if($this->convertToFloat($line[self::CSV_CAVE_VOLUME_ENTREE]) > 0) {
        $produit->entrees->cooperative += $this->convertToFloat($line[self::CSV_CAVE_VOLUME_ENTREE]);
      }

      if($this->convertToFloat($line[self::CSV_CAVE_VOLUME_SORTIE]) > 0) {
        $produit->sorties->cession += $this->convertToFloat($line[self::CSV_CAVE_VOLUME_SORTIE]);
      }

    }

    if(isset($detail) && $detail->volume < 0) {

        throw new sfException(sprintf("Le volume coop en sortie est négatif %s", $detail->volume));
    }

    if($produit->sorties->cession < 0) {

      throw new sfException(sprintf("Le volume coop cession / retrocédé est négatif %s", $produit->entrees->cooperative));
    }

    if($produit->entrees->cooperative < 0) {

      throw new sfException(sprintf("Le volume coop en entrée est négatif %s", $produit->entrees->cooperative));
    }
  }

  public function importLigneTransfert($drm, $line) {

    $produit = $drm->addProduit($this->getHash($this->getCodeProduit($line)));

    if($line[self::CSV_LIGNE_TYPE] == self::CSV_LIGNE_TYPE_TRANSFERT_SORTIE) {
      $produit->sorties->cession += $this->convertToFloat($line[self::CSV_TRANSFERT_VOLUME_HL]);
    }

    if($line[self::CSV_LIGNE_TYPE] == self::CSV_LIGNE_TYPE_TRANSFERT_ENTREE) {
      $produit->entrees->transfert += $this->convertToFloat($line[self::CSV_TRANSFERT_VOLUME_HL]);
    }
  }

  public function importLigneRegularisation($drm, $line) {
    $produit = $drm->addProduit($this->getHash($this->getCodeProduit($line)));
    $volume_agree = $this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_AGREE_COMMERCIALISABLE]);

    $drm->commentaire .= $this->getMouvementCommentaire("Régularisation", $line, $produit);

    if($volume_agree > 0) {
      $produit->entrees->regularisation += $volume_agree;
    } elseif($volume_agree < 0) {
      $produit->sorties->regularisation += $volume_agree * -1;
    }
  }

  public function importLigneRevendication($drm, $line) {
    $produit = $drm->addProduit($this->getHash($this->getCodeProduit($line)));
    $drm->commentaire .= $this->getMouvementCommentaire("Revendication", $line, $produit);

    $produit->entrees->recolte += $this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_AGREE_COMMERCIALISABLE]);
  }

  protected function verifyLine($line) {
    if (!preg_match('/^[0-9]{4}-[0-9]{4}$/', $line[self::CSV_LIGNE_CAMPAGNE])) {

      throw new sfException(sprintf("La campagne n'est pas au bon format %s", $line[self::CSV_LIGNE_CAMPAGNE]));
    }

    if (!preg_match('/^[0-9]{8}$/', $line[self::CSV_LIGNE_ETABLISSEMENT])) {

      throw new sfException(sprintf("L'identifiant n'est pas au bon format %s", $line[self::CSV_LIGNE_ETABLISSEMENT]));
    }

    if (!preg_match('/^[2]{1}[0-9]{3}[0-1]{1}[0-9]{1}$/', $line[self::CSV_LIGNE_PERIODE])) {

      throw new sfException(sprintf("La période n'est pas au bon format %s", $line[self::CSV_LIGNE_PERIODE]));
    }

    if (!preg_match('/^[0-9]{4}$/', $line[self::CSV_LIGNE_CODE_APPELLATION])) {

      throw new sfException(sprintf("Le produit n'est pas au bon format %s", $line[self::CSV_LIGNE_CODE_APPELLATION]));
    }

    if (DRMClient::getInstance()->buildCampagne($this->getPeriode($line)) != $line[self::CSV_LIGNE_CAMPAGNE]) {

      $this->logLigne("WARNING",sprintf("Le periode %s ne fait pas parti de la campagne %s", $this->getPeriode($line), $line[self::CSV_LIGNE_CAMPAGNE]), $line);
    }

    switch($line[self::CSV_LIGNE_TYPE]) {
      case self::CSV_LIGNE_TYPE_INFO:
        return true;
      case self::CSV_LIGNE_TYPE_DS:
        return $this->verifyLineDS($line);
      case self::CSV_LIGNE_TYPE_CONTRAT:
        return $this->verifyLineContrat($line);
      case self::CSV_LIGNE_TYPE_DIVERS:
        return $this->verifyLineDivers($line);
      case self::CSV_LIGNE_TYPE_CAVE_VITI:
      case self::CSV_LIGNE_TYPE_CAVE_COOP:
        return $this->verifyLineCave($line);
      case self::CSV_LIGNE_TYPE_TRANSFERT_ENTREE:
      case self::CSV_LIGNE_TYPE_TRANSFERT_SORTIE:
        return $this->verifyLineTransfert($line);
      case self::CSV_LIGNE_TYPE_VENTE:
        return $this->verifyLineVente($line);
      case self::CSV_LIGNE_TYPE_REGULARISATION:
        return $this->verifyLineRegularisation($line);
      case self::CSV_LIGNE_TYPE_REVENDICATION:
        return $this->verifyLineRevendication($line);
    }

    return false;
  }

  protected function verifyLineDS($line) {
     $this->verifyVolume($line[self::CSV_DS_VOLUME_LIBRE]);

     return true;
  }

  protected function verifyLineContrat($line) {
    $this->verifyVolume($line[self::CSV_CONTRAT_VOLUME_ENLEVE_HL], true);

    $numero_contrat = $this->constructNumeroContrat($line);
    $contrat = VracClient::getInstance()->findByNumContrat($numero_contrat, acCouchdbClient::HYDRATE_JSON);

    if(!$contrat) {
	    throw new sfException(sprintf("Le contrat '%s' n'existe pas", $numero_contrat));
    }
    if(!in_array($contrat->type_transaction, array(VracClient::TYPE_TRANSACTION_VIN_VRAC, VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE))) {

	    return false;
    }

    return true;
  }

  protected function verifyLineVente($line) {
    $this->verifyVolume($line[self::CSV_VENTE_VOLUME_CRD], true);
    $this->verifyVolume($line[self::CSV_VENTE_VOLUME_CONGE], true);
    $this->verifyVolume($line[self::CSV_VENTE_VOLUME_EXPORT], true);

    return true;
  }

  protected function verifyLineDivers($line) {
    $this->verifyVolume($line[self::CSV_DIVERS_VOLUME_HL], true);

    if($line[self::CSV_DIVERS_TEXTE_MOUVEMENT] == "REPLI") {
      $this->getHash($line[self::CSV_DIVERS_CODE_APPELLATION_2]);
    }

    return true;
  }

  protected function verifyLineTransfert($line) {
    $this->verifyVolume($line[self::CSV_TRANSFERT_VOLUME_HL], true);

    return true;
  }

  protected function verifyLineCave($line) {
    if($line[self::CSV_LIGNE_TYPE] == self::CSV_LIGNE_TYPE_CAVE_VITI) {
      $this->verifyVolume($line[self::CSV_CAVE_VOLUME_SORTIE], true);
    }

    if($line[self::CSV_LIGNE_TYPE] == self::CSV_LIGNE_TYPE_CAVE_COOP) {
      $this->verifyVolume($line[self::CSV_CAVE_VOLUME_ENTREE], true);
    }

    return true;
  }

  protected function verifyLineRegularisation($line) {
    $this->verifyVolume($line[self::CSV_MOUVEMENT_VOLUME_AGREE_COMMERCIALISABLE], true);

    return true;
  }

  protected function verifyLineRevendication($line) {
    $this->verifyVolume($line[self::CSV_MOUVEMENT_VOLUME_AGREE_COMMERCIALISABLE], true);

    return true;
  }

  protected function constructNumeroContrat($line) {

      return $this->convertToDateObject($line[self::CSV_CONTRAT_DATE_ENREGISTREMENT])->format('Ymd') . sprintf("%05d", $line[self::CSV_CONTRAT_NUMERO_CONTRAT]);
  }

  protected function convertCountry($country) {
    $countries = ConfigurationClient::getInstance()->getCountryList();

    if($country == 'FRA') {
      $country = 'FR';
    }

    if($country == 'TU') {
      $country = 'TR';
    }

    if(!array_key_exists($country, $countries)) {

      throw new sfException(sprintf("Code pays '%s' invalide", $country));
    }

    return $country;
  }


  protected function getMouvementCommentaire($libelle, $line, $produit) {

    return sprintf("%s en %s le %s de %01.02f hl : %s\n",
                                $libelle,
                                $produit->getLibelle("%format_libelle%"),
                                $this->convertToDateObject($line[self::CSV_MOUVEMENT_DATE_MOUVEMENT])->format('d/m/Y'),
                                $this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_AGREE_COMMERCIALISABLE]),
                                $line[self::CSV_MOUVEMENT_COMMENTAIRE]);
  }

  protected function initCoheranceWithMouvement() {
    return array();
  }

  protected function buildCoheranceWithMouvement($coherence, $line) {
    $code = $this->getCodeProduit($line);
    if(!array_key_exists($code, $coherence)) {
      $coherence[$code] = array("stock" => null, "entrees" => 0, "sorties" => 0);
    }

    if(in_array($line[self::CSV_MOUVEMENT_CODE_MOUVEMENT], array(self::CSV_CODE_MOUVEMENT_ENLEVEMENT,
                                                                 self::CSV_CODE_MOUVEMENT_ENLEVEMENT_ANNULATION,
                                                                 self::CSV_CODE_MOUVEMENT_ENLEVEMENT_REGUL))) {
      $coherence[$code]["sorties"] += $this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_ENLEVE]); //Contrat
    }


    if(in_array($line[self::CSV_MOUVEMENT_CODE_MOUVEMENT], array(self::CSV_CODE_MOUVEMENT_SAISIE_DMVDP,
                                                                 self::CSV_CODE_MOUVEMENT_MODIF_DMVDP,
                                                                 self::CSV_CODE_MOUVEMENT_ANNUL_DMVDP))) {
      if($this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_SORTIE]) > 0) {
        $coherence[$code]["sorties"] += $this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_SORTIE]);
      } elseif($this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_SORTIE]) < 0) {
        $coherence[$code]["entrees"] += abs($this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_SORTIE]));
      }
    }

    if($line[self::CSV_MOUVEMENT_CODE_MOUVEMENT] == self::CSV_CODE_MOUVEMENT_REPLI_SORTIE) {
      $coherence[$code]["sorties"] += abs($this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_REGULARISATION])); //Repli
    }

    if($line[self::CSV_MOUVEMENT_CODE_MOUVEMENT] == self::CSV_CODE_MOUVEMENT_REPLI_ENTREE) {
      $coherence[$code]["entrees"] += abs($this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_REGULARISATION])); //Repli
    }

    if(in_array($line[self::CSV_MOUVEMENT_CODE_MOUVEMENT], array(self::CSV_CODE_MOUVEMENT_CAVE_DEPOT,
                                                                 self::CSV_CODE_MOUVEMENT_CAVE_DEPOT_ANNULATION))) {
      if($this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_REGULARISATION]) > 0) {
        $coherence[$code]["entrees"] += abs($this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_REGULARISATION])); //Cave
      }

      if($this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_REGULARISATION]) < 0) {
        $coherence[$code]["sorties"] += abs($this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_REGULARISATION])); //Cave
      }
    }

    if(in_array($line[self::CSV_MOUVEMENT_CODE_MOUVEMENT], array(self::CSV_CODE_MOUVEMENT_CAVE_RETROCESSION,
                                                                 self::CSV_CODE_MOUVEMENT_CAVE_RETROCESSION_ANNULATION))) {
      if($this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_REGULARISATION]) > 0) {
        $coherence[$code]["entrees"] += abs($this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_REGULARISATION])); //Cave
      }

      if($this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_REGULARISATION]) < 0) {
        $coherence[$code]["sorties"] += abs($this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_REGULARISATION])); //Cave
      }
    }

    if(in_array($line[self::CSV_MOUVEMENT_CODE_MOUVEMENT], array(self::CSV_CODE_MOUVEMENT_CESSION_DU_VITI,
                                                                 self::CSV_CODE_MOUVEMENT_CESSION_AU_VITI))) {
      if($this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_REGULARISATION]) > 0) {
        $coherence[$code]["entrees"] += abs($this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_REGULARISATION])); //Cession
      }

      if($this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_REGULARISATION]) < 0) {
        $coherence[$code]["sorties"] += abs($this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_REGULARISATION])); //Cession
      }
    }

    if(in_array($line[self::CSV_MOUVEMENT_CODE_MOUVEMENT], array(self::CSV_CODE_MOUVEMENT_AUTRES,
                                                                 self::CSV_CODE_MOUVEMENT_CONSO_PERTES,
                                                                 self::CSV_CODE_MOUVEMENT_DISTILLATION,
                                                                 self::CSV_CODE_MOUVEMENT_DECLASSEMENT,
                                                                 self::CSV_CODE_MOUVEMENT_DIVERS))) {
      $coherence[$code]["sorties"] += -$this->convertToFloat($line[self::CSV_MOUVEMENT_VOLUME_REGULARISATION]); //Divers
    }



    return $coherence;
  }

  protected function verifCoherenceWithMouvement($coherence, $drm, $lines) {

    foreach($coherence as $code => $volumes) {
      $produit = $drm->addProduit($this->getHash($code));

      $volumes["entrees"] += $produit->entrees->recolte;

      $stock_fin_de_mois = round($produit->total_debut_mois + $volumes["entrees"] - $volumes["sorties"], 2);

      if (round($produit->total, 2) != $stock_fin_de_mois) {
        throw new sfException(sprintf("Le stock fin de mois %s != de celui des mouvements %s (%s hl de différence) pour le produit %s (peut être un contrat raisin/moût : ;code_produit;1 ou 2; à la ligne CONTRAT)", $produit->total, $stock_fin_de_mois, $stock_fin_de_mois - $produit->total, $code));
      }

    }
  }

  protected function verifCoherenceWithStock($drm, $line) {

    if(!$drm->exist($this->getHash($this->getCodeProduit($line)))) {

      $this->logLigne('WARNING', sprintf("Le produit %s n'existe pas dans cette campagne alors qu'il existe dans les stocks", $line[self::CSV_LIGNE_CODE_APPELLATION]), $line);
      return;
    }

    $produit = $drm->addProduit($this->getHash($this->getCodeProduit($line)));

    $stock_fin_campagne = $this->convertToFloat($line[self::CSV_STOCK_STOCK_FIN_CAMPAGNE]);
    $stock_fin_campagne += $this->convertToFloat($line[self::CSV_STOCK_VOLUME_AGREE_COMMERCIALISABLE]);
    $stock_fin_campagne += $this->convertToFloat($line[self::CSV_STOCK_VOLUME_SORTIE]) * -1;
    $stock_fin_campagne += $this->convertToFloat($line[self::CSV_STOCK_VOLUME_ENLEVEMENT]) * -1;
    $stock_fin_campagne += $this->convertToFloat($line[self::CSV_STOCK_VOLUME_REGULARISATION]);
    $stock_fin_campagne = round($stock_fin_campagne, 2);

    if (round($produit->total, 2) != $stock_fin_campagne) {

        $this->logLigne('WARNING', sprintf("Le volume fin de campagne %s ne correspond pas à celui pévu dans les stocks %s pour le produit %s", $produit->total, $stock_fin_campagne,  $line[self::CSV_LIGNE_CODE_APPELLATION]), $line);
    }
  }
}
