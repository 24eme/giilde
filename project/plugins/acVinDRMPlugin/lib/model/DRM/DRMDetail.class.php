<?php

/**
 * Model for DRMDetail
 *
 */
class DRMDetail extends BaseDRMDetail {

    public function getConfig() {
        return $this->getParent()->getConfigDetails();
    }

    public function getLibelle($format = "%format_libelle%", $label_separator = ", ") {
        $s = str_replace('&', ' et ', $this->getCepage()->getConfig()->getLibelleFormat($this->get('denomination_complementaire'), $format, $label_separator));
        if ($this->produit_libelle && $this->isDefaultProduit()) {
            $s = $this->produit_libelle;
            if ($this->denomination_complementaire != $this->produit_libelle) {
                $s .= " ".$this->denomination_complementaire;
            }
            $s .= " (Hors Interpro)";
        }
        if ($this->tav) {
            $s .= " - ".$this->tav.'°';
        }
        return $s;
    }

    public function isAlcoolPur() {
        return ($this->tav) && $this->entrees->exist('transfertsrecolte') && ($this->entrees->transfertsrecolte);
    }

    public function isMatierePremiere(){
        return $this->isCodeDouaneMatierePremiere();
    }

    public function isAlcoolPurOrMatierePremiere(){
      return $this->isAlcoolPur() || $this->isMatierePremiere();
    }

    public function isDefaultProduit() {

        return DRMConfiguration::getInstance()->hasEdiDefaultProduitHash($this->code_inao) && ($this->getCepage()->getHash() == DRMConfiguration::getInstance()->getEdiDefaultProduitHash($this->code_inao));
    }

    public function getCode($format = "%g%%a%%m%%l%%co%%ce%") {

        return $this->getCepage()->getConfig()->getCodeFormat($format);
    }

    public function getCodeProduit() {

        return $this->getCepage()->getConfig()->getCodeProduit();
    }

    public function hasLibelleModified() {
    	return ($this->produit_libelle && $this->produit_libelle !== $this->getLibelle())? true : false;
    }

    /**
     *
     * @return DRMCepage
     */
    public function getCepage() {
        return $this->getParent()->getParent();
    }

    /**
     *
     * @return DRMCouleur
     */
    public function getCouleur() {

        return $this->getCepage()->getCouleur();
    }

    /**
     *
     * @return DRMLieu
     */
    public function getLieu() {

        return $this->getCouleur()->getLieu();
    }

    /**
     *
     * @return DRMMention
     */
    public function getMention() {

        return $this->getLieu()->getMention();
    }

    /**
     *
     * @return DRMAppellation
     */
    public function getAppellation() {

        return $this->getLieu()->getAppellation();
    }

    public function hasProduitDetailsWithStockNegatif() {

        return $this->getCepage()->hasProduitDetailsWithStockNegatif();
    }

    public function getGenre() {
        return $this->getAppellation()->getGenre();
    }

    public function getCertification() {
        return $this->getGenre()->getCertification();
    }

    public function getLabelKeyString() {
        if ($this->labels) {
            return implode('|', $this->labels->toArray());
        }

        return '';
    }


    public function getTypeDRM() {

        return $this->getParent()->getTypeDRM();
    }

    public function getTypeDRMLibelle() {

        return $this->getParent()->getTypeDRMLibelle();
    }

    public function canSetStockDebutMois() {
       return $this->getDocument()->canSetStockDebutMois();
    }

    public function canSetLabels() {

        return !$this->hasPrecedente();
    }

    public function hasPrecedente() {
        if (!$this->getDocument()->hasPrecedente()) {

            return false;
        }


        return $this->getDocument()->getPrecedente()->exist($this->getHash());
    }

