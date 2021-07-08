<?php
use_helper('IvbdStatistique');
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
\usepackage{eurosym}
\usepackage[framemethod=tikz]{mdframed}

\def\LOGO{<?php echo sfConfig::get('sf_web_dir'); ?>/images/logo_ivbd.png}
\renewcommand{\arraystretch}{1.2}
\makeatletter
\setlength{\@fptop}{5pt}
\makeatother


\fancyhf{}
\renewcommand{\headrulewidth}{0cm}
\renewcommand\sfdefault{phv}
\renewcommand{\familydefault}{\sfdefault}
\fancyfoot[R]{1}
\fancyfoot[L]{<?php echo strftime("%e %B %Y", time()) ?>}
\fancyhead[L]{\includegraphics[scale=0.6]{\LOGO}}

\fancypagestyle{fstyle_0}{
\fancyhead[C]{Sorties <?php if ($periode): ?> - Période du \textbf{<?php echo $periode[0] ?>} au \textbf{<?php echo $periode[1] ?>}<?php endif; ?>}
}

\begin{document}

\pagestyle{fstyle_0}

\begin{table}[ht!]
\begin{tabularx}{\linewidth}{ | X | >{\centering\arraybackslash}p{0.08\linewidth} | >{\centering\arraybackslash}p{0.065\linewidth} | >{\centering\arraybackslash}p{0.075\linewidth} | >{\centering\arraybackslash}p{0.07\linewidth} | >{\centering\arraybackslash}p{0.065\linewidth} | >{\centering\arraybackslash}p{0.075\linewidth} | >{\centering\arraybackslash}p{0.07\linewidth} | >{\centering\arraybackslash}p{0.065\linewidth} | >{\centering\arraybackslash}p{0.075\linewidth} | }
\hline
\rowcolor{gray!40} ~ 					& Sorties sous & ~        & \textbf{Facturation} & Sorties hors             & ~        & \textbf{Facturation} & Total sorties         & ~        & \textbf{Facturation} \tabularnewline
\rowcolor{gray!40} \textbf{Appellations} & contrats & CVO & \textbf{attendue}  & Contrats & CVO & \textbf{attendue}  & réelles à & CVO & \textbf{Attendue} \tabularnewline
\rowcolor{gray!40} ~           & (vrac) hl     & \euro / hl & \textbf{ en \euro }  & (bouteilles) hl & \euro / hl & \textbf{ en \euro } & facturer en hl & \euro / hl & \textbf{ en \euro} \tabularnewline
\hline
<?php
	$i = 0;
    $bfArrayKeys = [0,3,6,9];
    $rightArrayKeys = [1,3,4,6,7,9];
	foreach ($items as $item):
    $values = explode(';', $item);
		foreach ($values as $key => $value) {
            if(is_numeric(str_replace(',','.',$value))){
                $values[$key] = number_format(str_replace(',','.',$values[$key]), 2, ',', ' ');
            }
			if(in_array($key,$bfArrayKeys)){
				$values[$key] = '\textbf{'.$values[$key].'}';
			}
            if(in_array($key,$rightArrayKeys)){
				$values[$key] = '\raggedleft '.$values[$key];
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
