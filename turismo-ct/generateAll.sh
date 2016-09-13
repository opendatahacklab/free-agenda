#!/bin/sh
php ontology.php >agenda.owl
java -jar ../../semanticoctopus/target/semanticoctopus-0.1.1.jar http://localhost/~cristianolongo/opendatahacklab/free-agenda/turismo-ct/agenda.owl >tmp.owl
curl -X PUT -H "Content-Type: application/rdf+xml" -u 'cristianolongo'  --data-binary @tmp.owl https://dydra.com/cristianolongo/agenda-libera-del-turismo-a-catania/service
rm tmp.owl

