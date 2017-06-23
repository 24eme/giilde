<?php

function display_teledeclaration_soussigne_NomCvi($object) {
    if (!$object) {
        echo "";
        return;
    }
    $result = $object->nom;
    if ($object->cvi) {
        $result.= '&nbsp(' . $object->cvi . ')';
    }
    echo $result;
}

function getCvoLabels($label) {
    $cvo_nature = array(VracClient::CVO_NATURE_MARCHE_DEFINITIF => 'Marché définitif',
        VracClient::CVO_NATURE_COMPENSATION => 'Compensation',
        VracClient::CVO_NATURE_NON_FINANCIERE => 'Non financière',
        VracClient::CVO_NATURE_VINAIGRERIE => 'Vinaigrerie');
    return $cvo_nature[$label];
}

function dateCampagneViticolePresent() {
    $date = date('mY');
    $mois = substr($date, 0, 2);
    $annee = substr($date, 2, 6);
    $campagne = ($mois < 8) ? ($annee - 1) . '/' . $annee : $annee . '/' . ($annee + 1);
    return $campagne;
}

function dateCampagneViticole($date) {
    $date_exploded = explode("/", $date);
    $mois = $date_exploded[1];
    $annee = $date_exploded[2];
    $campagne = ($mois < 8) ? ($annee - 1) . '/' . $annee : $annee . '/' . ($annee + 1);
    return $campagne;
}

function isARechercheParam($actifs, $label) {
    return in_array($label, $actifs);
}

function statusCssClass($status) {

    if (is_null($status))
        return '';

    switch ($status) {
        case VracClient::STATUS_CONTRAT_ANNULE:
            return 'danger';
        case VracClient::STATUS_CONTRAT_SOLDE:
            return 'success';
        case VracClient::STATUS_CONTRAT_NONSOLDE:
            return 'warning';
        default :
            return '';
    }
}

function formatNumeroBordereau($numero) {

    return preg_replace('/^(....)(.)/', '\1&nbsp;\2', $numero);
}

function typeToPictoCssClass($type) {

    if (is_null($type))
        return '';

    switch ($type) {
        case VracClient::TYPE_TRANSACTION_RAISINS:
            return 'icon-raisins';
        case VracClient::TYPE_TRANSACTION_MOUTS:
            return 'icon-mouts';
        case VracClient::TYPE_TRANSACTION_VIN_VRAC:
            return 'icon-vrac';
        case VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE:
            return 'icon-bouteille';
        default :
            return '';
    }
}

function tooltipForPicto($type) {

    if (is_null($type))
        return '';

    switch ($type) {
        case VracClient::TYPE_TRANSACTION_RAISINS:
            return 'Vendanges Fraîches';
        case VracClient::TYPE_TRANSACTION_MOUTS:
            return 'Moûts';
        case VracClient::TYPE_TRANSACTION_VIN_VRAC:
            return 'Vrac retiraison Vrac';
        case VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE:
            return 'Vrac retiraison Bouteilles';
        default :
            return '';
    }
}

function showRecapPrixUnitaire($vrac) {
    $unite = showPrixUnitaireUnite($vrac);

    if ($vrac->hasPrixVariable() && !$vrac->hasPrixDefinitif()) {
        return sprintf("%s (Prix non définitif)", showRecapPrixUnitaireByUniteAndPrix($unite, $vrac->prix_unitaire, $vrac->getPrixUnitaireHlOuInitial()));
    } elseif ($vrac->hasPrixVariable() && $vrac->hasPrixDefinitif()) {
        return sprintf("%s (Prix initial : %s)", showRecapPrixUnitaireByUniteAndPrix($unite, $vrac->prix_unitaire, $vrac->getPrixUnitaireHlOuInitial()), showRecapPrixUnitaireByUniteAndPrix($unite, $vrac->prix_initial_unitaire, $vrac->prix_initial_unitaire_hl));
    }
    return showRecapPrixUnitaireByUniteAndPrix($unite, $vrac->prix_unitaire, $vrac->getPrixUnitaireHlOuInitial());
}

function showRecapPrixUnitaireByUniteAndPrix($unite, $prix_unitaire, $prix_unitaire_hl) {
    if ($unite == '€/hl') {
        return sprintf('%s €/hl', echoF4($prix_unitaire));
    }

    return sprintf('%s %s, soit %s €/hl', echoF4($prix_unitaire), $unite, echoF($prix_unitaire_hl));
}

