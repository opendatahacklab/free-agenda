#!/bin/sh
php ontology.php >agenda.owl
java -jar ../../semanticoctopus/target/semanticoctopus-0.1.1.jar http://localhost/~cristianolongo/opendatahacklab/free-agenda/agenda-unica-ct/agenda.owl >tmp.owl
curl -X PUT -H "Content-Type: application/rdf+xml" -u 'cristianolongo'  --data-binary @tmp.owl http://dydra.com/cristianolongo/agenda-unica-ct/service
sleep 5
rm tmp.owl
php calendar.php >agenda.ics
php rssfeed.php >agenda.atom

