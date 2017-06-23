<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MaintenanceTransfertChaiTask
 *
 * @author mathurin
 */
class ExportContratsFATask extends sfBaseTask {

    const CSV_FA_NUM_LIGNE = 0;
    const CSV_FA_TYPE_CONTRAT = 1;
    const CSV_FA_CAMPAGNE = 2;
    const CSV_FA_NUM_ARCHIVAGE = 3;
    const CSV_FA_CODE_LIEU_VISA = 4;
    const CSV_FA_CODE_ACTION = 5;
    const CSV_FA_DATE_CONTRAT = 6;
    const CSV_FA_DATE_VISA = 7;
    const CSV_FA_CODE_COMMUNE_LIEU_VINIFICATION = 8;
    const CSV_FA_INDICATION_DOUBLE_FIN = 9;
    const CSV_FA_CODE_INSEE_DEPT_COMMUNE_ACHETEUR = 10;
    const CSV_FA_NATURE_ACHETEUR = 11;
    const CSV_FA_SIRET_ACHETEUR = 12;
    const CSV_FA_CVI_VENDEUR = 13;
    const CSV_FA_NATURE_VENDEUR = 14;
    const CSV_FA_SIRET_VENDEUR = 15;
    const CSV_FA_COURTIER = 16; // (O/N)
    const CSV_FA_DELAI_RETIRAISON = 17;
    const CSV_FA_POURCENTAGE_ACCOMPTE = 18;
    const CSV_FA_DELAI_PAIEMENT = 19;
    const CSV_FA_CODE_TYPE_PRODUIT = 20;
    const CSV_FA_CODE_DENOMINATION_VIN_IGP = 21;
    const CSV_FA_PRIMEUR = 22;
    const CSV_FA_BIO = 23;
    const CSV_FA_COULEUR = 24;
    const CSV_FA_ANNEE_RECOLTE = 25;
    const CSV_FA_CODE_ELABORATION = 26; // (O/N)
    const CSV_FA_VOLUME = 27;
    const CSV_FA_DEGRE = 28; //(Degré vin si type de contrat = V (vins) Degré en puissance si type de contrat = M (moût))
    const CSV_FA_PRIX = 29;
    const CSV_FA_UNITE_PRIX = 30; // H pour Hl
    const CSV_FA_CODE_CEPAGE = 31;
    const CSV_FA_CODE_DEST = 32; // Z

    protected $produitsConfiguration = null;

    protected function configure() {

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('dryrun', null, sfCommandOption::PARAMETER_REQUIRED, 'Mode de test ne sauvgarde pas en base', false),
                // add your own options here
        ));

        $this->namespace = 'export';
        $this->name = 'contrats-france-agrimer';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [export-contrats-france-agrimer|INFO] task update contrat from chai src to chai dst.