    protected function update($params = array()) {
        parent::update($params);
        $this->total_debut_mois = $this->stocks_debut->initial;
        $keysDetailsToRemove = array();
        foreach($this->entrees as $key => $item) {
            if(!$this->entrees->exist($key."_details")) {
                continue;
            }
            $this->entrees->set($key, 0);
            foreach ($this->entrees->get($key."_details") as $detail) {
                $this->entrees->set($key, $this->entrees->get($key) + $detail->volume);
            }
        }
        foreach($this->sorties as $key => $item) {
            if($item instanceof acCouchdbJson) {
                continue;
            }
            if(!$this->sorties->getConfig()->exist($key)){
                continue;
            }
            if(!$this->sorties->getConfig()->get($key)->hasDetails()) {
                continue;
            }
            if($this->sorties->getConfig()->get($key)->details == ConfigurationDetailLigne::DETAILS_ALCOOLPUR && !$this->isCodeDouaneAlcool() && !$this->isCodeDouaneMatierePremiere()) {
                $keysDetailsToRemove[] = $key."_details";
                continue;
            }
            $this->sorties->add($key."_details");
        }
        foreach($keysDetailsToRemove as $keyDetails) {
            $this->sorties->remove($keyDetails);
        }
        foreach($this->sorties as $key => $item) {
            if(!$this->sorties->exist($key."_details")) {
                continue;
            }
            $this->sorties->set($key, 0);
            foreach ($this->sorties->get($key."_details") as $detail) {
                $this->sorties->set($key, $this->sorties->get($key) + $detail->volume);
            }
        }

        $this->total_entrees = $this->getTotalByKey('entrees', 'recolte');
        $this->total_sorties = $this->getTotalByKey('sorties', 'recolte');

        $this->stocks_fin->final = round($this->stocks_debut->initial + $this->total_entrees - $this->total_sorties, FloatHelper::getInstance()->getMaxDecimalAuthorized());

        $this->total_entrees_revendique = $this->getTotalByKey('entrees', 'revendique');
        $this->total_sorties_revendique = $this->getTotalByKey('sorties', 'revendique');
        if($this->getConfig()->getDocument()->hasDontRevendique() && $this->stocks_fin->exist('dont_revendique')){
          $this->stocks_fin->dont_revendique = round($this->stocks_debut->dont_revendique + $this->total_entrees_revendique - $this->total_sorties_revendique, FloatHelper::getInstance()->getMaxDecimalAuthorized());
        }
        if ($this->entrees->exist('recolte')) {
            $this->total_recolte = $this->entrees->recolte;
        }

        $this->total_facturable = 0;
        $this->updateNoeud('entrees', -1);
        $this->updateNoeud('sorties', 1);

        $this->cvo->volume_taxable = $this->total_facturable;

        $this->total = $this->stocks_fin->final;
        if($this->getConfig()->getDocument()->hasDontRevendique() && $this->stocks_fin->exist('dont_revendique')){
          $this->total_revendique = $this->stocks_fin->dont_revendique;
        }

        $hasobs = false;
        foreach($this->entrees as $entree => $v) {
            if (!$v || !$this->getConfig()->exist('entrees/'.$entree) || !$this->getConfig()->get('entrees/'.$entree)->needDouaneObservation($this->getDocument()->isNegoce())) {
                continue;
            }
            $hasobs = true;
            if (!$this->exist('observations') || ! $this->observations) {
              $this->add('observations');
              $this->observations = (DRMConfiguration::getInstance()->isObservationsAuto()) ? $this->getConfig()->getDocument()->libelle_detail_ligne->get($this->getConfig()->getKey())->get('entrees')->get($entree)->libelle_long : "";
            }
        }
        foreach($this->sorties as $sortie => $v) {
            if($v instanceof acCouchdbJson || !$v || !$this->getConfig()->exist('sorties/'.$sortie)) {
                continue;
            }
            if (!$this->getConfig()->get('sorties/'.$sortie)->needDouaneObservation($this->getDocument()->isNegoce())) {
                continue;
            }
            $hasobs = true;
            if (!$this->exist('observations') || ! $this->observations) {
                $this->add('observations');
                $this->observations = (DRMConfiguration::getInstance()->isObservationsAuto()) ? $this->getConfig()->getDocument()->libelle_detail_ligne->get($this->getConfig()->getKey())->get('sorties')->get($sortie)->libelle_long : "";
            }
        }
        if (!$hasobs) {
          $this->remove('observations');
        }

        $needDateReplacement = false;
        foreach($this->entrees as $entree => $v) {
            if($v && $this->getConfig()->get('entrees')->exist($entree) && $this->getConfig()->get('entrees')->get($entree)->needDouaneDateReplacement() && !$this->entrees->exist($entree.'_details')) {
                $needDateReplacement = true;
            }
        }
        if($needDateReplacement) {
          $this->add('replacement_date');
        }else{
          $this->remove('replacement_date');
        }
    }

