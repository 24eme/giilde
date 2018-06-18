#!/bin/bash
. $(dirname $0)/config.inc

DATEREQUETE=$1
PERIODE=$2
NUMEROACCISE=$3
CVI=$4

APPLICATION=$(echo $SYMFONYTASKOPTIONS | sed -r 's|--application=(.+) (.*)|\1|');
LAST=""
PATHFILETMP=$WORKINGDIR"/cache/"$APPLICATION"/prod/majDrmUrl";

echo "" > $PATHFILETMP;

curl -s $CIEL_URL_RETOURXML"/?from="$DATEREQUETE | sort -r | while read url ; do
  CURRENT=$(echo $url | sed -r 's/(.+)\/([0-9]{4}\/[0-9]{2}\/[0-9A-Z]+).*/\2/g');
  ACCISELOCAL=$(echo $url | sed -r 's/(.+)\/(FR[0-9A-Z]{11})_([0-9]{7}).*/\2/g');
  if [ "$CURRENT" == "$LAST" ]; then
    continue;
  fi
    if [ "$NUMEROACCISE" == "$ACCISELOCAL" ] ; then
        CONTENTDRM=$(curl -s $url);
        MOIS=$(echo $CONTENTDRM | sed -r 's|.+<mois>([0-9]+)</mois>.+|\1|' | awk -F '' '{print sprintf("%02d",$0);}');
        ANNEE=$(echo $CONTENTDRM | sed -r 's|.+<annee>([0-9]+)</annee>.+|\1|');
        PERIODELOCAL=$ANNEE""$MOIS;
        if [ "$PERIODE" == "$PERIODELOCAL" ] ; then
            echo $url > $PATHFILETMP;
            break;
        fi
    fi
  LAST=$(echo $url | sed -r 's/(.+)\/([0-9]{4}\/[0-9]{2}\/[0-9A-Z]+).*/\2/g')
done

URLFOUND=$(cat $PATHFILETMP);
if ! test $URLFOUND ; then
    curl -s $CIEL_URL_RETOURXML"/?from="$DATEREQUETE | sort -r | while read url ; do
      CURRENT=$(echo $url | sed -r 's/(.+)\/([0-9]{4}\/[0-9]{2}\/[0-9A-Z]+).*/\2/g');

      if [ "$CURRENT" == "$LAST" ]; then
        continue;
      fi
      CONTENTDRM=$(curl -s $url);
      CVILOCAL=$(echo $CONTENTDRM | sed -r 's|.+<numero-cvi>([0-9]+)</numero-cvi>.+|\1|');
      if [ "$CVILOCAL" == "$CVI" ] ; then
          MOIS=$(echo $CONTENTDRM | sed -r 's|.+<mois>([0-9]+)</mois>.+|\1|' | awk -F '' '{print sprintf("%02d",$0);}');
          ANNEE=$(echo $CONTENTDRM | sed -r 's|.+<annee>([0-9]+)</annee>.+|\1|');
          PERIODELOCAL=$ANNEE""$MOIS;
          if [ "$PERIODE" == "$PERIODELOCAL" ] ; then
                echo $url > $PATHFILETMP;
                break;
          fi
      fi
      LAST=$(echo $url | sed -r 's/(.+)\/([0-9]{4}\/[0-9]{2}\/[0-9A-Z]+).*/\2/g')
    done
fi
URLFOUND=$(cat $PATHFILETMP);
echo "url:"$URLFOUND;
if test $URLFOUND ; then
    cd $WORKINGDIR;
    OUT=$(php5 symfony $SYMFONYTASKOPTIONS drm:storeXMLRetour $URLFOUND)
	RET=$?
	DRM=$(echo $OUT | sed 's/ .*//')
	echo $OUT
	if test $RET -eq 0 ; then
		php5 symfony $SYMFONYTASKOPTIONS drm:compareXMLs $DRM
	fi
fi

rm -f $PATHFILETMP;
