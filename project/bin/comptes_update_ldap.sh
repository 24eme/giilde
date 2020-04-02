#!/bin/bash

. bin/config.inc
LOCK="/tmp/compte_update_ldap.lock"
SEQ="/tmp/compte_update_ldap.seq"
if test -f $LOCK ; then
    exit 1
fi
touch $LOCK
if ! test -s $SEQ; then
    echo 0 > $SEQ
fi

curl -s "http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_changes?continuous=1&timeout=590000&since="$(cat $SEQ | sed 's/[^0-9]//g') | grep "COMPTE" | while read ligne
do
    echo $ligne | awk -F '"' '{print $3}' | sed 's/[^0-9]//g' > $SEQ
    php symfony compte:ldap-update $SYMFONYTASKOPTIONS $(echo $ligne | awk -F '"' '{print $6}')
done

rm $TMP/comptes_ldap_ids $LOCK
