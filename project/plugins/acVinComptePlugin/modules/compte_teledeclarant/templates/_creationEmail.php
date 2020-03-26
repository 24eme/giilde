<?php
$interpro = strtoupper(sfConfig::get('app_teledeclaration_interpro'));
?>
Madame, Monsieur,

Votre compte a bien été créé sur l'espace professionnel <?php echo $interpro; ?>.

Nous vous rappelons que votre identifiant restera toujours le : <?php echo $compte->getLogin(); ?>.

Cordialement,

L'espace professionnel <?php echo $interpro; ?>
