#!/bin/bash

. bin/config.inc

REMOTE_DATA=$1

SYMFODIR=$(pwd);
DATA_DIR=$TMP/data_ivso_csv

if test "$REMOTE_DATA"; then
    echo "Récupération de l'archive"
    scp $REMOTE_DATA $TMP/data_ivso.zip

    echo "Désarchivage"
    rm -rf $TMP/data_ivso_origin
    mkdir $TMP/data_ivso_origin
    cd $TMP/data_ivso_origin
    unzip $TMP/data_ivso.zip

    rm $TMP/data_ivso.zip

    rename 's/^Table //' *
    rename 's/ /_/' *
    rename 's/des_//' *

    cd $SYMFODIR

    echo "Conversion des fichiers en csv"

    rm -rf $DATA_DIR
    mkdir -p $DATA_DIR

    ls $TMP/data_ivso_origin | grep ".xlsx" | while read ligne
    do
        CSVFILENAME=$(echo $ligne | sed 's/\.xlsx/\.csv/')
        echo $DATA_DIR/$CSVFILENAME
        xlsx2csv -d ";" $TMP/data_ivso_origin/$ligne > $DATA_DIR/$CSVFILENAME
    done

    cp $TMP/data_ivso_origin/IVSO_AntSys_identiteextra.csv $DATA_DIR/IVSO_AntSys_identiteextra.csv

    rm -rf $TMP/data_ivso_origin
fi

echo "Import de la configuration"

#curl -sX DELETE "http://$COUCHHOST:$COUCHPORT/$COUCHBASE/CONFIGURATION"?rev=$(curl -sX GET "http://$COUCHHOST:$COUCHPORT/$COUCHBASE/CONFIGURATION" | grep -Eo '"_rev":"[a-z0-9-]+"' | sed 's/"//g' | sed 's/_rev://')
#php symfony import:configuration CONFIGURATION data/import/configuration/ivso
#php symfony import:CVO CONFIGURATION data/import/configuration/ivso/cvo.csv
#php symfony cc > /dev/null

cat $DATA_DIR/produits.csv | tr -d '\r' | awk -F ";" '{ print $5 ";" $4 }' | sort -t ";" -k 1,1 | sed 's/IGP Lot Blanc/IGP Côte du Lot Blanc/' | sed 's/IGP Lot Rouge/IGP Côte du Lot Rouge/' | sed 's/IGP Lot Rosé/IGP Côte du Lot Rosé/' | sed 's/IGP Tarn/IGP Côtes du Tarn/' | sed 's/AOP Pacherenc du Vic Bilh Moelleux/AOP Pacherenc du Vic Bilh Blanc Moelleux/' | sed 's/Côtes du Brulhois/Brulhois/' | sed 's/AOP Gaillac  Blanc sec - Premières cotes/AOP Gaillac Premières côtes Blanc sec/' | sed 's/AOP Gaillac Blanc Effervescent/AOP Gaillac Mousseux/' | sed 's/AOP Gaillac Doux - Vendanges tardives/AOP Gaillac Blanc doux Vendanges tardives/' | sed 's/AOP Entraygues et Fel/AOP Entraygues - Le Fel/' | sed 's/IGP Terroir Landais/IGP Landes/' | sed 's/AOP Lavilledieu/IGP Lavilledieu/' | sed 's/IGP Bigorre/IGP Comté Tolosan/' | sed 's/IGP Côtes du Condomois/IGP Côtes de Gascogne/' | sed 's/IGP Côtes du Tarn et Garonne/IGP Comté Tolosan/' | sed 's/IGP Ctx et Terrasse de Montauban/IGP Comté Tolosan/' | sed 's/IGP Pyrénées Atlantiques/IGP Comté Tolosan/' | sed 's/IGP Cantal/IGP Comté Tolosan/' | sed 's/IGP Coteaux de Glanes Blanc Sec/IGP Coteaux de Glanes Blanc/' | sed 's/IGP Autres Vins de Pays/IGP/' | sed 's/IGP Lot et Garonne/IGP/' | sed 's/VdT /Vin sans IG /' | sed 's/AOP Gaillac Rosé Effervescent/AOP Gaillac Mousseux/' | sed 's/IGP Blanc Doux/IGP Comté Tolosan Blanc sec/' | sed 's/IGP Haute Garonne/IGP Comté Tolosan/' | sed 's/IGP Saint-Sardos/IGP Comté Tolosan/' > $DATA_DIR/produits_conversion.csv
cat $DATA_DIR/cepages.csv | cut -d ";" -f 2,3 | sort -t ";" -k 1,1 > $DATA_DIR/cepages.csv.sorted

