#!/bin/bash

. bin/config.inc
LOCK="/tmp/compte_update_ldap.lock"
SEQ="/tmp/compte_update_ldap.seq"
if test -f $LOCK ; then
    exit 1
fi
touch $LOCK

curl -s "http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_changes?since="$(cat $SEQ | sed 's/[^0-9]//g') | grep "COMPTE" > $TMP/comptes_ldap_ids

cat $TMP/comptes_ldap_ids | while read ligne
do
    echo $ligne | awk -F '"' '{print $3}' | sed 's/[^0-9]//g' > $SEQ
    php symfony compte:ldap-update $SYMFONYTASKOPTIONS $(echo $ligne | awk -F '"' '{print $6}')
done

rm $TMP/comptes_ldap_ids $LOCK
