<?php

/**
 * Inheritance tree class _ConfigurationDeclaration
 *
 */
abstract class _ConfigurationDeclaration extends acCouchdbDocumentTree {

    protected $libelles = null;
    protected $codes = null;
    protected $noeud_droits = null;
    protected $droits_type = array();
    protected $produits_all = null;
    protected $produits = array();
    protected $libelle_format = array();
    protected $dates_droits = null;

    const ATTRIBUTE_CVO_FACTURABLE = 'CVO_FACTURABLE';
    const ATTRIBUTE_CVO_ACTIF = 'CVO_ACTIF';
    const ATTRIBUTE_DOUANE_FACTURABLE = 'DOUANE_FACTURABLE';
    const ATTRIBUTE_DOUANE_ACTIF = 'DOUANE_ACTIF';

    protected function loadAllData() {
        parent::loadAllData();

        $this->getDatesDroits();
        $this->getProduitsAll();
        $this->loadProduitsByDates();
        $this->getLibelles();
        $this->getCodes();
    }

    abstract public function getChildrenNode();

    public function getParentNode() {
        $parent = $this->getParent()->getParent();
        if (!$parent instanceof _ConfigurationDeclaration) {

            throw new sfException('Noeud racine atteint');
        } else {

            return $this->getParent()->getParent();
        }
    }

    public function getDatesDroits($interpro = "INTERPRO-declaration") {
        if (is_null($this->dates_droits)) {
            $this->dates_droits = $this->getDocument()->declaration->getDatesDroits($interpro);
        }

        return $this->dates_droits;
    }

    public function loadDatesDroits($interpro = "INTERPRO-declaration") {
        $dates_droits = array();

        $noeudDroits = $this->getDroits($interpro);
        if ($noeudDroits) {
            foreach ($noeudDroits as $droits) {
                foreach ($droits as $droit) {
                    $dateObj = new DateTime($droit->date);
                    $dates_droits[$dateObj->format('Y-m-d')] = true;
                }
            }
        }

        krsort($dates_droits);

        if (!$this->getChildrenNode()) {

            return $dates_droits;
        }

        foreach ($this->getChildrenNode() as $child) {
            $dates_droits = array_merge($dates_droits, $child->loadDatesDroits($interpro));
        }

        krsort($dates_droits);

        return $dates_droits;
    }

    public function getProduitsAll($interpro = null, $departement = null) {
        if (is_null($this->produits_all)) {
            $this->produits_all = array();
            foreach ($this->getChildrenNode() as $key => $item) {
                $this->produits_all = array_merge($this->produits_all, $item->getProduitsAll());
            }
        }

        return $this->produits_all;
    }

    public function findDroitsDate($date, $interpro) {
        $datesDroits = $this->getDatesDroits($interpro);

        foreach ($datesDroits as $dateDroits => $null) {
            if ($date >= $dateDroits) {

                return $dateDroits;
            }
        }

        throw new sfException(sprintf("Aucune date défini pour le droit (interpro: %s, hash: %s)", $interpro, $this->getHash()));
    }

    public function getKeyAttributes($attributes) {
        sort($attributes);

        return implode("", $attributes);
    }

    public function loadProduitsByDates($interpro = "INTERPRO-declaration") {
        $datesDroits = $this->getDatesDroits($interpro);
        $attributesCombinaison = array(
            array(),
            array(self::ATTRIBUTE_CVO_FACTURABLE),
            array(self::ATTRIBUTE_CVO_ACTIF),
            array(self::ATTRIBUTE_CVO_ACTIF, self::ATTRIBUTE_DOUANE_ACTIF)
        );
        foreach ($datesDroits as $dateDroit => $null) {
            foreach ($attributesCombinaison as $attributes) {
                $this->getProduits($dateDroit, $interpro, null, $attributes);
            }
        }
    }

