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
 * Create a RDFnode to insert in the XML file
 *
 * @author Michele Maresca
 */

class RDFEventsGenerator
{
	private $event;
	private $xml;

	public function __construct($event, $xml)
	{
		$this->event = $event;
		$this->xml = $xml;
		$this->generateEvent();
	}


	private function generateEvent()
	{
		$event = $this->xml->createElement("event:Event");
		$name = $this->xml->createElement("rdfs:Label");
		$eventTime = $this->xml->createElement("event:time");
		$timeInterval = $this->xml->createElement("time:Interval");
		$timeHasBeginning = $this->xml->createElement("time:hasBeginning");
		$timeInstant = $this->xml->createElement("time:Instant");
		$timeXSDDataTime = $this->xml->createElement("time:inXSDDateTime");

		$name->appendChild($this->xml->createTextNode($this->event->name));

		if($this->event->start)
			$date = $this->xml->createTextNode(date("c", $this->event->start->getTimestamp()));
		else
			$date = $this->xml->createComment("Data non valida!");

		$timeXSDDataTime->appendChild($date);

		$timeInstant->appendChild($timeXSDDataTime);
		$timeHasBeginning->appendChild($timeInstant);
		$timeInterval->appendChild($timeHasBeginning);
		$eventTime->appendChild($timeInterval);

		$event->appendChild($eventTime);
		$event->appendChild($name);
		$this->xml->appendChild($event);
	}
}


$xml = new DOMDocument();
$xml->preserveWhiteSpace = false;
$xml->formatOutput = true;

//The parse is set in AgendaSheetParser

foreach ($p as $e) 
{
	new RDFEventsGenerator($e, $xml);
}

echo "File \"events.xml\" creato!";
$xml->save(XML_FILE);