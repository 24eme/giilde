<?php
use_helper('Float');
use_helper('Display');
$firstLibelle = ($produit->origine_type)? str_replace("&#039;", "'", $produit->origine_type) : str_replace("&#039;", "'", $produit->libelle);
$secondLibelle = ($produit->origine_type)? ' \fontsize{8}{10}\selectfont '.str_replace("&#039;", "'", $produit->libelle) : "";
if( FactureConfiguration::getInstance()->isPdfProduitFirst() && !$produit->getDocument()->isFactureDivers()){
  $firstLibelle = ($produit->origine_type)? str_replace("&#039;", "'", $produit->libelle) : str_replace("&#039;", "'", $produit->libelle.' - Autres sorties');
  $secondLibelle = ($produit->origine_type)? ' \fontsize{8}{10}\selectfont '.str_replace("&#039;", "'", ' - '.$produit->origine_type) : "";
}
?>
~~~~\truncate{114mm}{~~~~~~\small{<?php echo escape_string_for_latex($firstLibelle); ?>} <?php echo escape_string_for_latex($secondLibelle); ?> } &
                            \multicolumn{1}{r|}{\small{<?php echoArialFloat($produit->quantite); ?>}} &
                            \multicolumn{1}{r|}{\small{<?php echoArialFloat($produit->prix_unitaire); ?>}} &
                            \multicolumn{1}{r}{\small{<?php echoArialFloat($produit->montant_ht); ?>}} \\
