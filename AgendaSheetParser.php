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

define('AGEND_UNICA_URL','https://docs.google.com/spreadsheets/d/1bzVASM5_JjCgvNp3Vs0GJ4vDgYsKo_ig5NHU1QI5USc/export?format=csv&exportFormat=csv');
define('DATE_FORMAT','d/m/Y H:i');
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
	
	public function __construct($rowStr){
		$row=str_getcsv($rowStr,",","\"");
		$this->name=$row[0];
		$this->start=$row[1]==null || $row[2]==null ? null : DateTime::createFromFormat(DATE_FORMAT, $row[1].' '.$row[2], new DateTimeZone('Europe/Rome')); 
		$this->organizedBy=explode(',', $row[5]);
	}
}

class AgendaSheetParser implements Iterator{

	private $rows;
	private $numItems;
	private $index;

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
	}

	//Iterator functions,  see http://php.net/manual/en/class.iterator.php

	public function current(){
		return new Event($this->rows[$this->index+1]);
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

$p=new AgendaSheetParser();
foreach($p as $e){
 	echo $e->name."\t";
 	if ($e->start!=null)
	 	echo $e->start->format(DATE_FORMAT)."\t";
 	echo "organized by ";
 	foreach($e->organizedBy as $o)
 		echo $o.' ';
 	echo "\n";
}
?>