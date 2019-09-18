<?php
/**
 * Helper class to create a Calendar file (see RFC 2445)
 *
 * Copyright 2019 Cristiano Longo
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

class Calendar {
	var $data;
	var $name;
	
	
	function Calendar($name) {
		$this->name=$name;
		$this->data = "BEGIN:VCALENDAR\r\nPRODID:-//Opendatahacklab Catania//$name//IT\r\nVERSION:2.0\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\n";
	}
		//FUNZIONE PER AGGIUNGERE EVENTI

	/**
	  * @param $star string
	  * @param $end string 
	  */ 
	function add_event($start,$end,$uid,$location,$summary, $url=null) {
		$this->data .= "BEGIN:VEVENT\r\nDTSTART:".gmdate("Ymd\THis\Z",strtotime($start))."\r\nDTEND:".gmdate("Ymd\THis\Z",strtotime($end))."\r\nDTSTAMP:".gmdate("Ymd\THis\Z")."\r\nUID:".$uid."\r\nDESCRIPTION:\r\nLOCATION:".$location."\r\nSEQUENCE:0\r\nSTATUS:CONFIRMED\r\nSUMMARY:".$summary."\r\n";
		if ($url!=null)
			$this->data .="URL:$url\r\n";
		$this->data .="TRANSP:OPAQUE\r\nEND:VEVENT\r\n";
	}
		//FUNZIONE PER CHIUDERE IL FILE E SCARICARLO
	function show() {
		$this->data .= "END:VCALENDAR";
		header('Content-Disposition: attachment; filename="'.$this->name.'.ics"');
		Header('Content-Length: '.strlen($this->data));
		Header('Connection: close');
		echo $this->data;
	}
}
	
?>
