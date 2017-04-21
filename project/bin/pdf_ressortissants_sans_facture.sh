#!/bin/bash

. bin/config.inc

GENERATION=$1

if ! echo $GENERATION | grep GENERATION  > /dev/null ; then
  echo "GENERATION argument missing"
  exit 1;
fi

curl -s http://$COUCHHOST:$COUCHPORT/$COUCHBASE/$GENERATION | sed 's/.*"documents":\[//' | sed 's/].*//' | sed 's/-[0-9]*",*/\n/g' | sed 's/"FACTURE-//' | sort -u > $TMP/factures.list
php symfony export:societe $SYMFONYTASKOPTIONS | grep ACTIF | grep 'REGION_CVO' | awk -F ';' '{print $1}' societe.csv | grep '^[0-9]' | sed 's/411//' | sort -u > $TMP/societes.list

diff /tmp/societes.list /tmp/factures.list | grep '<' | sed 's/< //' | while read $societeid; do
  php symfony societe:pdfentete $SYMFONYTASKOPTIONS $societeid
done
