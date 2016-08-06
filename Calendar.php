<?php
	class Calendar {
		var $data;
		var $name;
		
		
		function Calendar($name) {
			$this->name=$name;
			$this->data = "BEGIN:VCALENDAR\r\nPRODID:-//Opendatahacklab Catania//Agenda Unica//IT\r\nVERSION:2.0\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\n";
		}
			//FUNZIONE PER AGGIUNGERE EVENTI
		function add_event($start,$end,$uid,$name_,$description,$location) {
			
			$this->data .= "BEGIN:VEVENT\r\nDTSTART:".date("Ymd\THis\Z",strtotime($start))."\r\nDTEND:".date("Ymd\THis\Z",strtotime($end))."\r\nDTSTAMP:".date("Ymd\THis\Z")."\r\nUID:".$uid."\r\nDESCRIPTION:".$description."\r\nLOCATION:".$location."\r\nSEQUENCE:0\r\nSTATUS:CONFIRMED\r\nSUMMARY:".$name_."\r\nTRANSP:OPAQUE\r\nEND:VEVENT\r\n";
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
