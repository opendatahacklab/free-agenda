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

/**
 * Create an empty ontology with RDFXML serialization
 * 
 * @return the XML document corresponding to the ontology
 */
function createRDFXMLontology(){
	$rdfDocumentType = DOMImplementation::createDocumentType("rdf:RDF");
	$ontology = DOMImplementation::createDocument("http://www.w3.org/1999/02/22-rdf-syntax-ns#", "rdf:RDF",$rdfDocumentType);
	//$ontology = new DOMDocument("1.0", "UTF-8");
	$ontology->preserveWhiteSpace = false;
	$ontology->formatOutput = true;
	$ontology->version="0.1";
	$ontology->encoding="UTF-8";
	addNamespaces($ontology, array('rdfs'=>'http://www.w3.org/2000/01/rdf-schema#',	
		'owl'=>'http://www.w3.org/2002/07/owl#'));	
	return $ontology;
}

/**
 * Declare a set of namespaces in the root of the ontology.
 *
 * @param unknown $ontology
 * @param unknown $namespaces a map nsprefix => uri
 */
function addNamespaces($ontology, $namespaces){
	foreach($namespaces as $prefix => $uri)
		$ontology->documentElement->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:'.$prefix, $uri);
}

define('BASEURI', 'http://opendatahacklab.org/agenda-unica/');

//Create a XML file

$ontology=new RDFXMLOntology(BASEURI.'ontology');
$ontology->addNamespaces(RDFEventsGenerator::getRequiredNamespaces());
$ontology->addNamespaces(RDFLocnGenerator::getRequiredNamespaces());
$ontology->addImports(RDFEventsGenerator::getRequiredVocabularies());
$ontology->addImports(RDFLocnGenerator::getRequiredVocabularies());

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