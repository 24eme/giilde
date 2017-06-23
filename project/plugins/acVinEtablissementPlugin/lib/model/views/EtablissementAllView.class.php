<?php

class EtablissementAllView extends acCouchdbView
{
	const KEY_INTERPRO_ID = 0;
	const KEY_STATUT = 1;
  const KEY_FAMILLE = 2;
  const KEY_SOCIETE_ID = 3;
  const KEY_ETABLISSEMENT_ID = 4;
	const KEY_NOM = 5;
	const KEY_IDENTIFIANT = 6;
	const KEY_CVI = 7;
  const KEY_REGION = 8;

  const VALUE_RAISON_SOCIALE = 0;
  const VALUE_ADRESSE = 1;
  const VALUE_COMMUNE = 2;
  const VALUE_CODE_POSTAL = 3;
  const VALUE_NO_ACCISES = 4;
  const VALUE_CARTE_PRO = 5;
  const VALUE_EMAIL = 6;
  const VALUE_TELEPHONE = 7;
  const VALUE_FAX = 8;
  const VALUE_RECETTE_LOCALE_SOCIETE_ID = 9;
  const VALUE_RECETTE_LOCALE_NOM = 10;

	public static function getInstance() {

        return acCouchdbManager::getView('etablissement', 'all', 'Etablissement');
    }

    public function findByInterpro($interpro) {

        return $this->client->startkey(array($interpro))
                            ->endkey(array($interpro, array()))
			    ->reduce(false)
			    ->getView($this->design, $this->view);
    }

    public function findByInterproAndStatut($interpro, $statut, $filter = null, $limit = null) {
      return $this->findByInterproStatutAndFamilles($interpro, $statut, array(), $filter, $limit);
    }

    public function findByInterproAndFamilles($interpro, array $familles, $filter = null, $limit = null) {
      return $this->findByInterproStatutsAndFamilles($interpro, null, $familles, $filter, $limit);
    }

    public function findByInterproStatutAndFamilles($interpro, $statut, array $familles, $filter = null, $limit = null) {
      return $this->findByInterproStatutsAndFamilles($interpro, array($statut), $familles, $filter, $limit);
    }

    public function findByInterproAndFamille($interpro, $famille, $filter = null, $limit = null) {
      return $this->findByInterproStatutsAndFamilles($interpro, array(), array($famille), $filter, $limit);
    }

    public function findByInterproStatutsAndFamilles($interpro, array $statuts, array $familles, $filter = null, $limit = null) {
      return $this->findByInterproStatutsAndFamillesVIEW($interpro, $statuts, $familles, $filter, $limit) ;
    }

    private function findByInterproStatutsAndFamillesVIEW($interpro, array $statuts, array $familles, $filter = null, $limit = null) {
        $etablissements = array();

	if(!count($statuts)) {
	  $statuts = array_keys(EtablissementClient::$statuts);
	}

	if (count($familles)) {
	  foreach($statuts as $statut) {
	    foreach($familles as $famille) {
	      $etablissements = array_merge($etablissements, $this->findByInterproStatutAndFamille($interpro, $statut, $famille, $filter, $limit));
	    }
	  }
	}else{
	  foreach($statuts as $statut) {
	      $etablissements = array_merge($etablissements, $this->findByInterproStatutAndFamille($interpro, $statut, null, $filter, $limit));
	  }
	}

        return $etablissements;
    }

    public function findByInterproStatutAndFamille($interpro, $statut, $famille, $filter = null, $limit = null) {
      try{
	return $this->findByInterproStatutAndFamilleELASTIC($interpro, $statut, $famille, $filter, $limit);
      }catch(Exception $e) {
	return $this->findByInterproStatutAndFamilleVIEW($interpro, $statut, $famille, $filter, $limit);
      }
    }

