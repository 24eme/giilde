<?php
setlocale(LC_TIME, 'fr_FR');
$items = explode(PHP_EOL, $csv);
array_shift($items);
$headers = array();
foreach ($items as $item) {
$values = explode(';', $item);
if (!$values[0] || preg_match('/total/i', $values[0])) { continue; }
$headers[$values[0]] = $values[0];
}
$options = $options->getRawValue();
$periode = (isset($options['periode']) && isset($options['periode'][0]) && isset($options['periode'][1]))? $options['periode'] : null;
?>
\documentclass[a4paper, landscape, 10pt]{article}
\usepackage[utf8]{inputenc}
\usepackage[top=2.3cm, bottom=1.8cm, left=0.5cm, right=0.5cm, headheight=2cm, headsep=0.5cm, marginparwidth=0cm]{geometry}
\usepackage{fancyhdr}
\usepackage{graphicx}
\usepackage{amssymb}
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

\fancypagestyle{fstyle_0}{
\fancyhead[C]{Controle de facturation des sorties <?php if ($periode): ?> - Période du \textbf{<?php echo $periode[0] ?>} au \textbf{<?php echo $periode[1] ?>}<?php endif; ?>}
}

\begin{document}

\pagestyle{fstyle_0}

\begin{table}[ht!]
\begin{tabularx}{\linewidth}{ | X | >{\centering\arraybackslash}p{0.08\linewidth} | >{\centering\arraybackslash}p{0.07\linewidth} | >{\centering\arraybackslash}p{0.07\linewidth} | >{\centering\arraybackslash}p{0.07\linewidth} | >{\centering\arraybackslash}p{0.07\linewidth} | >{\centering\arraybackslash}p{0.07\linewidth} | >{\centering\arraybackslash}p{0.07\linewidth} | >{\centering\arraybackslash}p{0.07\linewidth} | >{\centering\arraybackslash}p{0.07\linewidth} | }
\hline
\rowcolor{gray!40} ~ 					& Sorties sous & ~        & \textbf{Facturation} & Sorties hors             & ~        & \textbf{Facturation} & Total sorties         & ~        & \textbf{Facturation} \tabularnewline
\rowcolor{gray!40} \textbf{Appellations} & contrats & CVO & \textbf{attendue €}  & Contrats & CVO & \textbf{attendue}  & réelles à & CVO & \textbf{Attendue} \tabularnewline
\rowcolor{gray!40} ~           & (vrac) hl     & \texteuro{}\/hl & \textbf{ \texteuro{} }  & (bouteilles) hl & \texteuro{}\/hl & en \texteuro{}   & facturer en hl & \texteuro{}\/hl & \textbf{\texteuro{}} \tabularnewline
\hline
<?php
	$i = 0;
	foreach ($items as $item):
    $values = explode(';', $item);
		foreach ($values as $key => $value) {
			if($key==0 || $key==3 || $key==6 || $key==9){
				$values[$key] = '\textbf{'.$value.'}';
			}
		}
		$isTotal = preg_match('/total/i', $item);
		$i++;
		$lastItem = (count($items) <= $i );
		?>
		<?php if ($isTotal): ?> \hline
			\rowcolor{gray!40} <?php echo implode(' & ', $values); ?> <?php if (!$lastItem): ?> \tabularnewline <?php endif; ?>\hline  <?php else: ?><?php  echo implode(' & ', $values); ?>  \tabularnewline <?php endif; ?>
<?php  endforeach;?>
\end{tabularx}
\end{table}

\end{document}
