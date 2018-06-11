<?php

require_once(dirname(__FILE__).'/../../config/ProjectConfiguration.class.php');
require_once dirname(__FILE__).'/../../lib/vendor/symfony/test/bootstrap/unit.php';

$t = new lime_test(64);
$t->comment("Tests de l'existence et du chargement des fichiers de configurations");

$applications = array(
    null,
    "generique",
    "ivso",
    "ivbd",
    "bivc",
);

$configurationsClass = array(
    "vrac" => "VracConfiguration",
    "facture" => "FactureConfiguration",
    "drm" => "DRMConfiguration",
    "sv12" => "SV12Configuration",
    "statistique" => "StatistiqueConfiguration",
);

foreach($applications as $application) {
    if($application) {
        $configuration = ProjectConfiguration::getApplicationConfiguration($application, 'dev', true);
        $t->is(get_class(ProjectConfiguration::getActive()), $application."Configuration", "L'application utilisé est ".$application."Configuration");
    } else {
        $configuration = new ProjectConfiguration();
        $t->is(get_class(ProjectConfiguration::getActive()), "ProjectConfiguration", "Aucune application utilisé");
    }

    foreach($configurationsClass as $name => $class) {
        try {
            $configurationInstance = new $class();
            $t->ok($application, "$application : La configuration $name est chargé");
            $t->isnt(count($configurationInstance->getAll()), 0, "$application : La configuration contient des éléments");
        } catch(Exception $e) {
            $t->ok(!$application, "$application : La configuration $name n'est pas chargé");
        }
    }
}

$t->comment("Tests spécifiques de la conf ivso");
$configuration = ProjectConfiguration::getApplicationConfiguration("ivso", 'dev', true);
$configurationInstance = new VracConfiguration();
$t->is($configurationInstance->getRepartitionCvo(), "50", "ivso : La repartition CVO d'un contrat est 50/50");
$t->is($configurationInstance->getRegionDepartement(), ".*", "ivso : La région est toute la france");

$configurationInstance = new FactureConfiguration();
$t->is($configurationInstance->getTVACompte(), "44571502", "ivso : Le tva est 44571502");

$configurationInstance = new DRMConfiguration();
$t->is($configurationInstance->isMouvementDivisable(), true, "ivso : Les mouvements sont divisables");
$t->is($configurationInstance->getMouvementDivisableSeuil(), 0, "ivso : Les mouvements sont divisables à partir de 0");
$t->is($configurationInstance->getMouvementDivisableNbMonth(), 12, "ivso : Les mouvements sont divisables sur 12 mois");

$t->comment("Tests spécifiques de la conf ivbd");
$configuration = ProjectConfiguration::getApplicationConfiguration("ivbd", 'dev', true);

$configurationInstance = new VracConfiguration();
$t->is($configurationInstance->getRepartitionCvo(), "100_ACHETEUR", "ivbd : La repartition CVO d'un contrat est 100% Acheteur");
$t->is($configurationInstance->getRegionDepartement(), "^(24|33|46|47)", "ivbd : Les départements de région sont 24,33,46,47");

$configurationInstance = new FactureConfiguration();
$t->is($configurationInstance->getTVACompte(), "44570100", "ivbd : Le tva est 44570100");

$configurationInstance = new DRMConfiguration();
$t->is($configurationInstance->isMouvementDivisable(), false, "ivbd : Les mouvements ne sont pas divisables");

$t->comment("Tests spécifiques de la conf bivc");
$configuration = ProjectConfiguration::getApplicationConfiguration("bivc", 'dev', true);
$configurationInstance = new VracConfiguration();
$t->is($configurationInstance->getRepartitionCvo(), "50", "bivc : La repartition CVO d'un contrat est 50/50");
$t->is($configurationInstance->getRegionDepartement(), false, "bivc : La région est manuel");

$configurationInstance = new FactureConfiguration();
$t->is($configurationInstance->getTVACompte(), "44571000", "bivc : Le tva est 44571000");

$configurationInstance = new DRMConfiguration();
$t->is($configurationInstance->isMouvementDivisable(), false, "ivbd : Les mouvements ne sont pas divisables");
