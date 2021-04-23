#!/bin/bash

. bin/config.inc
LOCK="/tmp/compte_update_ldap_"$PROJET".lock"
SEQ="/tmp/compte_update_ldap_"$PROJET".seq"
if test -f $LOCK ; then
    exit 1
fi
touch $LOCK
if test -s $SEQ; then
    SINCESEQ=$(echo -n "&since="$(cat $SEQ))
fi

curl -s "http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_changes?feed=continuous&timeout=59000"$SINCESEQ | grep "COMPTE" | grep -v "_design" | while read ligne
do
    echo $ligne | sed 's/.*"seq"://' | sed 's/,.*//' | sed 's/"//g' > $SEQ
    PARAM=$(echo $ligne | sed 's/.*"id":"//' | sed 's/",.*//' )
    php symfony compte:ldap-update $SYMFONYTASKOPTIONS --trace $PARAM || echo "ERROR with symfony compte:ldap-update $SYMFONYTASKOPTIONS $PARAM"
done

rm $LOCK
