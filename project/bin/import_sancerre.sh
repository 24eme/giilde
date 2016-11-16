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
cat $TMP/data_sancerre_origin/ADHERENT.utf8.XML | sed "s|<\ADHERENT>|\\\n|" | sed -r 's/<[a-zA-Z0-9_-]+>/"/' | sed -r 's|</[a-zA-Z0-9_-]+>|";|' |sed 's/\t//g' | tr -d "\r" | tr -d "\n" | sed 's/\\n/\n/g' | sed 's/";$//' | sed "s/&apos;/'/g" | sed 's/&amp;/\&/g' | sed 's/&quot;/"/g' | grep -v "<?xml" >> $DATA_DIR/adherents.csv

cat $DATA_DIR/adherents.csv | sed 's/^"//' | awk -F '";"' '{ print sprintf("%06d", $1) ";RESSORTISSANT;\"" $2 "\";\"" $2 "\";" (($18) ? "ACTIF" : "SUSPENDU") ";" $14 ";;;;;\"" $3 "\";;;;" $4 ";\"" $5 "\";;;FR;;" $6 ";;;" $7 ";;" $15    }' > $DATA_DIR/societes.csv

php symfony import:societe $DATA_DIR/societes.csv

echo "Import des établissements"

cat $DATA_DIR/adherents.csv | sed 's/^"//' | awk -F '";"' '{ famille=null; region="REGION_CVO"; if($8 == 1) { famille="PRODUCTEUR";} if($8 == 2) { famille="NEGOCIANT";} if($8 == 3) { famille="NEGOCIANT"; region="REGION_HORS_CVO" } if($8 == 4) { famille="COOPERATIVE"; }  print sprintf("%06d01", $1) ";SOCIETE-" sprintf("%06d", $1) ";" famille ";\"" $2 "\";" (($18) ? "ACTIF" : "SUSPENDU") ";" region ";" $9 ";;;" $11 ";;\"" $3 "\";;;;" $4 ";\"" $5 "\";;;FR;;" $6 ";;;" $7 ";;" $15 }' > $DATA_DIR/etablissements.csv

php symfony import:etablissement $DATA_DIR/etablissements.csv

echo "Construction du fichier d'import des DRM"

cat $TMP/data_sancerre_origin/MOUVEMENT.utf8.XML | sed "s|<\MOUVEMENT>|\\\n|" | sed -r 's/<[a-zA-Z0-9_-]+>/"/' | sed -r 's|</[a-zA-Z0-9_-]+>|";|' |sed 's/\t//g' | tr -d "\r" | tr -d "\n" | sed 's/\\n/\n/g' | sed 's/";$//' | grep -v "<?xml" | sort -t ';' -k 2,2 > $DATA_DIR/mvts.csv

cat $TMP/data_sancerre_origin/ARTICLE.utf8.XML | sed "s|<\ARTICLE>|\\\n|" | sed -r 's/<[a-zA-Z0-9_-]+>/"/' | sed -r 's|</[a-zA-Z0-9_-]+>|";|' |sed 's/\t//g' | tr -d "\r" | tr -d "\n" | sed 's/\\n/\n/g' | sed 's/";$//' | grep -v "<?xml" | sort -t ';' -k 1,1 > $DATA_DIR/produits.csv

cat $TMP/data_sancerre_origin/PAYS.utf8.XML | sed "s|^\t<\PAYS>|\\\n|" | sed -r 's/<[a-zA-Z0-9_-]+>/"/' | sed -r 's|</[a-zA-Z0-9_-]+>|";|' |sed 's/\t//g' | tr -d "\r" | tr -d "\n" | sed 's/\\n/\n/g' | sed 's/";$//' | sed 's/"NTZ";"Zone Neutre";";/"NTZ";"Zone Neutre";/' | grep -v "<?xml" | sed "s/&apos;/'/g" | sed 's/&amp;/\&/g' | sed 's/&quot;/"/g' | sed 's/Tchèque (République)/République tchèque/g' | sed 's/Corée du Sud, République de/Corée du Sud/' | sed 's/Hong-Kong/Hong Kong/' | sed 's/Taïwan, Province de chine/Taïwan/' | sed 's/Zaïre/Zaire/' | sed 's/Centrafricaine, République/République centrafricaine/' | sed 's/Dominicaine, République/République dominicaine/' | sed 's/Syrienne, République Arabe/Syrie/' | sed 's/Tanzanie, République-Unie de/Tanzanie/' | sort -t ';' -k 1,1 > $DATA_DIR/pays.csv

