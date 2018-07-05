<?php

function getLabelForKeyArgument($key)
{
   switch($key) {
       case 'regions' : return 'Régions :';
       case 'type_document' : return 'Type de document :';
       case 'operateur_types' : return "Types d'opérateur :";
       case 'date_declaration' : return 'Date de déclaration :';
       case 'date_facturation' : return 'Date de facturation :';
       case 'date_mouvement' : return "Prise en compte des mouvements jusqu'au :";
       case 'seuil' : return 'Seuil :';
       default: return "$key :";
   }
}

function statutToCssClass($statut)
{
    if($statut == GenerationClient::GENERATION_STATUT_ENATTENTE) {

        return 'info';
    }

    if($statut == GenerationClient::GENERATION_STATUT_ENCOURS) {

        return 'warning';
    }

    if($statut == GenerationClient::GENERATION_STATUT_GENERE) {

        return 'success';
    }

    if($statut == GenerationClient::GENERATION_STATUT_ENERREUR) {

        return 'danger';
    }

    return 'default';
}

function statutToIconCssClass($statut)
{
    if($statut == GenerationClient::GENERATION_STATUT_ENATTENTE) {

        return 'glyphicon glyphicon-time';
    }

    if($statut == GenerationClient::GENERATION_STATUT_ENCOURS) {

        return 'glyphicon glyphicon-record';
    }

    if($statut == GenerationClient::GENERATION_STATUT_GENERE) {

        return 'glyphicon glyphicon-ok-circle';
    }

    if($statut == GenerationClient::GENERATION_STATUT_ENERREUR) {

        return 'glyphicon glyphicon-remove-circle';
    }

    return null;
}

function statutToLibelle($statut)
{
    if($statut == GenerationClient::GENERATION_STATUT_ENATTENTE) {

        return "En attente";
    }

    if($statut == GenerationClient::GENERATION_STATUT_ENCOURS) {

        return "En cours";
    }

    if($statut == GenerationClient::GENERATION_STATUT_GENERE) {

        return "Généré";
    }

    if($statut == GenerationClient::GENERATION_STATUT_ENERREUR) {

        return "En erreur";
    }

    return $statut;
}