    public function getProduits($date = null, $interpro = "INTERPRO-declaration", $departement = null, $attributes = array()) {
        if (!$date) {
            $date = date('Y-m-d');
        }

        $date = $this->findDroitsDate($date, $interpro);
        $attributesKey = $this->getKeyAttributes($attributes);

        if (array_key_exists($date, $this->produits) && array_key_exists($attributesKey, $this->produits[$date])) {

            return $this->produits[$date][$attributesKey];
        }

        $produits = array();

        foreach ($this->getProduitsAll($interpro, $departement) as $hash => $item) {
            if (!$item->hasProduitAttributes($date, $attributes)) {

                continue;
            }

            $produits[$hash] = $item;
        }

        if (!array_key_exists($date, $this->produits)) {

            $this->produits[$date] = array();
        }

        $this->produits[$date][$attributesKey] = $produits;

        return $this->produits[$date][$attributesKey];
    }

    public function getLibelles() {
        if (is_null($this->libelles)) {
            $this->libelles = array_merge($this->getParentNode()->getLibelles(), array($this->libelle));
        }

        return $this->libelles;
    }

    public function getCodes() {
        if (is_null($this->codes)) {
            $this->codes = array_merge($this->getParentNode()->getCodes(), array($this->code));
        }

        return $this->codes;
    }

    public function getProduitsHashByCodeDouane($date, $interpro, $attributes = array()) {
        $produits = array();
        foreach ($this->getProduits($date, $interpro, $attributes) as $hash => $item) {
            $produits[$item->getCodeDouane()] = $hash;
        }

        return $produits;
    }

    public function getCodeDouane() {
        if (!$this->_get('code_douane')) {

            return $this->getParentNode()->getCodeDouane();
        }

        return $this->_get('code_douane');
    }

    public function getCodeProduit() {
        if (!$this->_get('code_produit')) {

            return $this->getParentNode()->getCodeProduit();
        }

        return $this->_get('code_produit');
    }

    public function getCodeComptable() {
        if (!$this->_get('code_comptable')) {

            return $this->getParentNode()->getCodeComptable();
        }

        return $this->_get('code_comptable');
    }

    public function getFormatLibelleCalcule() {
        if (!$this->getFormatLibelle()) {

            return $this->getParentNode()->getFormatLibelleCalcule();
        }

        return $this->getFormatLibelle();
    }

    public function getFormatLibelleDefinitionNoeud() {
        if ($this->getFormatLibelle()) {

            return $this;
        }

        return $this->getParentNode()->getFormatLibelleDefinitionNoeud();
    }

    public function getDensite() {
        if (!$this->exist('densite') || !$this->_get('densite')) {
            try {

                return $this->getParentNode()->getDensite();
            } catch (Exception $e) {

                return null;
            }
        }

        return $this->_get('densite');
    }

    public function getLibelleFormat($denomination_complementaire = null, $format = "%format_libelle%", $label_separator = ", ") {
        if (!array_key_exists($format, $this->libelle_format)) {
            $format_libelle = $this->getFormatLibelleCalcule();
            $format = str_replace("%format_libelle%", $format_libelle, $format);
            $libelle = $this->formatProduitLibelle($format);
            $libelle = $this->getDocument()->formatDenominationComplLibelle($denomination_complementaire, $libelle, $label_separator);
            $this->libelle_format[$format] = trim($libelle);
        }

        return $this->libelle_format[$format];
    }

    public function formatProduitLibelle($format = "%g% %a% %m% %l% %co% %ce%") {
        $libelle = ConfigurationClient::getInstance()->formatLibelles($this->getLibelles(), $format);

        $libelle = str_replace(array('%code%',
            '%code_produit%',
            '%code_comptable%'), array($this->getCodeFormat(),
            $this->getCodeProduit(),
            $this->getCodeComptable()), $libelle);
        $libelle = str_replace("()", "", $libelle);
        $libelle = preg_replace('/ +/', ' ', $libelle);


        return $libelle;
    }

    public function getCodeFormat($format = "%g%%a%%m%%l%%co%%ce%") {

        return ConfigurationClient::getInstance()->formatCodes($this->getCodes(), $format);
    }

