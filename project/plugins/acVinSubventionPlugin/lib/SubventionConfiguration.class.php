<?php

class SubventionConfiguration {

    private static $_instance = null;
    protected $configuration;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new SubventionConfiguration();
        }
        return self::$_instance;
    }

    public function __construct() {
        if(!sfConfig::has('subvention_configuration_subvention')) {
			throw new sfException("La configuration pour les subventions n'a pas été définie pour cette application");
		}

        $this->configuration = sfConfig::get('subvention_configuration_subvention', array());
    }

    public function getEngagements() {
        return (isset($this->configuration['engagements']))? $this->configuration['engagements'] : array();
    }


    public function getEngagementLibelle($key) {
        $engagements = $this->getEngagements();
        return (isset($engagements[$key]))? $engagements[$key] : '';
    }

    public function getPlateforme() {
        return (isset($this->configuration['plateforme']))? $this->configuration['plateforme'] : array();
    }

    public function getReferent() {
        return (isset($this->configuration['referent']))? $this->configuration['referent'] : array();
    }

    public function getInfosSchema($operation) {
        return array(
            "economique" => array(
                                "capital_social" => array("label" => "Capital Social", "type" => "float", "unite" => "€"),
                                "effectif" => array("label" => "Effectif", "type" => "float", "unite" => "ETP", "help" => "Équivalent temps plein"),
                                "effectif_permanent" => array("label" => "Dont effectif permanent", "type" => "float", "unite" => "ETP", "help" => "Équivalent temps plein"),
                            ),
            "economique_libelle" => "Données économiques",
            "contacts" => array("nom" => array("label" => "Prénom Nom"), "email" => array("label" => "Email"), "telephone" => array("label" => "Téléphone")),
            "contacts_libelle" => "Contacts de la personne en charge du dossier au sein de l’entreprise",

        );
    }

    public function getApprobationsSchema($operation) {

        return array(
            "criteres" => array(
              // case 1
                              "respect_interpro" =>
                                  array("label" => "Respect des accords interprofessionnels ou engagement", "type" => "checkbox", "default" => true),
                              "respect_interpro_appreciation" =>
                                  array("label" => " ", "type" => "text", "placeholder" => "Appréciation"),
                                  // case 2
                              "attente_dossierautre" =>
                                  array("label" => "Opérations concernant les vins conditionnés sous signe de qualité issus des AOP et IGP de la Région :<br/>
                                          1. Pays d’OC/Terres du Midi<br/>
                                          2. AOC du Languedoc/IGP Sud de France<br/>
                                          3. Vins du Sud-Ouest<br/>
                                          4. Vins de la Vallée du Rhône<br/>
                                          5. Vins du Roussillon (AOP/IGP)", "type" => "checkbox", "default" => true),
                              "attente_dossierautre_appreciation" =>
                                 array("label" => " ", "type" => "text", "placeholder" => "Appréciation"),
                                // case 3
                              "negociant_contractualisation" =>
                                  array("label" => "Pour les négociants, condition de contractualisation", "type" => "checkbox"),
                              "negociant_contractualisation_effective" =>
                                  array("label" => "Contractualisation effective", "type" => "checkbox"),
                              "negociant_contractualisation_engagement" =>
                                  array("label" => "Contractualisation engagement", "type" => "checkbox"),
                              "negociant_contractualisation_appreciation" =>
                                  array("label" => " ", "type" => "text", "placeholder" => "Appréciation"),
                                  // case 4
                              "conditions_eligibilite" =>
                                  array("label" => "Eligibilité et appréciation de la faisabilité et de la cohérence des opérations présentées (adéquation coût/action…)", "type" => "checkbox", "default" => true),
                                "conditions_eligibilite_appreciation" =>
                                  array("label" => " ", "type" => "text", "placeholder" => "Appréciation"),

                              ),
            "criteres_libelle" => "Critères de pré-qualification du dossier",
            "conclusionfavorable" => array(
                                "favorable" => array("label" => "Favorable sur l’ensemble des actions", "type" => "checkbox", "default" => true),
                                "partiellement_favorable" => array("label" => "Favorable uniquement sur les actions :", "type" => "text", "placeholder" => "Listez les actions séparées par des \",\""),
                                "partiellement_favorable_commentaire" => array("label" => "Commentaire :", "type" => "text")
                            ),
            "conclusionfavorable_libelle" => "Conclusions favorables ou partiellement favorables",
            "conclusionrejet" => array(
                                 "motif_rejet" => array("label" => "Motif de rejet du dossier", "type" => "text", "placeholder" => "Si le dossier est rejeter inscrivez ici les motifs de rejet du dossier")
                                ),
            "conclusionrejet_libelle" => "Conclusions de rejet"
        );
    }



    public function isActif() {
        return $this->configuration['actif'];
    }

    public function getOperationEnCours() {
        return $this->configuration['operation_en_cours'];
    }

}
