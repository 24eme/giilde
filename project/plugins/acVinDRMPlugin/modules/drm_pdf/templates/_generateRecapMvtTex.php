<?php
use_helper('Date');
use_helper('DRM');
use_helper('Orthographe');
use_helper('DRMPdf');
use_helper('Display');
$mvtsEnteesForPdf = $drmLatex->getMvtsEnteesForPdf($detailsNodes);
$mvtsSortiesForPdf = $drmLatex->getMvtsSortiesForPdf($detailsNodes);
$newPage = false;
$is_recap = true;
if(!isset($data)) {
    $data = $drm->declaration->getProduitsDetailsByCertifications(true,$detailsNodes);
    $is_recap = false;
}
if(!isset($tabTitle)) {
    $tabTitle = "Produits ".$libelleDetail."s";
}
?>

<?php foreach ($data as $certification => $produitsDetailsByCertifications) : ?>

    <?php
    $libelleCertif = $produitsDetailsByCertifications->certification_libelle;
    $nb_produits = count($produitsDetailsByCertifications->produits);
    if ($nb_produits == 0) {
        continue;
    }
    $nb_pages = ceil($nb_produits / DRMLatex::NB_PRODUITS_PER_PAGE);
    $nb_produits_per_page = DRMLatex::NB_PRODUITS_PER_PAGE;
    $nb_produits_displayed = 0;
    $produits_for_certifs = array_values($produitsDetailsByCertifications->produits->getRawValue());
    ?>
    <?php
    for ($index_page = 0; $index_page < $nb_pages; $index_page++):

        $index_first_produit = $index_page * $nb_produits_per_page;
        if ($index_page == $nb_pages - 1) {
            $nb_produits_per_page = $nb_produits - $nb_produits_displayed;
        }
        $size_col = 25;
        $size_col_sp = 40;
        $entete = '\begin{tabular}{C{'.$size_col_sp.'mm}|';
        for ($cpt_col = 0; $cpt_col < $nb_produits_per_page; $cpt_col++) {
            $entete .='C{'.$size_col.'mm}|';
        }
        if($is_recap){
            $entete .='C{'.$size_col.'mm}|}';
        }else{
            $entete .='}';
        }

        if ($index_page == 1) {
            $libelleCertif .= ' (Suite)';
        }
        $maxCol = 2 + $nb_produits_per_page;
        $index_last_produit = $index_first_produit + $nb_produits_per_page - 1;
        $produits_for_page = array();
        foreach (range($index_first_produit, $index_last_produit) as $indexProduit) {
            $produits_for_page[] = $produits_for_certifs[$indexProduit];
            $nb_produits_displayed++;
        }
        ?>

        <?php if ($newPage): ?>
          \newpage
          <?php $newPage = false; ?>
        <?php endif; ?>

        <?php
        /*
         * Début du Tabular
         */
        ?>
        <?php echo $entete; ?>


        <?php
        /*
         * Entête des Produits
         */
        ?>
        \cline{2-<?php echo $maxCol-1; ?>}

        \begin{large}
        \textbf{<?php echo $tabTitle ?> <?php echo $libelleCertif; ?>}
        \end{large} &
        <?php foreach ($produits_for_page as $counter => $produit): ?>
            <?php $libelleProduit = str_replace(array("AOC Alsace Grand Cru", "AOC Alsace Communale"), array("Gd Cru", "Communale"), $produit->libelle); ?>


            \multicolumn{1}{>{\columncolor[rgb]{0,0,0}}C{<?php echo $size_col; ?>mm}|}{ \small{\color{white}{\textbf{<?php echo escape_string_for_latex($libelleProduit); ?>}}}}
            <?php echo ($counter < count($produits_for_page) -1 ) ? "&" : ''; ?>
        <?php endforeach; ?>
        <?php if($is_recap):?>
            &\multicolumn{1}{>{\columncolor[rgb]{0,0,0}}C{<?php echo $size_col; ?>mm}|}{ \small{\color{white}{\textbf{TOTAL}}}}            
        <?php endif; ?>
        
        \\
        \hline
        <?php
        /*
         * STOCK DÉBUT DE MOIS
         */
        ?>
        \rowcolor{gray}
        <?php $totaldebuth = 0; ?>
        \multicolumn{1}{C{<?php echo $size_col_sp; ?>mm}|}{ \small{\color{white}{\textbf{STOCK DÉBUT DE MOIS}} }} &
        <?php foreach ($produits_for_page as $counter => $produit): ?>
            <?php if(count((array)$produit) > 1):?>
                <?php $totaldebuth += $produit->total_debut_mois; ?>
                \multicolumn{1}{r|}{  \small{\color{white}{\textbf{<?php echoFloatWithHl($produit->total_debut_mois); ?>}}}}
            <?php else: ?>
                \multicolumn{1}{r|}{  \small{\color{white}{\textbf{}}}}
            <?php endif; ?>
            <?php echo ($counter < count($produits_for_page) - 1) ? "&" : ''; ?>
        <?php endforeach; ?>
        <?php if($is_recap):?>
            &\multicolumn{1}{r|}{ \small{\color{white}{\textbf{ <?php echoFloatWithHl( $totaldebuth ); ?> }}}}
        <?php endif; ?>        
        \\
        \hline
        <?php
        /*
         * LES ENTREES
         */
        ?>
        
        <?php foreach ($mvtsEnteesForPdf as $cpt_entree => $entree): ?>
            <?php $entreeKey = $entree->key; ?>
            <?php if (!$cpt_entree): ?>
                \multicolumn{1}{C{<?php echo $size_col; ?>mm}|}{\multirow{<?php echo count($mvtsEnteesForPdf); ?>}{\small{\textbf{ENTREES DU MOIS}}}} &
            <?php endif; ?>
            <?php $totalentreeh = 0; ?>
            \multicolumn{1}{|l|}{  \small{<?php echo $entree->libelle; ?>} } &
            <?php foreach ($produits_for_page as $counter => $produit): ?>
                <?php if(count((array)$produit) > 1):?>
                    <?php $entreeVal = (($produit->entrees instanceof acCouchdbJson && $produit->entrees->exist($entreeKey)) || isset($produit->entrees->$entreeKey)) ? $produit->entrees->$entreeKey : null; ?>
                
                    \multicolumn{1}{r|}{ \small{ <?php echoFloatWithHl( $entreeVal ); ?> }}
                    <?php $totalentreeh += $entreeVal; ?>
                <?php else: ?>
                    \multicolumn{1}{r|}{  \small{\color{white}{\textbf{}}}}
                <?php endif; ?>
                
                <?php if($counter < count($produits_for_page) - 1): ?>
                    <?php echo "&"; ?>
                <?php else: ?>
                    <?php if($is_recap):?>
                        &\multicolumn{1}{r|}{ \small{ <?php echoFloatWithHl( $totalentreeh ); ?> }}
                    <?php endif; ?>                    
                <?php endif?>
            <?php endforeach; ?>           
            \\
            \hline
        <?php endforeach; ?>
        \hline
        <?php
        /*
         * TOTAL ENTREES
         */
        ?>
        \rowcolor{lightgray}
        <?php $tTotalentrees = 0; ?>
        \multicolumn{1}{|r|}{ \small{\textbf{TOTAL ENTREES}} } &
        <?php foreach ($produits_for_page as $counter => $produit): ?>
            <?php if(count((array)$produit) > 1):?>
                \multicolumn{1}{r|}{\small{\textbf{<?php echoFloatWithHl($produit->total_entrees); ?>}} }
                <?php $tTotalentrees += $produit->total_entrees;?>
            <?php else: ?>
                \multicolumn{1}{r|}{  \small{\textbf{}}}
            <?php endif; ?>
            <?php echo ($counter < count($produits_for_page) - 1) ? "&" : ''; ?>
        <?php endforeach; ?>
        <?php if($is_recap):?>
            &\multicolumn{1}{r|}{\small{\textbf{<?php echoFloatWithHl($tTotalentrees); ?>}} }
        <?php endif; ?>
        \\
        \hline

        <?php
        /*
         * LES SORTIES
         */
        ?>
        <?php foreach ($mvtsSortiesForPdf as $cpt_sortie => $sortie): ?>
            <?php $sortieKey = $sortie->key; ?>
            <?php if (!$cpt_sortie): ?>
                \multicolumn{1}{C{<?php echo $size_col_sp; ?>mm}|}{\multirow{<?php echo count($mvtsSortiesForPdf); ?>}{48mm}{\small{\textbf{SORTIES DU MOIS}}}} &
            <?php endif; ?>
            <?php $totalsortieh = 0; ?>
            \multicolumn{1}{|l|}{  \small{<?php echo $sortie->libelle; ?>} } &
            <?php foreach ($produits_for_page as $counter => $produit): ?>
                <?php if(count((array)$produit) > 1):?>
                    <?php $sortieVal = (($produit->sorties instanceof acCouchdbJson && $produit->sorties->exist($sortieKey)) || isset($produit->sorties->$sortieKey)) ? $produit->sorties->$sortieKey : null; ?>
                
                    \multicolumn{1}{r|}{ \small{ <?php echoFloatWithHl( $sortieVal ); ?> }}
                    <?php $totalsortieh += $sortieVal; ?>
                <?php else: ?>
                    \multicolumn{1}{r|}{  \small{\color{white}{\textbf{}}}}
                <?php endif; ?>
                <?php echo ($counter < count($produits_for_page) - 1) ? "&" : ''; ?>
            <?php endforeach; ?>
            <?php if($is_recap):?>
                &\multicolumn{1}{r|}{ \small{ <?php echoFloatWithHl($totalsortieh); ?> }}
            <?php endif; ?>  
            \\
            <?php if ((count($mvtsSortiesForPdf)) != $cpt_sortie): ?>
                \hline
            <?php endif; ?>
        <?php endforeach; ?>
        \hline

        <?php
        /*
         * TOTAL SORTIES
         */
        ?>
        \rowcolor{lightgray}
        <?php $tTotalsorties = 0; ?>
        \multicolumn{1}{|r|}{ \small{\textbf{TOTAL SORTIES}} } &
        <?php foreach ($produits_for_page as $counter => $produit): ?>
            <?php if(count((array)$produit) > 1):?>
                \multicolumn{1}{r|}{   \small{\textbf{<?php echoFloatWithHl($produit->total_sorties); ?>}} }
                <?php $tTotalsorties += $produit->total_sorties; ?>
            <?php else: ?>
                \multicolumn{1}{r|}{  \small{\color{white}{\textbf{}}}}
            <?php endif; ?>
            <?php echo ($counter < count($produits_for_page) - 1) ? "&" : ''; ?>
        <?php endforeach; ?>
        <?php if($is_recap):?>
            &\multicolumn{1}{r|}{   \small{\textbf{<?php echoFloatWithHl($tTotalsorties); ?>}} }
        <?php endif; ?>
        
        \\
        \hline \hline

        <?php
        /*
         * STOCK FIN DE MOIS
         */
        ?>
        \rowcolor{gray}
        <?php $totalstockh = 0; ?>
        \multicolumn{1}{C{<?php echo $size_col_sp; ?>mm}|}{ \small{\color{white}{\textbf{STOCK FIN DE MOIS}} }} &
        <?php foreach ($produits_for_page as $counter => $produit): ?>
            <?php if(count((array)$produit) > 1):?>
                \multicolumn{1}{r|}{  \small{\color{white}{\textbf{<?php $totalstockh += $produit->stocks_fin->final; echoFloatWithHl($produit->stocks_fin->final); ?>}}}}
            <?php else: ?>
                \multicolumn{1}{r|}{  \small{\color{white}{\textbf{}}}}
            <?php endif; ?>
            <?php echo ($counter < count($produits_for_page) - 1) ? "&" : ''; ?>
        <?php endforeach; ?>
        <?php if($is_recap):?>
            &\multicolumn{1}{r|}{  \small{\color{white}{\textbf{<?php echoFloatWithHl($totalstockh); ?>}}}}
        <?php endif; ?>        
        \\
        \hline
        \end{tabular}
        \newpage
        <?php if (($nb_pages > 1) && (($nb_pages - 1) == $index_page)) $newPage = true; ?>
    <?php endfor; ?>
    <?php $newPage = true; ?>
<?php endforeach; ?>
\newpage