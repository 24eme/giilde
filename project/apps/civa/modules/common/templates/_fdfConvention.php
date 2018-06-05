%FDF-1.2
%âãÏÓ
1 0 obj
<<
/FDF
<<
/Fields [
<<
/V (<?php echo ($etablissement->exist('siret')) ? $etablissement->siret : $etablissement->getSociete()->siret  ?>)
/T (siret)
>>
<<
/V (<?php echo $etablissement->siege->adresse.' '.$etablissement->siege->adresse_complementaire ?>)
/T (iadresse)
>>
<<
/V (CIVA)
/T (interpro)
>>
<<
/V (<?php echo ($etablissement->teledeclaration_email)? $etablissement->teledeclaration_email : $etablissement->getSociete()->getMasterCompte()->email ?>)
/T (email)
>>
<<
/V (<?php echo ($etablissement->teledeclaration_email)? $etablissement->teledeclaration_email : $etablissement->getSociete()->getMasterCompte()->email ?>)
/T (iemail)
>>
<<
/V ()
/T (nom)
>>
<<
/V (<?php echo $etablissement->no_accises ?>)
/T (iea1)
>>
<<
/V (<?php echo $etablissement->siege->code_postal.' '.$etablissement->siege->commune ?>)
/T (icommune)
>>
<<
/V (<?php echo ($etablissement->exist('siret')) ? $etablissement->siret : $etablissement->getSociete()->siret  ?>)
/T (isiret)
>>
<<
/V ()
/T (prenom)
>>
<<
/V ()
/T (signemail)
>>
<<
/V ()
/T (signtelephone)
>>
<<
/V ()
/T (faita)
>>
<<
/V ()
/T (faitle)
>>
<<
/V (<?php echo $etablissement->raison_sociale ?>)
/T (irs)
>> 
<<
/V (<?php echo $etablissement->raison_sociale ?>)
/T (rs)
>> 
<<
/V (<?php echo $etablissement->getSociete()->getMasterCompte()->telephone_bureau ?>)
/T (telephone)
>> <<
/V (<?php echo $etablissement->getSociete()->getMasterCompte()->telephone_bureau ?>)
/T (itelephone)
>> 
<<
/V (<?php echo $etablissement->siege->code_postal.' '.$etablissement->siege->commune ?>)
/T (commune)
>>  
<<
/V (<?php echo $etablissement->cvi ?>)
/T (icvi1)
>> 
<<
/V (<?php echo $etablissement->cvi ?>)
/T (cvi1)
>> 
<<
/V (<?php echo $etablissement->no_accises ?>)
/T (ea1)
>> 
<<
/V (<?php echo $etablissement->no_accises ?>)
/T (agrement)
>> 
<<
/V (<?php echo $etablissement->siege->adresse.' '.$etablissement->siege->adresse_complementaire ?>)
/T (adresse)
>> 
<<
/V (CIVA)
/T (iinterpro)
>>]
>>
>>
endobj 
trailer

<<
/Root 1 0 R
>>
%%EOF