    public function getDroitByType($date, $type, $interpro = "INTERPRO-declaration") {
        $date = $this->findDroitsDate($date, $interpro);
        if (array_key_exists($date, $this->droits_type) && array_key_exists($type, $this->droits_type[$date])) {

            return $this->droits_type[$date][$type];
        }

        if (!array_key_exists($date, $this->droits_type)) {
            $this->droits_type[$date] = array();
        }

        $this->droits_type[$date][$type] = $this->getDroits($interpro)->get($type)->getCurrentDroit($date,false);

        return $this->droits_type[$date][$type];
    }

    public function getDroitCVO($date, $interpro = "INTERPRO-declaration") {

        return $this->getDroitByType($date, ConfigurationDroits::DROIT_CVO, $interpro);
    }

    public function getDroitDouane($date, $interpro = "INTERPRO-declaration") {

        return $this->getDroitByType($date, ConfigurationDroits::DROIT_DOUANE, $interpro);
    }

    public function isCVOActif($date) {

        return $this->getTauxCVO($date) >= 0;
    }

    public function isCVOFacturable($date) {

        return $this->getTauxCVO($date) > 0;
    }

    public function isActif($date) {

        return $this->isCVOActif($date) && $this->isDouaneActif($date);
    }

    public function getTauxCVO($date) {
        try {
            $droit_produit = $this->getDroitCVO($date);
            $cvo_produit =null;
            if (is_object($droit_produit)) {
                $cvo_produit = $droit_produit->getTaux();
            } else {
                $cvo_produit = $droit_produit;
            }
        } catch (Exception $ex) {
            $cvo_produit = -1;
        }

        return $cvo_produit;
    }

    public function isDouaneActif($date) {

        return $this->getTauxDouane($date) >= 0;
    }

    public function isDouaneFacturable($date) {

        return $this->getTauxDouane($date) > 0;
    }

    public function getTauxDouane($date) {
        try {
            $droit_produit = $this->getDroitDouane($date);
            $douane_produit = $droit_produit->getTaux();
        } catch (Exception $ex) {
            $douane_produit = -1;
        }

        return $douane_produit;
    }

    public function hasProduitAttribute($date, $attribute) {

        if ($attribute == self::ATTRIBUTE_CVO_ACTIF) {

            return $this->isCVOActif($date);
        }

        if ($attribute == self::ATTRIBUTE_DOUANE_ACTIF) {

            return $this->isDouaneActif($date);
        }

        if ($attribute == self::ATTRIBUTE_CVO_FACTURABLE) {

            return $this->isCVOFacturable($date);
        }

        if ($attribute == self::ATTRIBUTE_DOUANE_FACTURABLE) {

            return $this->isDouaneFacturable($date);
        }

        return false;
    }

    public function hasProduitAttributes($date, $attributes) {
        if (!count($attributes)) {

            return true;
        }

        foreach ($attributes as $attribute) {
            if ($this->hasProduitAttribute($date, $attribute)) {

                return true;
            }
        }

        return false;
    }

    public function getDroits($interpro) {
        if (!is_null($this->noeud_droits)) {

            return $this->noeud_droits;
        }

        $droitsable = $this;
        while (!$droitsable->hasDroits()) {
            $droitsable = $droitsable->getParent()->getParent();
        }

        $this->noeud_droits = $droitsable->interpro->getOrAdd($interpro)->droits;

        return $this->noeud_droits;
    }

    public function compressDroits() {
        foreach ($this->getChildrenNode() as $child) {
            $child->compressDroits();
        }

        $this->compressDroitsSelf();
    }

    protected function compressDroitsSelf() {
        foreach ($this->interpro as $interpro => $object) {
            $droits = $this->getDroits($interpro);
            foreach ($droits as $droit) {
                $droit->compressDroits();
            }
        }
    }

