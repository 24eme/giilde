<?php
use_helper('Date');
use_helper('DRM');
use_helper('Orthographe');
use_helper('DRMPdf');
use_helper('Display');
?>

\def\InterloireAdresse{<?php echo getAdresseInterpro(); ?>}
\def\InterloireContact{<?php echo getInfosInterpro($drm); ?>}

\def\DRMSocieteRaisonSociale{<?php echo $drm->societe->raison_sociale; ?>}
\def\DRMSocieteAdresse{<?php echo getDrmSocieteAdresse($drm); ?>}

\def\DRMAdresseChai{<?php echo getDrmEtablissementAdresse($drm); ?>}
\def\DRMAdresseCompta{}

\def\DRMNumAccise{<?php echo $drm->declarant->no_accises; ?>}
\def\DRMCvi{<?php echo $drm->declarant->cvi; ?>}

\def\DRMSiret{<?php echo $drm->societe->siret; ?>}
\def\DRMIdentifiantIL{<?php echo $drm->identifiant; ?>}
\def\DRMAdresseComptaMatiere{<?php echo ($drm->declarant->adresse_compta)? $drm->declarant->adresse_compta : $drm->societe->raison_sociale; ?>}

\pagestyle{fancy}
\renewcommand{\headrulewidth}{0pt}
\fancyhf{}

\lhead{
\vspace{-2cm}
Raison sociale : \textbf{\DRMSocieteRaisonSociale} \\
Adresse du siège de l’Entrepôt : \textbf{\DRMAdresseChai} \\
Code client : \textbf{\DRMIdentifiantIL}~~~CVI : \textbf{\DRMCvi}~~~Siret : \textbf{\DRMSiret} \\
Numéro d'Accise : \textbf{\DRMNumAccise} \\
Adresse compta matière : \textbf{\DRMAdresseComptaMatiere} \\
}

\rhead{
\vspace{-2cm}
\InterloireAdresse
 \begin{small} \InterloireContact \\
 \end{small}
 \begin{large}
\textbf{DRM <?php echo getFrPeriodeElision($drm->periode); ?>} \\
\textbf{<?php if($drm->isValidee()): ?>Signé électroniquement le <?php echo $drm->getEuValideDate(); ?><?php endif; ?>}
\end{large}
}

\rfoot{page \thepage\ / <?php echo $nbPages ?>}
