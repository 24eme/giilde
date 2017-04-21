<?php use_helper('Display'); ?>
\documentclass[a4paper,8pt]{article}
\usepackage{geometry} % paper=a4paper
\usepackage[english]{babel}
\usepackage[utf8]{inputenc}
\usepackage{units}
\usepackage{geometry}
\usepackage{graphicx}
\usepackage{fancyhdr}
\usepackage{fp}
\usepackage[table]{xcolor}
\usepackage{tikz}
\usepackage{array}
\usepackage{multicol}
\usepackage{textcomp}
\usepackage{marvosym}
\usepackage{lastpage}
\usepackage{truncate}

\usetikzlibrary{fit}

\renewcommand\sfdefault{phv}

\renewcommand{\familydefault}{\sfdefault}
\renewcommand{\TruncateMarker}{\small{...}}

\setlength{\oddsidemargin}{-2cm}
\setlength{\evensidemargin}{-2cm}
\setlength{\textwidth}{19cm}
\setlength{\headheight}{5.3cm}
\setlength{\headwidth}{19.5cm}
\setlength{\topmargin}{-4.5cm}
\addtolength{\textheight}{29.9cm}

\pagestyle{fancy}
\renewcommand{\headrulewidth}{0pt}

\fancyhf{}
\def\PdfTitre{<?php echo $pdf_titre; ?>}
\def\NomInterpro{<?php echo sfConfig::get('facture_configuration_facture', array('pdf_nom_interpro'=>''))['pdf_nom_interpro']; ?>}
\def\InterproAdresse{ <?php echo sfConfig::get('app_configuration_facture')['emetteur_libre']['adresse']; ?>\\
		       <?php echo sfConfig::get('app_configuration_facture')['emetteur_libre']['code_postal']." ".sfConfig::get('app_configuration_facture_emetteur_libre_ville'); ?> }
\def\InterproContact{\\ Tel: <?php echo sfConfig::get('app_configuration_facture')['emetteur_libre']['telephone'];
$fax = sfConfig::get('app_configuration_facture')['emetteur_libre']['fax']; echo ($fax) ? ' - Fax :' .$fax : '' ;?>
                                                    \\ Email : <?php echo sfConfig::get('app_configuration_facture')['emetteur_libre']['email']; ?>
										}
\def\InterproSIRET{<?php echo sfConfig::get('app_configuration_facture')['infos_interpro']['siret']; ?>}
\def\InterproAPE{<?php echo sfConfig::get('app_configuration_facture')['infos_interpro']['ape']; ?>}
\def\InterproTVAIntracomm{<?php echo sfConfig::get('app_configuration_facture')['infos_interpro']['tva_intracom']; ?>}
\def\InterproBANQUE{<?php echo sfConfig::get('app_configuration_facture')['coordonnees_bancaire']['banque']; ?>}
\def\InterproBIC{<?php echo sfConfig::get('app_configuration_facture')['coordonnees_bancaire']['bic']; ?>}
\def\InterproIBAN{<?php echo sfConfig::get('app_configuration_facture')['coordonnees_bancaire']['iban']; ?>}

\def\RessortissantNom{<?php $nom = ($ressortissant->raison_sociale == '')? $ressortissant->nom : html_entity_decode($ressortissant->raison_sociale);
                            echo display_latex_string($nom,';',40);
                     ?>}
\def\RessortissantAdresse{<?php $adresse = ($ressortissant->adresse == '')? "~" : html_entity_decode($ressortissant->adresse);
                                                 echo display_latex_string($adresse,';',50,2); ?>}
\def\RessortissantAdresseComplementaire{<?php $adresseComplementaire = (!$ressortissant->adresse_complementaire)? "~" : "\\\\".html_entity_decode($ressortissant->adresse_complementaire);
                                          echo display_latex_string($adresseComplementaire,';',50,2);   ?>}
\def\RessortissantCP{<?php echo $ressortissant->code_postal; ?>}
\def\RessortissantVille{<?php echo $ressortissant->commune; ?>}