function showPrixUnitaireUnite($vrac) {
    switch ($vrac->type_transaction) {
        case VracClient::TYPE_TRANSACTION_RAISINS:
            return '€/kg';
        case VracClient::TYPE_TRANSACTION_MOUTS:
            return '€/hl';
        case VracClient::TYPE_TRANSACTION_VIN_VRAC:
            return '€/hl';
        case VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE:
            return '€/btle';
    }
}

function showRecapPrixTotal($vrac) {
    if ($vrac->hasPrixVariable() && !$vrac->hasPrixDefinitif()) {

        return sprintf('%s € (Prix non définitif)', echoF4($vrac->getPrixTotalOuInitial()));
    }

    if ($vrac->hasPrixVariable() && $vrac->hasPrixDefinitif()) {

        return sprintf('%s € (Prix initial : %s €)', echoF4($vrac->getPrixTotal()), echoF4($vrac->prix_initial_total));
    }

    return sprintf('%s €', echoF4($vrac->getPrixTotalOuInitial()));
}

function showType($vrac) {
    if ($type = $vrac->type_transaction) {
        return showTypeFromLabel($type, '', $vrac);
    }
    return '';
}

function showTypeFromLabel($type, $prefix = 'Type de transaction : ', $vrac = null) {
    switch ($type) {
        case VracClient::TYPE_TRANSACTION_VIN_VRAC: return $prefix . 'vin en vrac';
        case VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE: return $prefix . 'vrac retiraison bouteille';
        case VracClient::TYPE_TRANSACTION_RAISINS: return $prefix . 'vendange fraiche';
        case VracClient::TYPE_TRANSACTION_MOUTS: return $prefix . 'moûts';
        default: return $type;
    }
}

function showRecapVolumePropose($vrac) {
    if ($type = $vrac->type_transaction) {
        switch ($type) {
            case VracClient::TYPE_TRANSACTION_RAISINS:
                return echoF($vrac->raisin_quantite) . ' kg (raisins), soit ' . echoF($vrac->volume_propose) . ' hl';
            case VracClient::TYPE_TRANSACTION_MOUTS: return echoF($vrac->volume_propose) . ' hl (moûts)';
            case VracClient::TYPE_TRANSACTION_VIN_VRAC: return echoF($vrac->volume_propose) . ' hl (vrac)';
            case VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE:
                $libelle = (strstr($vrac->bouteilles_contenance_libelle, 'Bouteille'))?
                        str_replace('Bouteille', 'bouteilles de', $vrac->bouteilles_contenance_libelle) : $vrac->bouteilles_contenance_libelle ;

                return echoF($vrac->bouteilles_quantite) .
                        ' ' . $libelle . ', soit ' . echoF($vrac->volume_propose) . ' hl';
        }
    }
    return '';
}

function showUnite($vrac) {
    if ($type = $vrac->type_transaction) {
        switch ($type) {
            case VracClient::TYPE_TRANSACTION_RAISINS: return 'kg';
            case VracClient::TYPE_TRANSACTION_MOUTS: return 'hl';
            case VracClient::TYPE_TRANSACTION_VIN_VRAC: return 'hl';
            case VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE: {
                    return bouteilleUnitTerm($vrac, true);
                }
        }
    }
    return '';
}

function typeProduit($type) {
    switch ($type) {
        case VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE :
            return 'Btl';
        case VracClient::TYPE_TRANSACTION_VIN_VRAC :
            return 'V';
        case VracClient::TYPE_TRANSACTION_MOUTS :
            return 'M';
        case VracClient::TYPE_TRANSACTION_RAISINS :
            return 'R';
    }
    return '';
}

function typeProduitIcon($type) {
    switch ($type) {
        case VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE :
            return 'icon-bouteille';
        case VracClient::TYPE_TRANSACTION_VIN_VRAC :
            return 'icon-vrac';
        case VracClient::TYPE_TRANSACTION_MOUTS :
            return 'icon-mouts';
        case VracClient::TYPE_TRANSACTION_RAISINS :
            return 'icon-raisins';
    }
    return '';
}

function echoF($f) {
    return sprintf("%01.02f", round($f, 2));
}

function echoF4($f) {
    return sprintf("%01.04f", round($f, 4));
}

function vrac_get_words($vracs) {
    $words = array();

    foreach ($vracs as $vrac) {
        $words[vrac_get_id($vrac)] = vrac_get_word($vrac);
    }

    return $words;
}