    public function setImportableObservations($observations) {
      $this->add('observations', $observations);
    }

    protected function updateNoeud($hash, $coefficient_facturable) {
        foreach ($this->get($hash) as $key => $volume) {
            if (!$this->getConfig()->exist($hash . "/" . $key)) {
                continue;
            }
            $config = $this->getConfig()->get($hash . "/" . $key);

            if ($config->facturable && $this->getDocument()->isFacturable()) {
                $this->total_facturable += $volume * $coefficient_facturable;
            }
        }
        $this->total_facturable = round($this->total_facturable, FloatHelper::getInstance()->getMaxDecimalAuthorized());
    }

    private function getTotalByKey($key, $onlyCaracteristique = false) {
        $sum = 0;
        foreach ($this->get($key, true) as $n => $k) {
            if (!is_object($k)) {
                if ($onlyCaracteristique) {
                    if ($k && $this->getConfig()->$key->$n->$onlyCaracteristique) {
                        $sum += $k;
                    }
                } else {
                    $sum += $k;
                }
            }
        }
        return round($sum, FloatHelper::getInstance()->getMaxDecimalAuthorized());
    }

    public function getTotalDebutMois() {
        if (is_null($this->_get('total_debut_mois'))) {
            return 0;
        } else {
            return $this->_get('total_debut_mois');
        }
    }

    public function nbToComplete() {
        return $this->hasMouvementCheck();
    }

    public function nbComplete() {
        return $this->isComplete();
    }

    public function isComplete() {
        return $this->total_entrees > 0 || $this->total_sorties > 0;
    }

    public function getIdentifiantHTML() {
        return strtolower(str_replace($this->getDocument()->declaration->getHash(), '', str_replace('/', '_', preg_replace('|\/[^\/]+\/DEFAUT|', '', $this->getHash()))));
    }

    public function hasContratVrac() {
        $rows = $this->getContratsVrac();
        return count($rows);
    }

    public function getContratsVrac() {
        $contrats = $this->getContratsVracByHash($this->getCepage()->getHash());

        $correspondance_hash = $this->getCepage()->getConfig()->getCorrespondanceHash();
        if ($correspondance_hash) {
            $contrats = array_merge($contrats, $this->getContratsVracByHash($correspondance_hash));
        }

        return $contrats;
    }

    public function getContratsVracByHash($hash) {
        return DRMClient::getInstance()->getContratsFromProduit($this->getDocument()->identifiant, $hash, array(VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE, VracClient::TYPE_TRANSACTION_VIN_VRAC),$this->getDocument()->getDate());
    }

    public function isModifiedMother($key) {

        return $this->getDocument()->isModifiedMother($this->getHash(), $key);
    }

    public function getDroitVolume($type) {

        return $this->sommeLignes(DRMDroits::getDroitSorties()) - $this->sommeLignes(DRMDroits::getDroitEntrees());
    }

    protected function init($params = array()) {
        parent::init($params);

        $keepStock = isset($params['keepStock']) ? $params['keepStock'] : true;

        $this->stocks_debut->initial = ($keepStock) ? $this->total : null;
        $this->stocks_debut->revendique = $this->total_revendique;
        $this->total_entrees = null;
        $this->total_sorties = null;
        $this->total_revendique = null;
        $this->total = null;
    }

    public function sommeLignes($lines) {
        $sum = 0;
        foreach ($lines as $line) {
            $sum += $this->get($line);
        }
        return round($sum, FloatHelper::getInstance()->getMaxDecimalAuthorized());
    }

