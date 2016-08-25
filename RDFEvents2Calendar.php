<?php

require_once( "sparqllib.php" );
require_once('Calendar.php');

/**
 * Generate an Internet Calendar file (see RFC 2445)
 * by retrieving events from a sparql endpoint.
 *
 * Copyright 2016 Federico Frasca, Cristiano Longo
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *d
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Federico Frasca
 * @author Cristiano Longo
 */
class RDFEvents2Calendar{
	private $sparqlEndpoint;
	private $calendarId;
	
	/**
	 * Prepare a generator which will retrieve events from the specified 
	 * sparql endpoint.
	 * 
	 * @param URL $sparqlEndpoint
	 * @param string $calendarId calendar name (ascii chars without space) used to
	 * generate the calendar unique identifier
	 */
	public function __construct($sparqlEndpoint, $calendarId)
	{
		$this->sparqlEndpoint = $sparqlEndpoint;
		$this->calendarId= $calendarId;
	}
	
	/**
	 * Generate the calendar file, send it on the standard output
	 */
	public function generate(){
		$result=$this->retrieveEvents();
		$calendar = new Calendar($this->calendarId);
		
		//Aggiungo gli eventi al calendario
		while( $row = $result->fetch_array()) {
			$uid=rtrim(chunk_split( $row['item'], 65, "\r\n "),"\r\n ");
			$summary=rtrim(chunk_split( $row['itemlabel'], 65, "\r\n "),"\r\n ");
			$location=rtrim(chunk_split( $row['address'], 65, "\r\n "),"\r\n ");
			$calendar->add_event($row['timeStart'],$row['timeStart'],$uid,$location,$summary);
		}
		//Chiusura e download del calendario
		header("Content-type:text/calendar");
		$calendar->show();		
	}
	
	/**
	 * Perform the query to retrieve events. Throw a generic exception 
	 * if some error occur.
	 */
	private function retrieveEvents(){
		$db = sparql_connect($this->sparqlEndpoint);
		if( !$db ) 
			throw new Exception("Unable to connect to the endpoint $this->sparqlEndpoint: $db->error");
		
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
				
		$query = "SELECT DISTINCT ?item ?itemlabel ?timeStart ?address WHERE {
				?item locn:location ?site .
				?item rdfs:label ?itemlabel .
				?item event:time ?t .
				?t time:hasBeginning ?hasB .
				?hasB time:inXSDDateTime ?timeStart .
				?site locn:address ?a .
				?a locn:fullAddress ?address
		} ORDER BY DESC(?timeStart) ?item";
		
		$result = $db->query( $query );
		if( !$result ) 
			throw new Exception("Error performing sparql query: $db->error");
		return $result;
	}
}


