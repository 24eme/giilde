<?php
use_helper('Date');
use_helper('Display');

$coordonneesBancaires = $facture->getCoordonneesBancaire();
$infosInterpro = $facture->getInformationsInterpro();
?>
\def\InterproAdresse{<?php echo $facture->emetteur->adresse; ?> \\
		       <?php echo $facture->emetteur->code_postal.' '.$facture->emetteur->ville; ?>}
\def\InterproContact{\\<?php echo $facture->emetteur->telephone;?>
                                             <?php if($facture->emetteur->exist('email')): ?>
                                                    \\ Email : <?php echo $facture->emetteur->email; ?>
                                              <?php endif;?>}

\def\FactureReglement{ <?php echo FactureConfiguration::getInstance()->getReglement(); ?> }
\def\TVA{19.60}
\def\FactureNum{<?php echo $facture->numero_piece_comptable; ?>}
\def\FactureDate{<?php echo format_date($facture->date_facturation,'dd/MM/yyyy'); ?>}
\def\NomRefClient{<?php echo $facture->numero_adherent; ?>}
\def\FactureRefClient{<?php echo $facture->numero_adherent; ?>}
\def\FactureRefCodeComptableClient{<?php echo (FactureConfiguration::getInstance()->getPdfDiplayCodeComptable())? $facture->code_comptable_client : $facture->numero_adherent; ?>}
\newcommand{\CutlnPapillon}{
  	\multicolumn{2}{|c|}{ ~~~~~~~~~~~~~~~~~~~~~~~ } &
  	\multicolumn{3}{c}{\Rightscissors \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline  \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline  \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline  \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline}
\\
}

\newcommand{\CutlnPapillonEntete}{
      &  &  \multicolumn{3}{c}{\Rightscissors \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline  \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline  \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline  \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline \Cutline}
\\
}
