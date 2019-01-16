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
$produits = array();
foreach ($dataExport as $pays => $appellations) {
    $produits = array_keys($appellations);
    break;
}
?>

<?php if(count($produits)): ?>

\newpage

<?php
$size_col = 20;
$entete = '\begin{tabular}{C{48mm} |';
foreach($produits as $libelle) {
    $entete .='C{' . $size_col . 'mm}|';
}
$entete .='}';
?>

<?php echo $entete; ?>

\begin{large}
\textbf{Expédition AOC par pays }
\end{large} &
<?php $i = 1; ?>
<?php foreach ($produits as $libelle): ?>
    \multicolumn{1}{>{\columncolor[rgb]{0,0,0}}C{<?php echo $size_col; ?>mm}|}{ \small{\color{white}{\textbf{<?php echo escape_string_for_latex($libelle); ?>}}}}
    <?php echo ($i < count($produits)) ? "&" : '';  ?>
    <?php $i++; ?>
<?php endforeach; ?>
\\
\hline

<?php foreach($dataExport as $pays => $appellations): ?>
    \multicolumn{1}{|l|}{  \small{<?php echo ConfigurationClient::getInstance()->getCountry($pays) ?>} } &
    <?php $i = 1; ?>
    <?php foreach($appellations as $volume):?>
        \multicolumn{1}{r|}{ \small{<?php echoFloatWithHl($volume) ; ?>}}
        <?php echo ($i < count($appellations)) ? "&" : '';  ?>
        <?php $i++; ?>
     <?php endforeach; ?>
     \\
       \hline
<?php endforeach; ?>

\end{tabular}

\newpage

<?php endif; ?>
