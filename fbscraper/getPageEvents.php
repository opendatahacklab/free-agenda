<?php
/**
 * Extract events from a facebook public page
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

if ($argc>1)
	$page=$argv[1];
else
	$page=$_GET['page'];


function downloadPage($url){
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
	if ($error!=='') die($error);
	curl_close($ch);
	return $responsetxt;
}

function parseEvent($eventDiv){
	$child=$eventDiv->childNodes;
	if ($child->count()!=3)
		die ("Invalid child count $child->count()"); 
	parseDate($child->item(0));
	parseAddress($child->item(1));
	parseLink($child->item(2));
}

//just handle single day events
//see https://www.php.net/manual/en/datetime.formats.date.php/
function parseDate($dateSpanElement){
	$dateFull=explode(' at ',$dateSpanElement->textContent);
	$dateStr=$dateFull[0];
	$timeStr=$dateFull[1];
	echo "time $timeStr\n";
	$date=DateTimeImmutable::createFromFormat('*, M d,  Y', $dateStr);
	if ($date){
		$d=$date->format('d-m-y');
		echo "OK date $d\n";
	} else
		echo "NOK date $dateStr\n";
}

//does not handle locations, just addresses
function parseAddress($addressSpanElement){
	echo "address $addressSpanElement->textContent\n";
}

function parseLink($linkSpanElement){
	$aElement=$linkSpanElement->getElementsByTagName('a')->item(0);
	$relativeLink=$aElement->getAttribute('href');
	//24 is the size of the string "View detail events for "
	$label=substr($aElement->getAttribute('aria-label'),23);
	echo "Link $relativeLink\n";
	echo "Label $label\n";
}

$url="https://mbasic.facebook.com/$page/events?__nodl&_rdr";
$page=downloadPage($url);
$doc=new DOMDocument();
$doc->loadHTML($page);
foreach($doc->getElementsByTagName('div') as $eventDiv) {
	$divClass=$eventDiv->getAttribute('class');
	if (isset($divClass) && (strcmp($divClass,'bo bp bq')===0 || strcmp($divClass,'co cp cq')===0 || strcmp($divClass,'bq br bs')===0))
		parseEvent($eventDiv);		
} 
?>