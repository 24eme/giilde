. bin/config.inc

PRODUIT=$1
EXPORT_PATH=$2
IP_AUTHORIZED=$3
NUMEROSEQUENCE=0
COUCHDBSEQFILE=$EXPORT_PATH/.couchdbseq
CHANGESFILE=$TMP/$(date +%Y%m%d%H%M%S)_export_changes
LISTDRMFILE=$TMP/$(date +%Y%m%d%H%M%S)_export_drms

if test -f $COUCHDBSEQFILE
then
    NUMEROSEQUENCE=$(cat $COUCHDBSEQFILE)
fi

if ! test "$PRODUIT"; then
    echo "Il manque le nom du produit à filtrer en 1er argument"
    exit;
fi

if ! test "$EXPORT_PATH"; then
    echo "Il manque le chemin de stockage des fichiers CSV des DRM en 2ème argument"
    exit;
fi

echo -n > $LISTDRMFILE

curl -s http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_changes?since=$NUMEROSEQUENCE > $CHANGESFILE
LASTNUMEROSEQUENCE=$(grep "last_seq" $CHANGESFILE | sed 's/"last_seq"://' | sed 's/}//')

cat $CHANGESFILE | grep "\"DRM-" | grep -v "deleted" | cut -d "," -f 2 | sed 's/"id":"//' | sed 's/"//' | while read id
do
    curl -s http://$COUCHHOST:$COUCHPORT/$COUCHBASE/$id | jq -c '[._id,.teledeclare,.valide.date_signee,.declarant.no_accises]' | sed 's/\[//' | sed 's/\]//' | sed 's/"//g' | sed 's/null//' >> $LISTDRMFILE
done

mkdir -p $EXPORT_PATH 2> /dev/null

cat $LISTDRMFILE | while read ligne
do
    ID=$(echo $ligne | cut -d "," -f 1)
    DATE=$(echo $ligne | cut -d "," -f 3 | sed 's/-//g')
    NOACCISES=$(echo $ligne | cut -d "," -f 4)
    PERIODE=$(echo $ID | cut -d "-" -f 3)
    DRMFILE=$TMP/$id$(date +%Y%m%d%H%M%S).csv
    php symfony drm:export-csv $ID $SYMFONYTASKOPTIONS | grep -E "$PRODUIT" > $DRMFILE
    if test $DATE && test $(cat $DRMFILE | grep -E "^CAVE;" | grep -E "$PRODUIT" | wc -l | sed -r 's/^0$//'); then
        echo $ID
        cp $DRMFILE $EXPORT_PATH/"$DATE"_"$PERIODE"_"$NOACCISES"_"$ID".csv
    fi
    rm $DRMFILE
done

echo $LASTNUMEROSEQUENCE > $COUCHDBSEQFILE
rm $CHANGESFILE
rm $LISTDRMFILE

if test "$IP_AUTHORIZED"; then
    echo "<RequireAny>" > $EXPORT_PATH/.htaccess.tmp
    echo $IP_AUTHORIZED | tr " " "\n" | while read ip; do echo "    Require ip $ip" >> $EXPORT_PATH/.htaccess.tmp; done;
    echo "</RequireAny>" >> $EXPORT_PATH/.htaccess.tmp
    mv $EXPORT_PATH/.htaccess{.tmp,}
fi

echo "<?php
header(\"Content-Type: text/plain\");

\$files = scandir(dirname(__FILE__));
rsort(\$files);

\$date = null;

if(isset(\$_GET['date'])) {
    \$date = str_replace('-', '', \$_GET['date']);
}

foreach(\$files as \$file) {
	if(!preg_match('/.csv$/', \$file)) {
		continue;
	}

    preg_match('/^([0-9]+)_/', \$file, \$matches);

    \$fileDate = \$matches[1];

    if(\$date && \$fileDate < \$date) {
        continue;
    }

    echo \"http\".((isset(\$_SERVER['HTTPS'])) ? \"s\" : \"\").\"://\".\$_SERVER['HTTP_HOST'].preg_replace(\"|list\.php.*$|\", \"\", \$_SERVER['REQUEST_URI']).\$file.\"\n\";
}
" > $EXPORT_PATH/list.php
