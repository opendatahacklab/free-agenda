<?php

/**
 * Generate an RDF/XML representation of an event.
 *  
 * Copyright 2016 Michele Maresca
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  @author Michele Maresca
 */

/**
*  Include classes: Location e RDFLocnGenerator
*
*  @author Michele Maresca
*/
//include("./RDFLocnGenerator.php");

/**
*  Define the output file
*
*  @author Michele Maresca
*/
if(basename(__FILE__) == basename($argv[0]))
	define('XML_FILE', 'events.xml');


define('DATATYPE_ATTRIBUTE', 'rdf:datatype');

/**
*  Define RDF attribute rdf:About
*
*  @author Michele Maresca
*/

define('RDF_ATTRIBUTE', 'rdf:about');

/**
*  Define RDF DATATYPE
*
*  @author Michele Maresca
*/
define('RDF_DATATYPE', "http://www.w3.org/2001/XMLSchema#dateTime");

/**
*  Define Ontology attribute
*
*  @author Michele Maresca
*/
define('ONTOLOGY_VALUE', 'http://www.dmi.unict.it/~longo/opendatatour');

/**
 * Define RDF  Attributes value
 *
 * @author Michele Maresca
 */

/**
*  Define URI
*
*  @author Michele Maresca
*/
define('URI', 'http://opendatahacklab.org/agendaunica/');




/**
*  Attribute for the RDF node parent
*
*  @author Michele Maresca
*/

