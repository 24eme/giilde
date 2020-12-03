<?php

class SocieteClient extends acCouchdbClient {

    const TYPE_OPERATEUR = 'RESSORTISSANT';
    const TYPE_COURTIER = 'INTERMEDIAIRE';
    const TYPE_AUTRE = 'AUTRE';

    const SUB_TYPE_VITICULTEUR = 'VITICULTEUR';
    const SUB_TYPE_NEGOCIANT = 'NEGOCIANT';

    const STATUT_ACTIF = 'ACTIF';
    const STATUT_SUSPENDU = 'SUSPENDU';
    const STATUT_EN_CREATION = 'EN_CREATION';
    const NUMEROCOMPTE_TYPE_CLIENT = 'CLIENT';
    const NUMEROCOMPTE_TYPE_FOURNISSEUR = 'FOURNISSEUR';
    const FOURNISSEUR_TYPE_MDV = "MDV";
    const FOURNISSEUR_TYPE_PLV = "PLV";
    const FOURNISSEUR_TYPE_FOURNISSEUR = "FOURNISSEUR";

    private $societes = null;

    public static function getInstance() {
        return acCouchdbManager::getClient("Societe");
    }

    public function getId($identifiant) {
        if (preg_match('/^SOCIETE/', $identifiant))
            return $identifiant;
        return 'SOCIETE-' . $identifiant;
    }

    public function getIdentifiant($id_or_identifiant) {
        return $identifiant = str_replace('SOCIETE-', '', $id_or_identifiant);
    }

    public function findBySiret($siret) {
        $index = acElasticaManager::getType('SOCIETE');
        $elasticaQueryString = new acElasticaQueryQueryString();
        $elasticaQueryString->setDefaultOperator('AND');
        $elasticaQueryString->setQuery(sprintf("doc.siret:%s", $siret));

        $q = new acElasticaQuery();
        $q->setQuery($elasticaQueryString);
        $q->setLimit(1);

        $res = $index->search($q);

        foreach ($res->getResults() as $er) {
            $r = $er->getData();

            return $this->find($r['_id']);
        }

        return null;
    }

    public function getSocietesWithStatut($statut) {
        return array_reverse(SocieteAllView::getInstance()->findByInterproAndStatut('INTERPRO-declaration', $statut));
    }

    public function getSocietesWithTypeAndRaisonSociale($type, $raison_sociale) {
        return array_merge(SocieteAllView::getInstance()->findByInterproAndStatut('INTERPRO-declaration', CompteClient::STATUT_ACTIF, array($type), $raison_sociale),
                SocieteAllView::getInstance()->findByInterproAndStatut('INTERPRO-declaration', CompteClient::STATUT_SUSPENDU, array($type), $raison_sociale));
    }

    public function createSociete($raison_sociale, $type) {
        $societe = new Societe();
        $societe->raison_sociale = $raison_sociale;
        $societe->type_societe = $type;
        $societe->interpro = 'INTERPRO-declaration';
        $societe->identifiant = $this->getNextIdentifiantSociete();
        $societe->statut = SocieteClient::STATUT_ACTIF;
        $societe->cooperative = 0;
        $societe->setPays('FR');
        $societe->add("date_creation", date('Y-m-d'));
        $societe->constructId();

        return $societe;
    }

    public function find($id_or_identifiant, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        return parent::find($this->getId($id_or_identifiant), $hydrate, $force_return_ls);
    }

    public function clearSingleton() {
        $this->societes = null;
    }

    public function setSingleton($societe) {
        if (!$this->societes) {
          $this->societes = array();
        }

        $this->societes[$this->getId($societe->_id)] = $societe;
    }

    public function findSingleton($id_or_identifiant) {
      if (!$this->societes) {
        $this->societes = array();
      }
      $id = $this->getId($id_or_identifiant);
      if (isset($this->societes[$id])){
        return $this->societes[$id];
      }

      $this->societes[$id] = $this->find($id);

      return $this->societes[$id];
    }

    public function getNextIdentifiantSociete() {
        $id = '';
        $societes = $this->getSocietesIdentifiants(acCouchdbClient::HYDRATE_ON_DEMAND)->getIds();
        $last_num = 0;
        foreach ($societes as $id) {
            if (!preg_match('/^SOCIETE-([0-9]{6})$/', $id, $matches)) {
                continue;
            }

            $num = $matches[1];
            if ($num > $last_num) {
                $last_num = $num;
            }
        }

        return sprintf("%06d", $last_num + 1);
    }

