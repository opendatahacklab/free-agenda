<?php
/**
 * This class allows one to get and parse the entries the Agenda Unica sheet
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
define ( 'DATE_FORMAT', 'd/m/Y H:i' );
define ( 'DATE_FORMAT_bis', 'd/m/Y H.i.s' );
require ('RDFLocnGenerator.php');
require ('RDFEventsGenerator.php');

/**
 * Basic representation of a Event
 *
 * @author Cristiano Longo
 */
class Event {
	public $name;
	public $description;
	public $start;
	// array organizations
	public $organizedBy;
	public $locationId;
	public $creationTime;
	
	/**
	 * 
	 * @param unknown $row the row representing the event
	 * @param unknown $locationId the unique identifier of the location. 
	 * Null if no location is associated
	 * to the event.
	 */
	public function __construct($row, $locationId=null) {
		$this->name = $row [1];
		$this->start = $row [2] == null || $row [3] == null ? null : Event::parseTime ( $row [2], $row [3] );
		$this->organizedBy = isset ( $row [6] ) ? explode ( ',', $row [6] ) : null;
		$locationName = isset ( $row [8] ) ? $row [8] : null;
		$this->locationId=$locationId==null ? $locationName : $locationId;
		$this->description= $row[12];
		$this->creationTime=(isset($row[0]) && strlen($row[0])>0) ? 
			DateTime::createFromFormat ( DATE_FORMAT_bis, $row[0], new DateTimeZone ( 'Europe/Rome' ) ) : 
					null;
	}
	
	/**
	 * Get a date object from two strings representing date and time.
	 * Consider that time mmay be in two different formats.
	 */
	private static function parseTime($date, $time) {
		$fullDate = $date . ' ' . $time;
		$ret = DateTime::createFromFormat ( DATE_FORMAT, $fullDate, new DateTimeZone ( 'Europe/Rome' ) );
		return $ret != FALSE ? $ret : DateTime::createFromFormat ( DATE_FORMAT_bis, $fullDate, new DateTimeZone ( 'Europe/Rome' ) );
	}
}

class AgendaSheetParser implements Iterator {
	private $rows;
	private $numItems;
	private $index;
	
	// this is a map location name -> location,l populated during the parsing
	private $locations;
	
	/**
	 * Retrieve and parse an Agenda Sheet
	 *
	 * @param $url address
	 *        	of the sheet
	 *        	
	 * @throws Exception
	 */
	public function __construct($url) {
		$h = curl_init ( $url );
		if (! $h)
			throw new Exception ( "Unable to initialize cURL session" );
		curl_setopt ( $h, CURLOPT_RETURNTRANSFER, TRUE );
		$retrievedData = curl_exec ( $h );
		if ($retrievedData == FALSE)
			throw new Exception ( "Unable to execute request: " . curl_error ( $h ) );
		curl_close ( $h );
		$this->rows = explode ( "\n", $retrievedData );
		$this->numItems = count ( $this->rows ) - 1;
		$this->index = 0;
		$this->locations = array ();
	}
	
	/**
	 * Create a location by parsing a row of the sheet.
	 *
	 * @return the corresponding Location object or null if the location name is not provided.
	 */
	private function parseLocation($row) {
		$name = trim ( $row [8] );
		$city = $row [9];
		$address = $row [10];
		$houseNumber = $row [11];
		
		if ($city == null || strlen ( $city ) == 0 || $address == null || strlen ( $address ) == 0)
			return null;
			// coordinates not available in this release
		$lat = null;
		$lon = null;
		
		return new Location ( $name, $city, $address, $houseNumber, $lat, $lon );
	}
	
	// locations parsing
	
	/**
	 * Store the location in the locations map, if not already present.
	 */
	private function storeLocation($location) {
		// keep the more recent location description in the sheet
		// assuming that events are listed in ascending order ordered by time
		$locationId=$location->id;
		if (! array_key_exists ( $locationId, $this->locations )) {
			$this->locations [$locationId] = $location;
		}
	}
	
	/**
	 * Get a map location name -> location of all the locations met during the parsing
	 * until now.
	 */
	public function getAllParsedLocations() {
		$r = array ();
		foreach ( $this->locations as $n => $l )
			$r [$n] = $l;
		return $r;
	}
	
	// Iterator functions, see http://php.net/manual/en/class.iterator.php
	public function current() {
		$rowStr = $this->rows [$this->index + 1];
		$row = str_getcsv ( $rowStr, "\t", "\"" );
		$event = new Event ( $row );
		$location = $this->parseLocation ( $row );
		if ($location != null){
			$this->storeLocation ( $location );
			return new Event($row, $location->id);
		}
		return new Event($row);
	}
	
	public function key() {
		return $this->index;
	}
	public function next() {
		++ $this->index;
	}
	public function rewind() {
		$this->index = 0;
	}
	public function valid() {
		return $this->numItems > 0 && $this->index < $this->numItems;
	}
}
?>