#!/bin/bash

. bin/config.inc

DOCID=$1
NEWIDENTIFIANT=$2

if ! test "$DOCID"
then
    echo "Argument manquant, usage : bash bin/changedrmidentifiant.sh DOC_ID NEW_IDENFIANT"
fi

if ! test "$NEWIDENTIFIANT"
then
    echo "Argument manquant, usage : bash bin/changedrmidentifiant.sh DOC_ID NEW_IDENFIANT"
fi

OLDIDENTIFIANT=$(echo "$DOCID" | cut -d "-" -f 2)
NEWDOCID=$(echo "$DOCID" | sed "s/$OLDIDENTIFIANT/$NEWIDENTIFIANT/")

php symfony document:duplicate "$DOCID" "$NEWDOCID" $SYMFONYTASKOPTIONS;
php symfony document:setvalue "$NEWDOCID" identifiant $NEWIDENTIFIANT valide/identifiant $NEWIDENTIFIANT $SYMFONYTASKOPTIONS;
php symfony document:replace-hash "$NEWDOCID" --from="/mouvements/$OLDIDENTIFIANT" --to="/mouvements/$NEWIDENTIFIANT" $SYMFONYTASKOPTIONS

if test "$(php symfony document:get $NEWDOCID | grep "_rev")"
then
    php symfony document:delete $DOCID;
fi