echo "Construction du fichier d'import des Contacts"

#Affichage des entêtes en ligne

cat $DATA_DIR/contacts_extravitis.csv | sort -t ";" -k 1,1 > $DATA_DIR/contacts_extravitis.sorted.csv

echo "clé identité;code_comptable;num accises" > $DATA_DIR/IVSO_AntSys_identiteextra_entetes.csv
cat $DATA_DIR/IVSO_AntSys_identiteextra.csv >> $DATA_DIR/IVSO_AntSys_identiteextra_entetes.csv
cat $DATA_DIR/IVSO_AntSys_identiteextra_entetes.csv | sort -t ";" -k 1,1 > $DATA_DIR/IVSO_AntSys_identiteextra.sorted.csv

join -a 1 -t ";" -1 1 -2 1 -o auto $DATA_DIR/contacts_extravitis.sorted.csv $DATA_DIR/IVSO_AntSys_identiteextra.sorted.csv | sort > $DATA_DIR/contacts_extravitis_extra.csv

#cat $DATA_DIR/producteurs.csv | awk -F ";" '{ print $2 ";" $18 }' > $DATA_DIR/contacts_nature_inao.csv
cat $DATA_DIR/producteurs_produits.csv | awk -F ";" '{ print $1 ";" $17 }' > $DATA_DIR/contacts_nature_inao.csv
cat $DATA_DIR/negociant.csv | awk -F ";" '{ print $1 ";" $17 }' >> $DATA_DIR/contacts_nature_inao.csv
cat $DATA_DIR/contacts_nature_inao.csv | tr -d "\r" | sort | uniq | sort -t ";" -k 1,1 > $DATA_DIR/contacts_nature_inao.uniq.sorted.csv

cat $DATA_DIR/contacts_extravitis_extra.csv | tr -d "\r" | sort -t ";" -k 1,1 > $DATA_DIR/contacts_extravitis_extra.sorted.csv

join -a 1 -t ";" -1 1 -2 1 -o auto $DATA_DIR/contacts_extravitis_extra.sorted.csv $DATA_DIR/contacts_nature_inao.uniq.sorted.csv | sort > $DATA_DIR/contacts_extravitis_extra_nature_inao.csv

#head -n 1 /tmp/giilda/data_ivso_csv/contacts_extravitis_extra_nature_inao.csv | tr ";" "\n" | awk -F ";" 'BEGIN { nb=0 } { nb = nb + 1; print nb ";" $0 }'

cat $DATA_DIR/contacts_extravitis_extra_nature_inao.csv | tr -d '\r' | awk -F ';' '
      function ltrim(s) { sub(/^[ \t\r\n]+/, "", s); return s } function rtrim(s) { sub(/[ \t\r\n]+$/, "", s); return s } function trim(s)  { return rtrim(ltrim(s)); } {
    identifiant=sprintf("%06d", $1);
    famille="AUTRE" ;
    if($15) {
      famille="INTERMEDIAIRE";
    }
    if($13 || $14) {
      famille="RESSORTISSANT";
    }
    statut=($37 == "Oui" ? "SUSPENDU" : "ACTIF") ;
    insee=$8;
    code_comptable_client=$173;
    code_comptable_fournisseur="";
    nom=trim($2 " " $3 " " $4);
    siret=$34;
    code_naf="";
    tvaintra="";
    codepostal=$9;
    commune=$10;
    cedex=$12;
    if(cedex == "#N/A") {
      cedex = "";
    }

    pays = "FR";
    if(cedex || code_postal ~ /^99/) {
        pays="";
    }

    email=$19;
    tel_bureau=$16;
    tel_perso="";
    mobile=$18;
    tel_fax=$17;
    web=$20;

    print identifiant ";" famille ";" nom ";;" statut ";" code_comptable_client ";" code_comptable_fournisseur ";" siret ";" code_naf ";" tvaintra ";" $5 ";" $6 ";" $7 ";;" codepostal ";" commune ";" insee ";" cedex ";" pays ";" email ";" tel_bureau ";;" mobile ";" tel_fax ";" web ";"
}' | sed 's/;";/;;/g' > $DATA_DIR/societes.csv

