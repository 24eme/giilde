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
    php symfony drm:edi-import $DRMEXTERNEIMPORTDIR/$file $PERIODE $IDENTIFIANT $SYMFONYTASKOPTIONS --trace;
done
