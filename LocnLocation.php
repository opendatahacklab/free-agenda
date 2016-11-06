<?php
/**
 * 
 * Basic representation of a Location, as defined in the LOCN vocabulary
 * 
 * Copyright 2016 Michele Maresca and Cristiano Longo
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
 * @author Cristiano Longo
 */
class LocnLocation {
	public $name;
	public $city;
	public $address;
	public $houseNumber;
	public $lat;
	public $long;
	public $id;

	/**
	 *
	 * @param String $name
	 *        	not null, used to identify the location
	 * @param String $city
	 * @param String $address
	 * @param String $houseNumber
	 * @param double $lat
	 * @param double $long
	 */
	public function __construct($name, $city, $address, $houseNumber, $lat, $long) {
		$this->name = $name;
		$this->city = $city;
		$this->address = $address;
		$this->houseNumber = $houseNumber;
		$this->lat = $lat;
		$this->long = $long;
		$this->id = $this->generateId($name, $city, $address, $houseNumber);
	}

	/**
	 * Create a unique identifier for this location
	 */
	private function generateId($name, $city, $address, $houseNumber){
		if ($name!=null && strlen($name)>0)
			return $name;
			$city=$city==null ? '' : $city;
			$address=$address==null ? '' : $address;
			$houseNumber=$houseNumber==null ? '' : $houseNumber;
			return "$city-$address-$houseNumber";
	}
}
?>