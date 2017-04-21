#!/bin/bash

. bin/config.inc

REMOTE_DATA=$1

SYMFODIR=$(pwd);
DATA_DIR=$TMP/data_sancerre_csv

if test "$REMOTE_DATA"; then
    echo "Récupération de l'archive"
    scp $REMOTE_DATA $TMP/data_sancerre.zip

    echo "Désarchivage"
    rm -rf $TMP/data_sancerre_origin
    mkdir $TMP/data_sancerre_origin
    cd $TMP/data_sancerre_origin
    unzip $TMP/data_sancerre.zip
    rm $TMP/data_sancerre.zip

    cd $SYMFODIR

    echo "Conversion des fichiers en utf8"

    rm -rf $DATA_DIR
    mkdir -p $DATA_DIR

    file -i $TMP/data_sancerre_origin/*.XML | grep -E "(iso-8859-1|unknown-8bit|us-ascii)" | cut -d ":" -f 1 | sed -r 's|^.+/||' | while read ligne
    do
        newname=$(echo $ligne | sed 's/.XML/.utf8.XML/')
        iconv -f iso-8859-1 -t utf-8 $TMP/data_sancerre_origin/$ligne | tr -d "\r"  > $TMP/data_sancerre_origin/$newname
        echo $TMP/data_sancerre_origin/$newname
    done
fi

echo "Import des sociétés"

echo "Numéro adhérent;Nom adhérent;Adresse adhérent;code postal adhérent;ville adhérent;tel adhérent;fax adhérent;type adhérent;num cvi;tva;recette affectation;surface;seuil facturation;num compte;mémo adhérent;stock déclaré;abonné;activité;ntva" > $DATA_DIR/adherents.csv

cat $TMP/data_sancerre_origin/ADHERENT.utf8.XML | sed "s|<\ADHERENT>|\\\n|" | sed -r 's/<[a-zA-Z0-9_-]+>/"/' | sed -r 's|</[a-zA-Z0-9_-]+>|";|' |sed 's/\t//g' | sed 's/\([^;\\n">]\)$/\1|/' | tr -d "\r" | tr -d "\n" | sed 's/\\n/\n/g' | sed 's/";$//' | sed "s/&apos;/'/g" | sed 's/&amp;/\&/g' | sed 's/&quot;/"/g' | grep -v "<?xml" | sed "s/FIN D[' ]*A[ ]*CT[I]*VIT[ÉéE]*[RS]* //i" | sed 's/plus actif//i' | sed 's/|/\\n/' | sed 's/; - //' >> $DATA_DIR/adherents.csv


cat $DATA_DIR/adherents.csv | sed 's/^"//' | awk -F '";"' '{ print sprintf("%06d", $1) ";RESSORTISSANT;\"" $2 "\";\"" $2 "\";" (($18) ? "ACTIF" : "SUSPENDU") ";" $14 ";;;;;\"" $3 "\";;;;" $4 ";\"" $5 "\";;;FR;;" $6 ";;;" $7 ";;" $15  }' > $DATA_DIR/societes.csv

#php symfony import:societe $DATA_DIR/societes.csv $SYMFONYTASKOPTIONS

echo "Import des établissements"

cat $DATA_DIR/adherents.csv | sed 's/^"//' | awk -F '";"' '{ famille=null; region="REGION_CVO"; if($8 == 1) { famille="PRODUCTEUR";} if($8 == 2) { famille="NEGOCIANT";} if($8 == 3) { famille="NEGOCIANT"; region="REGION_HORS_CVO" } if($8 == 4) { famille="COOPERATIVE"; }  print sprintf("%06d01", $1) ";SOCIETE-" sprintf("%06d", $1) ";" famille ";\"" $2 "\";" (($18) ? "ACTIF" : "SUSPENDU") ";" region ";" $9 ";;;" $11 ";;\"" $3 "\";;;;" $4 ";\"" $5 "\";;;FR;;" $6 ";;;" $7 ";;" $15 }' > $DATA_DIR/etablissements.csv

#php symfony import:etablissement $DATA_DIR/etablissements.csv $SYMFONYTASKOPTIONS

cat $DATA_DIR/adherents.csv | cut -d ";" -f 1,17 | grep ";\"1\"$" | cut -d ";" -f 1 | sed 's/"//g' | sed 's/$/;Abonné BIVC/' > $DATA_DIR/tags_manuels_abonne_bivc.csv

echo "Construction du fichier d'import des DRM"

cat $TMP/data_sancerre_origin/MOUVEMENT.utf8.XML | sed "s|<\MOUVEMENT>|\\\n|" | sed -r 's/<[a-zA-Z0-9_-]+>/"/' | sed -r 's|</[a-zA-Z0-9_-]+>|";|' |sed 's/\t//g' | tr -d "\r" | tr -d "\n" | sed 's/\\n/\n/g' | sed 's/";$//' | grep -v "<?xml" | sort -t ';' -k 2,2 > $DATA_DIR/mvts.csv

cat $TMP/data_sancerre_origin/ARTICLE.utf8.XML | sed "s|<\ARTICLE>|\\\n|" | sed -r 's/<[a-zA-Z0-9_-]+>/"/' | sed -r 's|</[a-zA-Z0-9_-]+>|";|' |sed 's/\t//g' | tr -d "\r" | tr -d "\n" | sed 's/\\n/\n/g' | sed 's/";$//' | grep -v "<?xml" | sed 's/Sancerre/AOC Sancerre/' | sed 's/Menetou-Salon/AOC Menetou-Salon/' | sed 's/Pouilly-Fumé/AOC Pouilly-Fumé/' | sed 's/Pouilly Sur Loire/AOC Pouilly-sur-Loire/' | sed 's/Quincy Blanc/AOC Quincy/' | sed 's/Reuilly/AOC Reuilly/' | sed -r 's/"(AOVDQS Coteaux|Coteaux) du Giennois/"AOC Coteaux du Giennois/' | sed 's/Châteaumeillant/AOC Châteaumeillant/' | sort -t ';' -k 1,1 > $DATA_DIR/produits.csv

cat $TMP/data_sancerre_origin/PAYS.utf8.XML | sed "s|^\t<\PAYS>|\\\n|" | sed -r 's/<[a-zA-Z0-9_-]+>/"/' | sed -r 's|</[a-zA-Z0-9_-]+>|";|' |sed 's/\t//g' | tr -d "\r" | tr -d "\n" | sed 's/\\n/\n/g' | sed 's/";$//' | sed 's/"NTZ";"Zone Neutre";";/"NTZ";"Zone Neutre";/' | grep -v "<?xml" | sed "s/&apos;/'/g" | sed 's/&amp;/\&/g' | sed 's/&quot;/"/g' | sed 's/Tchèque (République)/République tchèque/g' | sed 's/Corée du Sud, République de/Corée du Sud/g' | sed 's/Hong-Kong/R.A.S. chinoise de Hong Kong/g' | sed 's/Taïwan, Province de chine/Taïwan/g' | sed 's/Zaïre/République démocratique du Congo/g' | sed 's/Iraq/Irak/g' | sed 's/Centrafricaine, République/République centrafricaine/g' | sed 's/Dominicaine, République/République dominicaine/g' | sed 's/Syrienne, République Arabe/Syrie/g' | sed 's/Tanzanie, République-Unie de/Tanzanie/g' | sed 's/Belise/Belize/g' | sed 's/Caïmanes,îles/Îles Caïmans/g' | sed 's/Cisjordanie - Bande de Gaza/Territoire palestinien/g' | sed 's/Dubai/Émirats arabes unis/g' | sed 's/Iles Vierges Britaniques/Royaume-Uni/g' | sed 's/Macao/R.A.S. chinoise de Macao/g' | sed 's/Saint-Vincent-et-Grenadines/Saint-Vincent-et-les Grenadines/g' | sed 's/Turks et Caïques, îles/Îles Turks et Caïques/g' | sed 's/Urugay/Uruguay/g' | sed 's/Vietnam/Viêt Nam/g' | sed 's/Vatican, Saint-Siège/Vatican/g' | sed 's/Wallis et Futuna, îles/Wallis-et-Futuna/g' | sed 's/Caraïbes, îles/inconnu/g' | sed 's/Autres/Autres pays/g' | sed 's/États fédérés de Micronésie/Micronésie/g' | sed 's/inconnu/Autres pays/g' | sed 's/Biélorussie/Bélarus/g' | sed 's/Kirghize/Kirghizistan/g' | sort -t ';' -k 1,1 > $DATA_DIR/pays.csv

cat $TMP/data_sancerre_origin/POSSEDE_ARTICLE.utf8.XML | sed "s|<\POSSEDE_ARTICLE>|\\\n|" | sed -r 's/<[a-zA-Z0-9_-]+>/"/' | sed -r 's|</[a-zA-Z0-9_-]+>|";|' |sed 's/\t//g' | tr -d "\r" | tr -d "\n" | sed 's/\\n/\n/g' | sed 's/";$//' | grep -v "<?xml" | sort -t ';' -k 1,1 > $DATA_DIR/stocks.csv

join -t ";" -1 2 -2 1 $DATA_DIR/mvts.csv $DATA_DIR/produits.csv | sed 's/;;/;/g' | sort -t ';' -k 18,18 > $DATA_DIR/mvts-produits.csv

join -a 1 -t ";" -1 18 -2 1 $DATA_DIR/mvts-produits.csv $DATA_DIR/pays.csv | sed 's/;;/;/g' > $DATA_DIR/result.csv

join -t ";" -1 1 -2 1 $DATA_DIR/stocks.csv $DATA_DIR/produits.csv | sed 's/;;/;/g' > $DATA_DIR/stocks-produits.csv

cat $DATA_DIR/result.csv | sed 's/;";/;/g' | sed 's/;";/;/g' | sed 's/";$/";"/g' | awk -F '";"' '{
if ($4 == 1) { print "CAVE;" substr($5, 1, 6) ";" sprintf("%06d01", $7) ";;;;;;;;;;" $21 ";suspendu;sorties;ventefrancecrd;" $6 ";;;;;" }
if ($4 == 2) { print "CAVE;" substr($5, 1, 6) ";" sprintf("%06d01", $7) ";;;;;;;;;;" $21 ";suspendu;sorties;export;" $6 ";" $25 ";;;;" }
if ($4 == 3) {
  prix=$15*100;
  periode=substr($5, 1, 6);
  if(periode < 200210){
      prix=prix/6.55957
  }
  print "CAVE;" periode ";" sprintf("%06d01", $7) ";;;;;;;;;;" $21 ";suspendu;sorties;creationvrac;" $6 ";" sprintf("%06d01", $11) ";" prix ";" $5 ";;"
   print "CAVE;" periode ";" sprintf("%06d01", $11) ";;;;;;;;;;" $21 ";suspendu;entrees;achatnoncrd;" $6 ";;;;;"
  }
}' > $DATA_DIR/drm.csv

cat $DATA_DIR/stocks-produits.csv | sed 's/";$/";"/g' | awk -F '";"' '{
print "CAVE;" substr($3, 1, 6) ";" sprintf("%06d01", $2) ";;;;;;;;;;" $10 ";suspendu;stocks_debut;initial;" $4 ";;;;;"
}'  >> $DATA_DIR/drm.csv

cat $DATA_DIR/drm.csv | sort -t ';' -k 3,3 -k 2,2 > $DATA_DIR/drm_final.csv

cat $DATA_DIR/drm_final.csv | grep -vE "^[A-Z]+;(2013(08|09|10|11|12)|201(4|5|6|7|8)[0-9]{2});" | grep -Ev "^CAVE;;" > $DATA_DIR/drm_final_avant_201308.csv

rm -rf $DATA_DIR/drms; mkdir $DATA_DIR/drms

awk -F ";" '{print >> ("'$DATA_DIR'/drms/" $3 "_" $2 ".csv")}' $DATA_DIR/drm_final_avant_201308.csv

echo "Import des DRMs"

ls $DATA_DIR/drms | while read ligne
do
    PERIODE=$(echo $ligne | sed 's/.csv//' | cut -d "_" -f 2)
    IDENTIFIANT=$(echo $ligne | sed 's/.csv//' | cut -d "_" -f 1)
    php symfony drm:edi-import $DATA_DIR/drms/$ligne $PERIODE $IDENTIFIANT --facture=true --creation-depuis-precedente=true $SYMFONYTASKOPTIONS
done

#echo "Import des tags"

#php symfony tag:addManuel --file=$DATA_DIR/tags_manuels_abonne_bivc.csv $SYMFONYTASKOPTIONS
