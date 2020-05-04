#!/bin/bash

. bin/config.inc
LOCK="/tmp/compte_update_ldap.lock"
SEQ="/tmp/compte_update_ldap.seq"
if test -f $LOCK ; then
    exit 1
fi
touch $LOCK
if test -s $SEQ; then
    echo -n "&since="$(cat $SEQ | sed 's/[^0-9a-z_-]//gi') > $SINCESEQ
fi

curl -s "http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_changes?feed=continuous&timeout=59000"$SINCESEQ | grep "COMPTE" | grep -v "_design" | while read ligne
do
    echo $ligne | sed 's/.*"seq"://' | sed 's/,.*//' | sed 's/"/g/' > $SEQ
    php symfony compte:ldap-update $SYMFONYTASKOPTIONS $(echo $ligne | sed 's/.*"id":"//' | sed 's/",.*//' )
done

rm $LOCK
