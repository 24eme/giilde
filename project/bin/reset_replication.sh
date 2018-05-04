#!/bin/bash

. $(echo $0 | sed 's/[^\/]*$//')config.inc

if ! test "$REPLICATIONDOC"; then
    echo "Le doc de replication n'est pas spécifié"
    exit;
fi

rev=$(curl -s http://$COUCHHOST:$COUCHPORT/_replicator/$REPLICATIONDOC | sed 's/.*rev":"//' | sed 's/".*//' )
curl -s -X DELETE http://$COUCHHOST:$COUCHPORT/_replicator/$REPLICATIONDOC?rev=$rev  > /dev/null
curl -s -X PUT -d '{"_id":"$REPLICATIONDOC","target":"'$COUCHBASE'","source":"'http://$COUCHDISTANTHOST:$COUCHPORT/$COUCHBASE'","continuous":true,"user_ctx": {"roles": ["_admin"]}}' http://$COUCHHOST:$COUCHPORT/_replicator/$REPLICATIONDOC  > /dev/null
