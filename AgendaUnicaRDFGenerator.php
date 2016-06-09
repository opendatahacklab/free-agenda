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
require('RDFXMLOntology.php');

define('BASEURI', 'http://opendatahacklab.org/agenda-unica/');

//Create a XML file

$ontology=new RDFXMLOntology(BASEURI.'ontology');
$ontology->addNamespaces(RDFEventsGenerator::getRequiredNamespaces());
$ontology->addNamespaces(RDFLocnGenerator::getRequiredNamespaces());
$ontology->addImports(RDFEventsGenerator::getRequiredVocabularies());
$ontology->addImports(RDFLocnGenerator::getRequiredVocabularies());

//additional semantics which provide bindings between vocabularies

$ontology->addSubPropertyAxiom('org:hasSite', 'locn:location');
$ontology->addSubPropertyAxiom('org:siteAddress', 'locn:address');
$ontology->addSubPropertyAxiom('http://purl.org/NET/c4dm/event.owl#place', 'http://www.w3.org/ns/locn#location');


$agendaParser = new AgendaSheetParser();
foreach ($agendaParser as $event){
	if ($event->start!=null)
		(new RDFEventsGenerator($event))->generateEvent($ontology->getXML(), $ontology->getXML()->documentElement, BASEURI);	
}
$locations=$agendaParser->getAllParsedLocations();

foreach($locations as $name => $location)
	(new RDFLocnGenerator($location))->generateLocation($ontology->getXML(), $ontology->getXML()->documentElement, BASEURI);

echo $ontology->getXML()->saveXML();
?>