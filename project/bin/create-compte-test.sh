#!/bin/bash

. bin/config.inc
. bin/import_functions.inc

RAISONSOCIALE=$1;
EMAIL=$2;
MOTDEPASSE=$3;
BASEDEST=$4;

if ! test "$RAISONSOCIALE"; then
    echo "Il manque la raison sociale en 1er argument"
    exit;
fi

if ! test "$MOTDEPASSE"; then
    echo "Il manque le mot de passe en 2ème argument"
    exit;
fi

if ! test "$EMAIL"; then
    echo "Il manque l'email en 3ème argument"
    exit;
fi

echo "Voulez vous créer société/etablissement/compte de $RAISONSOCIALE [$EMAIL] (mdp=$MOTDEPASSE) ? ";

if test "$BASEDEST"; then
    echo "/!\ Celui-ci sera directement copié vers la base $BASEDEST /!\ "
fi

if ! test "$BASEDEST"; then
    echo "/!\ PAS DE COPIE VERS UNE AUTRE BASE /!\ ";
fi

select yn in "Oui" "Non"; do
    case $yn in
        Oui ) RES=$(php symfony maintenance:create-compte-test $SYMFONYTASKOPTIONS "$1" "$2" "$3");
        echo $RES;
        ID=$(echo $RES | cut -d " " -f 1 | sed 's|SOCIETE-||');
        if test "$BASEDEST"; then
            echo "copie de SOCIETE-$ID COMPTE-"$ID"01 ETABLISSEMENT-"$ID"01 dans la base "$BASEDEST;
            curl http://$COUCHHOST:$COUCHPORT/$COUCHBASE/SOCIETE-$ID > SOCIETETEST
            cat SOCIETETEST | sed -r 's|","_rev":"[0-9a-z-]+","|","|' > SOCIETETEST.json
            curl -X POST -d '@SOCIETETEST.json' -H "content-type: application/json" $BASEDEST

            curl http://$COUCHHOST:$COUCHPORT/$COUCHBASE/ETABLISSEMENT-$ID"01" > ETBTEST
            cat ETBTEST | sed -r 's|","_rev":"[0-9a-z-]+","|","|' > ETBTEST.json
            curl -X POST -d '@ETBTEST.json' -H "content-type: application/json" $BASEDEST

            curl http://$COUCHHOST:$COUCHPORT/$COUCHBASE/COMPTE-$ID"01" > COMPTETEST
            cat COMPTETEST | sed -r 's|","_rev":"[0-9a-z-]+","|","|' > COMPTETEST.json
            curl -X POST -d '@COMPTETEST.json' -H "content-type: application/json" $BASEDEST

            rm SOCIETETEST ETBTEST COMPTETEST SOCIETETEST.json ETBTEST.json COMPTETEST.json
        fi
         break;;
        Non ) exit;;
    esac
done