cat $DATA_DIR/contacts_extravitis_extra_nature_inao.csv | tr -d '\r' | awk -F ';' '
function ltrim(s) { sub(/^[ \t\r\n]+/, "", s); return s } function rtrim(s) { sub(/[ \t\r\n]+$/, "", s); return s } function trim(s)  { return rtrim(ltrim(s)); } {
    nom=trim($2 " " $3 " " $4) ;
    famille="AUTRE" ;
    producteur=($13 != "");
    negociant=($14 != "");
    courtier=($15 != "");

    delete familles;

    if(producteur) {
      familles["PRODUCTEUR"] = "PRODUCTEUR";
    }

    if(negociant) {
      familles["NEGOCIANT"] = "NEGOCIANT";
    }

    if(courtier) {
      familles["COURTIER"] = "COURTIER";
    }

    statut=($37 == "Oui" ? "SUSPENDU" : "ACTIF") ;
    nom=nom ;
    code_postal=$9

    identifiant_societe=sprintf("%06d", $1);
    identifiant=identifiant_societe "01";
    insee=$8;
    cvi=$27;
    noaccises=$174;
    carte_pro="";
    recettelocale="";
    nature_inao=$176;
    commune=$10;
    cedex=$12;
    if(cedex == "#N/A") {
      cedex = "";
    }

    pays = "FR";
    if(cedex || code_postal ~ /^99/) {
        pays="";
    }

    region="REGION_CVO";
    if(pays != "FR") {
        region="REGION_HORS_CVO";
    }

    if (famille == "COURTIER") {
        region="REGION_HORS_CVO";
    }

    email="";
    tel_bureau="";
    tel_perso="";
    mobile="";
    tel_fax="";
    web="";

    for (famille in familles)
    {
        print ";" identifiant_societe ";" famille ";" nom ";" statut ";" region ";" cvi ";" noaccises ";" carte_pro ";" recettelocale ";" nature_inao ";" $5 ";" $6 ";" $7 ";;" code_postal ";" commune ";" insee ";" cedex ";" pays ";" email ";" tel_bureau ";;" mobile ";" tel_fax ";" web ";"
    }
}' > $DATA_DIR/etablissements.csv

cat $DATA_DIR/contacts_extravitis_extra_nature_inao.csv | tr -d '\r' | awk -F ';' '{
    identifiant_societe=sprintf("%06d", $1);
    statut = "ACTIF";

    contacts[20] = 20;
    contacts[38] = 38;
    contacts[50] = 50;
    contacts[55] = 55;

    i = 64;
    while(i < 171) {
        contacts[i] = i;
        i = i + 6;
    }

    for (num in contacts)
    {
        civilite="";
        nom = "";
        if(num != 20 && num !=55) {
            nom = $(num);
        }
        prenom = "";
        fonction = "";
        adresse = ";;;;;;;;"
        tel_bureau = $(num + 1);
        tel_perso = "";
        fax = $(num + 2);
        email = $(num + 3);
        mobile = $(num + 4);
        web = $(num + 5);
        commentaire = "";

        if(nom || tel_bureau || tel_perso || fax || email || mobile || web) {
            print ";" identifiant_societe ";" statut ";" civilite ";" nom ";" prenom ";" fonction ";" adresse ";" email ";" tel_bureau ";" tel_perso ";" mobile ";" fax ";" web ";" commentaire;
        }
    }

}' | sort > $DATA_DIR/interlocuteurs.csv

echo "Construction du fichier d'import des Contrats de vente"

cat $DATA_DIR/contrats.csv | tr -d "\n" | tr "\r" "\n" | sort -t ";" -k 16,16 > $DATA_DIR/contrats.csv.sorted.produits

join -a 1 -t ";" -1 16 -2 1 $DATA_DIR/contrats.csv.sorted.produits $DATA_DIR/produits_conversion.csv > $DATA_DIR/contrats_produits.csv

cat $DATA_DIR/contrats_produits.csv | sort -t ";" -k 24,24 > $DATA_DIR/contrats_produits.sorted.cepages

join -a 1 -t ";" -1 24 -2 1 $DATA_DIR/contrats_produits.sorted.cepages $DATA_DIR/cepages.csv.sorted > $DATA_DIR/contrats_produits_cepages.csv

