<?php
/**
 * Copyright 2016 Biagio Robert Pappalardo and Cristiano Longo
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
/*
 * L'intero tool si basa sulla seguente libreria per comunicare con il database SPARQL
 * Maggiori info sulla libreria qui: http://graphite.ecs.soton.ac.uk/sparqllib/
 */
require_once( "sparqllib.php" );
require_once( "AtomFeedGenerator.php" );

class RDFEvents2RSS{
	
	private $sparql_endpoint;
	private $feedTitle;
	private $feedHomePageUrl;
	private $feedSelfUrl;
	private $authorName;
	private $authorURI;
	private $authorEMail;
	private $logo;
	/**
	 * 
	 * @param URL $sparql_endpoint the address of the endpoint to retrieve events from.
	 */
	public function __construct($sparql_endpoint, 
			$feedTitle, 
			$feedHomePageUrl, 
			$feedSelfUrl, 
			$authorName, 
			$authorURI, 
			$authorEMail,
			$logo){
		$this->sparql_endpoint=$sparql_endpoint;		
		$this->feedTitle=$feedTitle;
		$this->feedHomePageUrl=$feedHomePageUrl;
		$this->feedSelfUrl=$feedSelfUrl;
		$this->authorName=$authorName;
		$this->authorURI=$authorURI;
		$this->authorEMail=$authorEMail;
		$this->logo=$logo;
	}

	/**
	 * Generate the feed and send it to the standard output
	 */
	public function generate(){
		$result=$this->retrieveEvents();
		$fstRow=$result->fetch_array();
		$feedUpdatedField = $this->getLastModification($result, $fstRow);
		$feedId = $this->getIdFromUrl($this->feedSelfUrl, $feedUpdatedField);
		
		//TODO updated
		$feed=new AtomFeedGenerator($feedId, $this->feedTitle, new DateTime($feedUpdatedField),
				$this->feedSelfUrl, $this->authorName, $this->authorURI, $this->authorEMail);
		if ($this->logo!=null)
			$feed->addFeedLogo("http://opendatahacklab.org/commons/imgs/logo_cog4_ter.png");
		//IFTTT does not like multiple links
		//$feed->addFeedHomepage("https://opendatahacklab.github.io");
		$this->populate($feed, $result, $fstRow);	
		$this->output($feed);		
	}
	
	/**
	 * Retrieven all the events provided by the endpoint.
	 */
	private function retrieveEvents(){
		//Mi collego al database e in caso di errore esco.
		$db = sparql_connect( $this->sparql_endpoint);
		if( !$db )
			throw new Exception("Unable to query the endpoint $this->sparql_endpoint : $db->error()");
	
		//Setto i prefissi "event", "locn", "time"
		$db->ns( "event","http://purl.org/NET/c4dm/event.owl#" );
		$db->ns( "locn","http://www.w3.org/ns/locn#" );
		$db->ns( "time","http://www.w3.org/2006/time#" );
		$db->ns( "dcterms","http://purl.org/dc/terms/" );
		$db->ns( "foaf","http://xmlns.com/foaf/0.1/" );
	
		//Imposto ed eseguo la query per estrarre tutti gli eventi, in caso di errore esco
		$query = "SELECT ?e ?label ?address ?time ?modified ?homepage ?description
		WHERE{
	  		?e a event:Event .
			?e rdfs:label ?label .
			?e event:place ?p .
			?p locn:address ?a .
			?a locn:fullAddress ?address .
			?e event:time ?timeInterval .
			?timeInterval time:hasBeginning ?begin .
			?begin time:inXSDDateTime ?time .
	  		?e dcterms:modified ?modified .
	  		OPTIONAL { ?e foaf:homepage ?homepage } .
	  		OPTIONAL { ?e rdfs:comment ?description }
		} ORDER BY DESC(?modified)";
		$result = $db->query( $query );
		if( !$result )
			throw new Exception("Error occurred performing the query: $db->error");
		$result->field_array( $result );
		return $result;
	}
	
	/**
	 * Get the last modification time for the feed
	 *
	 * @param unknown $result query result rows
	 * @param unknown $fstRow first result row
	 */
	private function getLastModification($result, $fstRow){
		/*
		 * Cerco il campo "modified" più alto (che corrisponderà al campo <updated> dell'ultima entry che si inserirà)
		 * per impostarlo come campo <updated> del feed. Esso è il primo perchè i risultati sono ordinati in base a questo campo.
		 */
		return $fstRow['modified'];
	}
	
	//Una funzione che genera un id univoco di un feed o di una entry basato sul link dello stesso e sulla sua data di creazione.
	private function getIdFromUrl($url, $dateAtomFormat) {
		$date = date("Y-m-d", strtotime($dateAtomFormat));
		$url = preg_replace('/https?:\/\/|www./', '', $url);
		if ( strpos($url, '/') !== false) {
			$ex = explode('/', $url);
			$urlpredomain = $ex['0'];
		} else $urlpredomain='';
		$urlpostdomain = str_replace($urlpredomain, "", $url);
		$id = "tag:" . $urlpredomain . "," . $date . ":" . $urlpostdomain;
		return $id;
	}	

	/**
	 * Populate the feed according to the query results
	 * 
	 * @param unknown $feed
	 * @param unknown $result
	 * @param unknown $fstRow the first result row
	 */
	private function populate($feed, $result, $fstRow){
		$row=$fstRow;
		//Imposta e stampa un entry del feed per ciascun evento ottenuto dalla query precedente
		// Usa un ciclo while perchè il primo "$row = $result->fetch_array()" è stato chiamato sopra
		// e non ho trovato un modo per resettarlo (data-seek non esiste a quanto pare)
		do {
			$entryTitle = $row["label"];
			$entryUrl = $row["e"];
			$entryUpdated = $row['modified'];
			$entryId = $this->getIdFromUrl($entryUrl, $entryUpdated);
			$entryContent = trim($entryTitle) . ' - '
					. trim($row['address']) .
					' - ' . strftime("%d %B %Y %H:%M" , strtotime($row['time']));
					if (isset($row['description'])) $entryContent.="\n ".$row['description'];
		
					$feed->addEntryWithTextContent($entryId, $entryTitle, new DateTime($entryUpdated), $entryContent, $row['homepage']);
		}while( $row = $result->fetch_array() );		
	}
	
	/**
	 * Output http headers and feed
	 */
	private function output($feed){
		//Setto l'header per far capire agli user agent che si tratta di una pagina che offre feed RSS in formato ATOM.
		header('Content-type: application/atom+xml; charset=UTF-8');
		
		/*
		 * Impostazioni locali in italiano, utilizzato per la stampa di data e ora
		 * (il server deve avere il locale italiano installato
		 */
		setlocale(LC_TIME, 'it_IT');		
		echo $feed->getFeed();
	}
	
}






//Imposto e stampo le informazioni da inserire nei campi del feed
?>
