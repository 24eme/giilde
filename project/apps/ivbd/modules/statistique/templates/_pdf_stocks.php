<?php
setlocale(LC_TIME, 'fr_FR');
$items = explode(PHP_EOL, html_entity_decode($csv, ENT_QUOTES));
array_shift($items);
$maxTableRowsPerPage = 30;
$nbPage = 0;
$compare = (isset($options['compare']))? $options['compare'] : false;
$appellations = (isset($options['appellations']) && count($options['appellations']))? $options['appellations'] : false;
$options = $options->getRawValue();
$periode = (isset($options['periode']) && isset($options['periode'][0]) && isset($options['periode'][1]))? $options['periode'] : null;


?>
\documentclass[a4paper, landscape, 10pt]{article}
\usepackage[utf8]{inputenc}
\usepackage[top=2.3cm, bottom=1.8cm, left=0.5cm, right=0.5cm, headheight=2cm, headsep=0.5cm, marginparwidth=0cm]{geometry}
\usepackage{fancyhdr}
\usepackage{graphicx}
\usepackage{multicol}
\usepackage{colortbl}
\usepackage{tabularx}
\usepackage{multirow}
\usepackage{booktabs}
\usepackage[framemethod=tikz]{mdframed}
\usepackage{lastpage}

\setlength{\aboverulesep}{-1pt}
\setlength{\belowrulesep}{0pt}

\def\LOGO{<?php echo sfConfig::get('sf_web_dir'); ?>/images/logo_ivbd.png}
\renewcommand{\arraystretch}{1.2}
\makeatletter
\setlength{\@fptop}{5pt}
\makeatother


\fancyhf{}
\renewcommand{\headrulewidth}{0cm}
\renewcommand\sfdefault{phv}
\renewcommand{\familydefault}{\sfdefault}

\fancyfoot[R]{\thepage~/~\pageref{LastPage}}
\fancyfoot[L]{<?php echo strftime("%e %B %Y", time()) ?>}
\fancyhead[L]{\includegraphics[scale=0.6]{\LOGO}}
\fancypagestyle{fstyle}{
	<?php if($appellations): ?>

		\fancyhead[R]{Stocks des produits pour les appellations
		<?php foreach ($appellations as $key => $appellation): ?>
			\textbf{<?php echo $appellation ?><?php echo ($key < count($appellations)-1)? ",~" :"";?>}
		<?php endforeach; ?>}
<?php else: ?>
		\vspace{1cm}
		\fancyhead[C]{\textbf{Stocks des produits pour toutes les appellations} <?php if ($periode): ?>\\Période du \textbf{<?php echo $periode[0] ?>} au \textbf{<?php echo $periode[1] ?>}<?php endif; ?>}

<?php endif; ?>
}

\begin{document}

\pagestyle{fstyle}

\begin{table}[ht!]
<?php if ($compare): ?>
\begin{tabularx}{\linewidth}{ | X | >{\raggedright}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | }
\hline
\rowcolor{gray!40} ~ & \multicolumn{4}{c |}{\textbf{Stock initial}} & \multicolumn{4}{c |}{\textbf{Mouvements}} & \multicolumn{4}{c |}{\textbf{Stock Fin}} \tabularnewline \cmidrule{2-13}
\rowcolor{gray!40} \textbf{Produit} & \multicolumn{1}{c |}{\textbf{N}} & \multicolumn{1}{c |}{\textbf{N-1}} & \multicolumn{2}{c |}{\textbf{Ecart}} & \multicolumn{1}{c |}{\textbf{N}} & \multicolumn{1}{c |}{\textbf{N-1}} & \multicolumn{2}{c |}{\textbf{Ecart}}& \multicolumn{1}{c |}{\textbf{N}} & \multicolumn{1}{c |}{\textbf{N-1}} & \multicolumn{2}{c |}{\textbf{Ecart}} \tabularnewline \cmidrule{4-5} \cmidrule{8-9} \cmidrule{12-13}
\rowcolor{gray!40} ~ & ~ & ~ & \multicolumn{1}{c |}{\textbf{HL}} & \multicolumn{1}{c |}{\textbf{\%}} & ~ & ~ & \multicolumn{1}{c |}{\textbf{HL}} & \multicolumn{1}{c |}{\textbf{\%}}& ~ & ~ & \multicolumn{1}{c |}{\textbf{HL}} & \multicolumn{1}{c |}{\textbf{\%}} \tabularnewline \hline
<?php else : ?>
\begin{tabularx}{\linewidth}{ | X | >{\raggedright}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | }
\hline
\rowcolor{gray!40} \textbf{Produit} & \multicolumn{1}{c |}{\textbf{Stock initial}} & \multicolumn{1}{c |}{\textbf{Mouvements}} & \multicolumn{1}{c |}{\textbf{Stock Fin}} \tabularnewline \hline
<?php endif; ?>
<?php
	$i = 1;
	$page = null;
	foreach ($items as $item):
		$values = explode(';', $item);
		if (!$values[0]) {
			continue;
		}
		$isTotal = preg_match('/total/i', $item);
		$isTotalTotal = preg_match('/(TOTAL ROUGES ET ROSES|TOTAL BLANC|TOTAL TOUTES APPELLATIONS);/', $item);
		$current = $values[0];
		$newSection = false;
	if ($i >= ($maxTableRowsPerPage - 3) && ($page != $current) && ($page!==null)):

			$page = $current;
			$newSection = true;
