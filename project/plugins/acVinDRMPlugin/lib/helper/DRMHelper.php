<?php

function produit_get_words($produits) {
    $words = array();

    foreach ($produits as $produit) {
        $words[produit_get_id($produit)] = produit_get_word($produit);
    }

    return $words;
}

function produit_get_word($produit) {
    return array_merge(
            Search::getWords($produit->mois), Search::getWords($produit->produit_libelle), array(str_replace(' ', '_', $produit->produit_libelle))
    );
}

function produit_get_id($produit) {

    return 'produit_' . $produit->periode . str_replace('/', '-', $produit->produit_hash);
}

function getDrmTitle($drm) {
    $annee = substr($drm->periode, 0, 4);
    $mois = substr($drm->periode, 4, 2);
    $date = $annee . '-' . $mois . '-01';
    return 'DRM - ' . format_date($date, "MMMM", "fr_FR") . ' ' . $annee;
}

function getFrPeriodeElision($periode) {
    $annee = substr($periode, 0, 4);
    $mois = substr($periode, 4, 2);
    $date = $annee . '-' . $mois . '-01';
    return elision('de', format_date($date, "MMMM", "fr_FR")) . ' ' . $annee;
}

function getNumberOfFirstProduitWithMovements($produits) {
    $cpt = 1;
    foreach ($produits as $produit) {
        if ($produit->hasMovements()) {
            return $cpt;
        }
        $cpt++;
    }
    return null;
}

function getClassGlobalEtatDRMCalendrier($isTeledeclarationMode, $calendrier, $periode, $etablissement = null) {
    $statut = $calendrier->getStatut($periode, $etablissement);

    if ($isTeledeclarationMode) {
        $statut = $calendrier->getStatutForAllEtablissements($periode);
    }

    if ($statut == DRMCalendrier::STATUT_VALIDEE) {

        return 'panel-success';
    }

    if (!$isTeledeclarationMode && $statut == DRMCalendrier::STATUT_VALIDEE_NON_TELEDECLARE) {

        return 'panel-success';
    }

    if (($statut == DRMCalendrier::STATUT_EN_COURS) || ($statut == DRMCalendrier::STATUT_EN_COURS_NON_TELEDECLARE)) {

        return 'panel-primary';
    }

    if (!$isTeledeclarationMode && $statut == DRMCalendrier::STATUT_EN_COURS_NON_TELEDECLARE) {
        return 'panel-primary';
    }

    return null;
}

function getClassButtonEtatDRMCalendrier($isTeledeclarationMode, $calendrier, $periode, $etablissement, $picto = false) {
    $statut = $calendrier->getStatut($periode, $etablissement);

    if ($isTeledeclarationMode && $picto && $statut == DRMCalendrier::STATUT_VALIDEE) {
        return 'btn-success';
    }

    if($statut == DRMCalendrier::STATUT_EN_COURS) {

        return "btn-warning";
    }

    if(!$isTeledeclarationMode && $statut == DRMCalendrier::STATUT_EN_COURS_NON_TELEDECLARE) {

        return "btn-warning";
    }

    return 'btn-default';
}

function getEtatDRMCalendrier($isTeledeclarationMode, $calendrier, $periode, $etablissement = false) {
    $statut = $calendrier->getStatut($periode, $etablissement);
    if ($statut == DRMCalendrier::STATUT_VALIDEE) {
        return 'Validée';
    }
    if ($statut == DRMCalendrier::STATUT_EN_COURS) {
        return 'En attente';
    }
    if ($statut == DRMCalendrier::STATUT_NOUVELLE) {
        return 'A créer';
    }
    if ($statut == DRMCalendrier::STATUT_NOUVELLE_BLOQUEE) {
        return "Saisie impossible";
    }
    if ($isTeledeclarationMode) {
        return 'Saisie interne';
    }
    if ($statut == DRMCalendrier::STATUT_VALIDEE_NON_TELEDECLARE) {
        return 'Validée';
    }
    if ($statut == DRMCalendrier::STATUT_EN_COURS_NON_TELEDECLARE) {
        return 'En attente';
    }
    return $statut;
}

function getTeledeclareeLabelCalendrier($isTeledeclarationMode, $calendrier, $periode, $etablissement = false) {
    if (isTeledeclareeCalendrier($isTeledeclarationMode, $calendrier, $periode) && isTransmiseDouane($isTeledeclarationMode, $calendrier, $periode)){
        if(isCoherenteDouane($isTeledeclarationMode, $calendrier, $periode)){
          return '(Douane OK)';
        }
        return '(Transmise)';
    }
    else if (isTeledeclareeCalendrier($isTeledeclarationMode, $calendrier, $periode))
        return  '(Téleclarée)';
    else if ($isTeledeclarationMode)
        return '';
    else {
        $a = $calendrier->getNumeroArchive($periode, $etablissement);
        if ($a)
            return '('.$a.')' ;
    }
}

