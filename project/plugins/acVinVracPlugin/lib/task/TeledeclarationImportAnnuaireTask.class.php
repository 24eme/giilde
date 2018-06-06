<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TeledeclarationImportAnnuaireTask
 *
 * @author mathurin
 */
class TeledeclarationImportAnnuaireTask extends sfBaseTask {

    protected function configure() {


//        $this->addArguments(array(
//            new sfCommandArgument('soussigneId', sfCommandArgument::REQUIRED, 'soussigne Identifiant'),
//        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'teledeclaration';
        $this->name = 'importAnnuaire';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [generateAlertes|INFO] task does things.
Call it with:

  [php symfony generatePDF|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $context = sfContext::createInstance($this->configuration);

        $soussignesContrat = $this->getSoussignesContrat();

        foreach ($soussignesContrat as $etbId) {
            $this->fillSoussignesAnnuaire($etbId);
        }
    }

    protected function getSoussignesContrat() {

        $rows = CompteAllView::getInstance()->findByInterproVIEW("INTERPRO-declaration");
        $soussignes = array();

        echo "---------------------\nTrie des Etablissements annuaire\n";
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
            $type_societe = $societe->type_societe;
            if (!$type_societe) {
                echo $this->red("ERREUR : ") . "La societe $societe->_id n'a aucun type.\n";
                continue;
            }
            if (!$compte->isActif()) {
                echo $this->red("ERREUR : ") . "Le est compte $row->id inactif.\n";
                continue;
            }

            if ($societe->isNegociant() || $societe->isCourtier()) {
                $etbPrincipal = $societe->getEtablissementPrincipal();
                if (!$etbPrincipal) {
                    echo $this->red("ERREUR : ") . "La societe $societe->_id n'a pas d'établissement principal.\n";
                    continue;
                }

                $masterCompte = $societe->getMasterCompte();

                if (!$masterCompte->exist("droits")) {
                    echo $this->red("ERREUR : ") . "Le compte $masterCompte->_id n'a aucun droits.\n";
                    continue;
                }

                $hasTelededeclaration = $masterCompte->hasDroit(Roles::TELEDECLARATION);
                $hasTelededeclarationVrac = $masterCompte->hasDroit(Roles::TELEDECLARATION_VRAC);
                $hasTelededeclarationVracCreation = $masterCompte->hasDroit(Roles::TELEDECLARATION_VRAC_CREATION);
                if (!$hasTelededeclaration) {
                    echo $this->red("ERREUR : ") . "La societe $societe->_id n'a pas droit à la télédeclaration.\n";
                    continue;
                }
                if (!$hasTelededeclarationVrac) {
                    echo $this->red("ERREUR : ") . "La societe $societe->_id n'a pas droit à la télédeclaration Vrac.\n";
                    continue;
                }

                if (!$hasTelededeclarationVracCreation) {
                    echo $this->red("ERREUR : ") . "La societe $societe->_id n'a pas droit à la télédeclaration Vrac en création.\n";
                    continue;
                }
                if (!array_key_exists($etbPrincipal->identifiant, $soussignes)) {
                    $soussignes[$etbPrincipal->identifiant] = $etbPrincipal->identifiant;
                    echo $this->green("Annuaire : ") . "L'annuaire de l'etb $etbPrincipal->identifiant va être rempli.\n";
                }
            }
        }
        return $soussignes;
    }

    protected function fillSoussignesAnnuaire($etbId) {
        $societe = SocieteClient::getInstance()->findByIdentifiantSociete(substr($etbId, 0, 6));

        $annuaire = AnnuaireClient::getInstance()->findOrCreateAnnuaire($etbId);
        $contrats = VracClient::getInstance()->retrieveBySocieteWithInfosLimit($societe, $etbId);
        echo "Ajout dans annuaire de " . $societe->identifiant . " (" . $societe->type_societe . ")\n ------ \n";

        foreach ($contrats->rows as $contrat) {
            $vendeur_typeKey = AnnuaireClient::ANNUAIRE_RECOLTANTS_KEY;
            $vendeurId = $contrat->value[VracClient::VRAC_VIEW_VENDEUR_ID];
            $vendeurNom = $contrat->value[VracClient::VRAC_VIEW_VENDEUR_NOM];

            $acheteur_typeKey = AnnuaireClient::ANNUAIRE_NEGOCIANTS_KEY;
            $acheteurId = $contrat->value[VracClient::VRAC_VIEW_ACHETEUR_ID];
            $acheteurNom = $contrat->value[VracClient::VRAC_VIEW_ACHETEUR_NOM];

            $courtier_typeKey = AnnuaireClient::ANNUAIRE_COMMERICAUX_KEY;
            if ($societe->isCourtier()) {
                $vendeur = $annuaire->get($vendeur_typeKey)->add('ETABLISSEMENT-' . $vendeurId, $vendeurNom);
                echo "Ajout dans l'annuaire de C " . $societe->identifiant . " Vendeur " . $vendeurId . " (" . $vendeurNom . ")\n";

                $acheteur = $annuaire->get($acheteur_typeKey)->add('ETABLISSEMENT-' . $acheteurId, $acheteurNom);
                echo "Ajout dans l'annuaire de C " . $societe->identifiant . " Acheteur " . $acheteurId . " (" . $acheteurNom . ")\n";
            } else {
                if ($mandataireId = $contrat->value[VracClient::VRAC_VIEW_MANDATAIRE_ID]) {
                    $mandataireNom = $contrat->value[VracClient::VRAC_VIEW_MANDATAIRE_NOM];
                    $mandataire = $annuaire->get($courtier_typeKey)->add('ETABLISSEMENT-' . $mandataireId, $mandataireNom);
                    echo "Ajout dans l'annuaire de " . $societe->identifiant . " Courtier " . $mandataireId . " (" . $mandataireNom . ")\n";
                }
                $identifiant_vendeur = substr(str_replace('ETABLISSEMENT-', '', $vendeurId), 0, 6);
                $identifiant_acheteur = substr(str_replace('ETABLISSEMENT-', '', $acheteurId), 0, 6);
//                if (substr($etbId, 0, 6) == $identifiant_vendeur) {
//                    $acheteur = $annuaire->get($acheteur_typeKey)->add($acheteurId, $acheteurNom);
//                    echo "Ajout dans l'annuaire de A " . $societe->identifiant . " Acheteur " . $acheteurId . " (" . $acheteurNom . ")\n";
//                }
                if (substr($etbId, 0, 6) == $identifiant_acheteur) {
                    $vendeur = $annuaire->get($vendeur_typeKey)->add('ETABLISSEMENT-' . $vendeurId, $vendeurNom);
                    echo "Ajout dans l'annuaire de V " . $societe->identifiant . " Vendeur " . $vendeurId . " (" . $vendeurNom . ")\n";
                }
            }
        }
        $annuaire->save();
        echo "  ------ \n";
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
