#!/bin/bash

. $(echo $0 | sed 's/[^\/]*$//')config.inc

if [ "$(echo $COUCHTEST | grep -E _test$)" == "" ]
then
    echo "La base COUCHTEST ($COUCHTEST) ne semble pas être une base de test ( doit se terminer par \"_test\", par exemple http://localhost:5984/giilda_app_test )"
    exit;
fi

APPLICATION=$1
FORCE=$2

if [ ! $APPLICATION ]
then
    echo "Vous devez définir une application en argument :"
    echo ;
    echo "$0 <APPLICATION> [FORCE]";
    exit;
fi

PID_PATH=/tmp/$APPLICATION".integrationcontinue.pid"

if test -e $PID_PATH; then
    echo "Une instance tourne déjà $PID_PATH"
exit 2;
fi

echo $$ > $PID_PATH

if ! test "$WORKINGDIR"; then
    WORKINGDIR=$(dirname $0)"/../"
fi

mkdir -p $XMLTESTDIR 2> /dev/null

#git fetch > /dev/null 2>&1
#git reset --hard origin/master

BRANCH=$(cat ../.git/HEAD | sed -r 's|^ref: refs/heads/||')
LASTCOMMIT=$(cat $WORKINGDIR"/../.git/refs/heads/"$BRANCH)
DATE=$(date +%Y%m%d%H%M%S)
BRANCH=$(echo $BRANCH | tr '/' '-')

if [ "$( ls $XMLTESTDIR | grep $LASTCOMMIT | grep $APPLICATION"" )" != "" ] && [ "$FORCE" = "" ]
then
    echo "Test déjà effectué sur le commit $LASTCOMMIT"
    rm $PID_PATH
    exit;
fi

curl -s -X DELETE $COUCHTEST
curl -s -X PUT $COUCHTEST  || ( echo "connexion à $COUCHTEST impossible"  ;  exit 2 )

cd ..
make clean
make
cd -

ls $WORKINGDIR"/data/configuration/"$APPLICATION | while read jsonFile
do
    curl -s -X POST -d @data/configuration/$APPLICATION/$jsonFile -H "content-type: application/json" $COUCHTEST
done

php symfony cc

XMLFILE=$XMLTESTDIR/"$DATE"_"$APPLICATION"_"$LASTCOMMIT"_"$BRANCH".xml

APPLICATION=$APPLICATION NODELETE=1 php symfony test:unit --xml=$XMLFILE
sed -i "s|$WORKINGDIR/||" $XMLFILE

rm $PID_PATH