function isTeledeclareeCalendrier($isTeledeclarationMode, $calendrier, $periode, $etablissement = false) {
    $statut = $calendrier->getStatut($periode, $etablissement);
    if (!$isTeledeclarationMode) {
        if ((($statut == DRMCalendrier::STATUT_VALIDEE) || ($statut == DRMCalendrier::STATUT_EN_COURS)) && $calendrier->isTeledeclare($periode)) {

            return true;
        }
    }
    return false;
}


function isTransmiseDouane($isTeledeclarationMode, $calendrier, $periode, $etablissement = false) {
  return $calendrier->getTransmise($periode, $etablissement);
}


function isCoherenteDouane($isTeledeclarationMode, $calendrier, $periode, $etablissement = false) {
  return $calendrier->getCoherente($periode, $etablissement);
}

function getEtatDRMHrefCalendrier($isTeledeclaration,$calendrier, $periode, $etablissement = false) {
    $etablissementId = ($etablissement) ? $etablissement->identifiant : $calendrier->getIdentifiant();
    $statut = $calendrier->getStatut($periode, $etablissement);
    $periode_version = $calendrier->getPeriodeVersion($periode, $etablissement);
    if ($statut == DRMCalendrier::STATUT_VALIDEE || ($statut == DRMCalendrier::STATUT_VALIDEE_NON_TELEDECLARE && !$isTeledeclaration)) {
        return url_for('drm_visualisation', array('identifiant' => $etablissementId, 'periode_version' => $periode_version));
    }
    if (($statut == DRMCalendrier::STATUT_EN_COURS) || ($statut == DRMCalendrier::STATUT_EN_COURS_NON_TELEDECLARE)) {
        return url_for('drm_redirect_etape', array('identifiant' => $etablissementId, 'periode_version' => $periode_version));
    }
    if ($statut == DRMCalendrier::STATUT_NOUVELLE) {
        if($isTeledeclaration){
           return '#drm_nouvelle_'.$periode . '_' . $etablissementId;
        }else{
            return url_for('drm_nouvelle', array('identifiant' => $etablissementId, 'periode' => $periode));
        }
    }
    return "";
}

function getEtatDRMLibelleCalendrier($isTeledeclarationMode, $calendrier, $periode, $etablissement = false, $picto = false) {
    $statut = $calendrier->getStatut($periode, $etablissement);
    if ($statut == DRMCalendrier::STATUT_VALIDEE) {

        return !$picto ? 'Voir la drm': '<span class="glyphicon glyphicon-ok"></span>';
    }
    if ($statut == DRMCalendrier::STATUT_EN_COURS) {

        return !$picto ? 'Continuer': '<span class="glyphicon glyphicon-pencil"></span>';
    }
    if ($statut == DRMCalendrier::STATUT_VALIDEE_NON_TELEDECLARE && !$isTeledeclarationMode) {

        return !$picto ? 'Voir la drm': '<span class="glyphicon glyphicon-ok"></span>';
    }
    if ($statut == DRMCalendrier::STATUT_VALIDEE_NON_TELEDECLARE && $isTeledeclarationMode) {

        return !$picto ? '&nbsp;': '<span style="opacity: 0.5;" class="glyphicon glyphicon-ban-circle"></span>';
    }
    if ($statut == DRMCalendrier::STATUT_EN_COURS_NON_TELEDECLARE) {

        return !$picto ? 'Continuer': '<span class="glyphicon glyphicon-pencil"></span>';
    }
    if ($statut == DRMCalendrier::STATUT_NOUVELLE) {

        return !$picto ? 'A créer': '<span class="glyphicon glyphicon-plus"></span>';
    }
    if ($statut == DRMCalendrier::STATUT_NOUVELLE_BLOQUEE) {

        return !$picto ? "Une DRM est en cours d'édition": '<span style="opacity: 0.5;" class="glyphicon glyphicon-ban-circle"></span>';
    }

    return $statut;
}

function getLibelleForGenre($genre) {
    if ($genre == 'TRANQ') {
        return 'TRANQUILLE';
    }
    return $genre;
}

function getLastDayForDrmPeriode($drm) {
    $dateFirst = new DateTime(substr($drm->periode, 0, 4) . '-' . substr($drm->periode, 4, 2) . '-01');
    return $dateFirst->format('t/m');
}
