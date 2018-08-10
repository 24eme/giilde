#!/bin/bash

. bin/config.inc

for exportarg in "${PUTDRMEXTERNE[@]}"
do
    eval bash bin/export_drm_teledeclare.sh $exportarg
done
