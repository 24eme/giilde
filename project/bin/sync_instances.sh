#!/bin/bash

. $(dirname $0)/config.inc

rsync -a $WORKINGDIR"/web/generation/" $COUCHDISTANTHOST":"$WORKINGDIR"/web/generation"
rsync -a $WORKINGDIR"/data/upload/" $COUCHDISTANTHOST":"$WORKINGDIR"/data/upload"
