<?php
use_helper('Date');
use_helper('DRMPdf');
use_helper('Display');

$produits = $drm->getProduitsReserveInterpro();

 if (count($produits)) : ?>
    \vspace{0.5cm}
    \begin{center}
    \begin{large}
    \textbf{Réserve interprofessionnelle}
    \end{large}
    \end{center}
    \begin{tabular}{C{80mm}|C{40mm}|}
    \rowcolor{lightgray}
    \hline
    \multicolumn{1}{|C{80mm}}{\small{\textbf{Produit}}} &
    \multicolumn{1}{|C{40mm}|}{\small{\textbf{Réserve interprofessionnelle}}}
    \\
    \hline
    <?php foreach ($produits as $produit): ?>

        \multicolumn{1}{|l}{\small{\textbf{<?php echo $produit->getLibelle(); ?>}}} &
        \multicolumn{1}{|r|}{\small{\textbf{<?php echoFloatWithHl($produit->getRerserveIntepro()); ?>}}}
        \\
        \hline

    <?php endforeach; ?>
    \end{tabular}
    \vspace{0.2cm}
<?php endif;
