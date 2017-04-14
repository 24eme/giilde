<?php

class DRMCsvFile extends CsvFile
{

  const NOEUD_TEMPORAIRE = 'TMP';
  const DEFAULT_KEY = 'DEFAUT';
  const CSV_COL_TYPE = 0;
  const CSV_COL_IDENTIFIANT_DECLARANT = 1;
  const CSV_COL_NOM_DECLARANT = 2;
  const CSV_COL_ANNEE = 3;
  const CSV_COL_MOIS = 4;
  const CSV_COL_RECTIFICATIVE = 5;
  const CSV_COL_ANNEE_PRECEDENTE = 6;
  const CSV_COL_MOIS_PRECEDENTE = 7;
  const CSV_COL_RECTIFICATIVE_PRECEDENTE = 8;
  const CSV_COL_CERTIFICATION = 9;
  const CSV_COL_CERTIFICATION_CODE = 10;
  const CSV_COL_GENRE = 11;
  const CSV_COL_GENRE_CODE = 12;
  const CSV_COL_APPELLATION = 13;
  const CSV_COL_APPELLATION_CODE = 14;
  const CSV_COL_MENTION = 15;
  const CSV_COL_MENTION_CODE = 16;
  const CSV_COL_LIEU = 17;
  const CSV_COL_LIEU_CODE = 18;
  const CSV_COL_COULEUR = 19;
  const CSV_COL_COULEUR_CODE = 20;
  const CSV_COL_CEPAGE = 21;
  const CSV_COL_CEPAGE_CODE = 22;
  const CSV_COL_MILLESIME = 23;
  const CSV_COL_MILLESIME_CODE = 24;
  const CSV_COL_LABELS = 25; //PLUS UTILISE
  const CSV_COL_LABELS_CODE = 26; //PLUS UTILISE
  const CSV_COL_MENTION_EXTRA = 27;
  const CSV_COL_CONTRAT_IDENTIFIANT = 28;
  const CSV_COL_CONTRAT_VOLUME = 29;
  const CSV_COL_DETAIL_TOTAL_DEBUT_MOIS = 28;
  const CSV_COL_DETAIL_STOCKDEB_BLOQUE = 29;
  const CSV_COL_DETAIL_STOCKDEB_WARRANTE = 30;
  const CSV_COL_DETAIL_STOCKDEB_INSTANCE = 31;
  const CSV_COL_DETAIL_STOCKDEB_COMMERCIALISABLE = 32;
  const CSV_COL_DETAIL_ENTREES = 33;
  const CSV_COL_DETAIL_ENTREES_ACHAT = 34;
  const CSV_COL_DETAIL_ENTREES_RECOLTE = 35;
  const CSV_COL_DETAIL_ENTREES_REPLI = 36;
  const CSV_COL_DETAIL_ENTREES_DECLASSEMENT = 37;
  const CSV_COL_DETAIL_ENTREES_MOUVEMENT = 38;
  const CSV_COL_DETAIL_ENTREES_CRD = 39;
  const CSV_COL_DETAIL_SORTIES = 40;
  const CSV_COL_DETAIL_SORTIES_VRAC = 41;
  const CSV_COL_DETAIL_SORTIES_EXPORT = 42;
  const CSV_COL_DETAIL_SORTIES_FACTURES = 43;
  const CSV_COL_DETAIL_SORTIES_CRD = 44;
  const CSV_COL_DETAIL_SORTIES_CONSOMMATION = 45;
  const CSV_COL_DETAIL_SORTIES_PERTES = 46;
  const CSV_COL_DETAIL_SORTIES_DECLASSEMENT = 47;
  const CSV_COL_DETAIL_SORTIES_REPLI = 48;
  const CSV_COL_DETAIL_SORTIES_MOUVEMENT = 49;
  const CSV_COL_DETAIL_SORTIES_DISTILLATION = 50;
  const CSV_COL_DETAIL_SORTIES_LIES = 51;
  const CSV_COL_DETAIL_TOTAL = 52;
  const CSV_COL_DETAIL_STOCKFIN_BLOQUE = 53;
  const CSV_COL_DETAIL_STOCKFIN_WARRANTE = 54;
  const CSV_COL_DETAIL_STOCKFIN_INSTANCE = 55;
  const CSV_COL_DETAIL_STOCKFIN_COMMERCIALISABLE = 56;
  const CSV_COL_DETAIL_DATEDESIGNATURE = 57;
  const CSV_COL_DETAIL_DATEDESAISIE = 58;
  const CSV_COL_DETAIL_MODEDESAISIE = 59;
  const CSV_COL_DETAIL_CVO_TAUX = 60;
  const CSV_COL_DETAIL_CVO_VOLUME = 61;
  const CSV_COL_DETAIL_CVO_PRIX = 62;
  const CSV_COL_DETAIL_IDDRMDECLARVIN = 63;
  const CSV_COL_DETAIL_ID_ETABLISSEMENT_INTERNE = 64;