    public function getNextCodeFournisseur() {
        $societes = SocieteExportView::getInstance()->findByInterpro('INTERPRO-declaration');
        $nextCF = 0;
        foreach ($societes as $societe) {
            if ($cf = $societe->value[SocieteExportView::VALUE_CODE_COMPTABLE_FOURNISSEUR]) {
                if (substr($cf, 1) > $nextCF) {
                    $nextCF = substr($cf, 1);
                }
            }
        }
        return 'F' . sprintf("%1$07d", ((double) ($nextCF + 1)));
    }

    public function getSocietesIdentifiants($hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        return $this->startkey('SOCIETE-000000')->endkey('SOCIETE-999999')->execute($hydrate);
    }

    public function findByIdentifiantSociete($identifiant) {
        return $this->find($this->getId($identifiant));
    }

    public function getInterlocuteursWithOrdre($identifiant, $withSuspendus) {
        $contactsArr = $this->findByIdentifiantSociete($identifiant)->getInterlocuteursWithOrdre();
        $result = array();
        foreach ($contactsArr as $id => $value) {
            $compte = CompteClient::getInstance()->find($id);
            if ($compte->statut != SocieteClient::STATUT_SUSPENDU) {
                $result[] = $compte;
            }
        }
        if ($withSuspendus) {
          foreach ($contactsArr as $id => $value) {
              $compte = CompteClient::getInstance()->find($id);
              if ($compte->statut == SocieteClient::STATUT_SUSPENDU) {
                  $result[] = $compte;
              }
          }
        }
        return $result;
    }

    public static function getSocieteTypes() {
        return array(self::TYPE_OPERATEUR => self::TYPE_OPERATEUR,
            self::TYPE_COURTIER => self::TYPE_COURTIER,
            self::TYPE_AUTRE => self::TYPE_AUTRE);
    }

    public static function getStatuts() {
        return array(self::STATUT_ACTIF => 'Actif', self::STATUT_SUSPENDU => 'Suspendu');
    }

//    public static function getTypesNumeroCompte() {
//        return array(self::NUMEROCOMPTE_TYPE_CLIENT => 'Client', self::NUMEROCOMPTE_TYPE_FOURNISSEUR => 'Fournisseur');
//    }

    public static function getSocieteTypesWithChais() {
        return array(self::TYPE_OPERATEUR => self::TYPE_OPERATEUR,
                     self::TYPE_COURTIER => self::TYPE_COURTIER);
    }

    public function addTagRgtEnAttenteFromFile($path, $societesCodeClientView) {
        $file = fopen($path, 'r');

        $nb_ligne = 1;
        $resultArr = array();
        while ($csv_arr = fgetcsv($file, 0, ';')) {
            if (!isset($csv_arr[1])) {
                throw new sfException("Le csv est mal formatté : la ligne $nb_ligne doit possèder un identifiant en deuxième valeur");
            }
            $code_comptable_client_csv = sprintf("%08d", $csv_arr[1]);
            if (!preg_match('/^[0-9]{8}$/', $code_comptable_client_csv)) {
                throw new sfException("Le code comptable client $code_comptable_client_csv de la ligne $nb_ligne est mal formatté");
            }

            $societe = null;
            foreach ($societesCodeClientView as $societeView) {

                if (!array_key_exists('ligne_' . $nb_ligne, $resultArr)) {
                    $resultArr['ligne_' . $nb_ligne] = array();
                }

                if ($societe) {
                    break;
                }

                $code_comptable_client_view = $societeView->value[SocieteExportView::VALUE_CODE_COMPTABLE_CLIENT];

                if ($code_comptable_client_view != $code_comptable_client_csv) {
                    continue;
                }

                $identifiant = $societeView->key[SocieteExportView::KEY_IDENTIFIANT];
                $societe = $this->find($identifiant);

                if (!$societe) {
                    $resultArr['ligne_' . $nb_ligne]['msg'] = "La société d'identifiant $identifiant n'a pas été trouvé";
                    $resultArr['ligne_' . $nb_ligne]['type'] = "ERREUR";
                    continue;
                }

                    $compte = $societe->getMasterCompte();
                try {
                    $compte->addTag("manuel", "rgt_en_attente");
                    $resultArr['ligne_' . $nb_ligne]['msg'] = "Ajout du Tag RgtEnAttente pour compte $compte->identifiant";
                    $resultArr['ligne_' . $nb_ligne]['type'] = "VALIDE";
                    $compte->save();
                } catch (sfException $e) {
                    $resultArr['ligne_' . $nb_ligne]['msg'] = "ERREUR problème d'enregistrement du tag pour le compte $compte->identifiant";
                    $resultArr['ligne_' . $nb_ligne]['type'] = "ERREUR";
                }
            }
            $nb_ligne++;
        }
        fclose($file);
        return $resultArr;
    }

}
