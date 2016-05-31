<?php

/**
*  Include classes: Event and AgendaSheetParser
*
*  @author Michele Maresca
*/
include("./AgendaSheetParser.php");

/**
*  Include classes: Location e RDFLocnGenerator
*
*  @author Michele Maresca
*/
include("./RDFLocnGenerator.php");

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


	public function generateEvent($xml, $rdfParent)
	{
//Contain the event URI
		$eventURI = "";

//Create the Node for the RDFEvent
		$eventRDF = $xml->createElement("event:Event");
		$eventAttribute = $xml->createAttribute(RDF_ATTRIBUTE);
		$eventRDF->appendChild($eventAttribute);

		$eventName = $xml->createElement("rdfs:Label");

		$eventTime = $xml->createElement("event:time");
		$timeInterval = $xml->createElement("time:Interval");
		$rdfTimeAttribute = $xml->createAttribute(RDF_ATTRIBUTE);
		$timeInterval->appendChild($rdfTimeAttribute);

		$timeHasBeginning = $xml->createElement("time:hasBeginning");

		$timeInstant = $xml->createElement("time:Instant");
		$rdfInstantAttribute = $xml->createAttribute(RDF_ATTRIBUTE);

		$timeXSDDataTime = $xml->createElement("time:inXSDDateTime");

		$eventName->appendChild($xml->createTextNode($this->event->name));

		if($this->event->locationName)
		{
			$eventPlace = $xml->createElement("event:place");
		    $eventPlaceAttribute = $xml->createAttribute("rdf:resource");
			$eventPlaceAttribute->value = RDFLocnGenerator::getLocationURI(URI, $this->event->locationName);
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
		$eventURI = $this->generateURIEvent($formatInstant[0]);

		$rdfInstantAttribute ->value = URI.$eventURI."time/begin";
		$timeInstant->appendChild($rdfInstantAttribute);


		$timeXSDDataTime->appendChild($date);
		$rdfXSDAttribute = $xml->createAttribute(DATATYPE_ATTRIBUTE);
		$rdfXSDAttribute->value = RDF_DATATYPE;
		$timeXSDDataTime->appendChild($rdfXSDAttribute);

		$rdfTimeAttribute->value = URI.$eventURI."time";
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
		$text = split("T",$date->wholeText);
//Here obtaine the date
		$str1 = join(split("-", $text[0]));
//Here the time
		$tmp = split("\+", $text[1]);
		$tmp = join(split(":", $tmp[0]));
		$str2 = substr($tmp, 0, 4);

		return [$str1, $str2];
	}

	private function generateURIEvent($data)
	{
		//Extract date from event following the format "YYYYMMDDHHMM"
//Strings who compose the URI of Event Object
		$stringURI = "";
		$timeURI = "";
		$nameURI = "";

		//Create timeURI and nameURI
		$timeURI = substr($data, 0, 8);		
		$nameURI = urlencode($this->event->name);
		$stringURI .= URI.$timeURI."/".$nameURI."/";

		return (string) $stringURI;
	}

}



//Create a XML file
$xml = new DOMDocument();

//Create a RDF parent node
$rdfParent = $xml->createElement("rdf:RDF");
$rdfParent2 = $xml->createElement("rdf:RDF2");

$xml->preserveWhiteSpace = false;
$xml->formatOutput = true;


//The parse is set in AgendaSheetParser

$p = new AgendaSheetParser();
foreach ($p as $e) 
{
	$r = new RDFEventsGenerator($e);
	$r->generateEvent($xml, $rdfParent);
}

$xml->appendChild($rdfParent);
$xml->appendChild($rdfParent2);



echo "File \"".XML_FILE."\" creato!";
$xml->save(XML_FILE);