  public static function datize($str) {
    if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $str)) {
      return $str;
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}([^T]|$)/', $str)) {
      return $str.'T00:00:00Z';
    }
    if (preg_match('/\//', $str)) {
      $str = preg_replace('/(\d{2})\/(\d{2})\/(\d{4})/', '\3-\2-\1', $str);
      return $str.'T00:00:00Z' ;
    }
    if (preg_match('/2012$/', $str)) {
      $str = preg_replace('/(\d{2})(\d{2})2012/', '2012-\2-\1', $str);
      return $str.'T00:00:00Z' ;
    }
    throw new sfException("Wrong date format $str");
  }

  public static function createFromArray($array) {
    $csv = new DRMCsvFile();
    $csv->csvdata = $array;
    return $csv;
  }

  public static function createFromDRM(DRM $drm) {
    $csv = new DRMCsvFile();
    $csv->csvdata = array();
    foreach ($drm->getProduitsDetails() as $d) {
      $line = array();
      $line[self::CSV_COL_TYPE] = 'DETAIL';
      $line[self::CSV_COL_IDENTIFIANT_DECLARANT] = $d->getDocument()->getIdentifiant();
      $line[self::CSV_COL_NOM_DECLARANT] = $d->getDocument()->getDeclarant()->getNom();
      $line[self::CSV_COL_ANNEE] = $d->getDocument()->getAnnee();
      $line[self::CSV_COL_MOIS] = $d->getDocument()->getMois();
      $line[self::CSV_COL_RECTIFICATIVE] = $d->getDocument()->getRectificative();
      $line[self::CSV_COL_ANNEE_PRECEDENTE] = $d->getDocument()->getPrecedente()->getAnnee();
      $line[self::CSV_COL_MOIS_PRECEDENTE] = $d->getDocument()->getPrecedente()->getMois();
      $line[self::CSV_COL_RECTIFICATIVE_PRECEDENTE] = $d->getDocument()->getPrecedente()->getRectificative();
      $line[self::CSV_COL_CERTIFICATION] = $d->getCertification()->getLibelle();
      $line[self::CSV_COL_CERTIFICATION_CODE] = $d->getCertification()->getCode();
      $line[self::CSV_COL_GENRE] = $d->getGenre()->getLibelle();
      $line[self::CSV_COL_GENRE_CODE] = $d->getGenre()->getCode();
      $line[self::CSV_COL_APPELLATION] = $d->getAppellation()->getLibelle();
      $line[self::CSV_COL_APPELLATION_CODE] = $d->getAppellation()->getCode();
      $line[self::CSV_COL_MENTION] = $d->getMention()->getLibelle();
      $line[self::CSV_COL_MENTION_CODE] = $d->getMention()->getCode();
      $line[self::CSV_COL_LIEU] = $d->getLieu()->getLibelle();
      $line[self::CSV_COL_LIEU_CODE] = $d->getLieu()->getCode();
      $line[self::CSV_COL_COULEUR] = $d->getCouleur()->getLibelle();
      $line[self::CSV_COL_COULEUR_CODE] = $d->getCouleur()->getCode();
      $line[self::CSV_COL_CEPAGE] = $d->getCepage()->getLibelle();
      $line[self::CSV_COL_CEPAGE_CODE] = $d->getCepage()->getCode();
      $line[self::CSV_COL_MILLESIME] = '';
      $line[self::CSV_COL_MILLESIME_CODE] = '';
      $line[self::CSV_COL_MENTION] = $d->label_supplementaire;
      $line[self::CSV_COL_DETAIL_TOTAL_DEBUT_MOIS] = $d->total_debut_mois;
      $line[self::CSV_COL_DETAIL_ENTREES] = $d->total_entrees;
      $line[self::CSV_COL_DETAIL_ENTREES_ACHAT] = $d->entrees->achat;
      $line[self::CSV_COL_DETAIL_ENTREES_RECOLTE] = $d->entrees->recolte;
      $line[self::CSV_COL_DETAIL_ENTREES_REPLI] = $d->entrees->repli;
      $line[self::CSV_COL_DETAIL_ENTREES_DECLASSEMENT] = $d->entrees->declassement;
      $line[self::CSV_COL_DETAIL_ENTREES_MOUVEMENT] = $d->entrees->mouvement;
      $line[self::CSV_COL_DETAIL_ENTREES_CRD] = $d->entrees->crd;
      $line[self::CSV_COL_DETAIL_SORTIES] = $d->total_sorties;
      $line[self::CSV_COL_DETAIL_SORTIES_VRAC] = $d->sorties->vrac;
      $line[self::CSV_COL_DETAIL_SORTIES_EXPORT] = $d->sorties->export;
      $line[self::CSV_COL_DETAIL_SORTIES_FACTURES] = $d->sorties->factures;
      $line[self::CSV_COL_DETAIL_SORTIES_CRD] = $d->sorties->crd;
      $line[self::CSV_COL_DETAIL_SORTIES_CONSOMMATION] = $d->sorties->consommation;
      $line[self::CSV_COL_DETAIL_SORTIES_PERTES] = $d->sorties->pertes;
      $line[self::CSV_COL_DETAIL_SORTIES_DECLASSEMENT] = $d->sorties->declassement;
      $line[self::CSV_COL_DETAIL_SORTIES_REPLI] = $d->sorties->repli;
      $line[self::CSV_COL_DETAIL_SORTIES_MOUVEMENT] = $d->sorties->mouvement;
      $line[self::CSV_COL_DETAIL_SORTIES_DISTILLATION] = $d->sorties->distillation;
      $line[self::CSV_COL_DETAIL_SORTIES_LIES] = $d->sorties->lies;
      $line[self::CSV_COL_DETAIL_TOTAL] = $d->total;
      $line[self::CSV_COL_DETAIL_STOCKFIN_BLOQUE] = $d->stocks_fin->bloque;
      $line[self::CSV_COL_DETAIL_STOCKFIN_WARRANTE] = $d->stocks_fin->warrante;
      $line[self::CSV_COL_DETAIL_STOCKFIN_INSTANCE] = $d->stocks_fin->instance;
      $line[self::CSV_COL_DETAIL_STOCKFIN_COMMERCIALISABLE] = $d->stocks_fin->commercialisable;
      $line[self::CSV_COL_DETAIL_CVO_TAUX] = $d->getDroit(DRMDroits::DROIT_CVO)->getTaux();
      $line[self::CSV_COL_DETAIL_CVO_VOLUME] = $d->getDroitVolume(DRMDroits::DROIT_CVO);
      $line[self::CSV_COL_DETAIL_CVO_PRIX] = $line[self::CSV_COL_DETAIL_CVO_TAUX] * $line[self::CSV_COL_DETAIL_CVO_VOLUME];
      $line[self::CSV_COL_DETAIL_DATEDESAISIE] = $d->getDocument()->valide->date_saisie;
      $line[self::CSV_COL_DETAIL_DATEDESIGNATURE] = $d->getDocument()->valide->date_signee;
      $line[self::CSV_COL_DETAIL_MODEDESAISIE] = $d->getDocument()->mode_de_saisie;
      $line[self::CSV_COL_DETAIL_IDDRMDECLARVIN] = $d->getDocument()->_id;
      $line[self::CSV_COL_DETAIL_ID_ETABLISSEMENT_INTERNE] = $d->getDocument()->getEtablissement()->num_interne;
      $csv->csvdata[] = $line;
    }
    return $csv;
  }

  private function verifyCsvLine($detail, $line) {
    if (round($line[self::CSV_COL_DETAIL_ENTREES], 2) != round($detail->total_entrees, 2))
      throw new sfException("la somme des entrees (".$detail->total_entrees.") n'est pas en accord avec les informations du csv (".$line[self::CSV_COL_DETAIL_ENTREES].")");
    if (round($line[self::CSV_COL_DETAIL_SORTIES], 2) != round($detail->total_sorties, 2))
      throw new sfException("la somme des sorties n'est pas en accord avec les informations du csv ('".$line[self::CSV_COL_DETAIL_SORTIES]."' != '".$detail->total_sorties."')");
    if (round($line[self::CSV_COL_DETAIL_TOTAL], 2) != round($detail->total, 2))
      throw new sfException("le total n'est pas en accord avec les informations du csv");
    if (round($line[self::CSV_COL_DETAIL_TOTAL_DEBUT_MOIS], 2) != round($detail->total_debut_mois, 2))
      throw new sfException("le total début de mois n'est pas en accord avec les informations historiques");
  }

  private function getProduit($line) {
    if($this->drm->getAnnee() != $line[self::CSV_COL_ANNEE])
      throw new sfException("Incoherence dans l'année de la DRM");
    if($this->drm->getMois() != $line[self::CSV_COL_MOIS])
      throw new sfException("Incoherence dans le mois de la DRM (".$this->drm->getMois().' <> '.$line[self::CSV_COL_MOIS].')');
    if($this->drm->identifiant != $line[self::CSV_COL_IDENTIFIANT_DECLARANT])
      throw new sfException("Incoherence dans l'identifiant de l'établissement DRM");

    $hash = $this->config->identifyProduct($line[self::CSV_COL_CERTIFICATION],
					   $line[self::CSV_COL_GENRE],
					   $line[self::CSV_COL_APPELLATION],
					   $line[self::CSV_COL_MENTION],
					   $line[self::CSV_COL_LIEU],
					   $line[self::CSV_COL_COULEUR],
					   $line[self::CSV_COL_CEPAGE]);
    $detail = $this->drm->addProduit($hash);
    if ($line[self::CSV_COL_MENTION])
      $detail->label_supplementaire = $line[self::CSV_COL_MENTION];
    return $detail;
  }

  private function parseContrat($line) {
    $detail = $this->getProduit($line);
    $contrat = VracClient::getInstance()->retrieveById($line[self::CSV_COL_CONTRAT_IDENTIFIANT]);
    $contratVol = $line[self::CSV_COL_CONTRAT_VOLUME];
    if ($contrat && $contratVol && !$contrat->actif) {
    	$contrat->actif = 1;
    	$contrat->save();
    }
    $detail->addVrac($line[self::CSV_COL_CONTRAT_IDENTIFIANT], $line[self::CSV_COL_CONTRAT_VOLUME]);
    $this->drm->update();
  }

  private function parseDetail($line) {
      $detail = $this->getProduit($line);
      $detail->total_debut_mois = $line[self::CSV_COL_DETAIL_TOTAL_DEBUT_MOIS]*1;
      $detail->entrees->achat = $line[self::CSV_COL_DETAIL_ENTREES_ACHAT] *1 ;
      $detail->entrees->recolte = $line[self::CSV_COL_DETAIL_ENTREES_RECOLTE] *1 ;
      $detail->entrees->repli = $line[self::CSV_COL_DETAIL_ENTREES_REPLI] * 1 ;
      $detail->entrees->declassement = $line[self::CSV_COL_DETAIL_ENTREES_DECLASSEMENT] *1;
      $detail->entrees->mouvement = $line[self::CSV_COL_DETAIL_ENTREES_MOUVEMENT] *1;
      $detail->entrees->crd = $line[self::CSV_COL_DETAIL_ENTREES_CRD] *1;
      $detail->sorties->vrac = $line[self::CSV_COL_DETAIL_SORTIES_VRAC] *1;
      $detail->sorties->export = $line[self::CSV_COL_DETAIL_SORTIES_EXPORT] *1;
      $detail->sorties->factures = $line[self::CSV_COL_DETAIL_SORTIES_FACTURES] *1;
      $detail->sorties->crd = $line[self::CSV_COL_DETAIL_SORTIES_CRD] *1;
      $detail->sorties->consommation = $line[self::CSV_COL_DETAIL_SORTIES_CONSOMMATION] *1;
      $detail->sorties->pertes = $line[self::CSV_COL_DETAIL_SORTIES_PERTES] *1;
      $detail->sorties->declassement = $line[self::CSV_COL_DETAIL_SORTIES_DECLASSEMENT] *1;
      $detail->sorties->repli = $line[self::CSV_COL_DETAIL_SORTIES_REPLI] *1;
      $detail->sorties->mouvement = $line[self::CSV_COL_DETAIL_SORTIES_MOUVEMENT] *1;
      $detail->sorties->distillation = $line[self::CSV_COL_DETAIL_SORTIES_DISTILLATION] *1;
      $detail->sorties->lies = $line[self::CSV_COL_DETAIL_SORTIES_LIES] *1;
      $detail->stocks_fin->bloque = $line[self::CSV_COL_DETAIL_STOCKFIN_BLOQUE] *1;
      $detail->stocks_fin->warrante = $line[self::CSV_COL_DETAIL_STOCKFIN_WARRANTE] *1;
      $detail->stocks_fin->instance = $line[self::CSV_COL_DETAIL_STOCKFIN_INSTANCE] *1;
      $detail->stocks_fin->commercialisable = $line[self::CSV_COL_DETAIL_STOCKFIN_COMMERCIALISABLE] *1;
      $this->drm->update();
      $this->verifyCsvLine($detail, $line);
  }

  public function importDRM($options = null) {
    $compte = null;
    if (isset($options['compte']))
      $compte = $options['compte'];
    $this->config = ConfigurationClient::getCurrent();
    $this->drm = null;
    $this->errors = array();
    $this->numline = (isset($options['init_line'])) ? $options['init_line'] : 0;

    try {
      foreach ($this->getCsv() as $line) {
	//Les CSV d'InterRhone et CIPV n'ont pas le noeud mention, on l'ajoute
	array_splice($line, self::CSV_COL_MENTION, 0, array('', ''));
	$this->numline++;
	if (!$this->drm) {
	  $etablissement = $line[self::CSV_COL_IDENTIFIANT_DECLARANT];
	  if ($compte) {
	    if (! $compte->getCompte()->hasEtablissementId($etablissement))
	      throw new sfException("L'établissement $etablissement n'est pas accessible depuis votre compte");
	  }
	  if ($lastDRM = DRMClient::getInstance()->findMasterByIdentifiantAndPeriode($etablissement, DRMClient::getInstance()->buildPeriode($line[self::CSV_COL_ANNEE], $line[self::CSV_COL_MOIS]))) {
	  	throw new sfException('Vous ne pouvez pas importer une DRM déjà existante ('.$lastDRM->get('_id').').');
	  }
	  $this->drm = DRMClient::getInstance()->findOrCreateByIdentifiantAndPeriode($etablissement, $line[self::CSV_COL_ANNEE], $line[self::CSV_COL_MOIS]);
	  $this->drm->valide->date_signee = self::datize($line[self::CSV_COL_DETAIL_DATEDESIGNATURE]);
	  $this->drm->valide->date_saisie = self::datize($line[self::CSV_COL_DETAIL_DATEDESAISIE]);
	}
	switch($line[self::CSV_COL_TYPE]) {
	case 'DETAIL':
	  $this->parseDetail($line);
	  break;
	case 'CONTRAT':
	  $this->parseContrat($line);
	  break;
	}
      }
    }catch(sfException $e){
      $this->errors[] = array('line'=> $this->numline, 'message'=>$e->getMessage());
      throw $e;
    }

    $validator = new DRMValidation($this->drm, $options);

    if ($validator->hasErrors()) {
      foreach($validator->getErrors() as $err) {
	$this->errors[] = array('message' => $err->getMessage());
      }
    }

    if (count($this->errors)) {
      throw new sfException('errors (cf. DRMCsvFile->errors)');
    }

    if ($this->drm->valide->date_signee && $this->drm->valide->date_saisie) {
      $this->drm->validate($options);
    }

    return $this->drm;
  }

  public function getErrors() {
    return $this->errors;
  }
}