cat $TMP/data_sancerre_origin/POSSEDE_ARTICLE.utf8.XML | sed "s|<\POSSEDE_ARTICLE>|\\\n|" | sed -r 's/<[a-zA-Z0-9_-]+>/"/' | sed -r 's|</[a-zA-Z0-9_-]+>|";|' |sed 's/\t//g' | tr -d "\r" | tr -d "\n" | sed 's/\\n/\n/g' | sed 's/";$//' | grep -v "<?xml" | sort -t ';' -k 1,1 > $DATA_DIR/stocks.csv

join -t ";" -1 2 -2 1 $DATA_DIR/mvts.csv $DATA_DIR/produits.csv | sed 's/;;/;/g' | sort -t ';' -k 18,18 > $DATA_DIR/mvts-produits.csv

join -a 1 -t ";" -1 18 -2 1 $DATA_DIR/mvts-produits.csv $DATA_DIR/pays.csv | sed 's/;;/;/g' > $DATA_DIR/result.csv

join -t ";" -1 1 -2 1 $DATA_DIR/stocks.csv $DATA_DIR/produits.csv | sed 's/;;/;/g' > $DATA_DIR/stocks-produits.csv

cat $DATA_DIR/result.csv | sed 's/;";/;/g' | 's/;";/;/g' | sed 's/";$/";"/g' | awk -F '";"' '{
if ($4 == 1) { print "CAVE;" substr($5, 1, 6) ";" sprintf("%06d01", $7) ";;;;;;;;;;" $21 ";suspendu;sorties;ventefrancecrd;" $6 ";;;;;" }
if ($4 == 2) { print "CAVE;" substr($5, 1, 6) ";" sprintf("%06d01", $7) ";;;;;;;;;;" $21 ";suspendu;sorties;export;" $6 ";" $25 ";;;;" }
if ($4 == 3) { print "CAVE;" substr($5, 1, 6) ";" sprintf("%06d01", $7) ";;;;;;;;;;" $21 ";suspendu;sorties;vracsanscontratsuspendu;" $6 ";;;;;" }
}' >> $DATA_DIR/drm.csv

cat $DATA_DIR/stocks-produits.csv | sed 's/";$/";"/g' | awk -F '";"' '{
print "CAVE;" substr($3, 1, 6) ";" sprintf("%06d01", $2) ";;;;;;;;;;" $10 ";suspendu;stocks_debut;initial;" $4 ";;;;;"
}'  >> $DATA_DIR/drm.csv

cat $DATA_DIR/drm.csv | sort -t ';' -k 3,3 -k 2,2 > $DATA_DIR/drm_final.csv

cat $DATA_DIR/drm_final.csv | grep -E "^[A-Z]+;(2014(08|09|10|11|12)|2015[0-9]{2}|2016[0-9]{2});" > $DATA_DIR/drm_final_201408.csv

rm -rf $DATA_DIR/drms; mkdir $DATA_DIR/drms

awk -F ";" '{print >> ("'$DATA_DIR'/drms/" $3 "_" $2 ".csv")}' $DATA_DIR/drm_final_201408.csv

echo "Import des DRMs"

ls $DATA_DIR/drms | while read ligne
do
    PERIODE=$(echo $ligne | sed 's/.csv//' | cut -d "_" -f 2)
    IDENTIFIANT=$(echo $ligne | sed 's/.csv//' | cut -d "_" -f 1)
    php symfony drm:edi-import $DATA_DIR/drms/$ligne $PERIODE $IDENTIFIANT --facture=true --creation-depuis-precedente=true --env="sancerre"
done
