<?php

$ressortissant = new stdClass();
$ressortissant->raison_sociale = $csv[0][1];
$ressortissant->adresse = html_entity_decode($csv[0][2]);
$ressortissant->adresse_complementaire = '';
$ressortissant->code_postal = $csv[0][3];
$ressortissant->ville = $csv[0][4];
$ressortissant->commune = $csv[0][4];
include_partial('facture/pdf_generique_prelatex', array('pdf_nb_pages' => 1, 'pdf_titre' => "Prix d'achats", 'ressortissant' => $ressortissant));
include_partial('facture/pdf_generique_entete');

?>

~ \\

Veuillez trouver ci-joint les mouvements d'achats sans prix enregistrés pour votre société <?php
if ($options['date_fin']) {
  echo "du ".preg_replace('/(\d+)-(\d+)-(\d+)/', '$3/$2/$1', $options['date_debut'])." au ".preg_replace('/(\d+)-(\d+)-(\d+)/', '$3/$2/$1', $options['date_fin']);
} else {
  echo "depuis le ".preg_replace('/(\d+)-(\d+)-(\d+)/', '$3/$2/$1', $options['date_debut']);
}?>.

~ \\

Merci de renseigner les prix afin de nous permettre d'établir les statistiques correspondantes :

~ \\

\centering
\begin{tabular}{|l|l|l|r|r|l|}
\hline
\multicolumn{1}{|c|}{\textbf{Date}} & \multicolumn{1}{c|}{\textbf{Vendeur}} & \multicolumn{1}{c|}{\textbf{Appellation /  Couleur}} & \multicolumn{1}{c|}{\textbf{hl}} & \multicolumn{1}{c|}{\textbf{Prix}} & \multicolumn{1}{c|}{\textbf{Vrac/blle}} \\ \hline
<?php foreach($csv as $c ): ?>
<?php echo preg_replace('/(\d{4})-(\d{2})-(\d{2})/', '$3/$2/$1', $c[5]); ?>                &
<?php echo $c[6]; ?>                &
<?php echo $c[7]; ?>                &
<?php printf("%.02f", $c[8]); ?>    &
  ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~             &
  ~ ~ ~ ~ ~ ~               \\ \hline
<?php endforeach; ?>
\end{tabular}

\end{document}
