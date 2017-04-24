<?php

$ressortissant = new stdClass();
$ressortissant->raison_sociale = $societe->raison_sociale;
$ressortissant->adresse = $societe->siege->adresse;
$ressortissant->adresse_complementaire = $societe->siege->adresse_complementaire;
$ressortissant->code_postal = $societe->siege->code_postal;
$ressortissant->ville = $societe->siege->commune;
$ressortissant->commune = $societe->siege->commune;

include_partial('facture/pdf_generique_prelatex', array('pdf_titre' => "", 'ressortissant' => $ressortissant));
include_partial('facture/pdf_generique_entete');
?>
\end{document}
