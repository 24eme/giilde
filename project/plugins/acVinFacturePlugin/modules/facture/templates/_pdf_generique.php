<?php
$prix_u_libelle = FactureConfiguration::getInstance()->getNomTaux();
$titre_type_facture = "Cotisation interprofessionnelle";
$qt_libelle = "Volume \\tiny{en hl}";
if($facture->hasArgument(FactureClient::TYPE_FACTURE_MOUVEMENT_DIVERS)){
    $qt_libelle = "Quantité";
    $prix_u_libelle = "Prix U.";
    $titre_type_facture = "";
}
$avoir = ($facture->total_ht <= 0);
include_partial('facture/pdf_generique_prelatex', array('pdf_titre' => $titre_type_facture, 'ressortissant' => $facture->declarant));
include_partial('facture/pdf_facture_def', array('facture' => $facture));
include_partial('facture/pdf_generique_entete', array('facture' => $facture, 'avoir' => $avoir));
?>
\centering
\fontsize{8}{10}\selectfont
    \begin{tikzpicture}
		\node[inner sep=1pt] (tab1){
                        \renewcommand{\arraystretch}{1.2}
			\begin{tabular}{p{120mm} |p{20mm}|p{12mm}|p{24mm}p{0mm}}
  			\rowcolor{lightgray}
                        \centering \small{\textbf{Libellé}} &
   			\centering \small{\textbf{<?php echo $qt_libelle; ?>}} &
                        \centering \small{\textbf{<?php echo $prix_u_libelle; ?>}} &
   			\centering \small{\textbf{Montant HT \tiny{en \texteuro{}}}} &
   			 \\
  			\hline
                      ~ & ~ & ~ & ~ &\\

<?php
$line_nb_current_page = FactureLatex::NB_LIGNES_ENTETE * ($nb_pages > 1);
$current_avg_nb_lines_per_page = floor($nb_lines / $nb_pages);
$max_line_nb_current_page = FactureLatex::MAX_LIGNES_PERPAGE - FactureLatex::NB_LIGNES_ENTETE;
$current_total_line_nb = 0;
$current_nb_pages = 0;
foreach ($facture->lignes as $type => $typeLignes) {
    $line_nb_current_page++;
    ?>
    \small{\textbf{<?php echo $typeLignes->getLibellePrincipal(); ?>}<?php if($typeLignes->getLibelleSecondaire()): ?> <?php echo $typeLignes->getLibelleSecondaire(); ?><?php endif; ?>} &
    \multicolumn{1}{r|}{~} &
    \multicolumn{1}{r|}{~} &
    \multicolumn{1}{r}{~}
    \\
    <?php
    foreach ($typeLignes->details as $prodHash => $produit) {
        include_partial('facture/pdf_generique_tableRow', array('produit' => $produit->getRawValue(), 'facture' => $facture));
        $line_nb_current_page++;
        if ($line_nb_current_page > $current_avg_nb_lines_per_page || $line_nb_current_page >= $max_line_nb_current_page) {
            for($i = 0 ; $i < ($max_line_nb_current_page - $line_nb_current_page); $i++):  ?>
            ~ & ~ & ~ & ~ &\\
            <?php endfor;?>
                ~ & ~ & ~ & ~ & \\
                            \end{tabular}
                    };
            \node[draw=gray, inner sep=-2pt, rounded corners=3pt, line width=2pt, fit=(tab1.north west) (tab1.north east) (tab1.south east) (tab1.south west)] {};
            \end{tikzpicture}
            <?php
            pdf_newpage();
            ?>
            \centering
            \fontsize{8}{10}\selectfont
                \begin{tikzpicture}
            		\node[inner sep=1pt] (tab1){
                                    \renewcommand{\arraystretch}{1.2}
            			\begin{tabular}{p{120mm} |p{20mm}|p{12mm}|p{24mm}p{0mm}}
              			\rowcolor{lightgray}
                                    \centering \small{\textbf{Libellé}} &
               			\centering \small{\textbf{<?php echo $qt_libelle; ?>}} &
                                    \centering \small{\textbf{<?php echo $prix_u_libelle; ?>}} &
               			\centering \small{\textbf{Montant HT \tiny{en \texteuro{}}}} &
               			 \\
              			\hline
                                  ~ & ~ & ~ & ~ &\\
            <?php
            $current_total_line_nb += $line_nb_current_page;
            $line_nb_current_page = 0;
            $current_nb_pages++;
            $max_line_nb_current_page = FactureLatex::MAX_LIGNES_PERPAGE;
            $current_avg_nb_lines_per_page = ($nb_lines - $current_total_line_nb) / ($nb_pages - $current_nb_pages);
        }
    }
}
$nb_blank = FactureLatex::MAX_LIGNES_PERPAGE - $line_nb_current_page - FactureLatex::NB_LIGNES_REGLEMENT;
$nb_echeances = count($facture->getEcheancesPapillon());
if ($nb_echeances)
    $nb_blank += - FactureLatex::NB_LIGNES_PAPILLONS_PAR_ECHEANCE * $nb_echeances - FactureLatex::NB_LIGNES_PAPILLONS_FIXE;
if (!$current_nb_pages)
    $nb_blank -= FactureLatex::NB_LIGNES_ENTETE;

    for($i=0; $i<$nb_blank;$i++):
    ?>
~ & ~ & ~ & ~ &\\
    <?php
    endfor;
    ?>
    \end{tabular}
    };
    \node[draw=gray, inner sep=-2pt, rounded corners=3pt, line width=2pt, fit=(tab1.north west) (tab1.north east) (tab1.south east) (tab1.south west)] {};
\end{tikzpicture}
<?php
include_partial('facture/pdf_generique_reglement', array('facture' => $facture));
if ($nb_echeances && !$avoir)
    include_partial('facture/pdf_generique_echeances', array('echeances' => $facture->getEcheancesPapillon(), 'societe' => $facture->getSociete()));
?>
\end{document}