define('BASE_VALUE', 'http://example.org');
define('DC_VALUE', 'http://purl.org/dc/elements/1.1/');
define('GEO_VALUE', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
define('FOAF_VALUE', 'http://xmlns.com/foaf/0.1/');
define('ORG_VALUE', 'ttp://www.w3.org/ns/org#');
define('LOCN_VALUE', 'http://www.w3.org/ns/locn#/');
define('DCTERMS_VALUE', 'http://purl.org/dc/terms/');
define('RDFS_VALUE', 'http://www.w3.org/2000/01/rdf-schema#');
define('OWL_VALUE', 'http://www.w3.org/2002/07/owl#');
define('XSD_VALUE', 'http://www.w3.org/2001/XMLSchema#');
define('EVENT_VALUE', 'http://purl.org/NET/c4dm/event.owl#');
define('RDF_VALUE', 'http://purl.org/NET/c4dm/event.owl#');
define('TIME_VALUE', 'http://www.w3.org/2006/time#');
define('SIOC_VALUE', '&sioc;');


/**
 * Create a Event RDFnode to insert in the XML file
 *
 * @param $event An Event Object generated from the Class AgensaSheetParser
 *  
 * @author Michele Maresca
 */
class RDFEventsGenerator
{
	private $event;

	public function __construct($event)
	{
		$this->event = $event;
	}
	
	/**
	 * Get the namespaces which are expected to be set (aside with respective
	 * abbreviation prefixes) in the destination ontology. excluding the default ones
	 * rdf, rdfs and owl-
	 * 
	 * @return a map prefix -> namespace
	 */
	public static function getRequiredNamespaces(){
		return  array('event'=>'http://purl.org/NET/c4dm/event.owl#',
    		'time'=>'http://www.w3.org/2006/time#', 'dcterms'=>DCTERMS_VALUE);		
	}
	
	/**
	 * Get the set of vocabulary iris to be imported in the target ontology
	 */
	public static function getRequiredVocabularies(){
		return array('http://motools.sf.net/event/event.n3');
	}
	
	public function generateEvent($xml, $rdfParent, $uri)
	{
//Contain the event URI
		$eventURI = "";

//Create the Node for the RDFEvent
		$eventRDF = $xml->createElement("event:Event");
		$eventAttribute = $xml->createAttribute(RDF_ATTRIBUTE);
		$eventRDF->appendChild($eventAttribute);

		$eventName = $xml->createElement("rdfs:label");
		
		if ($this->event->description!=null && strlen($this->event->description))
			$this->addEventDescriptionElement($xml, $eventRDF, $this->event->description);
		
		if ($this->event->creationTime!=null)
			$this->addEventModificationTime($xml, $eventRDF, $this->event->creationTime);

		$eventTime = $xml->createElement("event:time");
		$timeInterval = $xml->createElement("time:Interval");
		$rdfTimeAttribute = $xml->createAttribute(RDF_ATTRIBUTE);
		$timeInterval->appendChild($rdfTimeAttribute);

		$timeHasBeginning = $xml->createElement("time:hasBeginning");

		$timeInstant = $xml->createElement("time:Instant");
		$rdfInstantAttribute = $xml->createAttribute(RDF_ATTRIBUTE);

		$timeXSDDataTime = $xml->createElement("time:inXSDDateTime");

		$eventName->appendChild($xml->createTextNode($this->event->name));

		if($this->event->locationId)
		{
			$eventPlace = $xml->createElement("event:place");
		    $eventPlaceAttribute = $xml->createAttribute("rdf:resource");
			$eventPlaceAttribute->value = RDFLocnGenerator::getLocationURI($uri, $this->event->locationId);
			$eventPlace->appendChild($eventPlaceAttribute);
			$eventRDF->appendChild($eventPlace);
		}

//If the Event Date is valid, the information will be saved in $date
		if($this->event->start)
			$date = $xml->createTextNode(date("c", $this->event->start->getTimestamp()));
		else
			$date = $xml->createComment("Data non valida!");


		$formatInstant = $this->setDateAttribute($date);

//Generate Event URI from formatInstant
		$eventURI = $this->generateURIEvent($formatInstant[0], $uri);

		$rdfInstantAttribute ->value = $eventURI."time/begin";
		$timeInstant->appendChild($rdfInstantAttribute);


		$timeXSDDataTime->appendChild($date);
		$rdfXSDAttribute = $xml->createAttribute(DATATYPE_ATTRIBUTE);
		$rdfXSDAttribute->value = RDF_DATATYPE;
		$timeXSDDataTime->appendChild($rdfXSDAttribute);

		$rdfTimeAttribute->value = $eventURI."time";
		$timeInstant->appendChild($timeXSDDataTime);
		$timeHasBeginning->appendChild($timeInstant);
		$timeInterval->appendChild($timeHasBeginning);
		$eventTime->appendChild($timeInterval);

		$eventRDF->appendChild($eventTime);
		$eventAttribute->value = $eventURI; 
		$eventRDF->appendChild($eventName);



//Append RDFNode to the RDF Parent
		$rdfParent->appendChild($eventRDF);

	}
	
	/**
	 * Add descrption as rdfs:comment to an individual representing an event
	 * 
	 * @param unknown $xml the xml document
	 * @param unknown $eventElement
	 * @param unknown $descrpition
	 */
	private function addEventDescriptionElement($xml, $eventElement, $descrpition){
		$textEl=$xml->createTextNode($descrpition);
		$commentEl=$xml->createElement("rdfs:comment");
		$eventElement->appendChild($commentEl);
		$commentEl->appendChild($textEl);
	}
	
	/**
	 * Add dcterm:modified to an event.
	 * 
	 * @param unknown $xml
	 * @param unknown $eventElement the xml element representing the event
	 * @param unknown $creationTime a DateTime object
	 */
	private function addEventModificationTime($xml, $eventElement, $creationTime){
		$modifiedElement=$xml->createElement('dcterms:modified');
		$eventElement->appendChild($modifiedElement);
		$timeTextNode=$xml->createTextNode(date("c", $creationTime->getTimestamp()));
		$modifiedElement->appendChild($timeTextNode);
		$rdfXSDAttribute = $xml->createAttribute(DATATYPE_ATTRIBUTE);
		$rdfXSDAttribute->value = RDF_DATATYPE;
		$modifiedElement->appendChild($rdfXSDAttribute);
	}

/**
 * Format the date in the formate "YYYYMMDDHHMM"
 *
 * @param $date is Date Object
 *  
 * @author Michele Maresca
 */

	private function setDateAttribute($date)
	{
		$text = $tmp = [];
		$str1 = $str2 = "";
//Split the date and the time of the Event
		$text = explode("T",$date->wholeText);
//Here obtaine the date
		$str1 = join(explode("-", $text[0]));
//Here the time
		$tmp = explode("\+", $text[1]);
		$tmp = join(explode(":", $tmp[0]));
		$str2 = substr($tmp, 0, 4);

		return [$str1, $str2];
	}

	/*
	 * TODO make it static and add comments
	 */
	private function generateURIEvent($data, $uri)
	{
		//Extract date from event following the format "YYYYMMDDHHMM"
//Strings who compose the URI of Event Object
		$stringURI = "";
		$timeURI = "";
		$nameURI = "";

		//Create timeURI and nameURI
		$timeURI = substr($data, 0, 8);		
		$nameURI = urlencode($this->event->name);
		$stringURI .= $uri.$timeURI."/".$nameURI."/";

		return (string) $stringURI;
	}
}
