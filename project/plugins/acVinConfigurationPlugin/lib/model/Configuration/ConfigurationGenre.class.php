<?php
/**
 * Model for ConfigurationGenre
 *
 */

class ConfigurationGenre extends BaseConfigurationGenre {
    const TYPE_NOEUD = 'genre';

    public function getChildrenNode() {

        return $this->appellations;
    }

    public function getCertification() {

 	  return $this->getParentNode();
    }

    public function setDonneesCsv($datas) {
        parent::setDonneesCsv($datas);
      	$this->getCertification()->setDonneesCsv($datas);
      	$this->libelle = ($datas[ProduitCsvFile::CSV_PRODUIT_GENRE_LIBELLE])? $datas[ProduitCsvFile::CSV_PRODUIT_GENRE_LIBELLE] : null;
        $this->code = $this->formatCodeFromCsv($datas[ProduitCsvFile::CSV_PRODUIT_GENRE_CODE]);

      	$this->setDroitDouaneCsv($datas, ProduitCsvFile::CSV_PRODUIT_GENRE_CODE_APPLICATIF_DROIT);
      	$this->setDroitCvoCsv($datas, ProduitCsvFile::CSV_PRODUIT_GENRE_CODE_APPLICATIF_DROIT);
    }

	public function getTypeNoeud() {
		return self::TYPE_NOEUD;
	}

}