# /!\ Correction manuelle des lignes avec un problème d'année est 211 => 2011, 22012 => 2012, 201 => 2015, 20112 => 2012
cat $DATA_DIR/contrats_produits_cepages.csv | sed 's/;4;10222;10222;211;/;4;10222;10222;2011;/g' | sed 's/;3;10194;10194;22012;/;3;10194;10194;2012;/g' | sed 's/;2;10849;10849;201;12;91236;/;2;10849;10849;2015;12;91236;/g' | sed 's/;88;7922;7922;20112;/;88;7922;7922;2012;/g' > $DATA_DIR/contrats_produits_cepages.clean.csv

# Début génération des Id couchDB
#tail -n 1 $DATA_DIR/contrats_produits_cepages.clean.csv | tr ";" "\n" | awk -F ";" 'BEGIN { nb=0 } { nb = nb + 1; print nb ";" $0 }'

cat $DATA_DIR/contrats_produits_cepages.clean.csv | sed -r 's/^([0-9]*);([0-9]*);([0-9]*);([0-9]*);([0-9]{2});(.*)/\1;\2;\3;\4;20\5;\6/g' | awk -F ';' '{
date_signature=gensub(/^([0-9]+)-([0-9]+)-([0-9]+)$/,"\\3-\\1-\\2", 1, $9);
date_saisie=gensub(/^([0-9]+)-([0-9]+)-([0-9]+)$/,"\\3-\\1-\\2", 1, $11);
num_bordereau=$7;
if(length($7) > 7){
   num_bordereau=substr($7,1,7);
}
id_vrac=sprintf("%4d%09d", $5 , num_bordereau);

libelle_produit=$41;
libelle_cepage=$42;
millesime=$17;
vin_bio=$19;
vin_prepare=$20;
caracteristiques_vins="";
preparation_vin="";
if(vin_bio=="O"){
  caracteristiques_vins="agriculture_biologique";
}
if(vin_prepare=="P"){
  preparation_vin="NEGOCE_ACHEMINE";
}

date_debut_retiraison=$26;
date_fin_retiraison=$28;

if(!date_debut_retiraison) {
    date_debut_retiraison=null;
}

if(!date_debut_retiraison) {
    date_limite_retiraison=null;
}

clause_reserve_propriete=($31 == "O") ? "clause_reserve_propriete" : "";

acompte=$32;
cle_delais_paiement="";
libelle_delais_paiement=$33;
if(libelle_delais_paiement=="Comptant"){
  cle_delais_paiement="COMPTANT";
}else if(libelle_delais_paiement=="60 jours à compter de l'"'"'émission de la facture"){
  cle_delais_paiement="60_JOURS";
}else if(libelle_delais_paiement=="Délai prévu par accord professionnel"){
  cle_delais_paiement="ACCORD_INTERPROFESSIONNEL";
}else if(libelle_delais_paiement=="45 jours à compter du mois d'"'"'émission de la facture"){
  cle_delais_paiement="45_JOURS";
}

delais_paiement_libelle=$33;

commentaires="";
if(exclure_v2 == "O") {
    commentaires = "Ce contrat est un ancien contrat exclut depuis la v2\n";
}

commentaires=commentaires "" $30;
exclure_v2=$39;
degre=$23;
recipient_contenance="";
prix_unitaire_hl=$24;
volume_propose=$22;
if(!volume_propose) {
    volume_propose=$21;
}
volume_enleve="";


proprietaire="";
annule=$36;
statut="NONSOLDE";
if(annule=="O") {
  statut="ANNULE";
}

repartition_cvo="50";

clauses=clause_reserve_propriete "," preparation_vin;

print $4 ";" id_vrac ";" num_bordereau ";"  date_signature ";" date_saisie ";VIN_VRAC;" statut ";" $12 ";;;;" $13 ";" $14 ";" proprietaire ";;" libelle_produit ";" millesime ";;" libelle_cepage ";;;;;" degre ";" recipient_contenance ";"  volume_propose ";hl;" volume_propose ";" volume_enleve ";" prix_unitaire_hl ";" prix_unitaire_hl ";" cle_delais_paiement ";" delais_paiement_libelle ";;;" acompte ";;;" repartition_cvo ";" date_debut_retiraison ";" date_fin_retiraison ";" clauses ";" caracteristiques_vins ";" commentaires
}' | sort > $DATA_DIR/vracs.csv.tmp


