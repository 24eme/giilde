#!/bin/bash

. bin/config.inc

mkdir $DRMEXTERNEIMPORTDIR 2> /dev/null


for u in "${GETDRMEXTERNECMD[@]}"
do
    eval $u
done

ls $DRMEXTERNEIMPORTDIR | while read file
do
    echo "Import $DRMEXTERNEIMPORTDIR/$file"
    PERIODE=$(echo -n $file | cut -d "_" -f 2)
    IDENTIFIANT=$(echo -n $file | cut -d "_" -f 3)
    cat $DRMEXTERNEIMPORTDIR/$file | grep -v ";dont_revendique;" | grep -E "^(ANNEXE|CRD|CAVE)" | grep -v ';acquitté;' > $DRMEXTERNEIMPORTDIR/$file.tmp
    mv $DRMEXTERNEIMPORTDIR/$file.tmp $DRMEXTERNEIMPORTDIR/$file
    php symfony drm:edi-import $DRMEXTERNEIMPORTDIR/$file $PERIODE $IDENTIFIANT $SYMFONYTASKOPTIONS --trace;
done
