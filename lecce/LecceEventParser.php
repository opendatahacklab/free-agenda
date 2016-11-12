<?php 
/**
 * Parser for rows from the dataset Eventicondivisi of the Lecce municipality open data portal
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
 */
require ('../LocnLocation.php');
require ('../Event.php');
class LecceEventParser implements AgendaEventParser{
	static $DEFAULT_DATE='01.01.2000 00:00:00';
	static $DATE_FORMAT='d.m.Y H:i:s';
	
	/**
	 * Create a location by parsing a row of the sheet.
	 *
	 * @return the corresponding Location object or null if the location name is not provided.
	 */
	function parseLocation($row) {
		$name = trim ( $row [10] );
		$city = "Lecce";
		$address = null;
		$houseNumber = null;
	
//		if ($city == null || strlen ( $city ) == 0 || $address == null || strlen ( $address ) == 0)
//			return null;
		$lat = $row[15];
		$lon = $row[16];
	
		return new LocnLocation ( $name, $city, $address, $houseNumber, $lat, $lon );
	}
	
	/**
	 * Produce an event with the given location id by parsing a csv row
	 * @param row an array of fields obtained by splitting a csv row
	 * @param locationId a unique identifier for the location where the event take place
	 * @return an Event
	 */
	function parseEvent($row, $locationId=null){
		$e=new Event();
		$e->name = $row [4];
		$e->start = isset($row [7]) ? $this->parseTime ( $row [7]) : null;
		$e->organizedBy = isset ( $row [3] ) ? trim($row [3]) : null;
		$locationName = isset ( $row [10] ) ? trim($row [10]) : null;
		$e->locationId=$locationId==null ? $locationName : $locationId;
		$e->description= $row[6];
		$e->creationTime=$this->parseTime (isset($row [0]) ? $row [0]: LecceEventParser::$DEFAULT_DATE);
		$e->distributionAuthorization = true;		
		return $e;
	}
	
	/**
	 * Parse a date in a lecce csv field
	 * 
	 * @param unknown $dateStr
	 * @return unknown
	 */
	private function parseTime($dateStr) {
		return DateTime::createFromFormat ( LecceEventParser::$DATE_FORMAT, $dateStr, new DateTimeZone ( 'Europe/Rome' ) );
	}	
}
?>