Call it with:

  [php symfony export:contrats-france-agrimer|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->produitsConfiguration = ConfigurationClient::getCurrent()->getProduits();


        //echo "#num_ligne;type_contrat;campagne;num_archive;code_lieu_visa;code_action;date_contrat;date_visa;code_commune_lieu_vinification;indicateur_double_fin;code_insee_dept_commune_acheteur;nature_acheteur;siret_acheteur;cvi_vendeur;nature_vendeur;siret_vendeur;courtier (O/N);delai_retiraison;pourcentage_accompte;delai_paiement;code_type_produit;code_denomination_vin_IGP;primeur;bio;couleur;annee_recolte;code_elaboration (O/N);volume;degre (Degré vin si type de contrat = V (vins) Degré en puissance si type de contrat = M (moût));prix;unité_prix (H);code_cepage;code_dest (Z)\n";

        $contrats = $this->getContrats();
        $this->printCSV($contrats->rows, $options['dryrun']);
    }

    protected function getContrats() {

        return VracClient::getInstance()->retrieveAllVracs();
    }

    protected function printCSV($contratsView, $dryrun = false) {
        if (!count($contratsView)) {
            echo "Aucun contrats\n";
        }
        $cpt = 0;
        foreach ($contratsView as $contratView) {
            if (!preg_match('/\/[a-z]+\/[a-z]+\/IGP(.*)/', $contratView->value[VracHistoryView::VALUE_PRODUIT])) {
                continue;
            }
            $contrat = VracClient::getInstance()->find($contratView->id);

            if (!$this->isContratATransmettre($contrat)) {
                continue;
            }

            $cpt++;
            $ligne = array();

            $produit = $this->produitsConfiguration[$contratView->value[VracHistoryView::VALUE_PRODUIT]];

            $acheteur = $contrat->getAcheteurObject();
            $acheteurCompte = $acheteur->getMasterCompte();
            $acheteurSociete = $acheteur->getSociete();

            $vendeur = $contrat->getVendeurObject();
            $vendeurCompte = $vendeur->getMasterCompte();
            $vendeurSociete = $vendeur->getSociete();

            $ligne[self::CSV_FA_NUM_LIGNE] = "01"; // ? ou $cpt;
            $type_contrat = "";
            if ($contrat->type_transaction == VracClient::TYPE_TRANSACTION_VIN_VRAC) {
                $type_contrat = "V";
            }
            if ($contrat->type_transaction == VracClient::TYPE_TRANSACTION_MOUTS) {
                $type_contrat = "M";
            }
            $ligne[self::CSV_FA_TYPE_CONTRAT] = $type_contrat; // V pour vrac, M pour Mout
            $ligne[self::CSV_FA_CAMPAGNE] = substr($contrat->campagne, 0, 4);
            $ligne[self::CSV_FA_NUM_ARCHIVAGE] = $contrat->numero_archive; // Est-ce notre numéro d'archivage?
            $ligne[self::CSV_FA_CODE_LIEU_VISA] = "083"; //IVSO
            $ligne[self::CSV_FA_CODE_ACTION] = $contrat->versement_fa; // NC = Nouveau Contrat, SC = Supprimé Contrat, MC = Modifié Contrat
            $ligne[self::CSV_FA_DATE_CONTRAT] = Date::francizeDate($contrat->date_signature);
            $ligne[self::CSV_FA_DATE_VISA] = Date::francizeDate($contrat->date_visa);

            $ligne[self::CSV_FA_CODE_COMMUNE_LIEU_VINIFICATION] = $vendeurCompte->insee; // Code Insee Acheteur
            $ligne[self::CSV_FA_INDICATION_DOUBLE_FIN] = 'N'; // Quelle signification?
            /**
             * ACHETEUR
             */
            $ligne[self::CSV_FA_CODE_INSEE_DEPT_COMMUNE_ACHETEUR] = $acheteurCompte->insee; // Code Insee Acheteur
            $ligne[self::CSV_FA_NATURE_ACHETEUR] = ($acheteur->exist('nature_inao'))? $acheteur->nature_inao : '';
            $ligne[self::CSV_FA_SIRET_ACHETEUR] = $acheteurSociete->siret;
            /**
             * VENDEUR
             */
            $ligne[self::CSV_FA_CVI_VENDEUR] = $vendeur->cvi;
            $ligne[self::CSV_FA_NATURE_VENDEUR] = ($vendeur->exist('nature_inao'))? $vendeur->nature_inao : '';
            $ligne[self::CSV_FA_SIRET_VENDEUR] = $vendeurSociete->siret;
            /**
             * COURTIER
             */
            $ligne[self::CSV_FA_COURTIER] = ($contrat->mandataire_exist) ? 'O' : 'N';

            $delai_retiraison = $this->diffDate($contrat->date_limite_retiraison, $contrat->date_debut_retiraison, 'i');

            $ligne[self::CSV_FA_DELAI_RETIRAISON] = sprintf("%0.1f", $delai_retiraison);
            $ligne[self::CSV_FA_POURCENTAGE_ACCOMPTE] = sprintf("%d", $contrat->acompte);

            $ligne[self::CSV_FA_DELAI_PAIEMENT] = sprintf("%0.1f", $this->getDelaiPaiement($contrat));

            $ligne[self::CSV_FA_CODE_TYPE_PRODUIT] = "PA";
            $ligne[self::CSV_FA_CODE_DENOMINATION_VIN_IGP] = $this->getCodeDenomVinIGP($produit); // ASSIGNER LES CODE PRODUITS IGP
            $ligne[self::CSV_FA_PRIMEUR] = ($produit->getMention()->getKey() == "PM") ? "O" : "N";
            $ligne[self::CSV_FA_BIO] = ($contrat->isBio()) ? "O" : "N";
            $ligne[self::CSV_FA_COULEUR] = $this->getCouleurIGP($contrat, $produit);
            $ligne[self::CSV_FA_ANNEE_RECOLTE] = (substr($contrat->millesime, 0, 4))? substr($contrat->millesime, 0, 4) : "".(date('Y') - 1); //??
            $ligne[self::CSV_FA_CODE_ELABORATION] = ($contrat->conditionnement_crd == 'NEGOCE_ACHEMINE') ? "P" : "N";
            $ligne[self::CSV_FA_VOLUME] = $contrat->volume_propose;
            $ligne[self::CSV_FA_DEGRE] = sprintf("%0.1f", $contrat->degre);
            $ligne[self::CSV_FA_PRIX] = sprintf("%0.2f", $contrat->prix_initial_unitaire_hl);
            $ligne[self::CSV_FA_UNITE_PRIX] = 'H';
            $ligne[self::CSV_FA_CODE_CEPAGE] = $contrat->cepage; // Aucun code produit ajourd'hui
            $ligne[self::CSV_FA_CODE_DEST] = "Z";
            /*
              Comment connaitre?
              Z  = Consommation
              M = Vin destiné à l'élaboration de mousseux
              V  = Vinaigre
              O  = Apéritif à base de vin ou vermouth
              Pour les moûts :
              P = Vinification pour agrément en VDP (obsolète)
              T = Vinification pour agrément en VDT (obsolète)
              R = Enrichissement, édulcoration
              E = Elaboration de jus de raisin
              C = concentration
              A = Autres destinations
              X = Imprécis
             */

            foreach ($ligne as $champ) {
                echo '"' . $champ . '";';
            }
            echo "\n";
            $contrat->set('versement_fa', VracClient::VERSEMENT_FA_TRANSMIS);

            if($dryrun) {

                continue;
            }

            $contrat->save();
        }
    }

    public function diffDate($date1, $date2) {
        $datetime1 = new DateTime($date1);
        $datetime2 = new DateTime($date2);
        $diffMois = $datetime1->diff($datetime2)->m;
        return $diffMois;
    }

    protected function getDelaiPaiement($contrat) {

        switch ($contrat->delai_paiement) {
            case "60_JOURS": {
                    return 2.0;
                }
            case "30_JOURS": {
                    return 1.0;
                }
            case "90_JOURS": {
                    return 3.0;
                }
            case "COMPTANT": {
                    return 0.0;
                }
            case "ACCORD_INTERPROFESSIONNEL": {
                    return 2.5;
                }
            case "45_JOURS": {
                    return 1.5;
                }
        }
    }

    protected function getCodeDenomVinIGP($produit) {
        return sprintf('%03d',$produit->getCodeProduit());
    }

    protected function getCouleurIGP($contrat, $produit) {
        $couleur = $produit->getCouleur()->getKey();
        if ($contrat->type_transaction == VracClient::TYPE_TRANSACTION_MOUTS) {
            switch ($couleur) {
                case "blanc_sec":
                case "blanc_doux":
                case "blanc":
                    return "BL";

                default:
                    return "CO";
            }
        }
        switch ($couleur) {
            case "blanc_sec":
            case "blanc":
            case "blanc_doux":
                return "BL";
            case "rouge":
                return "RG";
            case "rose":
                return "RS";
        }
        return $couleur;
    }

    public function isContratATransmettre($contrat) {
        if (!$contrat->exist('versement_fa')) {
            return false;
        }
        if (($contrat->versement_fa == VracClient::VERSEMENT_FA_ANNULATION) || ($contrat->versement_fa == VracClient::VERSEMENT_FA_MODIFICATION) || ($contrat->versement_fa == VracClient::VERSEMENT_FA_NOUVEAU)) {
            return true;
        }
        return false;
    }

}
