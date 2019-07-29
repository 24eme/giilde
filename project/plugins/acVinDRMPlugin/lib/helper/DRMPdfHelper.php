<?php

function getCheckBoxe($b) {
    return ($b) ? '\squareChecked' : '$\square$';
}

function getInfosInterpro() {
    return sfConfig::get('app_teledeclaration_contact_drm');
}

function getAdresseInterpro() {
    return sfConfig::get('app_teledeclaration_adresse_interpro');
}

function getDrmContact($drm) {
    $infosInterloire = getInfosInterpro();
    $infosInterloireRegion = $infosInterloire[$drm->region];
    $drmContact = "Votre contact : " . $infosInterloireRegion['nom'] . ' - Tél: ' . $infosInterloireRegion['telephone'];
    $drmContact .= '\\\\ Email : ' . $infosInterloireRegion['email'];
    return $drmContact;
}

function getDrmSocieteAdresse($drm) {
    return $drm->societe->adresse . ' ' . $drm->societe->code_postal . ' ' . $drm->societe->commune;
}

function getDrmEtablissementAdresse($drm) {
    return $drm->declarant->adresse . ' ' . $drm->declarant->code_postal . ' ' . $drm->declarant->commune;
}

function sprintFloat($float, $format = "%01.02f") {
    if (is_null($float))
        $float = 0.00;
    return sprintf($format, $float);
}

function echoFloatWithHl($float) {
    if(!$float){
        echo '';

        return;
    }

    echo FloatHelper::getInstance()->format($float).' hl';
}


function getArialNumber($number) {
    return number_format($number, 0, '.', ' ');
}


function sprintDroitDouane($float) {
    if (is_null($float))
        $float = 0;
    return round($float);
}
