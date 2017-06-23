<?php 

class SocieteCsvFile extends CompteCsvFile 
{
    const CSV_ID = 0;
    const CSV_TYPE = 1;
    const CSV_NOM = 2;
    const CSV_NOM_REDUIT = 3;
    const CSV_STATUT = 4;
    const CSV_CODE_COMPTABLE_CLIENT = 5;
    const CSV_CODE_COMPTABLE_FOURNISSEUR = 6;
    const CSV_SIRET = 7;
    const CSV_CODE_NAF = 8;
    const CSV_TVA_INTRACOMMUNAUTAIRE = 9;
    const CSV_ADRESSE = 10;
    const CSV_ADRESSE_COMPLEMENTAIRE_1 = 11;
    const CSV_ADRESSE_COMPLEMENTAIRE_2 = 12;
    const CSV_ADRESSE_COMPLEMENTAIRE_3 = 13;
    const CSV_CODE_POSTAL = 14;
    const CSV_COMMUNE = 15;
    const CSV_INSEE = 16;
    const CSV_CEDEX = 17;
    const CSV_PAYS = 18;
    const CSV_EMAIL = 19;
    const CSV_TEL_BUREAU = 20;
    const CSV_TEL_PERSO = 21;
    const CSV_MOBILE = 22;
    const CSV_FAX = 23;
    const CSV_WEB = 24;
    const CSV_COMMENTAIRE = 25; 

    private function verifyCsvLine($line) {
        if (!preg_match('/[0-9]+/', $line[self::CSV_ID]) || ((int) $line[self::CSV_ID]) == 0) {

            throw new sfException(sprintf('ID invalide : %s', $line[self::CSV_ID]));
        }
    }

    public function importSocietes () {
        $this->errors = array();
        $societes = array();
        $csvs = $this->getCsv();
        foreach ($csvs as $line) {
            try {
              	$this->verifyCsvLine($line);
                $id = sprintf("%06d", $line[self::CSV_ID]);

              	$s = SocieteClient::getInstance()->find($id, acCouchdbClient::HYDRATE_JSON);
                if ($s) {
        	          echo "ERROR: Societe existe ".$id."\n";
        	          continue;
                }

              	$s = new Societe();
                $s->identifiant = $id;
                $s->constructId();
                $s->raison_sociale = trim($line[self::CSV_NOM]);
        	    $s->raison_sociale_abregee = trim($line[self::CSV_NOM_REDUIT]);
              	$s->interpro = 'INTERPRO-declaration';
                $s->siret = str_replace(" ", "", $line[self::CSV_SIRET]);
                $s->code_naf = str_replace(" ", "", $line[self::CSV_CODE_NAF]);
                $s->no_tva_intracommunautaire = str_replace(" ", "", $line[self::CSV_TVA_INTRACOMMUNAUTAIRE]);
                $s->commentaire = $line[self::CSV_COMMENTAIRE];

                $s->code_comptable_client = $line[self::CSV_CODE_COMPTABLE_CLIENT];
                $s->code_comptable_fournisseur = null;
                if($line[self::CSV_CODE_COMPTABLE_FOURNISSEUR]) {
                    $s->code_comptable_fournisseur = $line[self::CSV_CODE_COMPTABLE_FOURNISSEUR];
                }

                if(!$s->code_comptable_client) {
                    printf("Warning : le code comptable client n'existe pas #%s\n", implode(";", $line));
                }

                /*if ($line[self::CSV_COOPGROUP] == 'C') {
              		$s->cooperative = 1;
                }*/
                $s->statut = $line[self::CSV_STATUT];
                $s->type_societe = $line[self::CSV_TYPE];

                $s->save();

                $this->storeCompteInfos($s, $line);
                
              	$s->save();

            }catch(Exception $e) {
              if (isset($this->options['throw_exception']) && $this->options['throw_exception']) {
                throw $e;
              }else{
                echo $e->getMessage()."\n";
              }
            }
        }

        return $societes;
    }

    protected function getField($line, $strConstant) {
        $constante = constant("self::$strConstant");

        return $line[$constante];
    }
}
