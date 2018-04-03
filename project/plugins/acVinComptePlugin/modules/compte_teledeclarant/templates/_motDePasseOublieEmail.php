<?php
$interpro = strtoupper(sfConfig::get('app_teledeclaration_interpro'));
?>
Bonjour <?php echo $compte->nom ?>,

Vous avez oublié votre mot de passe.

Pour le redéfinir merci de cliquer sur le lien suivant : <?php echo $lien ?>

Cordialement,

L'espace professionnel <?php echo $interpro; ?>
