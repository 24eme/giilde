#!/bin/bash

. $(echo $0 | sed 's/[^\/]*$//')config.inc

if [ "$(echo $COUCHTEST | grep -E _test$)" == "" ]
then
    echo "La base $COUCHTEST ne semble pas être une base de test (doit se terminer par \"_test\")"
    exit;
fi

APPLICATION=$1
FORCE=$2

if [ ! $APPLICATION ]
then
    echo "Vous devez définir une application"
    exit;
fi

PID_PATH=/tmp/$APPLICATION".integrationcontinue.pid"

if test -e $PID_PATH; then
    echo "Une instance tourne déjà $PID_PATH"
exit 2;
fi

echo $$ > $PID_PATH

mkdir $XMLTESTDIR 2> /dev/null

git pull -f

BRANCH=$(cat ../.git/HEAD | sed -r 's|^ref: refs/heads/||')
LASTCOMMIT=$(cat $WORKINGDIR/../.git/refs/heads/$BRANCH)
DATE=$(date +%Y%m%d%H%M%S)

if [ "$(ls $XMLTESTDIR | grep $LASTCOMMIT | grep $APPLICATION)" != "" ] && [ "$FORCE" = "" ]
then
    echo "Test déjà effectué sur le commit $LASTCOMMIT"
    rm $PID_PATH
    exit;
fi

curl -X DELETE $COUCHTEST
curl -X PUT $COUCHTEST

cd ..
make clean
make
cd -

ls $WORKINGDIR/data/configuration/$APPLICATION | while read jsonFile
do
    curl -X POST -d @data/configuration/$APPLICATION/$jsonFile -H "content-type: application/json" $COUCHTEST
done

php symfony cc

XMLFILE=$XMLTESTDIR/"$DATE"_"$APPLICATION"_"$LASTCOMMIT"_"$BRANCH".xml

APPLICATION=$APPLICATION NODELETE=1 php symfony test:unit --xml=$XMLFILE
sed -i "s|$WORKINGDIR/||" $XMLFILE

rm $PID_PATH
