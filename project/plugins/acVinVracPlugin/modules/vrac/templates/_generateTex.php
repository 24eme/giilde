<?php use_helper('Date') ?>
\documentclass[a4paper,8pt]{extarticle}
\usepackage{geometry} % paper=a4paper
\usepackage[frenchb]{babel}
\usepackage[utf8]{inputenc}
\usepackage{units}
\usepackage{geometry}
\usepackage{graphicx}
\usepackage{fp}
\usepackage[table]{xcolor}
\usepackage{multicol}
\usepackage{textcomp}
\usepackage{marvosym}
\usepackage{truncate}
\usepackage{tabularx}
\usepackage{multirow}
\usepackage{amssymb}
\usepackage{ulem}
\usepackage{fmtcount}
\usepackage{eso-pic}

\makeatletter
\newlength\@tempdim@x
\newlength\@tempdim@y

\newcommand\AtLowerLeftCorner[3]{%
\begingroup
\@tempdim@x=0cm
\@tempdim@y=0cm
\advance\@tempdim@x#1
\advance\@tempdim@y#2
\put(\LenToUnit{\@tempdim@x},\LenToUnit{\@tempdim@y}){#3}%
\endgroup
}


\makeatother


\pagestyle{empty}

\renewcommand\sfdefault{phv}
\renewcommand{\familydefault}{\sfdefault}
\renewcommand{\TruncateMarker}{\small{...}}

\newcommand{\euro}{\EUR\xspace}

\newcommand{\squareChecked}{\makebox[0pt][l]{$\square$}\raisebox{.15ex}{\hspace{0.1em}$\checkmark$}}

\setlength{\oddsidemargin}{-1cm}
\setlength{\evensidemargin}{-1cm}
\setlength{\textwidth}{18cm}
\setlength{\textheight}{27.9cm}
\setlength{\topmargin}{-3cm}
\setlength{\parindent}{0pt}



\def\IVSOCOORDONNEESTITRE{}
\def\IVSOCOORDONNEESADRESSE{}
\def\IVSOCOORDONNEESCPVILLE{}
\def\IVSOCOORDONNEESFAX{}
\def\IVSOCOORDONNEESEMAIL{}

\def\CONTRATNUMENREGISTREMENT{<?php echo substr($vrac->numero_contrat, -6)?>}
\def\CONTRATVISA{<?php echo $vrac->numero_archive ?>}
\def\CONTRATDATEENTETE{<?php echo format_date($vrac->valide->date_saisie) ?>}

\def\CONTRAT_TITRE{CONTRAT D'ACHAT EN PROPRIETE <?php if($vrac->type_transaction == VracClient::TYPE_TRANSACTION_VIN_VRAC): ?>de vin AOP et IGP<?php elseif($vrac->type_transaction == VracClient::TYPE_TRANSACTION_MOUTS): ?>de Moût<?php else: ?>de Vendange<?php endif; ?>}
\def\CONTRATSOUSTITRE{}
<?php
$vendeur_raison_sociale = ($vrac->vendeur->raison_sociale) ?
        $vrac->vendeur->raison_sociale : $vrac->getVendeurObject()->getSociete()->raison_sociale;

$acheteur_raison_sociale = ($vrac->acheteur->raison_sociale) ?
        $vrac->acheteur->raison_sociale : $vrac->getAcheteurObject()->getSociete()->raison_sociale;

$mandataire_raison_sociale = "";
if ($vrac->mandataire_exist) {
    $mandataire_raison_sociale = ($vrac->mandataire->raison_sociale) ?
            $vrac->mandataire->raison_sociale : $vrac->getMandataireObject()->getSociete()->raison_sociale;
}
?>



\def\CONTRATVENDEURNOM{<?php echo $vendeur_raison_sociale ?><?php if ($vrac->responsable == 'vendeur'): ?> (responsable)<?php endif; ?>}
\def\CONTRATVENDEURCVI{<?php echo $vrac->vendeur->cvi ?>}
\def\CONTRATVENDEURACCISES{<?php echo $vrac->vendeur->no_accises ?>}
\def\CONTRATVENDEURADRESSE{<?php echo $vrac->vendeur->adresse ?>}
\def\CONTRATVENDEURCOMMUNE{<?php echo $vrac->vendeur->code_postal.' '.$vrac->vendeur->commune ?>}


\def\CONTRATACHETEUREURNOM{<?php echo $acheteur_raison_sociale ?><?php if ($vrac->responsable == 'acheteur'): ?> (responsable)<?php endif; ?>}
\def\CONTRATACHETEURCVI{<?php echo $vrac->acheteur->cvi ?>}
\def\CONTRATACHETEURACCISES{<?php echo $vrac->acheteur->no_accises ?>}
\def\CONTRATACHETEURADRESSE{<?php echo $vrac->acheteur->adresse ?>}
\def\CONTRATACHETEURCOMMUNE{<?php echo $vrac->acheteur->code_postal.' '.$vrac->acheteur->commune ?>}

\def\CONTRATCOURTIERNOM{<?php echo $mandataire_raison_sociale ?><?php if ($vrac->responsable == 'mandataire'): ?> (responsable)<?php endif; ?>}
\def\CONTRATCOURTIERCARTEPRO{, n° carte professionnelle:~<?php echo $vrac->mandataire->carte_pro ?>}

\def\CONTRATTYPE{Moûts}
\def\CONTRATTYPEUNITE{hl}
\def\CONTRATPRODUITNATURE{<?php if ($vrac->type_transaction == VracClient::TYPE_TRANSACTION_MOUTS): ?>Moûts<?php else: ?><?php if (preg_match('/AOC/', $vrac->produit)): ?>AOP<?php elseif (preg_match('/IGP/', $vrac->produit)): ?>IGP<?php endif;?><?php endif;?>}
\def\CONTRATPRODUITLIBELLE{<?php echo $vrac->produit_libelle ?>}
\def\CONTRATPRODUITCEPAGE{<?php echo $vrac->cepage_libelle ?>}
\def\CONTRATPRODUITMILLESIME{<?php echo $vrac->millesime ?>}
\def\CONTRATPRODUITDEGRE{<?php echo ($vrac->degre)? $vrac->degre.'°' : ''; ?>}
\def\CONTRATPRODUITQUANTITE{<?php echo ($vrac->jus_quantite)? $vrac->jus_quantite : $vrac->raisin_quantite ?>}
\def\CONTRATPRIXUNITAIRE{<?php echo $vrac->prix_initial_unitaire ?>}
\def\CONTRATTYPEEXPLICATIONPRIX{Le prix payé est exprimé en euros par hectolitre}

\def\CONTRATDATEMAXENLEVEMENT{<?php echo format_date($vrac->date_limite_retiraison) ?>}
\def\CONTRATDATEMINENLEVEMENT{<?php echo format_date($vrac->date_debut_retiraison) ?>}
\def\CONTRATOBSERVATIONS{<?php echo $vrac->conditions_particulieres ?>}
\def\CONTRATFRAISDEGARDE{ ~~~~~\euro/hl}

\def\CONTRATMOYENPAIEMENT{<?php echo $vrac->moyen_paiement_libelle ?>}
\def\CONTRATDELAIPAIEMENT{<?php echo $vrac->delai_paiement_libelle ?>}
\def\CONTRATACOMPTE{<?php echo $vrac->acompte ?>}
\def\CONTRATLIEUPRODUIT{<?php echo ($vrac->logement)? $vrac->logement : $vrac->vendeur->commune ?>}

\def\CONTRATLIEUCREATION{LES VERCHERS SUR LAYON}
\def\CONTRATDATECREATION{02/09/2015}

\def\CONTRATMANDATAIREVISA{<?php echo format_date($vrac->date_signature) ?>}
\def\CONTRATDATESIGNATUREVENDEUR{<?php echo format_date($vrac->date_signature) ?>}
\def\CONTRATDATESIGNATUREACHETEUR{<?php echo format_date($vrac->date_signature) ?>}

\def\CONTRATGENERIQUEDOMAINE{<?php echo $vrac->renderLabels(); ?>}
\def\CONTRATCONDITIONNEMENT{<?php if ($vrac->conditionnement_crd == 'NEGOCE_ACHEMINE'): ?>\textbf{P} : Vin préparé pour la mise en bouteille<?php elseif ($vrac->conditionnement_crd == 'ACHAT_TIRE_BOUCHE'): ?>\textbf{TB} : Tiré Bouché<?php else: ?>\textbf{N} : Vin non préparé<?php endif;?>}

\begin{document}


\begin{tabularx}{\textwidth}{c p{97mm} |p{37mm}}

	~ & ~ & ~  \\
	 \multirow{7}{*}{} &
	 \multicolumn{1}{c|}{\textbf{\IVSOCOORDONNEESTITRE}} &
	 N° Bordereau :  \textbf{\LARGE{\CONTRATNUMENREGISTREMENT}} \\
	 ~ & ~ & ~  \\
	 ~ & \multicolumn{1}{c|}{\IVSOCOORDONNEESADRESSE} &  Visa : \textbf{\LARGE{\CONTRATVISA}}  \\
	 ~ &\multicolumn{1}{c|}{\IVSOCOORDONNEESCPVILLE}  & ~ \\
	 ~ & \multicolumn{1}{c|}{\small{\IVSOCOORDONNEESFAX}}  &  ~ \\
	 ~ & \multicolumn{1}{c|}{\small{\IVSOCOORDONNEESEMAIL}}  & Le : \textbf{\CONTRATDATEENTETE}  \\
	 ~ & ~ & ~  \\
	  ~ & ~ & ~  \\

\end{tabularx}

\vspace{0.4cm}
  \begin{center}
   	\begin{huge}
   		\CONTRAT_TITRE
	\end{huge}
	\\ ~ \\
   	\begin{large}
   		\CONTRATSOUSTITRE
	\end{large}
    \end{center}

     Entre les soussignés,
\begin{multicols}{2}

\begin{minipage}[t]{0.485\textwidth}
\begin{tabularx}{\textwidth}{|Xr|}
	\hline
         ~ & ~ \\
	 \multicolumn{2}{|c|}{\textbf{\CONTRATVENDEURNOM}} \\
         ~ & ~ \\
	 C.V.I. & \textbf{\CONTRATVENDEURCVI} \\
         N° d'ACCISE & \textbf{\CONTRATVENDEURACCISES} \\
	 Adresse & \textbf{\CONTRATVENDEURADRESSE} \\
        Commune & \textbf{\CONTRATVENDEURCOMMUNE} \\
	 ~ & ~ \\
	 \multicolumn{2}{|r|}{Ci après dénommé le vendeur,} \\

	\hline
\end{tabularx}
\end{minipage}

\begin{minipage}[t]{0.485\textwidth}
\begin{tabularx}{\textwidth}{|Xr|}
	\hline
         ~ & ~ \\
	 \multicolumn{2}{|c|}{\textbf{\CONTRATACHETEUREURNOM}} \\
         ~ & ~ \\
         C.V.I. & \textbf{\CONTRATACHETEURCVI} \\
             N° d'ACCISE & \textbf{\CONTRATACHETEURACCISES} \\
	     Adresse & \textbf{\CONTRATACHETEURADRESSE} \\
         Commune & \textbf{\CONTRATACHETEURCOMMUNE} \\
         ~ & ~ \\
	 \multicolumn{2}{|r|}{Ci après dénommé l'acheteur,}\\
	 \hline
\end{tabularx}
\end{minipage}
\end{multicols}

<?php if ($vrac->mandataire_identifiant): ?>
Par l'entremise de \CONTRATCOURTIERNOM, Courtier en vins\CONTRATCOURTIERCARTEPRO \\
<?php endif; ?>

A été conclu le marché suivant: \\

<?php if($vrac->type_transaction == VracClient::TYPE_TRANSACTION_RAISINS): ?>
\begin{tabularx}{\textwidth}{|X|p{13mm}|p{13mm}|p{13mm}|p{13mm}|}
\hline
~ & ~ & ~ & ~ & ~  \\
\textbf{Cépage} & \multicolumn{1}{c|}{\textbf{Nature}} & \multicolumn{1}{c|}{\textbf{Quantité prévisionnelle}} & \multicolumn{1}{c|}{\textbf{Prix}} & \textbf{Quantité définitive} \\
~ & ~ & ~ & ~ & ~  \\
\hline
~ & ~ & ~ & ~ & ~  \\

\large{\CONTRATPRODUITCEPAGE}  & \multicolumn{1}{c|}{\CONTRATPRODUITNATURE}  &  \multicolumn{1}{c|}{ \large{\CONTRATPRODUITQUANTITE~\normalsize{\CONTRATTYPEUNITE}}} & \multicolumn{1}{c|}{\large{\CONTRATPRIXUNITAIRE~\normalsize{\euro/\CONTRATTYPEUNITE}}} & ~ \\

\multicolumn{1}{|l|}{\textit{\CONTRATGENERIQUEDOMAINE}}  & ~ & ~ & ~ & ~  \\
~ & ~ & ~ & ~ & ~  \\
\hline
\end{tabularx}
<?php else: ?>
\begin{tabularx}{\textwidth}{|p{60mm}|p{13mm}|p{13mm}|p{13mm}|p{13mm}|X|}
\hline
~ & ~ & ~ & ~ & ~ & ~  \\
\textbf{Produit} & \multicolumn{1}{c|}{\textbf{Degré}} & \multicolumn{1}{c|}{\textbf{Année}} & \multicolumn{1}{c|}{\textbf{Volume}} & \multicolumn{1}{c|}{\textbf{Prix}} & \textbf{Cépage} \\
~ & ~ & ~ & ~ & ~ & ~  \\
\hline
~ & ~ & ~ & ~ & ~ & ~  \\

\CONTRATPRODUITNATURE ~ \large{\CONTRATPRODUITLIBELLE}  & \multicolumn{1}{c|}{\large{\CONTRATPRODUITDEGRE}} & \multicolumn{1}{c|}{\large{\CONTRATPRODUITMILLESIME}} &  \multicolumn{1}{c|}{ \large{\CONTRATPRODUITQUANTITE~\normalsize{\CONTRATTYPEUNITE}}} & \multicolumn{1}{c|}{\large{\CONTRATPRIXUNITAIRE~\normalsize{\euro/\CONTRATTYPEUNITE}}} & \CONTRATPRODUITCEPAGE \\
\multicolumn{1}{|l|}{\textit{\CONTRATCONDITIONNEMENT}}  & ~ & <?php if ($vrac->exist('85_15')): ?>\multicolumn{1}{c|}{\textit{85/15(\%)}}<?php else: ?>~<?php endif; ?> & ~  & ~ & ~ \\
\multicolumn{1}{|l|}{\textit{\CONTRATGENERIQUEDOMAINE}}  & ~ & ~ & ~  & ~ & ~ \\
~ & ~ & ~ & ~ & ~ & ~  \\
\hline
\end{tabularx}
<?php endif; ?>
\\ ~ \\ ~ \\
Ce vin est logé dans la commune de : \textbf{\CONTRATLIEUPRODUIT}
\\ ~ \\
\textbf{Clause de réserve de proriété:}~~~~\textbf{OUI}~ <?php if ($vrac->clause_reserve_propriete): ?>\squareChecked<?php else: ?>$\square$<?php endif; ?> ~~~\textbf{NON}~ <?php if (!$vrac->clause_reserve_propriete): ?>\squareChecked<?php else: ?>$\square$<?php endif; ?>
\\
\begin{multicols}{2}

\begin{minipage}[t]{0.485\textwidth}

\textbf{\normalsize{\underline{Condition de paiement} :}} \\ ~ \\
<?php if ($vrac->acompte): ?>
Acompte à la signature : \textbf{\CONTRATACOMPTE}~\euro \\
<?php endif; ?>
Moyen de paiement : \textbf{\CONTRATMOYENPAIEMENT}\\
Delai de paiement : \textbf{\CONTRATDELAIPAIEMENT}\\
\end{minipage}

\begin{minipage}[t]{0.485\textwidth}

\textbf{\normalsize{\underline{Condition de retiraison} :}} \\ ~ \\
Date de début de retiraison : \textbf{\CONTRATDATEMINENLEVEMENT}\\
Date de fin de retiraison : \textbf{\CONTRATDATEMAXENLEVEMENT}\\

\end{minipage}
\end{multicols}

 ~ \\

\textbf{OBSERVATIONS:} \\
\fbox{
\parbox{17.7cm}{~ \\ \CONTRATOBSERVATIONS \\ }
}

 ~ \\ ~ \\ ~ \\

\begin{tabularx}{\textwidth}{<?php if ($vrac->mandataire_identifiant): ?>|X<?php endif; ?>|X|X|}
\hline
<?php if ($vrac->mandataire_identifiant): ?>\cellcolor{gray!25}\textbf{Le courtier} & <?php endif; ?>\cellcolor{gray!25}\textbf{Le vendeur} & \cellcolor{gray!25}\textbf{L'acheteur} \\
\hline
<?php if ($vrac->mandataire_identifiant): ?>~ & <?php endif; ?>~ & ~ \\
<?php if ($vrac->mandataire_identifiant): ?>Le \CONTRATMANDATAIREVISA, & <?php endif; ?>Le \CONTRATDATESIGNATUREVENDEUR, & Le \CONTRATDATESIGNATUREACHETEUR, \\
<?php if ($vrac->mandataire_identifiant): ?>~ & <?php endif; ?>~ & ~ \\
<?php if ($vrac->mandataire_identifiant): ?>\textbf{Signé électroniquement} & <?php endif; ?>\textbf{Signé électroniquement} & \textbf{Signé électroniquement} \\
\hline
\end{tabularx}

\end{document}