    public function hasStockFinDeMoisDRMPrecedente() {
        $result = false;
        $drmPrecedente = $this->getDocument()->getPrecedente();
        if (!$drmPrecedente->isNew()) {
            if ($drmPrecedente->exist($this->getHash())) {
                if ($drmPrecedente->get($this->getHash())->total) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    public function hasMouvement() {

        return $this->total_entrees > 0.0000001 || $this->total_entrees_revendique > 0.0000001 || $this->total_sorties > 0.0000001 || $this->total_sorties_revendique > 0.0000001;
    }

    public function hasStockEpuise() {

        return $this->total_debut_mois <= 0.0000001 && !$this->hasMouvement() && $this->total <= 0.0000001 && $this->total_revendique <= 0.0000001;
    }

    public function isSupprimable() {

        return $this->hasStockEpuise() && !$this->getDocument()->hasVersion();
    }

    public function hasMouvementCheck() {

        return !$this->pas_de_mouvement_check;
    }

    public function getMouvements() {

        return array_replace_recursive($this->getMouvementsByNoeud('entrees'), $this->getMouvementsByNoeud('sorties'));
    }

    public function getMouvementsByNoeud($hash) {
        $mouvements = array();
        foreach ($this->get($hash) as $key => $volume) {
            if ($volume instanceof acCouchdbJson) {

                continue;
            }
            if (!$this->getConfig()->exist($hash . "/" . $key)) {
                continue;
            }

            $config = $this->getConfig()->get($hash . '/' . $key);

            $mouvement = DRMMouvement::freeInstance($this->getDocument());
            $mouvement->produit_libelle = $this->getLibelle();
            $mouvement->produit_hash = $this->getHash(); //WARNING : ceci change tout je pense
            $mouvement->type_drm = $this->getTypeDRM();
            $mouvement->type_drm_libelle = $this->getTypeDRMLibelle();
            $mouvement->facture = 0;
            $mouvement->region = $this->getDocument()->region;
            $mouvement->cvo = ($mouvement->region == EtablissementClient::REGION_HORS_CVO && VracConfiguration::getInstance()->getRegionDepartement() == ".*")? 0.0 : $this->getCVOTaux();
            $mouvement->facturable = ($config->facturable && $mouvement->cvo > 0) ? 1 : 0;

            if(!$this->getDocument()->isFacturable()){
                $mouvement->facturable = 0;
            }

            $mouvement->version = $this->getDocument()->getVersion();
            $mouvement->date_version = ($this->getDocument()->valide->date_saisie) ? ($this->getDocument()->valide->date_saisie) : date('Y-m-d');
            $mouvement->categorie = FactureClient::FACTURE_LIGNE_MOUVEMENT_TYPE_PROPRIETE;

            if(!$this->getDocument()->isFacturable() && $config->isFacturableInverseNegociant() && $mouvement->cvo > 0) {
                $mouvement->facturable = 1;
                $mouvement->add('coefficient_facturation', 1);
                $mouvement->categorie = FactureClient::FACTURE_LIGNE_MOUVEMENT_TYPE_NEGOCIANT_RECOLTE;
            }

            if ($this->exist($hash . "/" . $key . "_details") && $this->get($hash . "/" . $key . "_details")) {
                $mouvements = array_replace_recursive($mouvements, $this->get($hash . "/" . $key . "_details")->createMouvements($mouvement));
                continue;
            }

            if(!$this->getDocument()->isFacturable() && $mouvement->facturable && DRMConfiguration::getInstance()->isMouvementDivisable() && $volume * $config->mouvement_coefficient > DRMConfiguration::getInstance()->getMouvementDivisableSeuil() && $config->isDivisable()) {
                $nbDivision = DRMConfiguration::getInstance()->getMouvementDivisableNbMonth();
                $date = new DateTime($this->getDocument()->getDate());
                $volumePart = round($volume / $nbDivision, FloatHelper::getInstance()->getMaxDecimalAuthorized());
                $volumeTotal = $volume;
                for($i=1; $i <= $nbDivision; $i++) {
                    $mouvementPart = $this->createMouvement(clone $mouvement, $hash . '/' . $key, $volumePart, $date->format('Y-m-d'));
                    $mouvementPart->categorie .= '_divise';
                    $date->modify("last day of next month");
                    if (!$mouvementPart) {
                        continue;
                    }
                    $volumeTotal = $volumeTotal - $volumePart;
                    if($i == $nbDivision && $volumeTotal) {
                        $mouvementPart->volume += $volumeTotal;
                    }
                    $mouvementPart->detail_libelle = sprintf("%0".strlen($nbDivision)."d", $i)."/".$nbDivision;

                    $mouvements[$this->getDocument()->getIdentifiant()][$mouvementPart->getMD5Key()] = $mouvementPart;
                }
                continue;
            }

            $mouvement = $this->createMouvement(clone $mouvement, $hash . '/' . $key, $volume);
            if (!$mouvement) {
                continue;
            }

            $mouvements[$this->getDocument()->getIdentifiant()][$mouvement->getMD5Key()] = $mouvement;
        }

        return $mouvements;
    }

    public function createMouvement($mouvement, $hash, $volume, $date = null) {
        if ($this->getDocument()->hasVersion() && !$this->getDocument()->isModifiedMother($this, $hash)) {
            return null;
        }

        if ($this->getDocument()->hasVersion() && $this->getDocument()->motherExist($this->getHash() . '/' . $hash)) {
            $volume = $volume - $this->getDocument()->motherGet($this->getHash() . '/' . $hash);
        }

        $config = $this->getConfig()->get($hash);
        $volume = $config->mouvement_coefficient * $volume;

        if ($volume == 0) {
            return null;
        }
        if(!$date) {
            $date = $this->getDocument()->getDate();
        }
        $mouvement->type_hash = $hash;
        $mouvement->type_libelle = $config->getLibelle();
        $mouvement->volume = $volume;

        $mouvement->date = $date;

        return $mouvement;
    }

    public function isContratExterne() {

        return $this->getCVOTaux() <= 0;
    }

    public function getCVOTaux() {
        $this->cvo->calcul();

        return $this->cvo->taux;
    }

    public function storeDroits() {
        $this->cvo->taux = null;
        $this->cvo->calcul();
    }

    public function isEdited() {

        return $this->exist('edited') && $this->edited;
    }

    public function hasMovements() {
        if ($this->hasMouvement()) {

            return true;
        }

        return !$this->exist('no_movements') || !$this->no_movements;
    }

    public function updateDroitsDouanes() {
        $droitsNode = $this->getDocument()->getOrAdd('droits')->getOrAdd('douane');
        $cepageConfig = $this->getCepage()->getConfig();
        $genreKey = $this->getGenre()->getKey();

        foreach ($this->getEntrees() as $entreeKey => $entree) {
            if (!$this->getConfig()->exist('entrees/' . $entreeKey)) {
                continue;
            }
            $entreeConf = $this->getConfig()->get('entrees/' . $entreeKey);
            $entreeDrm = $this->get('entrees/' . $entreeKey);

            if ($entreeConf->taxable_douane && $entreeDrm && $entreeDrm > 0) {
                $droitsNode->updateDroitDouane($genreKey, $cepageConfig, $entreeDrm, true);
            }
        }
        foreach ($this->getSorties() as $sortieKey => $sortie) {
            if (!$this->getConfig()->exist('sorties/' . $sortieKey)) {
                continue;
            }
            $sortieConf = $this->getConfig()->get('sorties/' . $sortieKey);
            $sortieDrm = $this->get('sorties/' . $sortieKey);

            if ($sortieConf->taxable_douane && $sortieDrm && $sortieDrm > 0) {
                $droitsNode->updateDroitDouane($genreKey, $cepageConfig, $sortieDrm, false);
            }
        }
    }

    public function hasTypeDoc($nodeName) {
        $nodeNameComplete = $nodeName . '_details';
        foreach ($this->sorties->$nodeNameComplete as $detailRow) {

            if ($detailRow->type_document) {
                return true;
            }
        }
        return false;
    }

    public function getCodeDouane() {
        if($this->exist("code_inao") && $this->code_inao) {
            return $this->code_inao;
        }
        if ($this->getDocument()->isNegoce() && $this->getCorrespondanceNegoce()) {
            $this->_set('code_inao', $this->getLibelleFiscalNegocePur());

            return $this->_get('code_inao');
        }

        return $this->getCepage()->getConfig()->code_douane;
    }

    public function isPremix() {
        if (preg_match('/premix/i', $this->produit_libelle) || preg_match('/premix/i', $this->denomination_complementaire) || preg_match('/premix/i', $this->code_douane)) {
            return true;
        }
        return false;
    }

    public function isCodeDouaneNonINAO(){
      $inao = $this->_get('code_inao');
      if (!$inao) {
          $inao = $this->getCepage()->getConfig()->code_douane;
      }
      if(!$inao){
        return false;
      }
      if(preg_match('/^[0-9]/', $inao)){
        return false;
      }
      return true;
    }

    public function isCodeDouaneAlcool(){
        return ConfigurationCepage::isCodeDouaneNeedTav($this->getCodeDouane());
    }

    public function isCodeDouaneMatierePremiere(){
        return ConfigurationCepage::isCodeDouaneMatierePremiere($this->getCodeDouane());
    }

    public function isCodeDouanePI(){
        return ConfigurationCepage::isCodeDouanePI($this->getCodeDouane());
    }


    public function getReplacementDate() {
      $d = $this->_get('replacement_date');
      return preg_replace('/(\d{4})-(\d{2})-(\d{2})/', '\3/\2/\1', $d);
    }

    public function setReplacementDate($d) {
      $d = preg_replace('/(\d{2}).(\d{2}).(\d{4})/', '$3-$2-$1', $d);
      return $this->_set('replacement_date', $d);
    }

    public function getReplacementMonth() {
      $d = $this->_get('replacement_date');
      return sprintf('%02d', preg_replace('/.*(-|\/)(\d{2})(-|\/).*/', '\2', $d));
    }
    public function getReplacementYear() {
      $d = $this->_get('replacement_date');
      return preg_replace('/.*(\d{4}).*/', '\1', $d);
    }

    public function setDenominationComplementaire($denomination_complementaire){
      $denomChanged = ($this->get('denomination_complementaire') && ($this->get('denomination_complementaire') != $denomination_complementaire));

      if(!$denomChanged){
        $this->_set('denomination_complementaire',$denomination_complementaire);
      }else{
        $oldKey = $this->getKey();
        $parent = $this->getParent();
        $detailNode = clone $this;
        $detailNode->_set('denomination_complementaire',$denomination_complementaire);

        $newKey = $parent->createSHA1Denom($denomination_complementaire);
        $parent->add($newKey,$detailNode);
        $parent->get($oldKey)->delete();
      }
    }
    public function getTav(){
      if(!$this->exist('tav')){
       return false;
      }
      return $this->_get('tav');
    }

    public function getCorrespondanceNegoce()
    {
        if ($this->isCodeDouaneNonINAO()) {
            return $this->getCepage()->getHash();
        }
        $correspondances = sfConfig::get('app_drm_negoce_correspondances_produits');
        if (!is_array($correspondances)) {
            throw new sfException('no correspondances_produits set in configuration app.yml');
        }
        $c = (isset($correspondances[$this->getGenre()->getHash()])) ? $correspondances[$this->getGenre()->getHash()] : null;
        if (!$c) {
            //throw new sfException('correspondances_produits not found in configuration app.yml for key '.$this->getGenre()->getHash());
            return null;
        }
        if (is_array($c)) {
            $keys = array_keys($c);
            $diff1 = array_diff(array('cep', 'sanscep'), $keys);
            $diff2 = array_diff(array('vdlsup', 'vdlinf'), $keys);
            if (!$diff1) {
                return ($this->getCepage()->getKey() == Configuration::DEFAULT_KEY)? $c['sanscep'] : $c['cep'];
            }
            if (!$diff2) {
                return ($this->getTav() > 18)? $c['vdlsup'] : $c['vdlinf'];
            }
            throw new sfException('errors in configuration app.yml for keys '.implode(', ', $keys));
        } else {
            return $c;
        }
    }

    public function getLibelleFiscalNegocePur() {
        $hash = $this->getCorrespondanceNegoce();
        $hash = preg_replace('/.details.DEFAUT$/', '', $hash);
        $p = ConfigurationClient::getCurrent()->get($hash);
        return $p->getCodeDouane();
    }

}
