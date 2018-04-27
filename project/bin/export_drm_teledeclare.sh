. bin/config.inc

PRODUIT=$1
EXPORT_PATH=$2

if ! test "$PRODUIT"; then
    echo "Il manque le nom du produit à filtrer en 1er argument"
    exit;
fi

if ! test "$EXPORT_PATH"; then
    echo "Il manque le chemin (relatif depuis le dossier \"project\") de stockage des fichiers CSV des DRM en 2ème argument"
    exit;
fi

curl -s "http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_design/drm/_view/ciel?startkey=\[1\]&endkey=\[1,\[\]\]" | cut -d "," -f 1 | sed 's/{"id":"//' | sed 's/"//' | grep -E "^DRM" | while read id
do
    DRMFILETMP=$TMP/$id$(date +%Y%m%d%H%M%S)
    php symfony drm:export-csv $id $SYMFONYTASKOPTIONS > $DRMFILETMP
    if test $(cat $DRMFILETMP | grep -E "^CAVE;" | grep $PRODUIT | wc -l | sed -r 's/^0$//'); then
        echo $id
        mkdir -p $WORKINGDIR/$EXPORT_PATH 2> /dev/null
        cp $DRMFILETMP $WORKINGDIR/$EXPORT_PATH/$id.csv
    fi
    rm $DRMFILETMP
done
