#!/bin/bash

. $(dirname $0)/config.inc

if ! test $WORKINGDIRDISTANT
then
	WORKINGDIRDISTANT=$WORKINGDIR
fi

rsync -aO $WORKINGDIR"/web/generation/" $COUCHDISTANTHOST":"$WORKINGDIRDISTANT"/web/generation"
rsync -aO $WORKINGDIR"/data/upload/" $COUCHDISTANTHOST":"$WORKINGDIRDISTANT"/data/upload"
rsync -aO $WORKINGDIR"/data/dateDrmDouane" $COUCHDISTANTHOST":"$WORKINGDIRDISTANT"/data/"
rsync -aO $WORKINGDIR"/web/doc/docs/" $COUCHDISTANTHOST":"$WORKINGDIRDISTANT"/web/doc/docs"
rsync -aO $WORKINGDIR"/web/doc-edi/" $COUCHDISTANTHOST":"$WORKINGDIRDISTANT"/web/doc-edi"
