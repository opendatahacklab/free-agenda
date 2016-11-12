#!/bin/sh
php ontology.php >agenda.owl
java -jar ../../semanticoctopus/target/semanticoctopus-0.1.1.jar http://localhost/~cristianolongo/opendatahacklab/free-agenda/lecce/agenda.owl >tmp.owl
curl -X PUT -H "Content-Type: application/rdf+xml" -u 'cristianolongo'  --data-binary @tmp.owl http://dydra.com/cristianolongo/agenda-comune-di-lecce/service
sleep 5
rm tmp.owl
rm agenda.owl
