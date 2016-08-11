<?php

header("Content-type:text/calendar");

require_once( "sparqllib.php" );
require_once('Calendar.php');

//Mi collego al database e in caso di errore esco.
$db = sparql_connect( "http://dydra.com/cristianolongo/agenda-unica-ct/sparql" );

if( !$db ) {
	print $db->errno() . ": " . $db->error(). "\n";
	exit;
}

$db->ns( "event","http://purl.org/NET/c4dm/event.owl#" );
$db->ns( "locn","http://www.w3.org/ns/locn#" );
$db->ns( "time","http://www.w3.org/2006/time#" );
$db->ns( "dcterms","http://purl.org/dc/terms/" );
$db->ns( "foaf","http://xmlns.com/foaf/0.1/" );
$db->ns( "locn","http://www.w3.org/ns/locn#");
$db->ns( "wsg84","http://www.w3.org/2003/01/geo/wgs84_pos#");
$db->ns( "rdfs","http://www.w3.org/2000/01/rdf-schema#");
$db->ns( "sioc","http://rdfs.org/sioc/ns#");
$db->ns( "dc","http://purl.org/dc/elements/1.1/");

//Imposto ed eseguo la query per estrarre tutti gli eventi, in caso di errore esco
$query = "SELECT DISTINCT ?item ?itemlabel ?timeStart ?address WHERE {
  ?item locn:location ?site .
	?item rdfs:label ?itemlabel .
	?item event:time ?t .
	?t time:hasBeginning ?hasB .
	?hasB time:inXSDDateTime ?timeStart .
	?site locn:address ?a .
	?a locn:fullAddress ?address .

} ORDER BY DESC(?timeStart) ?item";

$result = $db->query( $query );
if( !$result ) {
	print $db->errno() . ": " . $db->error(). "\n";
	exit;
}
	//Creazione di un nuovo calendario
$calendar = new Calendar("agenda-unica-calendar");

	//Aggiungo gli eventi al calendario
while( $row = $result->fetch_array()) {
	$uid=rtrim(chunk_split( $row['item'], 65, "\r\n "),"\r\n ");	
	$summary=rtrim(chunk_split( $row['itemlabel'], 65, "\r\n "),"\r\n ");	
	$calendar->add_event($row['timeStart'],$row['timeStart'],$uid,$summary,$row['address']);
}
	//Chiusura e download del calendario
	$calendar->show();

