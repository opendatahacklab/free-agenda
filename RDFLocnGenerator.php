<?php

/**
*  Define URI
*
*  @author Michele Maresca
*/
define('URI', 'http://opendatahacklab.org/agendaunica/');

/**
 * Basic representation of a Location
 *
 * @author Michele Maresca
 */

class Location
{
	public $city;
	public $address;
	public $houseNumber;

	public function __construct($city, $address, $houseNumber)
	{
		$this->city = $city;
		$this->address = $address;
		$this->houseNumber = $houseNumber;
	}
}

/**
 * Create a Locn RDFnode
 *  
 * @author Michele Maresca
 */

class RDFLocnGenerator
{
	private $location;
	
	public function __construct($location)
	{
		$this->location = $location;
	}

	public function formatLabel()
	{
		return (string)($this->location->houseNumber.", ".$this->location->address.", ".$this->location->city.", Italy");
	}
}

$l = new Location("Catania", "Via Grotte Bianche", "112");
$rdfl = new RDFLocnGenerator($l);

echo $rdfl->formatLabel();