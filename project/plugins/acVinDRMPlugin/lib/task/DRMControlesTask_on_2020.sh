#!/bin/bash
i#Params:
#$1 is server
#$2 is application
#$3 is env
if [ "$#" -ne 3 ]
  then
    echo "Erro No argument: please put server | application name (ivbd, ivso ...) | env";
    exit 1
fi

curl http://$1:5984/giilda_$2_$3/_design/drm/_view/all?reduce=false > /tmp/drm_id_$2
grep -Po '(DRM-[0-9]+-[0-9]{6}-M[0-9]{2}|DRM-[0-9]+-[0-9]{6})' /tmp/drm_id_$2 | uniq > /tmp/drm_ids_$2
grep -Po 'DRM-[0-9]+-2020[0-9]{2}-M[0-9]{2}|DRM-[0-9]+-2020[0-9]{2}' /tmp/drm_id_$2 | uniq > /tmp/drm_id2_$2
file="/tmp/drm_id2_$2"
i=0
while IFS= read -r line
do
	echo "**$i ** $line"
	i=$((i+1))
 	php ./symfony drm:controles --application=$2 "$line"
done < "$file" 
exit 0