cat $DATA_DIR/vracs.csv.tmp | awk -F ';' 'BEGIN { id_vrac_prec=0; num_incr=1; num_incr_aux=1; } {
  id_vrac=$2;
  num_bordereau=$3;
if(id_vrac_prec==id_vrac) {
  if(!num_bordereau){
    num_bordereau=sprintf("9%08d",num_incr);
    num_incr=num_incr+1;
  }else{
    num_bordereau=sprintf("%1d%08d",num_incr_aux,substr($3,2,6));
    num_incr_aux=num_incr_aux+1;
  }
}else{
  if(!num_bordereau){
    num_bordereau=sprintf("9%08d",num_incr);
    num_incr=num_incr+1;

if(length(num_bordereau) > 7){
}
  }
  num_incr_aux=1;
}
id_vrac=substr($2,0,4) "" sprintf("%09d",num_bordereau);
print $1 ";" id_vrac ";" num_bordereau ";" $0
id_vrac_prec=$2;
}' | sed -r 's/^([0-9]*);([0-9]*);([0-9]*);([0-9]*);([0-9]*);([0-9]*);(.*)/\1;\2;\7/g' | sed 's/^Numéro Contrat;   00000000;Numéro ;//g' | sed 's/;   00000000//g' > $DATA_DIR/vracs.csv

echo "Construction du fichier d'import des DRM"

cat $DATA_DIR/DRM.csv | tr -d "\r" | sort -t ";" -k 6,6 > $DATA_DIR/drm.csv.produits.sorted

join -a 1 -t ";" -1 6 -2 1  $DATA_DIR/drm.csv.produits.sorted $DATA_DIR/produits_conversion.csv | sort -t ";" -k 2,3 > $DATA_DIR/drm_produits.csv

cat $DATA_DIR/drm_produits.csv | awk -F ';' '{
identifiant=sprintf("%06d01", $4);
base="CAVE;" $5 ";" identifiant ";;" $37 ";;;;;;;" ;
print base "stocks_debut;initial;" $10 ;
print base "stocks_debut;dont_revendique;" $10 ;
if($11 > 0) { print base "entrees;recolte;" $11 } #récolte
if($11 < 0) { print base "sorties;entree_recolte_negative;" $11*-1 ";;;;entrée négative de récolte" ; } #récolte
if($12 > 0) { print base "entrees;revendication;" $12 ; } #volume agréé
if($12 < 0) { print base "sorties;entree_negative;" $12*-1 ";;;;entrée négative de volume agrée" ; } #volume agréé
if($13 > 0) { print base "entrees;declassement;" $13 ; } #declassement
if($13 < 0) { print base "sorties;entree_negative;" $13*-1 ";;;;entrée négative de déclassement" ; } #declassement
if($14 > 0) { print base "sorties;destructionperte;" $14 ; } #perte
if($15 > 0) { print base "sorties;distillationusageindustriel;" $15 ; } #lie_et_mouts
if($16 > 0) { print base "sorties;distillationusageindustriel;" $16 ; } #usages_industriels
if($17 > 0) { print base "sorties;ventefrancecrd;" $17 ; } #collective_ou_individuelle
if($17 < 0) { print base "entrees;sortie_negative;" $17*-1 ";;;;sortie négative de collective_ou_individuelle" ; }
if($18 > 0) { print base "sorties;vracsanscontratacquitte;" $18 ; } #dsa_dsac
if($18 < 0) { print base "entrees;sortie_negative;" $18*-1 ";;;;sortie négative de dsa dsac" ; } #dsa_dsac
if($19 > 0) { print base "sorties;vracsanscontratacquitte;" $19 ; } #facture_etc
if($19 < 0) { print base "entrees;sortie_negative;" $19*-1 ";;;;sortie négative de facture etc" } #facture_etc
if($20 > 0) { print base "sorties;vracsanscontratsuspendu;" $20 ; } #france_sans_contrat
if($20 < 0) { print base "entrees;sortie_negative;" $20*-1 ";;;;sortie négative de france sans contrat" ; } #france_sans_contrat
if($21 != 0) { print base "sorties;vracsanscontratsuspendu;" $21 ; } #france_sous_contrat
if($22 > 0) { print base "sorties;export;" $22 ";Union Européenne" ; }  #expedition_ue
if($22 < 0) { print base "entrees;sortie_negative;" $22*-1 ";;;;sortie négative de expedition ue" ; }  #expedition_ue
if($23 > 0) { print base "sorties;export;" $23 ";Hors Union Européenne" ; } #expedition_hors_ue
if($23 < 0) { print base "entrees;sortie_negative;" $23*-1 ";;;;sortie négative de expedition hors ue" ; } #expedition_hors_ue
if($24 > 0) { print base "sorties;transfertcomptamatiere;" $24 ; } #relogement
if($24 < 0) { print base "entrees;sortie_negative;" $24*-1 ";;;;sortie négative de relogement" ; } #relogement
print base "stocks_fin;final;" $25 ;
print base "stocks_fin;dont_revendique;" $25 ;
# print base "stocks?;dont_volume_bloque;" $26 ; #dont_volume_bloque
# print base "stocks?;quantite_gagees;" $27 ; #quantite_gagees
}' > $DATA_DIR/drm_cave.csv