function vrac_get_word($vrac) {

    $num_archive = ($vrac->value[VracClient::VRAC_VIEW_NUMARCHIVE]) ? $vrac->value[VracClient::VRAC_VIEW_NUMARCHIVE] : '';
    $libelle_produit = ($vrac->value[VracClient::VRAC_VIEW_PRODUIT_ID]) ? ConfigurationClient::getCurrent()->get($vrac->value[VracClient::VRAC_VIEW_PRODUIT_ID])->getLibelleFormat() : "";
    return array_merge(
            Search::getWords($num_archive), Search::getWords($vrac->value[VracClient::VRAC_VIEW_VENDEUR_NOM]), Search::getWords($vrac->value[VracClient::VRAC_VIEW_ACHETEUR_NOM]), Search::getWords($vrac->value[VracClient::VRAC_VIEW_MANDATAIRE_NOM]), Search::getWords($vrac->value[VracClient::VRAC_VIEW_MANDATAIRE_NOM]), Search::getWords($libelle_produit)
    );
}

function vrac_get_id($vrac) {

    return 'vrac_' . $vrac->value[VracClient::VRAC_VIEW_NUMCONTRAT];
}

function formatQuantite($vrac, $dec_point = ".", $thousands_sep = "") {
    $quantite = $vrac->getQuantite();
    $decimals = 0;
    $nb_decimals = strlen(preg_replace("/^[0-9]+\.?/", "", $quantite));
    if ($nb_decimals > 0) {
        $decimals = 2;
    }
    switch ($vrac->type_transaction) {
        case VracClient::TYPE_TRANSACTION_RAISINS:

            return number_format($quantite, $decimals, $dec_point, $thousands_sep);
        case VracClient::TYPE_TRANSACTION_MOUTS:

            return number_format($quantite, 2, $dec_point, $thousands_sep);
        case VracClient::TYPE_TRANSACTION_VIN_VRAC:

            return number_format($quantite, 2, $dec_point, $thousands_sep);
        case VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE:

            return number_format($quantite, $decimals, $dec_point, $thousands_sep);
    }
}

function formatPrix($prix, $dec_point = ".", $thousands_sep = "") {
    $decimals = 4;

    $nb_decimals = strlen(preg_replace("/^[0-9]+\.?/", "", $prix));
    if ($nb_decimals > 4) {
        $decimals = $nb_decimals;
    }

    return number_format($prix, $decimals, $dec_point, $thousands_sep);
}

function formatPrixFr($prix) {

    return formatPrix($prix, ",", " ");
}

function formatQuantiteFr($vrac) {

    return formatQuantite($vrac, ",", " ");
}

function bouteilleUnitTerm($vrac, $abbr = false) {
    if ($vrac->type_transaction != VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE) {
        return '';
    }
    if (preg_match('/(Bouteille|cl)/', $vrac->bouteilles_contenance_libelle)) {
        return ($abbr) ? 'btles' : 'bouteilles';
    }
    return 'BIB®';
}

function getPictoSignature($societe, $contrat, $type, $hide = false) {
    if (!$societe || $hide)
        return '';
    $statut = $contrat->value[VracClient::VRAC_VIEW_STATUT];
    if (!$statut || $statut == VracClient::STATUS_CONTRAT_BROUILLON){
        return '';
    }
    $createur_contrat = (isset($contrat->value[VracClient::VRAC_VIEW_CREATEURIDENTIFANT])) ? $contrat->value[VracClient::VRAC_VIEW_CREATEURIDENTIFANT] : null;
    if(is_null($createur_contrat) &&
            (($statut == VracClient::STATUS_CONTRAT_SOLDE) ||  ($statut == VracClient::STATUS_CONTRAT_NONSOLDE)))
    {
        return '';
    }

    $signature_vendeur = (isset($contrat->value[VracClient::VRAC_VIEW_SIGNATUREVENDEUR]))? $contrat->value[VracClient::VRAC_VIEW_SIGNATUREVENDEUR] : null;
    $signature_acheteur = (isset($contrat->value[VracClient::VRAC_VIEW_SIGNATUREACHETEUR]))? $contrat->value[VracClient::VRAC_VIEW_SIGNATUREACHETEUR] : null;
    $signature_courtier = (isset($contrat->value[VracClient::VRAC_VIEW_SIGNATURECOURTIER]))? $contrat->value[VracClient::VRAC_VIEW_SIGNATURECOURTIER] : null;
    $toBeSigned = VracClient::getInstance()->toBeSignedBySociete($statut, $societe, $signature_vendeur, $signature_acheteur, $signature_courtier);
    if ($societe->type_societe == SocieteClient::SUB_TYPE_VITICULTEUR) {

        if ($type == 'Vendeur') {
            if (!$toBeSigned) {
                return 'contrat_signe_moi ';
            } else {
                return 'contrat_attente_moi';
            }
        }
        if ((($type == 'Acheteur') && $signature_acheteur) || (($type == 'Courtier') && $signature_courtier)) {

            return 'contrat_signe_soussigne';
        } else {

            return 'contrat_attente_soussigne';
        }
    }
    if ($societe->type_societe == SocieteClient::SUB_TYPE_NEGOCIANT) {
        if ($type == 'Acheteur') {
            if (!$toBeSigned) {
                return 'contrat_signe_moi ';
            } else {
                return 'contrat_attente_moi';
            }
        }
        if ((($type == 'Vendeur') && $signature_vendeur) || (($type == 'Courtier') && $signature_courtier)) {
            return 'contrat_signe_soussigne';
        } else {
            return 'contrat_attente_soussigne';
        }
    }

    if ($societe->type_societe == SocieteClient::TYPE_COURTIER) {
        if ($type == 'Courtier') {
            if (!$toBeSigned) {
                return 'contrat_signe_moi ';
            } else {
                return 'contrat_attente_moi';
            }
        }
        if ((($type == 'Vendeur') && $signature_vendeur) || (($type == 'Acheteur') && $signature_acheteur)) {

            return 'contrat_signe_soussigne';
        } else {

            return 'contrat_attente_soussigne';
        }
    }
}