?>
\end{tabularx}
\end{table}
\clearpage
\pagestyle{fstyle}
\begin{table}[ht!]
<?php if ($compare): ?>

	\begin{tabularx}{\linewidth}{ | X | >{\raggedright}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | >{\raggedleft}p{0.05\linewidth} | }
	\hline
	\rowcolor{gray!40} ~ & \multicolumn{4}{c |}{\textbf{Stock initial}} & \multicolumn{4}{c |}{\textbf{Mouvements}} & \multicolumn{4}{c |}{\textbf{Stock Fin}} \tabularnewline \cmidrule{2-13}
	\rowcolor{gray!40} \textbf{Produit} & \multicolumn{1}{c |}{\textbf{N}} & \multicolumn{1}{c |}{\textbf{N-1}} & \multicolumn{2}{c |}{\textbf{Ecart}} & \multicolumn{1}{c |}{\textbf{N}} & \multicolumn{1}{c |}{\textbf{N-1}} & \multicolumn{2}{c |}{\textbf{Ecart}}& \multicolumn{1}{c |}{\textbf{N}} & \multicolumn{1}{c |}{\textbf{N-1}} & \multicolumn{2}{c |}{\textbf{Ecart}} \tabularnewline \cmidrule{4-5} \cmidrule{8-9} \cmidrule{12-13}
	\rowcolor{gray!40} ~ & ~ & ~ & \multicolumn{1}{c |}{\textbf{HL}} & \multicolumn{1}{c |}{\textbf{\%}} & ~ & ~ & \multicolumn{1}{c |}{\textbf{HL}} & \multicolumn{1}{c |}{\textbf{\%}}& ~ & ~ & \multicolumn{1}{c |}{\textbf{HL}} & \multicolumn{1}{c |}{\textbf{\%}} \tabularnewline \hline
<?php else : ?>
	\begin{tabularx}{\linewidth}{ | X | >{\raggedright}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | }
<?php endif; ?>
	<?php if ($newSection): ?>
	\hline
	<?php if ($compare): ?>
		\rowcolor{gray!40} ~ & \multicolumn{4}{c |}{\textbf{Stock initial}} & \multicolumn{4}{c |}{\textbf{Mouvements}} & \multicolumn{4}{c |}{\textbf{Stock Fin}} \tabularnewline \cmidrule{2-13}
		\rowcolor{gray!40} \textbf{Produit} & \multicolumn{1}{c |}{\textbf{N}} & \multicolumn{1}{c |}{\textbf{N-1}} & \multicolumn{2}{c |}{\textbf{Ecart}} & \multicolumn{1}{c |}{\textbf{N}} & \multicolumn{1}{c |}{\textbf{N-1}} & \multicolumn{2}{c |}{\textbf{Ecart}}& \multicolumn{1}{c |}{\textbf{N}} & \multicolumn{1}{c |}{\textbf{N-1}} & \multicolumn{2}{c |}{\textbf{Ecart}} \tabularnewline \cmidrule{4-5} \cmidrule{8-9} \cmidrule{12-13}
		\rowcolor{gray!40} ~ & ~ & ~ & \multicolumn{1}{c |}{\textbf{HL}} & \multicolumn{1}{c |}{\textbf{\%}} & ~ & ~ & \multicolumn{1}{c |}{\textbf{HL}} & \multicolumn{1}{c |}{\textbf{\%}}& ~ & ~ & \multicolumn{1}{c |}{\textbf{HL}} & \multicolumn{1}{c |}{\textbf{\%}} \tabularnewline \hline
<?php else : ?>
		\rowcolor{gray!40} \textbf{Produit} & \multicolumn{1}{c |}{\textbf{Stock initial}} & \multicolumn{1}{c |}{\textbf{Mouvements}} & \multicolumn{1}{c |}{\textbf{Stock Fin}} \tabularnewline
	<?php endif; ?>
	<?php endif; ?>
	\hline
<?php $i=($newSection)? 1 : 0;
else:
$i++;
$page = $current;
endif; ?>

<?php
	if($isTotalTotal): ?> \rowcolor{gray!40}
<?php endif;
	foreach ($values as $key => $value){
		if ($isTotal && !$key): ?> \multicolumn{1}{| c |}{\textbf{<?php echo strtoupper($value); ?>}} <?php endif;
	  if ($isTotal && $key) echo '\textbf{'.formatNumberLatex($value).'}';
	  if(!$isTotal) echo ($key)? formatNumberLatex($value) : $value;
		if($key < count($values) -1 ) echo "&";

 		} ?>
	\tabularnewline \hline
<?php  endforeach; ?>
\end{tabularx}
\end{table}
\end{document}
