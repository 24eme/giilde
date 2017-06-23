<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of updateCompteWithDroitsAndTypeSociete
 *
 * @author mathurin
 */
class setCodeCreationForAllTask extends sfBaseTask {

    protected $debug = false;

    protected function configure() {
        $this->addArguments(array(
            new sfCommandArgument('code_creation', sfCommandArgument::OPTIONAL, '0'),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('debug', null, sfCommandOption::PARAMETER_OPTIONAL, 'use only one code creation', '0'),
        ));

        $this->namespace = 'teledeclaration';
        $this->name = 'setCodeCreationForAllTask';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [maintenanceCompteStatut|INFO] task does things.
Call it with:

  [php symfony maintenance:update-comptes-with-droits|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
// initialize the database connection

        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->debug = array_key_exists('debug', $options) && $options['debug'];
        $this->createCodeCreation($arguments);
    }

    public function createCodeCreation($arguments = array()) {
        $this->code_creation = null;

        if ($this->debug) {
            echo $this->yellow("WARNING => bebug Mode") . "\n";
            $this->codeCreation = null;
            if (array_key_exists('code_creation', $arguments)) {
                if ($arguments['code_creation'] && (!preg_match('/^[0-9]{4}$/', $arguments['code_creation']))) {
                    throw new sfException("Les codes de création doivent faire 4 chiffres!");
                }
                $this->code_creation = $arguments['code_creation'];
                echo $this->yellow("tout les code de création seront assigné comme ") . $this->green($this->code_creation) . "\n";
            }
        }


        $comptesCDC = $this->getCompteCodeCreation();
        echo "ASSIGNEMENT CODE DE CREATIONS \n\n\n";
        echo "###identifiant;code_creation;raison_sociale;raison_sociale_abregee;adresse;adresse_complementaire;code_postal;commune;type_societe\n";
        foreach ($comptesCDC as $compteCDCId) {
            $this->code_creation = ($this->debug && $this->codeCreation) ? sprintf("%04d", $this->code_creation) : sprintf("%04d", rand(0, 9999));
            $compte = CompteClient::getInstance()->findByIdentifiant($compteCDCId);
            if (!$this->debug || $this->codeCreation) {
                $compte->add('mot_de_passe', "{TEXT}" . $this->code_creation);
                $compte->save(false, false, false, true);
            }
            $societe = $compte->getSociete();
            if ($this->debug && !$this->codeCreation) {
                $compte = $societe->getMasterCompte();
                echo "###" . $societe->identifiant . ";" . $compte->mot_de_passe . ";" . $societe->raison_sociale . ";" . $societe->raison_sociale_abregee . ";" . $societe->siege->adresse . ";" . $societe->siege->adresse_complementaire . ";" . $societe->siege->code_postal . ";" . $societe->siege->commune . ";" . $societe->type_societe . "\n";
            } else {
                echo "###" . $societe->identifiant . ";" . $this->code_creation . ";" . $societe->raison_sociale . ";" . $societe->raison_sociale_abregee . ";" . $societe->siege->adresse . ";" . $societe->siege->adresse_complementaire . ";" . $societe->siege->code_postal . ";" . $societe->siege->commune . ";" . $societe->type_societe . "\n";
            }
        }
    }

    protected function getCompteCodeCreation() {

        $rows = CompteAllView::getInstance()->findByInterproVIEW("INTERPRO-declaration");

        $comptesCodeCreation = array();

        echo "---------------------\nTrie des Comptes pour code créations\n";
        foreach ($rows as $row) {
            $compte = CompteClient::getInstance()->find($row->id);
            if (!$compte) {
                echo $this->red("ERREUR : ") . "Le compte $row->id est introuvable en base.\n";
                continue;
            }
            $societe = $compte->getSociete();
            if (!$societe) {
                echo $this->red("ERREUR : ") . "Le compte $row->id n'appartient a aucune société.\n";
                continue;
            }

            if (!$compte->isActif()) {
                echo $this->red("ERREUR : ") . "Le est compte $row->id inactif.\n";
                continue;
            }


            $masterCompte = $societe->getMasterCompte();

            if (!$masterCompte->exist("droits")) {
                echo $this->red("ERREUR : ") . "Le compte $masterCompte->_id n'a aucun droits.\n";
                continue;
            }

            $hasTelededeclaration = $masterCompte->hasDroit(Roles::TELEDECLARATION);
            if (!$hasTelededeclaration) {
                echo $this->red("ERREUR : ") . "La societe $societe->_id n'a pas droit à la télédeclaration.\n";
                continue;
            }

            if ($societe->isViticulteur() || $societe->isCourtier() || $societe->isNegociant()) {

                $current_campagne = ConfigurationClient::getInstance()->getCurrentCampagne();
                $previous_campagne = ConfigurationClient::getInstance()->getPreviousCampagne($current_campagne);
                $previous_previous_campagne = ConfigurationClient::getInstance()->getPreviousCampagne($previous_campagne);

                $current_contrats = VracClient::getInstance()->retrieveByCampagneEtablissementAndStatut($societe, $current_campagne);
                $previous_contrats = VracClient::getInstance()->retrieveByCampagneEtablissementAndStatut($societe, $previous_campagne);
                $previous_previous_contrats = VracClient::getInstance()->retrieveByCampagneEtablissementAndStatut($societe, $previous_previous_campagne);
                $nbContrats = count($current_contrats) + count($previous_contrats) + count($previous_previous_contrats);

                if (!$nbContrats) {
                    echo $this->yellow("Warning : ") . "La societe $societe->_id n'a pas pas de contrat dans les années précedentes\n";
                    continue;
                }
            }

            if (!array_key_exists($masterCompte->identifiant, $comptesCodeCreation)) {
                $comptesCodeCreation[$masterCompte->identifiant] = $masterCompte->identifiant;
                echo $this->green("Code Creation : ") . "Code de creation pour le compte $masterCompte->identifiant\n";
            }
        }
        return $comptesCodeCreation;
    }

    public function green($string) {
        return "\033[32m" . $string . "\033[0m";
    }

    public function yellow($string) {
        return "\033[33m" . $string . "\033[0m";
    }

    public function red($string) {
        return "\033[31m" . $string . "\033[0m";
    }

}
