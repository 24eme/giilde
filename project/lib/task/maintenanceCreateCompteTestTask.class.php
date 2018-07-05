<?php

class maintenanceCreateCompteTestTask extends sfBaseTask
{
  protected function configure()
  {
     $this->addArguments(array(
       new sfCommandArgument('raisonSociale', sfCommandArgument::REQUIRED, 'Raison Sociale'),
       new sfCommandArgument('email', sfCommandArgument::REQUIRED, 'Email'),
       new sfCommandArgument('motDePasse', sfCommandArgument::REQUIRED, 'Mot de passe'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
    ));

    $this->namespace        = 'maintenance';
    $this->name             = 'create-compte-test';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [maintenanceCreateCompteTest|INFO] Créer un compte de test
Appel :

  [php symfony maintenanceCompteStatut --application="xxxx" raisonSociale monMotDePasse|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    if(!isset($arguments['raisonSociale']) || !isset($arguments['email']) || !isset($arguments['motDePasse'])){
        throw new sfException("php symfony maintenanceCompteStatut --application=\"xxxx\" raisonSociale monMotDePasse email");

    }
    $raisonSociale = $arguments['raisonSociale'];
    $email = $arguments['email'];
    $motDePasse = $arguments['motDePasse'];
    if(!$raisonSociale || !$motDePasse || !$email){
        throw new sfException("php symfony maintenanceCompteStatut --application=\"xxxx\" raisonSociale monMotDePasse email");

    }
    $societe = SocieteClient::getInstance()->createSociete($raisonSociale,SocieteClient::TYPE_OPERATEUR);
    $societe->siege->adresse = '1, rue de '.$raisonSociale;
    $societe->siege->commune = 'Commune de '.$raisonSociale;
    $societe->siege->code_postal = '75000';
    $societe->siret = "12345678901234";
    $societe->telephone = "001122334455";
    $societe->save();

    $compte = $societe->getMasterCompte();
    $compte->commentaire = "Compte fictif créer à la demande de ".$raisonSociale." pour effectuer des tests (".date('d/m/Y').")";
    $compte->add('droits',array("teledeclaration",  "teledeclaration_drm"));
    $compte->save();

    $etb = $societe->createEtablissement(EtablissementFamilles::FAMILLE_PRODUCTEUR);
    $etb->no_accises = "FR1122334455123";
    $etb->nom = "Chai de ".$raisonSociale;
    $etb->save();
    echo "$societe->_id : $raisonSociale créée : $compte->mot_de_passe\n";

    $compte = $societe->getMasterCompte();
    $compte->email = $email;
    try {
        $compte->setMotDePasseSSHA($motDePasse);
        echo " => ".$compte->_id." mot de passe écrit dans le ldap : ".$motDePasse."\n";
    } catch (Exception $e) {
        echo " => ".$compte->_id." pas accès au ldap ici !!!\n";
    }

    $compte->save();

    $compte->updateLdap();



  }
}
