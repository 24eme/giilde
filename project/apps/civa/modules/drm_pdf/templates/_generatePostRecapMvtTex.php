\newpage

<?php
$dataGlobal = $drm->declaration->getProduitsDetailsAggregateByAppellation(true, 'details', '/genres/VCI/');
$data = array();

if(isset($dataGlobal['/declaration/certifications/AOC_ALSACE'])) {
    $data['/declaration/certifications/AOC_ALSACE'] = $dataGlobal['/declaration/certifications/AOC_ALSACE'];
}

include_partial('drm_pdf/generateRecapMvtTex', array('drm' => $drm,'drmLatex' => $drmLatex, 'detailsNodes' => 'details', "libelleDetail" => null, 'data' => $data, 'tabTitle' => 'Récapitulatif')); ?>

<?php $dataExport = $drm->declaration->getMouvementsAggregateByAppellation('export.*_details', '/declaration/certifications/AOC_ALSACE')->getRawValue(); ?>

<?php
$list_pays = array_keys($dataExport);
$produits = array();
$libelleCertif = "Expédition AOC par pays";
$nb_produits_per_page = DRMLatex::NB_PRODUITS_PER_PAGE;
foreach ($dataExport as $pays => $appellations) {
    $produits = array_keys($appellations);
    break;
}
?>

<?php
$nb_produits = count($produits);
if($nb_produits): ?>

    \newpage

    <?php
    $nb_pages = ceil($nb_produits / $nb_produits_per_page);
    $nb_produits_displayed = 0;
    $size_col = 40;
    for ($index_page = 0; $index_page < $nb_pages; $index_page++): ?>
        <?php
        $index_first_produit = $index_page * $nb_produits_per_page;
        if ($index_page == $nb_pages - 1) {
            $nb_produits_per_page = $nb_produits - $nb_produits_displayed;
        }
        $entete = '\begin{tabular}{C{'. $size_col .'mm} |';
        for ($cpt_col = 0; $cpt_col < $nb_produits_per_page; $cpt_col++) {
            $entete .='C{'.$size_col.'mm}|';
        }
        $entete .='C{' . $size_col . 'mm}|}';
        if ($index_page == 1) {
            $libelleCertif .= ' (Suite)';
        }
        $maxCol = 2 + $nb_produits_per_page;
        $index_last_produit = $index_first_produit + $nb_produits_per_page - 1;
        $produits_for_page = array();
        $produits_labelles = array();
        
        foreach ($list_pays as $p) {
            $val = array();
            foreach (range($index_first_produit, $index_last_produit) as $indexProduit) {
                if($indexProduit < count(array_values($dataExport[$p]))){
                    $val[] = array_values($dataExport[$p])[$indexProduit];
                }
            }
            $produits_for_page[$p][] = $val;
        }
        foreach (range($index_first_produit, $index_last_produit) as $indexProduit) {
            if($indexProduit < count($produits)){
                $produits_labelles[] = $produits[$indexProduit];
            }
        }
        ?>

        <?php echo $entete; ?>
        
        \cline{2-<?php echo $maxCol-1; ?>}

        \begin{large}
        \textbf{<?php echo $libelleCertif; ?> }
        \end{large} &
        <?php $i = 1; $nbcol = 0; $tabTotal = ["Total" => array()];?>
        <?php foreach ($produits_labelles as $libelle): ?>
            \multicolumn{1}{>{\columncolor[rgb]{0,0,0}}C{<?php echo $size_col; ?>mm}|}{ \small{\color{white}{\textbf{<?php echo escape_string_for_latex($libelle); ?>}}}}&
            <?php $i++; $tabTotal["Total"][$i] = 0;?>
        <?php $nbcol = $i; endforeach; ?>
        \multicolumn{1}{>{\columncolor[rgb]{0,0,0}}C{<?php echo $size_col; ?>mm}|}{ \small{\color{white}{\textbf{TOTAL}}}}
        \\
        \hline
 <?php foreach ($list_pays as $counter => $pays): ?>
    \multicolumn{1}{|l|}{\small{<?php echo ConfigurationClient::getInstance()->getCountry($pays) ?>}} &
    <?php $i = 1; ?>
    <?php $totalh = 0; ?>
    <?php foreach($produits_for_page[$pays] as $c => $volumes):?>
        <?php foreach ($volumes as $key => $volume): ?>
            \multicolumn{1}{r|}{\small{<?php $totalh += $volume; echoFloatWithHl($volume) ; ?>}}&
            <?php $i++; $tabTotal["Total"][$i] += $volume; ?>
            
        <?php endforeach; ?>
        \multicolumn{1}{r|}{ \small{<?php echoFloatWithHl($totalh) ; ?>}}
        \\
       \hline
     <?php endforeach; ?>
<?php endforeach; ?>

    \rowcolor{lightgray}
    <?php $totalh = 0; ?>
    \multicolumn{1}{|r|}{ \small{\textbf{TOTAL}} } &
    <?php foreach ($tabTotal["Total"] as $key => $totalv): ?>
        \multicolumn{1}{r|}{\small{\textbf{<?php echoFloatWithHl($totalv); ?>}} }&
        <?php $totalh += $totalv; ?>
    <?php endforeach; ?>
    \multicolumn{1}{r|}{\small{\textbf{<?php echoFloatWithHl($totalh); ?>}} }
    \\
    \hline

    \end{tabular}

    \newpage
    <?php endfor; ?>
<?php endif; ?>
