<?php
/**
 * 
 * Generate an RDF/XML representation of a location.
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
 * @author Michele Maresca
 */

/**
 * Define adminUnitL1 value
 *
 * @author Michele Maresca
 *        
 */
define ( 'ADMIN_UNIT', 'IT' );
/**
 * Create a Locn RDFnode
 *
 * @author Michele Maresca
 */
class RDFLocnGenerator {
	private $location;
	public function __construct($location) {
		$this->location = $location;
	}
	public function generateLocation($xml, $rdfParent, $prefix) {
		$locationId=
		$uri=RDFLocnGenerator::getLocationURI($prefix,$this->location->id);
		
		// Create RDF Node for location
		$locnRDF = $xml->createElement ( "locn:Location" );
		$locnRDFAttribute = $xml->createAttribute ( 'rdf:about' );
		$locnRDFAttribute->value = $uri;
		$locnRDF->appendChild ( $locnRDFAttribute );
		
		if ($this->location->name!=null && strlen($this->location->name)>0){
			$locationNameElement = $xml->createElement('rdfs:label');
			$locnRDF->appendChild($locationNameElement);
			$locationNameElement->appendChild($xml->createTextNode($this->location->name));
		}
		
		// Create NODE locn:address
		$parentLocnAddress = $xml->createElement ( "locn:address" );
		// Create NODE locn:Address
		$locnAddress = $xml->createElement ( "locn:Address" );
		$locnAddressAttribute = $xml->createAttribute ( 'rdf:about' );
		$locnAddressAttribute->value = $uri. "/address";
		$locnAddress->appendChild ( $locnAddressAttribute );
		
		// Create node rdfs:label
		$rdfLabel = $xml->createElement ( "rdfs:label" );
		$rdfLabel->appendChild ( $xml->createTextNode ( $this->formatLabel () ) );
		// Create node locn:fullAddress
		$fullAddress = $xml->createElement ( "locn:fullAddress" );
		$fullAddress->appendChild ( $xml->createTextNode ( $this->formatLabel () ) );
		
		// Create node locn:throughfare
		$locnThroughfare = $xml->createElement ( "locn:throughfare" );
		$locnThroughfare->appendChild ( $xml->createTextNode ( $this->location->address ) );
		
		// Create node locn:locatorDesignator
		$locnDesignator = $xml->createElement ( "locn:locatorDesignator" );
		$locnDesignator->appendChild ( $xml->createTextNode ( $this->location->houseNumber ) );
		
		// Create node locn:locatorPostname
		$locnPostName = $xml->createElement ( "locn:postName" );
		$locnPostName->appendChild ( $xml->createTextNode ( $this->location->city ) );
		
		// Create node locn:adminUnitL1
		$locnAdminUnitL1 = $xml->createElement ( "locn:adminUnitL1" );
		$locnAdminUnitL1->appendChild ( $xml->createTextNode ( ADMIN_UNIT ) );
		
		// Insert node in locn:Address
		$locnAddress->appendChild ( $rdfLabel );
		$locnAddress->appendChild ( $fullAddress );
		$locnAddress->appendChild ( $locnThroughfare );
		$locnAddress->appendChild ( $locnDesignator );
		$locnAddress->appendChild ( $locnPostName );
		$locnAddress->appendChild ( $locnAdminUnitL1 );
		
		$parentLocnAddress->appendChild ( $locnAddress );
		
		// Create locn:geometry
		if (isset ( $this->location->lat ) && strlen ( $this->location->lat ) > 0 && isset ( $this->location->long ) && strlen ( $this->location->long ) > 0) {
			$parentGeometry = $xml->createElement ( "locn:geometry" );
			
			// Create NODE locn:Geometry
			$geoLocnGeometry = $xml->createElement ( "locn:Geometry" );
			$geoAttribute = $xml->createAttribute ( "rdf:about" );
			$geoAttribute->value = $uri . "/geometry";
			$geoLocnGeometry->appendChild ( $geoAttribute );
			
			// Create node geo:lat
			$geoLat = $xml->createElement ( "geo:lat" );
			$geoLat->appendChild ( $xml->createTextNode ( $this->location->lat ) );
			
			// Create node geo:long
			$geoLong = $xml->createElement ( "geo:long" );
			$geoLong->appendChild ( $xml->createTextNode ( $this->location->long ) );
			
			$geoLocnGeometry->appendChild ( $geoLat );
			$geoLocnGeometry->appendChild ( $geoLong );
			$parentGeometry->appendChild ( $geoLocnGeometry );
			$locnRDF->appendChild ( $parentGeometry );
		}
		$locnRDF->appendChild ( $parentLocnAddress );
		
		$rdfParent->appendChild ( $locnRDF );
	}
	
	/**
	 * Get the namespaces which are expected to be set (aside with respective
	 * abbreviation prefixes) in the destination ontology.
	 * excluding the default ones
	 * rdf, rdfs and owl-
	 *
	 * @return a map prefix -> namespace
	 */
	public static function getRequiredNamespaces() {
		return array (
				'geo' => 'http://www.w3.org/2003/01/geo/wgs84_pos#',
				'locn' => 'http://www.w3.org/ns/locn#' 
		);
	}
	
	/**
	 * Get the set of vocabulary iris to be imported in the target ontology
	 */
	public static function getRequiredVocabularies() {
		return array (
				'http://www.w3.org/ns/locn',
				'https://www.w3.org/2003/01/geo/wgs84_pos' 
		);
	}
	public static function getLocationURI($prefix, $name) {
		return ( string ) $prefix . urlencode ( $name );
	}
	
	private function formatLabel() {
		return ( string ) ($this->location->houseNumber . ", " . $this->location->address . ", " . $this->location->city . ", Italy");
	}
}

//Insert a URI

//Create a XML file
// $xml = new DOMDocument();

// //Create a RDF parent node
// $rdfParent = $xml->createElement("rdf:RDF");

// $xml->preserveWhiteSpace = false;
// $xml->formatOutput = true;


// $l = new Location("Hackspace catania", Catania", "Via Grotte Bianche", "112", "37.513287", "15.088008");
// $rdfl = new RDFLocnGenerator($l);
// $rdfl->generateLocation($xml, $rdfParent);

// $xml->appendChild($rdfParent);

// echo "File \"".XML_FILE."\" creato!";
// $xml->save(XML_FILE);