    public function getDateCirulation($campagne, $interpro = "INTERPRO-declaration") {
        $dateCirculationAble = $this;
        while (!$dateCirculationAble->exist('interpro') ||
        !$dateCirculationAble->interpro->getOrAdd($interpro)->exist('dates_circulation') ||
        !count($dateCirculationAble->interpro->getOrAdd($interpro)->dates_circulation) ||
        !$dateCirculationAble->interpro->getOrAdd($interpro)->dates_circulation->exist($campagne)) {
            if($dateCirculationAble instanceOf ConfigurationDeclaration){
                               return null;
            }
            $dateCirculationAble = $dateCirculationAble->getParent()->getParent();
        }
        if (!$dateCirculationAble->exist('interpro') ||
                !$dateCirculationAble->interpro->getOrAdd($interpro)->exist('dates_circulation') ||
                !count($dateCirculationAble->interpro->getOrAdd($interpro)->dates_circulation) ||
                !$dateCirculationAble->interpro->getOrAdd($interpro)->dates_circulation->exist($campagne)) {
            return null;
        }
        return $dateCirculationAble->interpro->getOrAdd($interpro)->dates_circulation->get($campagne);
    }

    public function setLabelCsv($datas) {
        $labels = $this->interpro->getOrAdd('INTERPRO-' . strtolower($datas[LabelCsvFile::CSV_LABEL_INTERPRO]))->labels;
        $canInsert = true;
        foreach ($labels as $label) {
            if ($label == $datas[LabelCsvFile::CSV_LABEL_CODE]) {
                $canInsert = false;
                break;
            }
        }
        if ($canInsert) {
            $labels->add(null, $datas[LabelCsvFile::CSV_LABEL_CODE]);
        }
    }

    protected function setDepartementCsv($datas) {
        if (!array_key_exists(ProduitCsvFile::CSV_PRODUIT_DEPARTEMENTS, $datas) || !$datas[ProduitCsvFile::CSV_PRODUIT_DEPARTEMENTS]) {

            $this->departements = array();

            return;
        }

        $this->departements = explode(',', $datas[ProduitCsvFile::CSV_PRODUIT_DEPARTEMENTS]);
    }

    protected function setDroitDouaneCsv($datas, $code_applicatif) {

        if (!array_key_exists(ProduitCsvFile::CSV_PRODUIT_DOUANE_NOEUD, $datas) || $code_applicatif != $datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_NOEUD]) {

            return;
        }

