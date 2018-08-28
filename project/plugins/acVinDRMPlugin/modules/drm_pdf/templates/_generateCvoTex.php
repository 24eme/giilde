<?php
use_helper('Date');
use_helper('DRMPdf');
use_helper('Display');

$totalPrixDroitCvo = 0;
$totalVolumeDroitsCvo = 0;
$totalVolumeReintegration = 0;
$cvoTotal = 0;
$nbMvtsFacturable = 0;

if ($drm->mouvements->exist($drm->identifiant)) {
	foreach ($drm->mouvements->get($drm->identifiant) as $mouvement) {
		if ($mouvement->facturable) {
			$nbMvtsFacturable++;
			$cvoTotal += $mouvement->cvo;
	    	$totalPrixDroitCvo += $mouvement->volume * -1 * $mouvement->cvo;
	        $totalVolumeDroitsCvo += $mouvement->volume * -1;
		}
	    if ($mouvement->type_hash == 'entrees/reintegration' && $mouvement->facturable) {
			$totalVolumeReintegration += $mouvement->volume;
	    }
	}
}
?>

~ \\
\hspace{-0.5cm}
\vspace{0.5cm}
\begin{tabular}{C{43mm}|C{43mm}|C{43mm}|C{43mm}|<?php if ($totalVolumeReintegration): ?>C{43mm}|<?php endif; ?>}
\multicolumn{<?php if ($totalVolumeReintegration): ?>5<?php else: ?>4<?php endif; ?>}{|c}{\cellcolor[gray]{0.3}\small{\color{white}{\textbf{CVO}}}}
\\
\hline
\rowcolor{lightgray}
\multicolumn{1}{|C{43mm}}{\small{\textbf{Libellé}}} &
\multicolumn{1}{|C{43mm}|}{\small{\textbf{Volume}}} &
<?php if ($totalVolumeReintegration): ?>
\multicolumn{1}{|C{43mm}|}{\small{\textbf{Réintégré}}} &
<?php endif; ?>
\multicolumn{1}{|C{43mm}|}{\small{\textbf{Taux}}} &
\multicolumn{1}{|C{43mm}|}{\small{\textbf{Total~HT}}}
\\
\hline
\multicolumn{1}{|l}{\small{\textbf{CVO Totale}}} &
\multicolumn{1}{|r|}{\small{\textbf{<?php echo sprintFloat($totalVolumeDroitsCvo).' hl';  ?>}}} &
<?php if ($totalVolumeReintegration): ?>
\multicolumn{1}{|r|}{\small{\textbf{<?php echo sprintFloat($totalVolumeReintegration).' hl';  ?>}}} &
<?php endif; ?>
\multicolumn{1}{|r|}{\small{\textbf{<?php echo ($cvoTotal)? round($cvoTotal/$nbMvtsFacturable,2).' €/hl' : ''; ?>}}} &
\multicolumn{1}{|r|}{\small{\textbf{<?php echo sprintFloat($totalPrixDroitCvo).' €'; ?>}}}
\\
\hline
\end{tabular}
~ \\
~ \\
