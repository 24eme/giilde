#!/bin/bash
. $(dirname $0)/config.inc

NUMEROACCISE=$1
PERIODE=$2
CVI=$3
DATEREQUETE=$4;
#if test "$DATEREQUETE"; then
#    DATEREQUETE=$(cat $WORKINGDIR"/data/dateDrmDouane");
#fi

APPLICATION=$(echo $SYMFONYTASKOPTIONS | sed -r 's|--application=(.+) (.*)|\1|');
PATHFILETMP=$WORKINGDIR"/cache/"$APPLICATION"/prod/majDrmUrl";

URL_PERIODE="/"
if test "$PERIODE" ; then
    URL_PERIODE="/"$(echo $PERIODE | sed 's/..$//')"/"$(echo $PERIODE | sed 's/^....//')"/"
fi
URL_LASTDOC=$(curl -s $CIEL_URL_RETOURXML""$URL_PERIODE"?accise="$NUMEROACCISE"&from="$DATEREQUETE | sort -r | head -n 1)

echo "url:"$URL_LASTDOC;
if test $URL_LASTDOC ; then
if !test "$CVI" || curl $URL_LASTDOC | grep "$CVI" > /dev/null ; then
    cd $WORKINGDIR;
    OUT=$(php5 symfony $SYMFONYTASKOPTIONS drm:storeXMLRetour --force-update="1" $URL_LASTDOC)
	RET=$?
	DRM=$(echo $OUT | sed 's/ .*//')
	echo $OUT
	if test $RET -eq 0 ; then
		php5 symfony $SYMFONYTASKOPTIONS drm:compareXMLs $DRM
	fi
fi
fi

rm -f $PATHFILETMP;
