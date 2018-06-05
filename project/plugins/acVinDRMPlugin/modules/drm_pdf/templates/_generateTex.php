\documentclass[a4paper,oneside, landscape, 9pt]{extarticle}

\usepackage[english]{babel}
\usepackage[utf8]{inputenc}
\usepackage{units}
\usepackage{graphicx}
\usepackage{fp}
\usepackage[table]{xcolor}
\usepackage{lscape}
\usepackage{eso-pic}
\usepackage{tikz}
\usepackage{array,multirow,makecell}
\usepackage{multicol}
\usepackage{textcomp}
\usepackage{marvosym}
\usepackage{lastpage}
\usepackage{truncate}
\usepackage{fancyhdr}
\usepackage{lastpage}
\usepackage{amssymb}
\usepackage{geometry}

\usetikzlibrary{fit}

\renewcommand\sfdefault{phv}
\newcommand{\squareChecked}{\makebox[0pt][l]{$\square$}\raisebox{.15ex}{\hspace{0.1em}$\checkmark$}}
\renewcommand{\familydefault}{\sfdefault}
\renewcommand{\TruncateMarker}{\small{...}}

\usepackage{array}
\newcolumntype{L}[1]{>{\raggedright\let\newline\\\arraybackslash\hspace{0pt}}m{#1}}
\newcolumntype{C}[1]{>{\centering\let\newline\\\arraybackslash\hspace{0pt}}m{#1}}
\newcolumntype{R}[1]{>{\raggedleft\let\newline\\\arraybackslash\hspace{0pt}}m{#1}}

\newcommand\BackgroundPic{
\put(0,0){
\parbox[b][\paperheight]{\paperwidth}{%
\vfill
\centering
\includegraphics[width=\paperwidth,height=\paperheight,
keepaspectratio]{<?php echo realpath(dirname(__FILE__) . "/../../../../../web/data") . "/img_brouillon.png"; ?>}%
\vfill
}}}

\setlength{\oddsidemargin}{-2cm}
\setlength{\evensidemargin}{-2cm}
\setlength{\textwidth}{29.7cm}
\setlength{\textheight}{15.5cm}
\setlength{\headheight}{4.5cm}
\setlength{\headwidth}{28.2cm}
\setlength{\topmargin}{-3.5cm}
\setlength{\footskip}{0cm}


<?php include_partial('drm_pdf/generateEnteteTex', array('drm' => $drm, 'nbPages' => $nbPages)); ?>
\begin{document}{~}

<?php if (!$drm->isValidee()): ?>
\AddToShipoutPicture{\BackgroundPic}
<?php endif; ?>

<?php foreach (DRMClient::$types_libelles as $typeDetailsNodes => $libelle) : ?>
  <?php include_partial('drm_pdf/generateRecapMvtTex', array('drm' => $drm,'drmLatex' => $drmLatex, 'detailsNodes' => $typeDetailsNodes, "libelleDetail" => $libelle)); ?>
<?php endforeach; ?>
<?php include_partial('drm_pdf/generateCRDTex', array('drm' => $drm)); ?>
<?php include_partial('drm_pdf/generateDroitsDouaneTex', array('drm' => $drm)); ?>
\end{document}
