#!/bin/bash

. bin/config.inc

GENERATION=$1

if ! echo $GENERATION | grep GENERATION  > /dev/null ; then
  echo "GENERATION argument missing"
  exit 1;
fi

curl -s http://$COUCHHOST:$COUCHPORT/$COUCHBASE/$GENERATION | sed 's/.*"documents":\[//' | sed 's/].*//' | sed 's/-[0-9]*",*/\n/g' | sed 's/"FACTURE-//' | sort -u > $TMP/factures.list
php symfony export:societe $SYMFONYTASKOPTIONS | grep ACTIF | grep 'REGION_CVO' | awk -F ';' '{print $1}' | grep '^[0-9]' | sed 's/411/0/' | sort -u > $TMP/societes.list

diff $TMP/societes.list $TMP/factures.list | grep '<' | sed 's/< //' | while read societeid; do
  php symfony societe:pdfentete $SYMFONYTASKOPTIONS $societeid
done > $TMP/pdfentete.list

pdftk $($TMP/pdfentete.list) cat output $TMP/$GENERATION".pdf"

