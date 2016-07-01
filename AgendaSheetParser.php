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


define('AGEND_UNICA_URL','https://docs.google.com/spreadsheets/d/1zls9A4FnTBOwlTnUtfwyIXzeSh8DKvgmbk4f2KyU_Jc/export?format=tsv&exportFormat=tsv&ndplr=1');
define('DATE_FORMAT','d/m/Y H:i');
require('RDFLocnGenerator.php');
require('RDFEventsGenerator.php');

/**
 * Basic representation of a Event
 *
 * @author Cristiano Longo
 */
class Event{
	public $name;
	public $start;
	//array organizations
	public $organizedBy;
	public $locationName;
	
	public function __construct($row){
		$this->name=$row[1];
		$this->start=$row[2]==null || $row[3]==null ? null : DateTime::createFromFormat(DATE_FORMAT, $row[2].' '.$row[3], new DateTimeZone('Europe/Rome')); 
		$this->organizedBy=isset($row[6]) ? explode(',', $row[6]) : null;
		$this->locationName=isset($row[8]) ? $row[8] : null;
	}
}

class AgendaSheetParser implements Iterator{

	private $rows;
	private $numItems;
	private $index;
	
	//this is a map location name -> location,l populated during the parsing
	private $locations;

	/**
	 * Retrieve and parse the Agenda Unica Sheet
	 *
	 * @throws Exception
	 */
	public function __construct(){
		$h=curl_init(AGEND_UNICA_URL);
		if (!$h) throw new Exception("Unable to initialize cURL session");
		curl_setopt($h, CURLOPT_RETURNTRANSFER, TRUE);
		$retrievedData=curl_exec($h);
		if($retrievedData==FALSE)
			throw new Exception("Unable to execute request: ".curl_error($h));
		curl_close($h);
		$this->rows=explode("\n", $retrievedData);
		$this->numItems=count($this->rows)-1;
		$this->index=0;
		$this->locations=array();
	}

	/**
	 * Create a location by parsing a row of the sheet.
	 *
	 * @return the corresponding Location object or null if the location name is not provided.
	 */
	private function parseLocation($row){
		echo "Parsing location ".$row[8]."\n";
		$name=trim($row[8]);
		$city=$row[9];
		$address=$row[10];
		$houseNumber=$row[11];
		
		if (!isset($name) || $name==null || strlen($name)==0 || 
			$city==null || strlen($city)==0 ||
			$address==null || strlen($address)==0)
			return null;
		//coordinates not available in this release
		$lat=null;
		$lon=null;
		
		return new Location($name, $city, $address, $houseNumber, $lat, $lon);
	}

	//locations parsing
	
	/**
	 * Store the location in the locations map, if not already present.
	 */
	private function storeLocation($location){
		//keep the more recent location description in the sheet
		//assuming that events are listed in ascending order ordered by time
		if (!array_key_exists($location->name, $this->locations)){
			$this->locations[$location->name]=$location;
		}
	}
	
	/**
	 * Get a map location name -> location of all the locations met during the parsing
	 * until now.
	 *
	 */
	public function getAllParsedLocations(){
		$r=array();
		foreach($this->locations as $n => $l)
			$r[$n]=$l;
		return $r;
	}
	
	//Iterator functions,  see http://php.net/manual/en/class.iterator.php
	public function current(){
		$rowStr=$this->rows[$this->index+1];
		$row=str_getcsv($rowStr,"\t","\"");
		$event=new Event($row);
		$location=$this->parseLocation($row);
		if ($location!=null) $this->storeLocation($location);
		return $event;
	}


	public function key (){
		return $this->index;
	}

	public function next(){
		++$this->index;
	}

	public function rewind(){
		$this->index=0;
	}

	public function valid(){
		return $this->numItems>0 && $this->index<$this->numItems;
	}
}
?>