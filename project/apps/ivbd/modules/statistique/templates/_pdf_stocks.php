<?php
setlocale(LC_TIME, 'fr_FR');
$items = explode(PHP_EOL, html_entity_decode($csv, ENT_QUOTES));
array_shift($items);
$maxTableRowsPerPage = 30;
$nbPage = 0;
$compare = (isset($options['compare']))? $options['compare'] : false;
$appellations = (isset($options['appellations']) && count($options['appellations']))? $options['appellations'] : false;

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

		\fancyhead[R]{Stocks des articles pour les appellations
		<?php foreach ($appellations as $key => $appellation): ?>
			\textbf{<?php echo $appellation ?><?php echo ($key < count($appellations)-1)? ",~" :"";?>}
		<?php endforeach; ?>}
<?php else: ?>
		\vspace{1cm}
		\fancyhead[R]{Stocks des articles pour toutes les appellations}
<?php endif; ?>
}

\begin{document}

\pagestyle{fstyle}

\begin{table}[ht!]
<?php if ($compare): ?>
\begin{tabularx}{\linewidth}{ | X | >{\raggedright}p{0.08\linewidth} | >{\raggedleft}p{0.08\linewidth} | >{\raggedleft}p{0.08\linewidth} | >{\raggedleft}p{0.08\linewidth} | >{\raggedleft}p{0.08\linewidth} | >{\raggedleft}p{0.08\linewidth} | >{\raggedleft}p{0.08\linewidth}| >{\raggedleft}p{0.08\linewidth} | }
\hline
\rowcolor{gray!40} \textbf{Article} & \multicolumn{1}{c |}{\textbf{Catégorie}} & \multicolumn{1}{c |}{\textbf{Stock initial}} & \multicolumn{1}{c |}{\textbf{Stock initial N-1}} & \multicolumn{1}{c |}{\textbf{Mouvements}} & \multicolumn{1}{c |}{\textbf{Mouvements N-1}} & \multicolumn{1}{c |}{\textbf{Stock Fin}} & \multicolumn{1}{c |}{\textbf{Stock Fin N-1}} \tabularnewline \hline
<?php else : ?>
\begin{tabularx}{\linewidth}{ | X | >{\raggedright}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | }
\hline
\rowcolor{gray!40} \textbf{Article} & \multicolumn{1}{c |}{\textbf{Catégorie}} & \multicolumn{1}{c |}{\textbf{Stock initial}} & \multicolumn{1}{c |}{\textbf{Mouvements}} & \multicolumn{1}{c |}{\textbf{Stock Fin}} \tabularnewline \hline
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
\begin{tabularx}{\linewidth}{ | X | >{\raggedright}p{0.08\linewidth} | >{\raggedleft}p{0.08\linewidth} | >{\raggedleft}p{0.08\linewidth} | >{\raggedleft}p{0.08\linewidth} | >{\raggedleft}p{0.08\linewidth} | >{\raggedleft}p{0.08\linewidth} | >{\raggedleft}p{0.08\linewidth}| >{\raggedleft}p{0.08\linewidth} | }
<?php else : ?>
	\begin{tabularx}{\linewidth}{ | X | >{\raggedright}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | >{\raggedleft}p{0.1\linewidth} | }
<?php endif; ?>
	<?php if ($newSection): ?>
	\hline
	<?php if ($compare): ?>
		\rowcolor{gray!40} \textbf{Article} & \multicolumn{1}{c |}{\textbf{Catégorie}} & \multicolumn{1}{c |}{\textbf{Stock initial}} & \multicolumn{1}{c |}{\textbf{Stock initial N-1}} & \multicolumn{1}{c |}{\textbf{Mouvements}} & \multicolumn{1}{c |}{\textbf{Mouvements N-1}} & \multicolumn{1}{c |}{\textbf{Stock Fin}} & \multicolumn{1}{c |}{\textbf{Stock Fin N-1}} \tabularnewline \hline
	<?php else : ?>
		\rowcolor{gray!40} \textbf{Article} & \multicolumn{1}{c |}{\textbf{Catégorie}} & \multicolumn{1}{c |}{\textbf{Stock initial}} & \multicolumn{1}{c |}{\textbf{Mouvements}} & \multicolumn{1}{c |}{\textbf{Stock Fin}} \tabularnewline
	<?php endif; ?>
	<?php endif; ?>
	\hline
<?php $i=($newSection)? 1 : 0;
else:
$i++;
$page = $current;
endif; ?>
<?php if (preg_match('/total/i', $current)): ?>\hline<?php endif; ?>
<?php
foreach ($values as $key => $value) {
	if ($isTotal): ?> \textbf{<?php endif;
		echo $value;
	if ($isTotal): ?>}<?php endif;
	if($key < count($values) -1 ) echo "&";
}

?> \tabularnewline \hline
<?php  endforeach;?>
\end{tabularx}
\end{table}

\end{document}
