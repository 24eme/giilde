<?php

/**
 * Model for ConfigurationCepage
 *
 */
class ConfigurationCepage extends BaseConfigurationCepage {

    const TYPE_NOEUD = 'cepage';

    public function getChildrenNode() {

        return null;
    }

    public function getAppellation() {

        return $this->getCouleur()->getLieu()->getAppellation();
    }

    public function getCertification() {

        return $this->getAppellation()->getCertification();
    }

    public function getGenre() {

        return $this->getAppellation()->getGenre();
    }

    public function getLieu() {

        return $this->getCouleur()->getLieu();
    }

    public function getMention() {

        return $this->getLieu()->getMention();
    }

    public function getCepage() {

        return $this;
    }

    public function getProduitsAll($interpro = null, $departement = null) {

        return array($this->getHash() => $this);
    }

    public function compressDroits() {
        $this->compressDroitsSelf();
    }

    public function getCouleur() {
        return $this->getParentNode();
    }

    public static function isCodeDouaneNeedTav($code_douane) {
        if(!$code_douane){

            return false;
        }

        return preg_match('/^(RHUM_|ALCOOL_|SPIRITUEUX_|AUTRES_ALCOOLS)/', $code_douane);
    }

    public function needTav() {

        return self::isCodeDouaneNeedTav($this->code_douane);
    }

    public function setDonneesCsv($datas) {
        parent::setDonneesCsv($datas);

        $this->getCouleur()->setDonneesCsv($datas);
        $this->libelle = ($datas[ProduitCsvFile::CSV_PRODUIT_CEPAGE_LIBELLE]) ? $datas[ProduitCsvFile::CSV_PRODUIT_CEPAGE_LIBELLE] : null;
        $this->code = $this->formatCodeFromCsv($datas[ProduitCsvFile::CSV_PRODUIT_CEPAGE_CODE]);

        $this->cepages_autorises = ($datas[ProduitCsvFile::CSV_PRODUIT_CEPAGES_AUTORISES]) ? explode('|', $datas[ProduitCsvFile::CSV_PRODUIT_CEPAGES_AUTORISES]) : array();

        $this->setDroitDouaneCsv($datas, ProduitCsvFile::CSV_PRODUIT_CEPAGE_CODE_APPLICATIF_DROIT);
        $this->setDroitCvoCsv($datas, ProduitCsvFile::CSV_PRODUIT_CEPAGE_CODE_APPLICATIF_DROIT);
    }

    public function isCepageAutorise($cepage) {
    	return in_array($cepage, $this->cepages_autorises->toArray());
    }

    public function getCorrespondanceHash() {

        return $this->getDocument()->getCorrespondanceHash($this->getHash());
    }

    public function getTypeNoeud() {

        return self::TYPE_NOEUD;
    }

    public function addInterpro($interpro)
    {

        return $this->getParentNode()->addInterpro($interpro);
    }

    public function hasDroits() {
        return true;
    }

    public function hasCodes() {

        return true;
    }

    public function hasCepagesAutorises(){
      return $this->exist('cepages_autorises') && count($this->cepages_autorises);
    }

    public function setCepagesAutorises($cepages_autorises_str){
      if($this->hasCepagesAutorises()){
        $cepages_autorises = explode(',',$cepages_autorises_str);
        $c_a_new = array();
        foreach ($cepages_autorises as $cepage_autorise) {
          $c_a = strtoupper(trim($cepage_autorise));
          if($c_a){
            if(!preg_match('/^[A-Z\.\ Ç0-9\-ÉéèÈÔôÜüûÛ\=\(\)\']+$/',$c_a)){
              throw new sfException("Le cépage autorisé $c_a n'a pas un bon format. ");
            }
            $c_a_new[] = $c_a;
          }
        }
        $this->_set('cepages_autorises',$c_a_new);
      }
    }
}
