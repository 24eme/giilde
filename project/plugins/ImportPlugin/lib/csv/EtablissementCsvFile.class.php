<?php

class EtablissementCsvFile extends CompteCsvFile
{

    const CSV_ID = 0;
    const CSV_ID_SOCIETE = 1;
    const CSV_TYPE = 2;
    const CSV_NOM = 3;
    const CSV_STATUT = 4;
    const CSV_REGION = 5;
    const CSV_CVI = 6;
    const CSV_NO_ACCISES = 7;
    const CSV_CARTE_PRO = 8;
    const CSV_RECETTE_LOCALE = 9;
    const CSV_NATURE_INAO = 10;
    const CSV_ADRESSE = 11;
    const CSV_ADRESSE_COMPLEMENTAIRE_1 = 12;
    const CSV_ADRESSE_COMPLEMENTAIRE_2 = 13;
    const CSV_ADRESSE_COMPLEMENTAIRE_3 = 14;
    const CSV_CODE_POSTAL = 15;
    const CSV_COMMUNE = 16;
    const CSV_INSEE = 17;
    const CSV_CEDEX = 18;
    const CSV_PAYS = 19;
    const CSV_EMAIL = 20;
    const CSV_TEL_BUREAU = 21;
    const CSV_TEL_PERSO = 22;
    const CSV_MOBILE = 23;
    const CSV_FAX = 24;
    const CSV_WEB = 25;
    const CSV_COMMENTAIRE = 26;

    private function verifyCsvLine($line) {
          if (!preg_match('/[0-9]+/', $line[self::CSV_ID_SOCIETE])) {

              throw new sfException(sprintf('ID invalide : %s', $line[self::CSV_ID_SOCIETE]));
          }
      }

    public function importEtablissements() {
      $this->errors = array();
      $etablissements = array();
      $csvs = $this->getCsv();
        foreach ($csvs as $line) {
          try {
        	  $this->verifyCsvLine($line);
            /*$famille = $this->convertTypeInFamille($line[self::CSVPAR_TYPE_PARTENAIRE]);
            if (!$famille) {
  	          echo "Etablissement ERROR: ".$line[self::CSVPAR_CODE_CLIENT].": Pas de Famille connue\n";
              continue;
            }*/

            $id = $line[self::CSV_ID];
            $id_societe = $line[self::CSV_ID_SOCIETE];

            if($id && EtablissementClient::getInstance()->find($id, acCouchdbClient::HYDRATE_JSON)) {
    	          echo "ERROR: Etablissement ".$id." existe\n";
    	          continue;
            }

            $s = SocieteClient::getInstance()->find($id_societe);

            if (!$s) {
              echo "WARNING: Societe ".$id_societe." n'existe pas\n";
              continue;
            }

            $e = $s->createEtablissement($line[self::CSV_TYPE]);
            $e->constructId();

            $e->famille=$line[self::CSV_TYPE];

            if(!array_key_exists($e->famille, EtablissementFamilles::getFamilles())) {

                throw new sfException(sprintf("La famille %s n'est pas connue", $e->famille));
            }

          	$e->nom = $this->getNom($line, $s, $e);
            $e->cvi = (isset($line[self::CSV_CVI])) ? str_replace(" ", "", $line[self::CSV_CVI]) : null;
            if($e->cvi && !preg_match("/^[0-9]+$/", $e->cvi)) {
              $e->addCommentaire("CVI provenant de l'import : ".$e->cvi);
              $e->cvi = null;
            }
            $e->no_accises = (isset($line[self::CSV_NO_ACCISES])) ? str_replace(" ", "", $line[self::CSV_NO_ACCISES]) : null;
            $e->carte_pro = (isset($line[self::CSV_CARTE_PRO])) ? str_replace(" ", "", $line[self::CSV_CARTE_PRO]) : null;
            $e->interpro = 'INTERPRO-declaration';
            $e->statut = ($s->statut == SocieteClient::STATUT_SUSPENDU) ? $s->statut : $line[self::CSV_STATUT];
            $e->region = (isset($line[self::CSV_REGION])) ? $line[self::CSV_REGION] : null;

            $e->nature_inao = null;
            $natures_inao = array_flip(EtablissementClient::$natures_inao_libelles);
            if($line[self::CSV_NATURE_INAO] && !array_key_exists($line[self::CSV_NATURE_INAO], $natures_inao)) {
                printf("Warning : la nature inao \"%s\" n'a pas été trouvé dans la liste #%s\n", $line[self::CSV_NATURE_INAO], implode(";", $line));
            } elseif($line[self::CSV_NATURE_INAO]){
                $e->nature_inao = $natures_inao[$line[self::CSV_NATURE_INAO]];
            }

            if($this->isSameAdresseThanSociete($line, $s, $e)) {
              $line[self::CSV_ADRESSE] = "";
              $line[self::CSV_ADRESSE_COMPLEMENTAIRE_1] = "";
              $line[self::CSV_ADRESSE_COMPLEMENTAIRE_2] = "";
              $line[self::CSV_ADRESSE_COMPLEMENTAIRE_3] = "";
              $line[self::CSV_CODE_POSTAL] = "";
              $line[self::CSV_COMMUNE] = "";
              $line[self::CSV_INSEE] = "";
              $line[self::CSV_CEDEX] = "";
              $line[self::CSV_PAYS] = $s->getPays();
              $line[self::CSV_EMAIL] = "";
              $line[self::CSV_TEL_BUREAU] = "";
              $line[self::CSV_TEL_PERSO] = "";
              $line[self::CSV_MOBILE] = "";
              $line[self::CSV_FAX] = "";
              $line[self::CSV_WEB] = "";
            }

            $e->save();
            $this->storeCompteInfos($e, $line, false);

            $e->save();

            $s->pushToCompteOrEtablissementAndSave($s->getMasterCompte(), $e);
        }catch(Exception $e) {
          if (isset($this->options['throw_exception']) && $this->options['throw_exception']) {
            throw $e;
          }else{
            echo $e->getMessage()."\n";
          }
        }
      }

      return $etablissements;
    }

