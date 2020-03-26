<?php
setlocale(LC_TIME, 'fr_FR');
$items = explode(PHP_EOL, html_entity_decode($csv, ENT_QUOTES));
array_shift($items);
$headers = array();
$maxTableRowsPerPage = 30;
$nbPage = 0;
foreach ($items as $item) {
$values = explode(';', $item);
$appellation = $values[0];
if (!$appellation || preg_match('/total/i', $appellation)) { continue; }
$headers[$appellation] = $appellation;
}
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
\usepackage[framemethod=tikz]{mdframed}
\usepackage{lastpage}

\def\LOGO{<?php echo sfConfig::get('sf_web_dir'); ?>/images/logo_bivc.png}
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

<?php $i=0; foreach ($headers as $header): ?>
\fancypagestyle{fstyle_<?php echo $i ?>}{
\fancyhead[C]{Stocks des articles pour l'appellation \textbf{<?php echo $header ?>}}
}
<?php $i++; endforeach; ?>

\begin{document}

<?php $fstyle = 0; ?>

\pagestyle{fstyle_<?php echo $fstyle ?>}

\begin{table}[ht!]
\begin{tabularx}{\linewidth}{ | X | >{\raggedright}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | }
\hline
\rowcolor{gray!40} Article & \multicolumn{1}{c |}{Catégorie} & \multicolumn{1}{c |}{Stock initial} & \multicolumn{1}{c |}{Stock actuel} & \multicolumn{1}{c |}{Total mouvement} \tabularnewline \hline
<?php
	$i = 1;
	$page = null;
	foreach ($items as $i => $item):
		$values = explode(';', $item);
		if (!$values[1]) {
			continue;
		}
		$isTotal = preg_match('/total/i', $item);
		$current = $values[0];
		if (!$page) {
			$page = $values[0];
		}
		array_shift($values);
?>
<?php
	if ($i == $maxTableRowsPerPage || ($page != $current && !preg_match('/total/i', $current))):
	$newSection = false;
	if ($page != $current) {
		$fstyle++;
		$page = $current;
		$newSection = true;
	}
?>
\end{tabularx}
\end{table}
\clearpage
\pagestyle{fstyle_<?php echo $fstyle ?>}
\begin{table}[ht!]
\begin{tabularx}{\linewidth}{ | X | >{\raggedright}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | }
<?php if ($newSection): ?>
\hline
\rowcolor{gray!40} Article & \multicolumn{1}{c |}{Catégorie} & \multicolumn{1}{c |}{Stock initial} & \multicolumn{1}{c |}{Stock actuel} & \multicolumn{1}{c |}{Total mouvement} \tabularnewline
<?php endif; ?>
\hline
<?php $i=($newSection)? 1 : 0; else: $i++;endif; ?>
<?php if (preg_match('/total/i', $current)): ?>\hline<?php endif; ?><?php if ($isTotal): ?>\rowcolor{gray!40} <?php endif; if (preg_match('/total/i', $current)) {unset($values[1]); echo 'TOTAL général & '; } echo implode(' & ', $values); ?> \tabularnewline \hline
<?php  endforeach;?>
\end{tabularx}
\end{table}

\end{document} 