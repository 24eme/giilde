<?php
use_helper('Date');
use_helper('Float');
use_helper('Display');
$criteres = $subvention->approbations->criteres;
$conclusionfavorable = $subvention->approbations->conclusionfavorable;
$conclusionrejet = $subvention->approbations->conclusionrejet;
?>
\documentclass[a4paper,oneside, portrait, 10pt]{extarticle}

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
\usepackage{amssymb}
\usepackage{geometry}
\usepackage{indentfirst}
\usetikzlibrary{fit}
\usepackage{enumitem}
\usepackage{pifont}
\newlist{todolist}{itemize}{2}
\setlist[todolist]{label=$\square$}

\newcommand{\cmark}{\ding{51}}%
\newcommand{\xmark}{\ding{55}}%
\newcommand{\done}{\rlap{$\square$}{\raisebox{2pt}{\large\hspace{1pt}\cmark}}%
\hspace{-2pt}}
\newcommand{\wontfix}{\rlap{$\square$}{\large\hspace{1pt}\cmark}}

\renewcommand\sfdefault{phv}
\newcommand{\cmark}{\ding{51}}%
\newcommand{\xmark}{\ding{55}}%
\newcommand{\squareChecked}{\rlap{$\square$}{\raisebox{2pt}{\large\hspace{1pt}\cmark}}}
\renewcommand{\familydefault}{\sfdefault}
\renewcommand{\TruncateMarker}{\small{...}}

\setlength{\oddsidemargin}{-2cm}
\setlength{\evensidemargin}{-2cm}
\setlength{\textwidth}{20cm}
\setlength{\textheight}{28.7cm}
\setlength{\topmargin}{-3.5cm}
\setlength{\footskip}{0cm}

\def\Plateforme{<?php echo escape_string_for_latex($subvention->getConfiguration()->getPlateforme()); ?>}
\def\Referent{<?php echo escape_string_for_latex($subvention->getConfiguration()->getReferent()); ?>}
\def\DeclarantRaisonSociale{<?php echo escape_string_for_latex($subvention->declarant->raison_sociale); ?>}
\def\DeclarantSiret{<?php echo $subvention->declarant->siret; ?>}
\def\DeclarantAdresse{<?php echo escape_string_for_latex($subvention->declarant->adresse); ?>}
\def\DeclarantCp{<?php echo $subvention->declarant->code_postal; ?>}
\def\DeclarantVille{<?php echo escape_string_for_latex($subvention->declarant->commune); ?>}
\def\DeclarantCapital{<?php echo escape_string_for_latex(sprintFloatFr($subvention->infos->economique->capital_social, "%01.02f", true). " €"); ?>}
\def\DeclarantEffectif{<?php echo escape_string_for_latex(str_replace(".", ",", $subvention->infos->economique->effectif)); ?>}
\def\DeclarantPermanent{<?php echo escape_string_for_latex(str_replace(".", ",", $subvention->infos->economique->effectif_permanent)); ?>}
\def\ContactDossierNom{<?php echo escape_string_for_latex($subvention->infos->contacts->nom); ?>}
\def\ContactDossierTel{<?php echo escape_string_for_latex($subvention->infos->contacts->telephone); ?>}
\def\ContactDossierEmail{<?php echo escape_string_for_latex($subvention->infos->contacts->email); ?>}
\def\DateSignature{<?php echo format_date($subvention->signature_date, 'D'); ?>}
\def\logos{<?php echo sfConfig::get('sf_web_dir'); ?>/images/logos_region_occitanie.jpg}

\pagestyle{fancy}
\renewcommand{\headrulewidth}{0pt}
\fancyhf{}

\title{\textbf{Contrat <?php if(!$subvention->isValideInterpro()): ?>Brouillon <?php endif; ?>Relance Viti \\ Fiche de pré-qualification}\vspace{0.5cm}}
\date{}

