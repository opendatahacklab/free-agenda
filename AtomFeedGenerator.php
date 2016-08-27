<?php 
/**
 * An utility to produce ATOM feeds, see http://www.ietf.org/rfc/rfc4287.txt.
 * 
 * @author Cristiano Longo
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

class AtomFeedGenerator{

	private $doc;
	private $feedEl;

	/**
	 * @param string $title the feed title
	 * @param string $description optional, a feed description
	 * @param string $homepage optional, an home page describing the feed
	 * @param string $url the url where the feed is published
	 * @return number
	 */
	public function __construct($id, $title, $updated, $selfURI, $mainAuthorName, $mainAuthorURI, $mainAuthorEMail) {
		$this->doc=new DOMDocument('1.0', 'UTF-8');
		$this->doc->formatOutput = true;

		$this->feedEl=$this->doc->createElementNS('http://www.w3.org/2005/Atom', 'feed');
		$this->doc->appendChild($this->feedEl);

		$this->feedEl->appendChild($this->doc->createElement('id', $id));
		$this->feedEl->appendChild($this->doc->createElement('title', $title));
		$this->feedEl->appendChild($this->doc->createElement('updated',
				$updated->format(DateTime::ATOM)));
		
		$selfURIEl=$this->doc->createElement('link');
		$this->feedEl->appendChild($selfURIEl);
		$selfURIEl->setAttribute('rel', 'self');
		$selfURIEl->setAttribute('href', $selfURI);
		$this->addAuthor($this->feedEl, $mainAuthorName, $mainAuthorURI, $mainAuthorEMail);		
	}

	
	/**
	 * Add an author block to an element.
	 * 
	 * @param DOMElement $el the element to which add the author 
	 * @param string $name (mandatory) author name
	 * @param string $uri (optional) homepage of the authom
	 * @param string $email (optional) e-mail address of the author
	 */
	private function addAuthor($el, $name, $uri, $email){
		$authorEl=$this->doc->createElement("author");
		$el->appendChild($authorEl);
		$authorEl->appendChild($this->doc->createElement("name", $name));
		if (isset($uri))
			$authorEl->appendChild($this->doc->createElement("uri", $uri));
		if (isset($email))
			$authorEl->appendChild($this->doc->createElement("email", $email));
	}
	
	
	/**
	 * Add an author block to the feed element.
	 * 
	 * @param string $name (mandatory) author name
	 * @param string $uri (optional) homepage of the authom
	 * @param string $email (optional) e-mail address of the author
	 */
	public function addFeedAuthor($name, $uri, $email){
		$this->addAuthor($this->feedEl, $name, $uri, $email);		
	}
	
	
	/**
	 * Add feed logo
	 * 
	 * @param string $uri
	 */
	public function addFeedLogo($uri){
		$this->feedEl->appendChild(
				$this->doc->createElement('logo', $uri));	
	}
	
	/**
	 * Add feed home page as alternate link
	 *
	 * @param string $uri
	 */
	public function addFeedHomepage($uri){
		$linkEl=$this->doc->createElement('link');
		$this->feedEl->appendChild($linkEl);
		$linkEl->setAttribute('href',$uri);
		$linkEl->setAttribute('rel','alternate');
	}
	
	/**
	 * Add an entry with textual content
	 * 
	 * @param string $id
	 * @param string $title 
	 * @param string $updated last update of the entry as DateTime object
	 * @param string $content the entry content as plain text
	 * @param string $link optional link to the source content
	 */
	public function addEntryWithTextContent($id, $title, $updated, $content, $link){
		$entryEl=$this->addEntry($id, $title, $updated);
		$contentEl=$this->doc->createElement("content", $content);
		$contentEl->setAttribute('type', 'text');
		$entryEl->appendChild($contentEl);		
		
		if (isset($link)){
			$linkEl=$this->doc->createElement("link");
			$linkEl->setAttribute("href",$link);
			$entryEl->appendChild($linkEl);
		}
	}
	
	/**
	 * Add an entry with neither link or content.
	 * 
	 * @param string $id
	 * @param string $title 
	 * @param string $updated
	 * 
	 * @return the created element
	 */
	private function addEntry($id, $title, $updated){
		$entryEl=$this->doc->createElement("entry");
		$this->feedEl->appendChild($entryEl);
		$entryEl->appendChild($this->doc->createElement("id", $id));
		$entryEl->appendChild($this->doc->createElement("title", $title));
		$entryEl->appendChild($this->doc->createElement("updated", 
				$updated->format(DateTime::ATOM)));
		return $entryEl; 
	}
	/**
	 * Get the feed as string
	 */
	public function getFeed(){
		return $this->doc->saveXML();
	}
}
?>