    protected function isSameAdresseThanSociete($line, $s, $e) {
        if(!$line[self::CSV_CODE_POSTAL] || !$line[self::CSV_ADRESSE]) {

            return true;
        }

        $adresseS = preg_replace("/[ ]+/", " ", $s->siege->adresse." ".$s->siege->code_postal." ".$s->siege->commune);
        $adresseE = preg_replace("/[ ]+/", " ", trim(preg_replace('/,/', '', $line[self::CSV_ADRESSE]))." ".$line[self::CSV_CODE_POSTAL]." ".$line[self::CSV_COMMUNE]);

        $cs = $s->getMasterCompte();

        if(preg_replace("/-_/", "", KeyInflector::slugify($adresseE)) == preg_replace("/-_/", "", KeyInflector::slugify($adresseS))) {

            return true;
        }
        if($line[self::CSV_INSEE] == $cs->insee) {
            $e->addCommentaire("Adresse provenant de l'import : ".$adresseE);

            return true;
        }

        return false;
    }

    protected function getNom($line, $s, $e) {
        if(trim($line[self::CSV_NOM])) {
            $nomE = preg_replace("/[ ]+/", " ", strtoupper(preg_replace('/[\._()-]+/', " ", $line[self::CSV_NOM])));
            $nomS = preg_replace("/[ ]+/", " ", strtoupper(preg_replace('/[\._()-]+/', " ", $s->raison_sociale)));
            $wordsE = explode(" ", $nomE);
            $wordsS = explode(" ", $nomS);
            $score = 0;
            foreach($wordsE as $wordE) {
              if(strlen($wordE) <= 3) {
                continue;
              }
              $minScore = 99;
              $wordFind = null;
              foreach($wordsS as $key => $wordS) {
                  if(strlen($wordS) <= 3) {
                    continue;
                  }
                  $percent = levenshtein(KeyInflector::slugify($wordE), KeyInflector::slugify($wordS));
                  if($percent < $minScore) {
                    $minScore = $percent;
                  }
              }
              $score += $minScore;
            }
            $scoreGlobal = levenshtein($nomE, $nomS);

            if($scoreGlobal < $score) {
              $score = $scoreGlobal;
            }
            if($score <= 1) {

              return $s->raison_sociale;
            } elseif($score <= 4) {

              $e->addCommentaire("Nom provenant de l'import: ".$line[self::CSV_NOM]);

              return $s->raison_sociale;
            } else {

              return strtoupper($line[self::CSV_NOM]);
            }
        }

        return $s->raison_sociale;
    }

    protected function getField($line, $strConstant) {
        $constante = constant("self::$strConstant");

        return $line[$constante];
    }
}
