#!/bin/sh
java -jar ~/semanticoctopus/0.1.1/semanticoctopus-0.1.1.jar http://localhost/~cristianolongo/opendatahacklab/free-agenda/turismo-ct/ontology.php >tmp.owl
curl -X PUT -H "Content-Type: application/rdf+xml" -u 'cristianolongo'  --data-binary @tmp.owl https://dydra.com/cristianolongo/agenda-libera-del-turismo-a-catania/service
sleep 5
rm tmp.owl
