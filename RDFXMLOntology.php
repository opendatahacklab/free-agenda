<?php 
/**
 * Helper class to create an ontology serialized as RDFXML
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
class RDFXMLOntology{
	private $xmlDocument;
	private $ontologyElement;
	
	/**
	 * Create an empty ontology but with the specified IRI.
	 * @param unknown $iri
	 */
	public function __construct($iri){
		$rdfDocumentType = DOMImplementation::createDocumentType("rdf:RDF");
		$this->xmlDocument = DOMImplementation::createDocument("http://www.w3.org/1999/02/22-rdf-syntax-ns#", "rdf:RDF",$rdfDocumentType);
		$this->xmlDocument->preserveWhiteSpace = false;
		$this->xmlDocument->formatOutput = true;
		$this->xmlDocument->version="1.0";
		$this->xmlDocument->encoding="UTF-8";
		RDFXMLOntology::addNamespacesToOntology($this->xmlDocument, array('rdfs'=>'http://www.w3.org/2000/01/rdf-schema#',
				'owl'=>'http://www.w3.org/2002/07/owl#'));
		
		$this->ontologyElement=RDFXMLOntology::addOntologyElement($this->xmlDocument, $iri);		
	}
	
	/**
	 * Declare a set of namespaces in the root of the ontology.
	 *
	 * @param unknown $ontology
	 * @param unknown $namespaces a map nsprefix => uri
	 */
	private static function addNamespacesToOntology($xmlDocument, $namespaces){
		foreach($namespaces as $prefix => $uri)
			$xmlDocument->documentElement->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:'.$prefix, $uri);
	}

	/**
	 * Create an ontology element as child of the root element in the specified 
	 * document.
	 * 
	 * @return the newly created element
	 */
	private static function addOntologyElement($xmlDocument, $ontologyIRI){
		$ontologyElement = $xmlDocument->createElement("owl:Ontology");
		$ontologyElement->setAttribute("rdf:about", $ontologyIRI);
		$xmlDocument->documentElement->appendChild($ontologyElement);
		return $ontologyElement;
	}
	/**
	 * Declare a set of namespaces in the root of the ontology.
	 *
	 * @param unknown $namespaces a map nsprefix => uri
	 */
	public function addNamespaces($namespaces){
		RDFXMLOntology::addNamespacesToOntology($this->xmlDocument, $namespaces);
	}
	
	/**
	 * Add a set of vocabularies to be imported
	 */
	public function addImports($importIris){
		foreach($importIris as $iri){
			$importStm=$this->xmlDocument->createElement('owl:imports');
			$importStm->setAttribute('rdf:resource', $iri);
			$this->ontologyElement->appendChild($importStm);
		}
	}
	
	/**
	 * Add an axiom stating that $p1 is a subproperty of $p2
	 * 
	 * @param unknown $p1 iri of property p1
	 * @param unknown $p2 iri of property p2
	 */
	public function addSubPropertyAxiom($p1, $p2){
		$p1Element=$this->xmlDocument->createElement('rdf:Description');
		$this->xmlDocument->documentElement->appendChild($p1Element);
		$p1Element->setAttribute('rdf:about', $p1);
		
		$subPropertyElement=$this->xmlDocument->createElement('rdfs:subPropertyOf');
		$p1Element->appendChild($subPropertyElement);
		$subPropertyElement->setAttribute('rdf:resource',$p2);
	}
	
	/**
	 * Get the xml document
	 */
	public function getXML(){
		return $this->xmlDocument;
	}	
}
?>