    private function findByInterproStatutAndFamilleELASTIC($interpro, $statut, $famille, $query = null, $limit = 100) {
      $q = explode(' ', $query);
      for($i = 0 ; $i < count($q); $i++) {
	$q[$i] = '*'.$q[$i].'*';
      }
      if ($statut) {
	$q[] = 'doc.statut:'.$statut;
      }

			if ($famille) {
				$q[] = 'doc.famille:'.$famille;
      }

      $query = implode(' ', $q);

      $index = acElasticaManager::getType('ETABLISSEMENT');
      $elasticaQueryString = new acElasticaQueryQueryString();
      $elasticaQueryString->setDefaultOperator('AND');
      $elasticaQueryString->setQuery($query);

      // Create the actual search object with some data.
      $q = new acElasticaQuery();
      $q->setQuery($elasticaQueryString);
      $q->setLimit($limit);

      //Search on the index.
      $res = $index->search($q);

      $viewres = $this->elasticRes2View($res);
      return $viewres;
    }

    private function elasticRes2View($resultset) {
      $res = array();
      foreach ($resultset->getResults() as $er) {
	$r = $er->getData();
	$e = new stdClass();
	$e->id = $er->getId();
	$e->key = array($r['doc']['interpro'], $r['doc']['statut'], $r['doc']['famille'], $r['doc']['id_societe'], $er->getId(), $r['doc']['nom'], $r['doc']['identifiant'], $r['doc']['cvi'], $r['doc']['region']);
	$e->value = array($r['doc']['nom'],$r['doc']['siege']['adresse'], $r['doc']['siege']['commune'], $r['doc']['siege']['code_postal'], $r['doc']['no_accises']);
	$res[] = $e;
      }
      return $res;
    }

    public function findByInterproStatutAndFamilleVIEW($interpro, $statut, $famille, $filter = null, $limit = null) {
      $keys = array($interpro, $statut);
      if ($famille) {
	$keys[] = $famille;
      }
      $view = $this->client->reduce(false)->startkey($keys);
      $keys[] = array();
      $view = $view->endkey($keys);
      $rows = $view->getView($this->design, $this->view)->rows;
      return $rows;
    }

    public function findByEtablissement($identifiant) {
        $etablissement = $this->client->find($identifiant, acCouchdbClient::HYDRATE_JSON);

        if(!$etablissement) {
            return null;
        }

        return $this->client->startkey(array($etablissement->interpro, $etablissement->statut, $etablissement->famille, $etablissement->id_societe, $etablissement->_id))
                            ->endkey(array($etablissement->interpro, $etablissement->statut, $etablissement->famille, $etablissement->id_societe,$etablissement->_id, array()))
			    ->reduce(false)
			    ->getView($this->design, $this->view)->rows;

    }

		public static function makeLibelle($row) {
			$libelle = '';

			if ($nom = $row->key[self::KEY_NOM]) {
				$libelle .= $nom;
			}

			$libelle .= ' ('.$row->key[self::KEY_IDENTIFIANT];

			if (isset($row->key[self::KEY_CVI]) && $cvi = $row->key[self::KEY_CVI]) {
				$libelle .= ' / '.$cvi;
			}

			if (isset($row->value[self::VALUE_NO_ACCISES]) && $numAccises = $row->value[self::VALUE_NO_ACCISES]) {
				$libelle .= ' / '.$numAccises . ') ';
			}else {
				$libelle .= " / sans n° d'accise ) ";
			}

			if (isset($row->key[self::KEY_FAMILLE]))
				$libelle .= $row->key[self::KEY_FAMILLE];

			if (isset($row->value[self::VALUE_COMMUNE]))
				$libelle .= ' '.$row->value[self::VALUE_COMMUNE];

			if (isset($row->value[self::VALUE_CODE_POSTAL]))
				$libelle .= ' '.$row->value[self::VALUE_CODE_POSTAL];

			$libelle .= " (Etablissement)";

			return trim($libelle);
		}

}
