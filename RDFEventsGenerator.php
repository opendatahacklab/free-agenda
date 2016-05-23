<?php

//Include this for get event

include("./AgendaSheetParser.php");

/**
*  Define the output file
*
*  @author Michele Maresca
*/
define('XML_FILE', 'events.xml');

/**
*  Define string for event attribute
*
*  @author Michele Maresca
*/

define('EVENT_STRING', 'stage');

define('DATATYPE_ATTRIBUTE', 'rdf:datatype');

/**
*  Define RDF attribute rdf:About
*
*  @author Michele Maresca
*/

define('RDF_ATTRIBUTE', 'rdf:About');

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
 * Create a RDFnode to insert in the XML file
 *
 * @author Michele Maresca
 */
class RDFEventsGenerator
{
	private $event;
	private $xml;
	private static $stage = 0;
	private static $firstTime = true;

	public function __construct($event, $xml)
	{
		$this->event = $event;
		$this->xml = $xml;
		$this->generateEvent();
		$this->rdf = $this->xml->createElement("rdf:RDF");
	}


	private function generateEvent()
	{
		++self::$stage;
		static $rdf;
		$formatInstant = [];

		if(self::$firstTime)
		{
			$rdf = $this->xml->createElement("rdf:RDF");
			$owlElement = $this->xml->createElement("owl:Ontology");
			$owlAttribute = $this->xml->createAttribute(RDF_ATTRIBUTE);
			$owlAttribute->value = ONTOLOGY_VALUE;

			$owlElement->appendChild($owlAttribute);
			$rdf->appendChild($owlElement);
			$this->xml->appendChild($rdf);

		}

		$event = $this->xml->createElement("event:Event");
		$eventAttribute = $this->xml->createAttribute(RDF_ATTRIBUTE);
		$eventAttribute->value = EVENT_STRING.((string)self::$stage);
		$event->appendChild($eventAttribute);


		$name = $this->xml->createElement("rdfs:Label");
		$eventTime = $this->xml->createElement("event:time");

		$timeInterval = $this->xml->createElement("time:Interval");
		$rdfTimeAttribute = $this->xml->createAttribute(RDF_ATTRIBUTE);
		$rdfTimeAttribute->value = EVENT_STRING.((string)self::$stage)."time";
		$timeInterval->appendChild($rdfTimeAttribute);
		$timeHasBeginning = $this->xml->createElement("time:hasBeginning");

		$timeInstant = $this->xml->createElement("time:Instant");
		$rdfInstantAttribute = $this->xml->createAttribute(RDF_ATTRIBUTE);

		$timeXSDDataTime = $this->xml->createElement("time:inXSDDateTime");

		$name->appendChild($this->xml->createTextNode($this->event->name));

		if($this->event->start)
			$date = $this->xml->createTextNode(date("c", $this->event->start->getTimestamp()));
		else
			$date = $this->xml->createComment("Data non valida!");

		$formatInstant = $this->setDateAttribute($date);
		$rdfInstantAttribute ->value = "time".$formatInstant[0].$formatInstant[1];
		$timeInstant->appendChild($rdfInstantAttribute);


		$timeXSDDataTime->appendChild($date);
		$rdfXSDAttribute = $this->xml->createAttribute(DATATYPE_ATTRIBUTE);
		$rdfXSDAttribute->value = RDF_DATATYPE;
		$timeXSDDataTime->appendChild($rdfXSDAttribute);

		$timeInstant->appendChild($timeXSDDataTime);
		$timeHasBeginning->appendChild($timeInstant);
		$timeInterval->appendChild($timeHasBeginning);
		$eventTime->appendChild($timeInterval);

		$event->appendChild($eventTime);
		$event->appendChild($name);

		$rdf->appendChild($event);
		$this->xml->appendChild($rdf);


		if(self::$firstTime)
			self::$firstTime = false;

	}

	private function setDateAttribute($date)
	{
		$text = $tmp = [];
		$str1 = $str2 = "";
		$text = split("T",$date->wholeText);
		$str1 = join(split("-", $text[0]));
		$tmp = split("\+", $text[1]);
		$tmp = join(split(":", $tmp[0]));
		$str2 = substr($tmp, 0, 4);

		return [$str1, $str2];
	}

}




$xml = new DOMDocument();

$xml->preserveWhiteSpace = false;
$xml->formatOutput = true;

ob_start();

?>

<!DOCTYPE rdf:RDF[
	<!ENTITY org "http://www.w3.org/ns/org#" >
	<!ENTITY dcterms "http://purl.org/dc/terms/" >
    <!ENTITY locn "http://www.w3.org/ns/locn#" >
    <!ENTITY foaf "http://xmlns.com/foaf/0.1/" >
	<!ENTITY owl "http://www.w3.org/2002/07/owl#" >
	<!ENTITY xsd "http://www.w3.org/2001/XMLSchema#" >
	<!ENTITY rdfs "http://www.w3.org/2000/01/rdf-schema#" >
	<!ENTITY geo "http://www.w3.org/2003/01/geo/wgs84_pos#" >
	<!ENTITY rdf "http://www.w3.org/1999/02/22-rdf-syntax-ns#" >
	<!ENTITY odt "http://www.dmi.unict.it/~longo/opendatatour/" >
	<!ENTITY event "http://purl.org/NET/c4dm/event.owl#" >
	<!ENTITY time "http://www.w3.org/2006/time#" >
	<!ENTITY cct "http://www.comune.catania.it/comunect.owl/" >
	<!ENTITY sioc "http://rdfs.org/sioc/ns#" >
]>

<?php
$doctype = ob_get_contents();
ob_flush();

$xml->loadHTML($doctype);


//The parse is set in AgendaSheetParser

foreach ($p as $e) 
{
	new RDFEventsGenerator($e, $xml);
}

echo "File \"events.xml\" creato!";
$xml->save(XML_FILE);