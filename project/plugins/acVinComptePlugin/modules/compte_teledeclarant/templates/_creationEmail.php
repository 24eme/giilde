<?php
$societe = $compte->getSociete();
$identifiant = $societe->getIdentifiant();
$etablissement = $societe->getEtablissementPrincipal();
$interpro = strtoupper(sfConfig::get('app_teledeclaration_interpro'));
?>
Madame, Monsieur,

Votre compte a bien été créé sur l'espace professionnel <?php echo $interpro; ?>.

Nous vous rappelons que votre identifiant restera toujours le : <?php echo $identifiant ?>.

Cordialement,

L'espace professionnel <?php echo $interpro; ?>
