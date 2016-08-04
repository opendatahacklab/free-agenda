<?php
	class Calendar {
		var $data;
		var $name;
		
		
		function Calendar($name) {
			$this->name=$name;
			$this->data = "BEGIN:VCALENDAR\nVERSION:2.0\nCALSCALE:GREGORIAN\nMETHOD:PUBLISH\n";
		}
			//FUNZIONE PER AGGIUNGERE EVENTI
		function add_event($start,$end,$name_,$description,$location) {
			
			$this->data .= "BEGIN:VEVENT\nDTSTART:".date("Ymd\THis\Z",strtotime($start))."\nDTEND:".date("Ymd\THis\Z",strtotime($end))."\nDTSTAMP:".date("Ymd\THis\Z")."\nUID:\nDESCRIPTION:".$description."\nLOCATION:".$location."\nSEQUENCE:0\nSTATUS:CONFIRMED\nSUMMARY:".$name_."\nTRANSP: OPAQUE\nEND:VEVENT\n";
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
