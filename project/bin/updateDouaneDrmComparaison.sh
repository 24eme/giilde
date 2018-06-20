#!/bin/bash

. $(dirname $0)/config.inc

FILEDATE=$WORKINGDIR"/data/dateDrmDouane";

if [ ! -f $FILEDATE ];then
    echo "2016-01-01" > $FILEDATE
fi;
APPLICATION=$(echo $SYMFONYTASKOPTIONS | sed -r 's|(.+)application=(.+)\ (.+)|\2|');

DATEREQUETE=$(cat $FILEDATE);
DATE=$(date +%Y%m%d%H%M%S)
DATEFORMAT=`date '+%Y-%m-%d %H:%M:%S'`
mkdir -p $TMP"/retoursDouanes" > /dev/null
LOGFILE=$TMP"/retoursDouanes/retrieveXMLAndCompare_"$DATE".log"

bash $(dirname $0)/retrieveXMLAndCompare.sh $DATEREQUETE > $LOGFILE


RAPPORTBODY=$TMP"/retoursDouanes/retrieveXMLAndCompare_rapport_"$DATE".txt"
NBXMLIDENTIQUES=`cat $LOGFILE | grep "XML sont identiques" | wc -l`
NBXMLDIFFERENTS=`cat $LOGFILE | grep -C 1 "XML differents" | sed "s|--|#|g" | tr "\n" " " | tr "#" "\n" | grep -v "il existe une DRM Suivante" | wc -l`
NBXMLNONTRANSMIS=`cat $LOGFILE | grep -C 1 "n'a pas été transmise aux douanes" | grep "XML differents" | wc -l`

NBDRMDOUANEABSENTE=`cat $LOGFILE | grep "n'a pas été trouvée" | wc -l`
NBNUMACCISEMALDRM=`cat $LOGFILE | grep "Le numéro d'accise" | cut -d ":" -f 3 | sed "s/ Le numéro d'accise //" | sed "s/ ne correspond pas a celui de l'établissement (/;/g" | sed "s/ | /;/" | sed "s/)//" | wc -l`
NBNUMACCISEMAL=`cat $LOGFILE | grep "Le numéro d'accise" | cut -d ":" -f 3 | sed "s/ Le numéro d'accise //" | sed "s/ ne correspond pas a celui de l'établissement (/;/g" | sed "s/ | /;/" | sed "s/)//" | sort | uniq | wc -l`

echo -e "Du $DATEREQUETE à ce jour\n" > $RAPPORTBODY;
echo -e "   XML identiques = "$NBXMLIDENTIQUES" DRM" >> $RAPPORTBODY;
echo -e "   XML différents = "$NBXMLDIFFERENTS" DRM (dont "$NBXMLNONTRANSMIS" DRM télédéclarées qui n'ont pas été transmises aux douanes) " >> $RAPPORTBODY;
echo -e "   Opérateurs télédéclarant sur CIEL mais pas sur la plateforme = "$NBDRMDOUANEABSENTE" \n" >> $RAPPORTBODY;
echo -e "Détails des différences :\n\n" >> $RAPPORTBODY;

echo -e "   DRM non transmises aux douanes : \n" >> $RAPPORTBODY;

cat $LOGFILE | grep -C 1 "n'a pas été transmise aux douanes" | grep "XML differents" | cut -d ' ' -f 1 | sort | uniq | sed -r "s|DRM-([0-9]+)-([0-9]+)|         $URLDRMINTERNE\1\/visualisation\/\2|" >> $RAPPORTBODY;

echo -e "\n\n   DRM pour lesquelles une modificatrice devrai être ouverte : \n\n" >> $RAPPORTBODY;

cat $LOGFILE | grep -C 1 "DRM modificatrice ouverte" | grep "XML differents" | cut -d ' ' -f 1 | sed -r "s|DRM-([0-9]+)-([0-9]+)|         $URLDRMINTERNE\1\/visualisation\/\2|" >> $RAPPORTBODY;


echo -e "\n\nOpérateurs connus télédéclarants sur CIEL mais pas sur la plateforme. DRM ouvertes avec le retour douane pour ces opérateurs :\n\n" >> $RAPPORTBODY;


cat $LOGFILE | grep "n'a pas été trouvée" | sed -r "s/(.*)La DRM de (.+) (.+) n'a pas été trouvée(.+)/DRM-\2-\3/g" | sort | uniq | sed -r "s|DRM-([0-9]+)-([0-9]+)|         $URLDRMINTERNE\1\/edition\/\2\/validation|g" >> $RAPPORTBODY;

echo -e "\n\nAvaries : Numéros d'accise mal référencés = "$NBNUMACCISEMALDRM" DRM correspondant à "$NBNUMACCISEMAL" accises :\n\n" >> $RAPPORTBODY;

cat $LOGFILE | grep "Le numéro d'accise" | cut -d ":" -f 3 | sed "s/ Le numéro d'accise //" | sed "s/ ne correspond pas a celui de l'établissement (/;/g" | sed "s/ | /;/" | sed "s/)//" | sort | uniq | sed -r "s|(FR.+);(.+);(.*)|         $URLDRMINTERNE\2 \1 (obtenu des douanes) Accise référencée sur le portail de $APPLICATION  : \3|g" >> $RAPPORTBODY;


cat $RAPPORTBODY  | iconv -t ISO-8859-1 | mail -s "[RAPPORT RETOUR DOUANE de $APPLICATION du $DATEFORMAT]" $MAILRETOURDOUANE;

echo $(date +%Y-%m-%d) > $FILEDATE