\def\arraystretch{3}
\setlength\extrarowheight{-3pt}
\newcolumntype{L}[1]{>{\raggedright\let\newline\\\arraybackslash\hspace{0pt}}m{#1}}
\newcolumntype{C}[1]{>{\centering\let\newline\\\arraybackslash\hspace{0pt}}m{#1}}
\newcolumntype{R}[1]{>{\raggedleft\let\newline\\\arraybackslash\hspace{0pt}}m{#1}}

\setlength{\textfloatsep}{0pt}

\fancyfoot[R]{\thepage ~/ 2}

\fancypagestyle{plain}{
\fancyfoot[R]{\thepage ~/ 2}
}

\begin{document}


\begin{figure}[t]
  \centering
  \includegraphics[width=19.5cm]{\logos}
\end{figure}

\maketitle

\vspace{-2cm}

\begin{tabular}{|L{3cm}|L{5.5cm}|L{3cm}|L{5.5cm}|}
\hline
\small{Portail} & \textbf{\Plateforme} & \small{Agent référent} & \textbf{\Referent} \\
\hline
\small{Date de réception de la demande} & \textbf{\DateSignature} & \small{Personne en charge du dossier au sein de l'entreprise} & \textbf{\ContactDossierNom} \\
\hline
\end{tabular}

\vspace{0.5cm}

\section{IDENTIFICATION DU DEMANDEUR}

Qualité du demandeur : <?php if ($subvention->getEtablissement()->isNegociant()): ?>\squareChecked<?php else: ?>$\square$<?php endif; ?> Négociant \medbreak
Metteur en marché direct : <?php if ($subvention->getEtablissement()->isCaveCooperative()): ?>\squareChecked<?php else: ?>$\square$<?php endif; ?> Cave coopérative <?php if ($subvention->getEtablissement()->isViticulteur()): ?>\squareChecked<?php else: ?>$\square$<?php endif; ?> Vigneron indépendant \bigbreak
Raison sociale : \textbf{\DeclarantRaisonSociale} \medbreak
SIRET : \textbf{\DeclarantSiret} \bigbreak
Adresse : \textbf{\DeclarantAdresse} \medbreak
Code Postal : \textbf{\DeclarantCp} \hspace{0.5cm} Ville : \textbf{\DeclarantVille} \bigbreak
<?php foreach($subvention->infos as $categorie => $items): ?>
\textbf{<?php echo $items->getLibelle() ?>} \medbreak
<?php foreach($items as $key => $item): ?>
<?php echo $items->getSchemaItem($key, "label") ?>~: \textbf{<?php echo $item ?>}~<?php echo $items->getSchemaItem($key, "unite") ?> \medbreak
<?php endforeach; ?>
<?php endforeach; ?>

<?php if ($subvention->signature_date): ?>

\section{ENGAGEMENTS DU BÉNÉFICIAIRE}

\begin{todolist}[itemsep=7pt,parsep=7pt]
<?php foreach ($subvention->getConfiguration()->getEngagements() as $key => $value): ?>
	<?php if ($subvention->engagements->exist($key)): ?>
	\item[\done] <?php echo $value ?>
	<?php else: ?>
	\item <?php echo $value ?>
	<?php endif; ?>
<?php endforeach; ?>
\end{todolist}

\newpage

~ \\

\vspace{0,6cm}


\section{CRITÈRES DE PRÉ-QUALIFICATION}

\begin{tabular}{|C{0.5cm}|L{10cm}|L{7cm}|}
\hline
  \multicolumn{2}{|c|}{\cellcolor{gray!25}CRITÈRES} & \cellcolor{gray!25} \hfill APPRÉCIATION\hfill\null  \\
\hline
  <?php if($criteres->respect_interpro): ?>\squareChecked<?php else: ?>$\square$<?php endif; ?> &
  Respect des accords interprofessionnels ou engagement &
  <?php if($criteres->respect_interpro_appreciation): echo $criteres->respect_interpro_appreciation; endif; ?> \\
\hline
  <?php if($criteres->attente_dossierautre): ?>\squareChecked<?php else: ?>$\square$<?php endif; ?> &
  Opérations concernant les vins conditionnés sous signe de qualité issus des AOP et IGP de la Région : \begin{enumerate}[itemsep=1pt,parsep=1pt] \item Pays d’OC/Terres du Midi \item AOC du Languedoc/IGP Sud de France \item Vins du Sud-Ouest \item Vins de la Vallée du Rhône \item Vins du Roussillon (AOP/IGP) \end{enumerate} &
   <?php if($criteres->attente_dossierautre_appreciation): echo $criteres->attente_dossierautre_appreciation; endif; ?> \\
\hline
  <?php if($criteres->negociant_contractualisation): ?>\squareChecked<?php else: ?>$\square$<?php endif; ?> &
   \underline{Pour les négociants}, contractualisation : \begin{todolist}[itemsep=1pt,parsep=1pt]
                                                            \item<?php if($criteres->negociant_contractualisation_effective): ?>[\done]<?php endif; ?> effective*
                                                            \item<?php if($criteres->negociant_contractualisation_engagement): ?>[\done]<?php endif; ?> engagement*
                                                            \end{todolist} \small{* Préciser obligatoirement les volumes concernés} &
  <?php if($criteres->negociant_contractualisation_appreciation): echo $criteres->negociant_contractualisation_appreciation; endif; ?>
    \\
\hline
 <?php if($criteres->conditions_eligibilite): ?>\squareChecked<?php else: ?>$\square$<?php endif; ?> &
 Eligibilité et appréciation de la faisabilité et de la cohérence des opérations présentées (adéquation coût/action...) &
  <?php if($criteres->conditions_eligibilite_appreciation): echo $criteres->conditions_eligibilite_appreciation; endif; ?> \\
\hline
\end{tabular}

\section{CONCLUSION}

\begin{tabular}{|L{8,75cm}|L{8,75cm}|}
\hline
<?php if($conclusionfavorable->favorable || $conclusionfavorable->partiellement_favorable): ?>\squareChecked<?php else: ?>$\square$<?php endif; ?> Favorable \begin{todolist}[itemsep=1pt,parsep=1pt]
                                                                                                    \item<?php if($conclusionfavorable->favorable): ?>[\done]<?php endif; ?> sur l'ensemble des actions
                                                                                                    \item<?php if($conclusionfavorable->partiellement_favorable): ?>[\done]<?php endif; ?> sur les actions n° <?php if($conclusionfavorable->partiellement_favorable): echo $conclusionfavorable->partiellement_favorable; endif; ?>\end{todolist}
                                                                                                    Commentaires : <?php if($conclusionfavorable->partiellement_favorable_commentaire): echo $conclusionfavorable->partiellement_favorable_commentaire; endif; ?> &
Numéro de dossier : <?php if($subvention->numero_archive): echo $subvention->numero_archive; endif; ?> \bigbreak Version du dossier : \begin{todolist}[itemsep=1pt,parsep=1pt] \item<?php if($subvention->version == 1): ?>[\done]<?php endif; ?> initiale \item<?php if($subvention->version > 1): ?>[\done]<?php endif; ?> modifiée \end{todolist}  \\
\hline
\multicolumn{2}{|l|}{ <?php if($conclusionrejet->motif_rejet): ?>\squareChecked<?php else: ?>$\square$<?php endif; ?>  Proposition de rejet} \\
\multicolumn{2}{|l|}{Motif : <?php if($conclusionrejet->motif_rejet): echo $conclusionrejet->motif_rejet; endif; ?>} \\
\multicolumn{2}{|l|}{~} \\
\multicolumn{2}{|l|}{~} \\
\hline
\end{tabular}

\bigbreak
\bigbreak

<?php if($subvention->isValideInterpro()): ?>
\textbf{Signé électroniquement le \DateSignature}
<?php else: ?>
\textbf{Dossier brouillon (en attente de qualification par l'interprofession)}
<?php endif; ?>
<?php endif; ?>

\end{document}