cat $DATA_DIR/DRM_Factures.csv | tr -d "\r" | sort -t ";" -k 5,5 > $DATA_DIR/drm_factures.csv.produits.sorted

join -a 1 -t ";" -1 5 -2 1  $DATA_DIR/drm_factures.csv.produits.sorted $DATA_DIR/produits_conversion.csv > $DATA_DIR/drm_factures_produits.csv

cat $DATA_DIR/drm_factures_produits.csv | awk -F ';' '{
identifiant=sprintf("%06d01", $17);
base="CAVE;" $5 ";" identifiant ";;" $45 ";;;;;;;" ;
numero_contrat=gensub(/-/, "0000", 1, $10);
mouvement="vrac"

if(!numero_contrat || numero_contrat == "INCONNU") {
    mouvement="vracsanscontratsuspendu";
    numero_contrat="";
    next;
}
volume = $19 + $20 + $21;
if(!volume) {
    next;
}
print base "sorties;" mouvement ";" volume ";;" numero_contrat ;
print base "sorties;vracsanscontratsuspendu;" volume * -1 ";;" ;
}' > $DATA_DIR/drm_cave_contrats.csv

cat $DATA_DIR/drm_cave.csv $DATA_DIR/drm_cave_contrats.csv | sort -t ";" -k 2,3 > $DATA_DIR/drm.csv

cat $DATA_DIR/drm.csv | grep -E "^[A-Z]+;(2010(08|09|10|11|12)|2011[0-9]{2}|2012[0-9]{2}|20130[0-7]{1});" > $DATA_DIR/drm_201008_201307.csv

cat $DATA_DIR/drm.csv | grep -E "^[A-Z]+;(2005(08|09|10|11|12)|2006[0-1]{1}[0-9]{1}|2007[0-1]{1}[0-9]{1}|2008[0-1]{1}[0-9]{1}|2009[0-1]{1}[0-9]{1}|20100[1-7]{1});" > $DATA_DIR/drm_200508_201007.csv

rm -rf $DATA_DIR/drms; mkdir $DATA_DIR/drms

awk -F ";" '{print >> ("'$DATA_DIR'/drms/" $3 "_" $2 ".csv")}' $DATA_DIR/drm_201008_201307.csv

echo "Import des contacts"

#php symfony import:societe $DATA_DIR/societes.csv --env="ivso"
#php symfony import:etablissement $DATA_DIR/etablissements.csv --env="ivso"
#php symfony import:compte $DATA_DIR/interlocuteurs.csv --env="ivso"

echo "Import des contrats"

#php symfony import:vracs $DATA_DIR/vracs.csv --env="ivso"

echo "Import des DRM"

ls $DATA_DIR/drms | while read ligne
do
    PERIODE=$(echo $ligne | sed 's/.csv//' | cut -d "_" -f 2)
    IDENTIFIANT=$(echo $ligne | sed 's/.csv//' | cut -d "_" -f 1)
    php symfony drm:edi-import $DATA_DIR/drms/$ligne $PERIODE $IDENTIFIANT --facture=true --creation-depuis-precedente=true --env="ivso"
done

echo "Contrôle de cohérence des DRM"

cat $DATA_DIR/drm.csv | cut -d ";" -f 3 | sort | uniq | while read ligne
do
    #php symfony drm:controle-coherence "$ligne"
done
