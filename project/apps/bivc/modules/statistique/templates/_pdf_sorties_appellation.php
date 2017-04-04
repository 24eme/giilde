<?php
setlocale(LC_ALL, 'fr_FR');
$items = explode(PHP_EOL, $csv);
array_shift($items);
$maxTableRowsPerPage = 30;
$nbPage = 0;
$options = $options->getRawValue();
$periode = (isset($options['periode']) && isset($options['periode'][0]) && isset($options['periode'][1]))? $options['periode'] : null;
$compare = (isset($options['compare']))? $options['compare'] : false;
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
\fancyfoot[L]{<?php echo strftime("%e %B %Y", mktime()) ?>}
\fancyhead[L]{\includegraphics[scale=0.6]{\LOGO}}


\fancypagestyle{fstyle_0}{
\fancyhead[C]{Sorties réelles de l'appellation<?php if ($periode): ?>\\Période du \textbf{<?php echo $periode[0] ?>} au \textbf{<?php echo $periode[1] ?>}<?php endif; ?>}
}

\begin{document}

<?php $fstyle = 0; ?>

\pagestyle{fstyle_<?php echo $fstyle ?>}

\begin{table}[ht!]
<?php if ($compare): ?>
\begin{tabularx}{\linewidth}{ | X | >{\raggedleft}p{0.061\linewidth} | >{\raggedleft}p{0.061\linewidth} | >{\raggedleft}p{0.028\linewidth} | >{\raggedleft}p{0.061\linewidth} | >{\raggedleft}p{0.061\linewidth} | >{\raggedleft}p{0.028\linewidth} | >{\raggedleft}p{0.061\linewidth} | >{\raggedleft}p{0.061\linewidth} | >{\raggedleft}p{0.028\linewidth} | >{\raggedleft}p{0.061\linewidth} | >{\raggedleft}p{0.061\linewidth} | >{\raggedleft}p{0.028\linewidth} | }
\hline
\rowcolor{gray!40} & \multicolumn{3}{c |}{France} & \multicolumn{3}{c |}{Export} & \multicolumn{3}{c |}{Nég. extérieur} & \multicolumn{3}{c |}{Total} \tabularnewline
\rowcolor{gray!40} Article & \multicolumn{1}{c |}{N-1} & \multicolumn{1}{c |}{N} & \multicolumn{1}{c |}{\%} & \multicolumn{1}{c |}{N-1} & \multicolumn{1}{c |}{N} & \multicolumn{1}{c |}{\%} & \multicolumn{1}{c |}{N-1} & \multicolumn{1}{c |}{N} & \multicolumn{1}{c |}{\%} & \multicolumn{1}{c |}{N-1} & \multicolumn{1}{c |}{N} & \multicolumn{1}{c |}{\%} \tabularnewline \hline
<?php else: ?>
\begin{tabularx}{\linewidth}{ | X | >{\raggedleft}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | }
\hline
\rowcolor{gray!40} Article & \multicolumn{1}{c |}{France} & \multicolumn{1}{c |}{Export} & \multicolumn{1}{c |}{Nég. extérieur} & \multicolumn{1}{c |}{Total} \tabularnewline \hline
<?php endif; ?>
<?php 
	$i = ($compare)? 2 : 1;
	foreach ($items as $item):
		$values = explode(';', $item);
		if (!$values[0]) {
			continue;
		}
		$isTotal = preg_match('/total/i', $item);
		$values[0] = ($values[0] != $values[1])? $values[0].' '.$values[1] : $values[0];
		unset($values[1]);
?>
<?php 
	if ($i == $maxTableRowsPerPage): 
	$newSection = true;
?>
\end{tabularx}
\end{table}
\clearpage
\pagestyle{fstyle_0}
\begin{table}[ht!]
<?php if ($compare): ?>
\begin{tabularx}{\linewidth}{ | X | >{\raggedleft}p{0.061\linewidth} | >{\raggedleft}p{0.061\linewidth} | >{\raggedleft}p{0.028\linewidth} | >{\raggedleft}p{0.061\linewidth} | >{\raggedleft}p{0.061\linewidth} | >{\raggedleft}p{0.028\linewidth} | >{\raggedleft}p{0.061\linewidth} | >{\raggedleft}p{0.061\linewidth} | >{\raggedleft}p{0.028\linewidth} | >{\raggedleft}p{0.061\linewidth} | >{\raggedleft}p{0.061\linewidth} | >{\raggedleft}p{0.028\linewidth} | }
<?php if ($newSection): ?>
\hline
\rowcolor{gray!40} & \multicolumn{3}{c |}{France} & \multicolumn{3}{c |}{Export} & \multicolumn{3}{c |}{Nég. extérieur} & \multicolumn{3}{c |}{Total} \tabularnewline
\rowcolor{gray!40} Article & \multicolumn{1}{c |}{N-1} & \multicolumn{1}{c |}{N} & \multicolumn{1}{c |}{\%} & \multicolumn{1}{c |}{N-1} & \multicolumn{1}{c |}{N} & \multicolumn{1}{c |}{\%} & \multicolumn{1}{c |}{N-1} & \multicolumn{1}{c |}{N} & \multicolumn{1}{c |}{\%} & \multicolumn{1}{c |}{N-1} & \multicolumn{1}{c |}{N} & \multicolumn{1}{c |}{\%} \tabularnewline
<?php endif; ?>
<?php else: ?>
\begin{tabularx}{\linewidth}{ | X | >{\raggedleft}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | }
<?php if ($newSection): ?>
\hline
\rowcolor{gray!40} Article & \multicolumn{1}{c |}{France} & \multicolumn{1}{c |}{Export} & \multicolumn{1}{c |}{Nég. extérieur} & \multicolumn{1}{c |}{Total} \tabularnewline
<?php endif; ?>
<?php endif; ?>
\hline
<?php $i=($newSection)? ($compare)? 2 : 1 : 0; else: $i++;endif; ?>
<?php if (preg_match('/^total$/i', $values[0])): ?>\hline<?php endif; ?><?php if ($isTotal): ?>\rowcolor{gray!40} <?php endif; if (preg_match('/^total$/i', $values[0])) {unset($values[0]); echo 'TOTAL général & '; } echo implode(' & ', $values); ?> \tabularnewline \hline
<?php  endforeach;?>
\end{tabularx}
\end{table}

\end{document}