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
require ('AgendaEventParser.php');
require ('RDFLocnGenerator.php');
require ('RDFEventsGenerator.php');


class AgendaSheetParser implements Iterator {
	private $rowParser;
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
	 * @param $rowParser an implementation of AgendaEventParser. 
	 *        	
	 * @throws Exception
	 */
	public function __construct($url, $rowParser) {
		$this->rowParser=$rowParser;
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
		$location = $this->rowParser->parseLocation ( $row );
		if ($location != null){
			$this->storeLocation ( $location );
			return $this->rowParser->parseEvent($row, $location->id);
		}
		return $this->rowParser->parseEvent($row);
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