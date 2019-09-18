<?php

require_once('Calendar.php');

/**
 * Generate an Internet Calendar file (see RFC 2445 see also RFC 5545)
 * by retrieving events from a facebook page
 *
 * Copyright 2019 Cristiano Longo
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *d
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
class FBPageEvents2Calendar{
	private $pageIds;
	private $calendarId;
	
	/**
	 * Prepare a generator which will retrieve events from the specified 
	 * sparql endpoint.
	 * 
	 * @param string $pageIds array of string s representing identifiers of facebook pages
	 * @param string $calendarId calendar name (ascii chars without space) used to
	 * generate the calendar unique identifier
	 */
	public function __construct($pageIds, $calendarId)
	{
		$this->pageIds=$pageIds;
		$this->calendarId= $calendarId;
	}
	
	/**
	 * Generate the calendar file, send it on the standard output
	 */
	public function generate(){
		$calendar = new Calendar($this->calendarId);
		foreach($this->pageIds as $pageId)
			$this->generateEventsFromPage($pageId,$calendar);
		//Chiusura e download del calendario
		header("Content-type:text/calendar");
		$calendar->show();		
	}

	/**
	  * Construct page URL from page id.
	  */
	public function getFBPageURL($fbPageId){
		return "https://mbasic.facebook.com/$fbPageId/events?__nodl&_rdr";
	}

	/**
	  * Get events from a facebook page and put them into a calendar.
	  */
	public function generateEventsFromPage($fbPageId,$calendar){
		$fbPageURL=$this->getFBPageURL($fbPageId);
		$page=$this->downloadPage($fbPageURL);
		if ($page==FALSE)
			return;
		$doc=new DOMDocument();
		$doc->loadHTML($page);

		foreach($doc->getElementsByTagName('div') as $eventDiv) {
			$divClass=$eventDiv->getAttribute('class');
			if (isset($divClass) && (strcmp($divClass,'bo bp bq')===0 || strcmp($divClass,'co cp cq')===0 || strcmp($divClass,'bq br bs')===0 || strcmp($divClass,'ci cj ck')===0 || strcmp($divClass,'bs bt bu')===0)){
				$event=$this->parseEvent($eventDiv);
				if ($event!=FALSE)
					$calendar->add_event($event['begin']->format(DateTimeInterface::ISO8601),$event['end']->format(DateTimeInterface::ISO8601),$event['link'],$event['address'],$event['title'],$event['link']);
			}
		} 
	}

	/**
	 * Just download events page catenated, for testing purposes
	 */
	public function getPages(){
		$ret='';
		foreach($this->pageIds as $pageId)
 			$ret.=$this->downloadPage($this->getFBPageURL($pageId));
		return $ret;
	}	

	/**
	   * just download the page using CURL.
	   */
	private function downloadPage($url){
		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:68.0) Gecko/20100101 Firefox/68.0');
		
		$header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
		$header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
		$header[] = "Cache-Control: max-age=0";
		$header[] = "Connection: keep-alive";
		$header[] = "Keep-Alive: 300";
		$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		$header[] = "Accept-Language: en-us,en;q=0.5";
		$header[] = "Pragma: "; // browsers keep this blank. 
		$header[] = "User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:68.0) Gecko/20100101 Firefox/68.0";
		
		curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
		$responsetxt = curl_exec($ch);
		$error=curl_error($ch);
		curl_close($ch);
		if ($error!==''){
			fwrite(STDERR, "Unable to load page  $url: $error\n");
			return FALSE;
		}
		return $responsetxt;
	}

	/**
	  * parse the event
	  *
	  * @param $eventDiv the DIV element containing the event in the page
	  * @return an map with title, link, address, begin and end of the event, FALSE if some parsing error occurred.
	  */		
	private function parseEvent($eventDiv){
		$event=array();
		$child=$eventDiv->childNodes;
		if ($child->count()!=3){
			fwrite(STDERR, "Invalid child count $child->count()\n");
			return FALSE;
		} 
		$dateEl=$child->item(0);
		$dates=$this->parseDate($dateEl);
		if ($dates==FALSE){
			//fwrite(STDERR, "unable to parse event $eventDiv->textContent\n");
			fwrite(STDERR, "unable to parse event $dateEl->textContent\n");
			return FALSE;
		}
		$event['begin']=$dates[0];
		$event['end']=$dates[1];
		$event['address']=$this->parseAddress($child->item(1));
		$titleAndLink=$this->parseLink($child->item(2));
		$event['title']=$titleAndLink[0];
		$event['link']="https://mbasic.facebook.com".$titleAndLink[1];
		return $event;
	}

	//just handle single day events
	//see https://www.php.net/manual/en/datetime.formats.date.php/
	/**
	  *
	  * @return a map with two dates (begine and end), false if $str is not in the expected format.
	  */
	private function parseDate($dateSpanElement){
		$datesStr=$dateSpanElement->textContent;
		$d=$this->parseSingleDayEventDate($datesStr);
		if ($d!=FALSE) return $d;
		$d=$this->parseMultipleDaysEventCurrentYear($datesStr);
		if ($d!=FALSE) return $d;
		else return FALSE;
	}

	/**
	   * Parse begin and end of a date for an event occurring in a single day, for example Monday, August 24, 2020 at 12:00 PM – 3:00 PM UTC+02
	   *
	   * @param $str 
	   * 
	   * @return a map with two dates (begine and end), false if $str is not in the expected format.
	   */
	private function parseSingleDayEventDate($str){
		$dateFull=explode(' at ',$str);
		if (count($dateFull)!=2) return FALSE;
		$dayStr=$dateFull[0];
		$hoursParts=explode(' – ',$dateFull[1]);
		if (count($hoursParts)!=2) return FALSE;
		$endHourAndTz=$hoursParts[1];
		$endHourAndTzParts=explode(' ', $endHourAndTz);
		if (count($endHourAndTzParts)!=3) return FALSE;
		$timezone=$endHourAndTzParts[2];
		$beginStr="$dayStr $hoursParts[0] $timezone";
		$endStr="$dayStr $endHourAndTz";
	
		$ret=array();
		$ret[0]=DateTimeImmutable::createFromFormat('*, M d, Y g:i A \U\T\CO', $beginStr);	
	
		if ($ret[0]==FALSE){
			return FALSE;
		}
		$ret[1]=DateTimeImmutable::createFromFormat('*, M d, Y g:i A \U\T\CO', $endStr);	
		if ($ret[1]==FALSE){
			return FALSE;
		}
		return $ret;
	}

	/**
	   * Parse begin and end of a date for an event spanning on multiple days but in the current year, for example Sep 24 at 8:00 PM – Sep 25 at 9:30 PM UTC+02
	   *
	   * @param $str 
	   * 
	   * @return a map with two dates (begine and end), false if $str is not in the expected format.
	   */
	public static function parseMultipleDaysEventCurrentYear($str){
		$beginEndPieces=explode(' – ',$str);
		if (count($beginEndPieces)!=2)
			return FALSE;
		$begin=$beginEndPieces[0];
		$endWithTz=$beginEndPieces[1];
		$endWithTzPieces=explode(' at ',$endWithTz);
		if (count($endWithTzPieces)!=2)
			return FALSE;
		$endHourWithTzPieces=explode(' ',$endWithTzPieces[1]);
		if (count($endHourWithTzPieces)!=3)
			return FALSE;
		$beginWithTz="$begin $endHourWithTzPieces[2]";
		$dateFormat='M d \a\t g:i A \U\T\CO';
		$dateFormatWithYear='M d, Y \a\t g:i A \U\T\CO';
		$ret=array();
		$ret[0]=DateTimeImmutable::createFromFormat($dateFormat, $beginWithTz);		
		if ($ret[0]==FALSE){
			$ret[0]=DateTimeImmutable::createFromFormat($dateFormatWithYear, $beginWithTz);		
			if ($ret[0]==FALSE)
				return FALSE;
		}
		$ret[1]=DateTimeImmutable::createFromFormat($dateFormat, $endWithTz);	
		if ($ret[1]==FALSE){
			$ret[1]=DateTimeImmutable::createFromFormat($dateFormatWithYear, $endWithTz);		
			if ($ret[1]==FALSE)
				return FALSE;
		}
		return $ret;
	}


	//does not handle locations, just addresses
	private function parseAddress($addressSpanElement){
		return $this->getTextChildrenCommaSeparated($addressSpanElement);
	}

	/**
	  * Get all  the text elements in $el and its children, recursively. Return a string of those strings, concatenaded with commas.
	  *
	  * @param DOMElement $el
	  */
	private function getTextChildrenCommaSeparated($el){
		$ret='';
		foreach($el->childNodes as $child){
			if ($child->nodeType===XML_ELEMENT_NODE){
				if (strlen($ret)!==0) $ret.=', ';
				$ret.=$this->getTextChildrenCommaSeparated($child);
			} else if ($child->nodeType===XML_TEXT_NODE)
				$ret.=$child->textContent;
		}
		return $ret;
	}
	/**
	  * @return an array with title and relative link as elements
	  */	
	private function parseLink($linkSpanElement){
		$ret=array();
		$aElement=$linkSpanElement->getElementsByTagName('a')->item(0);
		//24 is the size of the string "View detail events for "
		$ret[0]=substr($aElement->getAttribute('aria-label'),23);
		$ret[1]=$aElement->getAttribute('href');
		return $ret;
	}

}


