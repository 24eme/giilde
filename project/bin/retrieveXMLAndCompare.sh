#!/bin/bash

. $(dirname $0)/config.inc
LAST=""
curl -s $CIEL_URL_RETOURXML"/?from="$1 > /tmp/retrieveXML.$$.url

cat /tmp/retrieveXML.$$.url | sort -r | while read url ; do
  CURRENT=$(echo $url | sed -r 's/(.+)\/([0-9]{4}\/[0-9]{2}\/[0-9A-Z]+).*/\2/g');
  if [ "$CURRENT" == "$LAST" ]; then
    echo "L'xml d'url "$url" n'est pas la version la plus récente";
    continue;
  fi
	OUT=$(php5 symfony $SYMFONYTASKOPTIONS drm:storeXMLRetour --force-update="1" $url)
	RET=$?
	DRM=$(echo $OUT | sed 's/ .*//')
	echo $OUT
	if test $RET -eq 0 ; then
		php5 symfony $SYMFONYTASKOPTIONS drm:compareXMLs $DRM
	fi
  LAST=$(echo $url | sed -r 's/(.+)\/([0-9]{4}\/[0-9]{2}\/[0-9A-Z]+).*/\2/g')
done

if test "$2" && test -d $(dirname $2) ; then
    cat /tmp/retrieveXML.$$.url | awk -F '_' '{print $4}' | sort  | tail -n 1 | sed 's/\(....\)\(..\)\(..\).xml/\1-\2-\3/' > $2
fi