function echoPictoSignatureFromObject($societe, $contrat, $type, $hide = false) {
    if(!$societe || $hide) return '';

    if (!$contrat->isTeledeclare()) {
        return;
     }
    $fctName = 'isSigne' . $type;
    $isSigne = $contrat->$fctName();
    if (($societe->type_societe == SocieteClient::SUB_TYPE_VITICULTEUR && $type == 'Vendeur')
            || ($societe->type_societe == SocieteClient::SUB_TYPE_NEGOCIANT && $type == 'Acheteur')
            || ($societe->type_societe == SocieteClient::SUB_TYPE_COURTIER && $type == 'Courtier')){
        if($isSigne){
            echo ' contrat_signe_moi ';
        }else{
             echo ' contrat_attente_moi ';
        }
    } else {
        echo ($isSigne) ? ' contrat_signe_soussigne ' : ' contrat_attente_soussigne ';
     }
 }



function getClassStatutPicto($vrac) {

    if ($vrac->valide->statut == VracClient::STATUS_CONTRAT_NONSOLDE) {
        return 'statut_non-solde';
    } elseif ($vrac->valide->statut == VracClient::STATUS_CONTRAT_SOLDE) {
        return 'statut_solde';
    } elseif ($vrac->valide->statut == VracClient::STATUS_CONTRAT_ANNULE) {
        return 'statut_annule';
    } elseif ($vrac->isTeledeclare()) {
        return 'statut_teledeclare';
    }
    return 'statut_solde';
}

function echoClassLignesVisu(&$cpt) {
    echo ($cpt % 2) ? 'ligne_form ' : 'ligne_form ligne_form_alt ';
    $cpt++;
}

function dateFirstSignatureFromView($signature_vendeur,$signature_acheteur,$signature_courtier,$contrat){
    if(!$signature_vendeur && !$signature_acheteur && !$signature_courtier){
        return "";
    }
    if(!$signature_vendeur && $signature_acheteur && !$signature_courtier){
        return Date::francizeDate($contrat->value[VracClient::VRAC_VIEW_SIGNATUREACHETEUR]);
    }
    if(!$signature_vendeur && !$signature_acheteur && $signature_courtier){
        return Date::francizeDate($contrat->value[VracClient::VRAC_VIEW_SIGNATURECOURTIER]);
    }
    return "";
}

function contrats_get_words($contrats) {
    $words = array();
    foreach($contrats as $contrat) {
        $idRow = 'vrac_'.$contrat->value[VracClient::VRAC_VIEW_NUMCONTRAT];
        $words[$idRow] = contrat_get_word($contrat->value);
    }

    return $words;
}

function contrat_get_word($contrat) {
    return array_merge(
        Search::getWords($contrat[VracClient::VRAC_VIEW_VENDEUR_NOM]),
        Search::getWords($contrat[VracClient::VRAC_VIEW_ACHETEUR_NOM]),
        Search::getWords($contrat[VracClient::VRAC_VIEW_MANDATAIRE_NOM]),
        Search::getWords($contrat[VracClient::VRAC_VIEW_NUMCONTRAT]),
        Search::getWords($contrat[VracClient::VRAC_VIEW_NUMARCHIVE]),
        Search::getWords($contrat[VracClient::VRAC_VIEW_PRODUIT_LIBELLE])
    );
}

function revendication_get_id($revendication) {

    return $revendication->id+$revendication->ligne_identifiant;

}
