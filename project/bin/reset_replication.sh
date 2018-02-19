#!/bin/bash

. $(echo $0 | sed 's/[^\/]*$//')config.inc

rev=$(curl -s http://$COUCHHOST:$COUCHPORT/_replicator/REPLICATION_PROD | sed 's/.*rev":"//' | sed 's/".*//' )
curl -s -X DELETE http://$COUCHHOST:$COUCHPORT/_replicator/REPLICATION_PROD?rev=$rev  > /dev/null
#Insure views are replicated as well
curl -s -X POST -H "Content-Type: application/json" -d '{"target":"'$COUCHBASE'","source":"'http://$COUCHDISTANTHOST:$COUCHPORT/$COUCHBASE'"}' http://$COUCHHOST:$COUCHPORT/_replicate > /dev/null
#Repost the replication
curl -s -X PUT -d '{"_id":"REPLICATION_PROD","target":"'$COUCHBASE'","source":"'http://$COUCHDISTANTHOST:$COUCHPORT/$COUCHBASE'","continuous":true}' http://$COUCHHOST:$COUCHPORT/_replicator/REPLICATION_PROD  > /dev/null
