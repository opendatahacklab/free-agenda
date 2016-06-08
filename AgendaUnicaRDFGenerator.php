<?php 
/**
 * A generator to produce the ontology from the parsed csv.
 * 
 * Copyright 2016 Cristiano Longo
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
 * @author Cristiano Longo
 */
require('AgendaSheetParser.php');

define('BASEURI', 'http://opendatahacklab.org/agenda-unica/');

//Create a XML file
$ontology = new DOMDocument();
$ontology->preserveWhiteSpace = false;
$ontology->formatOutput = true;

//the root element
$rdfElement = $ontology->createElement("rdf:RDF");
$ontology->appendChild($rdfElement);

$agendaParser = new AgendaSheetParser();
foreach ($agendaParser as $event)
	(new RDFEventsGenerator($event))->generateEvent($ontology, $rdfElement, BASEURI);
$locations=$agendaParser->getAllParsedLocations();

foreach($locations as $name => $location)
	(new RDFLocnGenerator($location))->generateLocation($ontology, $rdfElement, BASEURI);

echo $ontology->saveXML();
?>