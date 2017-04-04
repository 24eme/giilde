<?php
$csv = "#nom complet ; type ; civilité ; nom ; prénom ; adresse ; adresse complémentaire ; code postal ; commune ; pays ; téléphone bureau ; téléphone mobile ; téléphone perso ; fax ; email ; commentaire ; id société ; type société ; société raison sociale ; société adresse ; société adresse complémentaire ; société code postal ; société commune ; société téléphone ; société fax ; société email; code de création \n";
foreach ($results as $res) {
  $data = $res->getData();

  $societe_informations = $data['doc']['societe_informations'];

  $csv .= '"'.$data['doc']['nom_a_afficher']. '";';
  $csv .= '"'.CompteClient::getInstance()->createTypeFromOrigines($data['doc']['origines']).'";';
  $csv .= '"'.$data['doc']['civilite']. '";';
  $csv .= '"'.$data['doc']['prenom']. '";';
  $csv .= '"'.$data['doc']['nom']. '";';
  $csv .= '"'.$data['doc']['adresse']. '";';
  $csv .= '"'.$data['doc']['adresse_complementaire']. '";';
  $csv .= '"'.$data['doc']['code_postal']. '";';
  $csv .= '"'.$data['doc']['commune']. '";';
  $csv .= '"'.$data['doc']['pays']. '";';
  $csv .= '"'.$data['doc']['telephone_bureau']. '";';
  $csv .= '"'.$data['doc']['telephone_mobile']. '";';
  $csv .= '"'.$data['doc']['telephone_perso']. '";';
  $csv .= '"'.$data['doc']['fax']. '";';
  $csv .= '"'.$data['doc']['email']. '";';
  $csv .= '"'.$data['doc']['commentaire']. '";';
  $csv .= '"'.preg_replace('/SOCIETE-/', '', $data['doc']['id_societe']). '";';
  $csv .= '"'.$societe_informations['type']. '";';
  $csv .= '"'.$societe_informations['raison_sociale']. '";';
  $csv .= '"'.$societe_informations['adresse']. '";';
  $csv .= '"'.$societe_informations['adresse_complementaire']. '";';
  $csv .= '"'.$societe_informations['code_postal']. '";';
  $csv .= '"'.$societe_informations['commune']. '";';
  $csv .= '"'.$societe_informations['telephone']. '";';
  $csv .= '"'.$societe_informations['fax']. '";';
  $csv .= '"'.$societe_informations['email']. '";';
  $csv .= '"'.(preg_match("/\{TEXT\}/", $data['doc']['mot_de_passe'])) ? str_replace("{TEXT}", "", $data['doc']['mot_de_passe']) : null . '"';
  $csv .= "\n";
}
echo utf8_decode($csv);
