#!/bin/bash

. $(dirname $0)/config.inc

DATEREQUETE="2016-01-01"
DATE=$(date +%Y%m%d%H%M%S)
DATEFORMAT=`date '+%Y-%m-%d %H:%M:%S'`
mkdir -p $TMP"/retoursDouanes" > /dev/null
LOGFILE=$TMP"/retoursDouanes/retrieveXMLAndCompare_"$DATE".log"

bash $(dirname $0)/retrieveXMLAndCompare.sh $DATEREQUETE > $LOGFILE


RAPPORTBODY=$TMP"/retoursDouanes/retrieveXMLAndCompare_rapport_"$DATE".txt"
NBXMLIDENTIQUES=`cat $LOGFILE | grep "XML sont identiques" | wc -l`
NBXMLDIFFERENTS=`cat $LOGFILE | grep "XML differents" | wc -l`
NBXMLNONTRANSMIS=`cat $LOGFILE | grep -C 1 "n'a pas été transmise aux douanes" | grep "XML differents" | wc -l`
NBDRMDOUANEABSENTE=`cat $LOGFILE | grep "n'a pas été trouvée" | wc -l`
NBNUMACCISEMALDRM=`cat $LOGFILE | grep "Le numéro d'accise" | cut -d ":" -f 3 | sed "s/ Le numéro d'accise //" | sed "s/ ne correspond pas a celui de l'établissement (/;/g" | sed "s/ | /;/" | sed "s/)//" | wc -l`
NBNUMACCISEMAL=`cat $LOGFILE | grep "Le numéro d'accise" | cut -d ":" -f 3 | sed "s/ Le numéro d'accise //" | sed "s/ ne correspond pas a celui de l'établissement (/;/g" | sed "s/ | /;/" | sed "s/)//" | sort | uniq | wc -l`

echo -e "Du $DATEREQUETE à ce jour\n\n" > $RAPPORTBODY;
echo -e "   XML identiques = "$NBXMLIDENTIQUES" DRM\n\n" >> $RAPPORTBODY;
echo -e "   XML différents = "$NBXMLDIFFERENTS" DRM (dont "$NBXMLNONTRANSMIS" DRM télédéclarées qui n'ont pas été transmises aux douanes) \n\n" >> $RAPPORTBODY;
echo -e "Détails :\n\n" >> $RAPPORTBODY;
echo -e "   DRM pour lesquelles une modificatrice a été ouverte : \n\n" >> $RAPPORTBODY;
cat $LOGFILE | grep -C 1 "DRM modificatrice ouverte" | grep "XML differents" | cut -d ' ' -f 1 | sed -r "s|DRM-([0-9]+)-([0-9]+)|         $URLDRMINTERNE\1\/visualisation\/\2|" >> $RAPPORTBODY;

echo -e "\n\n   DRM non trouvées sur la plateforme mais existantes sur CIEL = "$NBDRMDOUANEABSENTE" (les identifiants existent sur la plateforme). Liste des Identifiants impacté par ce cas :\n\n" >> $RAPPORTBODY;

cat $LOGFILE | grep "n'a pas été trouvée" | sed -r 's/(.*)La DRM de (.+) (.+)/\2/g' | cut -d ' ' -f 1 | sort | uniq | sed -r "s|(.*)|         $URLDRMINTERNE/etablissement\1|g" >> $RAPPORTBODY;

echo -e "\n\n   Numéro d'accise mal référencé = "$NBNUMACCISEMALDRM" DRM correspondant à "$NBNUMACCISEMAL" accises :\n\n" >> $RAPPORTBODY;

cat $LOGFILE | grep "Le numéro d'accise" | cut -d ":" -f 3 | sed "s/ Le numéro d'accise //" | sed "s/ ne correspond pas a celui de l'établissement (/;/g" | sed "s/ | /;/" | sed "s/)//" | sort | uniq | sed -r "s|(FR.+);(.+);(.*)|         $URLDRMINTERNE/etablissement\2 \1 (obtenu des douanes) Accise référencée sur le portail de l'interpro  : \3|g" >> $RAPPORTBODY;

cat $RAPPORTBODY  | iconv -t ISO-8859-1 | mail -s "[RAPPORT RETOUR DOUANE du $DATEFORMAT]" "adresse@adresse.com";
