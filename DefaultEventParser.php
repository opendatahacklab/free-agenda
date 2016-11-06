<?php 
/**
 * Parser for rows in a standard free-agenda csv
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
require ('LocnLocation.php');
require ('Event.php');
class DefaultEventParser implements AgendaEventParser{
	static $DEFAULT_DATE='01/01/2016 00.00.00';
	static $DATE_FORMAT='d/m/Y H:i';
	static $DATE_FORMAT_bis='d/m/Y H.i.s';
	
	/**
	 * Create a location by parsing a row of the sheet.
	 *
	 * @return the corresponding Location object or null if the location name is not provided.
	 */
	function parseLocation($row) {
		$name = trim ( $row [8] );
		$city = $row [9];
		$address = $row [10];
		$houseNumber = $row [11];
	
		if ($city == null || strlen ( $city ) == 0 || $address == null || strlen ( $address ) == 0)
			return null;
		// coordinates not available in this release
		$lat = null;
		$lon = null;
	
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
		$e->name = $row [1];
		$e->start = $row [2] == null || $row [3] == null ? null : $this->parseTime ( $row [2], $row [3] );
		$e->organizedBy = isset ( $row [6] ) ? explode ( ',', $row [6] ) : null;
		$locationName = isset ( $row [8] ) ? $row [8] : null;
		$e->locationId=$locationId==null ? $locationName : $locationId;
		$e->description= $row[12];
		$e->creationTime=(isset($row[0]) && strlen($row[0])>0) ?
		DateTime::createFromFormat ( DefaultEventParser::$DATE_FORMAT_bis, $row[0], new DateTimeZone ( 'Europe/Rome' ) ) :
		DateTime::createFromFormat ( DefaultEventParser::$DATE_FORMAT_bis, DefaultEventParser::$DEFAULT_DATE, new DateTimeZone ( 'Europe/Rome' ) );
		$e->distributionAuthorization = (count($row)<19 || $row[18]==null || strcasecmp('NO',$row[18])) ? true : false;		
		return $e;
	}
	
	/**
	 * Get a date object from two strings representing date and time.
	 * Consider that time mmay be in two different formats.
	 */
	private function parseTime($date, $time) {
		$fullDate = $date . ' ' . $time;
		$ret = DateTime::createFromFormat ( DefaultEventParser::$DATE_FORMAT, $fullDate, new DateTimeZone ( 'Europe/Rome' ) );
		return $ret != FALSE ? $ret : DateTime::createFromFormat ( DefaultEventParser::$DATE_FORMAT_bis, $fullDate, new DateTimeZone ( 'Europe/Rome' ) );
	}	
}
?>