        $droits = $this->getDroits('INTERPRO-' . strtolower($datas[ProduitCsvFile::CSV_PRODUIT_INTERPRO]));
        $date = ($datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_DATE]) ? $datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_DATE] : '1900-01-01';
        $taux = ($datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_TAXE]) ? str_replace(',', '.', $datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_TAXE]) : 0;
        $code = ($datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_CODE]) ? $datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_CODE] : null;
        $libelle = ($datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_LIBELLE]) ? $datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_LIBELLE] : null;

        $currentDroit = null;
        foreach ($droits->douane as $droit) {
            if ($code != $droit->code) {
                continue;
            }

            if ($currentDroit && $droit->date < $currentDroit->date) {
                continue;
            }

            $currentDroit = $droit;
        }

        if ($currentDroit && $currentDroit->taux == $taux) {
            return;
        }

        $droits = $droits->douane->add();
        $droits->date = $date;
        $droits->taux = $taux;
        $droits->code = $code;
        $droits->libelle = $libelle;
    }

    public function setDroitCvoCsv($datas, $code_applicatif) {

        if (!isset($datas[ProduitCsvFile::CSV_PRODUIT_CVO_NOEUD]) || $code_applicatif != $datas[ProduitCsvFile::CSV_PRODUIT_CVO_NOEUD]) {

            return;
        }

        $droits = $this->getDroits('INTERPRO-' . strtolower($datas[ProduitCsvFile::CSV_PRODUIT_INTERPRO]));
        $date = ($datas[ProduitCsvFile::CSV_PRODUIT_CVO_DATE]) ? $datas[ProduitCsvFile::CSV_PRODUIT_CVO_DATE] : '1900-01-01';
        $taux = ($datas[ProduitCsvFile::CSV_PRODUIT_CVO_TAXE]) ? "" . str_replace(',', '.', $datas[ProduitCsvFile::CSV_PRODUIT_CVO_TAXE]) : "0.0";
        $code = ConfigurationDroits::CODE_CVO;
        $libelle = ConfigurationDroits::LIBELLE_CVO;
        $currentDroit = null;
        foreach ($droits->cvo as $droit) {
            if ($currentDroit && $droit->date < $currentDroit->date) {
                continue;
            }

            $currentDroit = $droit;
        }

        if ($currentDroit && $currentDroit->taux == $taux) {
            return;
        }

        $droits = $droits->cvo->add();
        $droits->date = $date;
        $droits->taux = $taux;
        $droits->code = $code;
        $droits->libelle = $libelle;
    }

    public function formatProduits($date = null, $interpro = null, $departement = null, $format = "%format_libelle% (%code_produit%)", $attributes = array()) {
        if (!$date) {
            $date = date('Y-d-m');
        }

        $produits = $this->getProduits($date, $interpro, $departement, $attributes);
        $produits_formated = array();
        foreach ($produits as $hash => $produit) {
            $produits_formated[$hash] = $produit->getLibelleFormat( null, $format, ',');
        }
        return $produits_formated;
    }

    public function getLabels($interpro) {

        throw new sfException("The method \"getLabels\" is not defined");
    }

    public function setDonneesCsv($datas) {
        if ($datas[ProduitCsvFile::CSV_PRODUIT_CODE_PRODUIT_NOEUD] == $this->getTypeNoeud()) {
            $this->code_produit = ($datas[ProduitCsvFile::CSV_PRODUIT_CODE_PRODUIT]) ? $datas[ProduitCsvFile::CSV_PRODUIT_CODE_PRODUIT] : null;
        }

        if ($datas[ProduitCsvFile::CSV_PRODUIT_CODE_COMPTABLE_NOEUD] == $this->getTypeNoeud()) {
            $this->code_comptable = ($datas[ProduitCsvFile::CSV_PRODUIT_CODE_COMPTABLE]) ? $datas[ProduitCsvFile::CSV_PRODUIT_CODE_COMPTABLE] : null;
        }

        if ($datas[ProduitCsvFile::CSV_PRODUIT_CODE_DOUANE_NOEUD] == $this->getTypeNoeud()) {
            $this->code_douane = ($datas[ProduitCsvFile::CSV_PRODUIT_CODE_DOUANE]) ? $datas[ProduitCsvFile::CSV_PRODUIT_CODE_DOUANE] : null;
        }

        if (isset($datas[ProduitCsvFile::CSV_PRODUIT_FORMAT_LIBELLE_NOEUD]) && $datas[ProduitCsvFile::CSV_PRODUIT_FORMAT_LIBELLE_NOEUD] == $this->getTypeNoeud()) {
            $this->format_libelle = ($datas[ProduitCsvFile::CSV_PRODUIT_FORMAT_LIBELLE]) ? $datas[ProduitCsvFile::CSV_PRODUIT_FORMAT_LIBELLE] : null;
        }
    }

    public function formatCodeFromCsv($code) {
        $code = preg_replace("|/.+$|", "", $code);

        if (!$code) {

            return null;
        }

        return $code;
    }

    public abstract function getTypeNoeud();

    public function getDetailConfiguration($key = 'details') {

        return $this->getDocument()->declaration->get($key);;
    }

    public function getKeys($noeud) {
        if ($noeud == $this->getTypeNoeud()) {

            return array($this->getKey() => $this);
        }

        $items = array();
        foreach ($this->getChildrenNode() as $key => $item) {
            $items = array_merge($items, $item->getKeys($noeud));
        }

        return $items;
    }

    public function addInterpro($interpro) {
        if ($this->exist('interpro')) {
            $this->interpro->getOrAdd($interpro);
        }
        return $this->getParentNode()->addInterpro($interpro);
    }

    public function hasDepartements() {
        return false;
    }

    public function hasDroits() {
        return true;
    }

    public function hasLabels() {
        return false;
    }

    public function hasDetails() {
        return false;
    }

    public function hasDroit($type) {
        if (!$this->hasDroits()) {

            return false;
        }

        if ($type == ConfigurationDroits::DROIT_CVO) {
            return true;
        }

        return false;
    }

    public function hasCodes() {
        return false;
    }

    public function hasCepagesAutorises(){
      return false;
    }

}
