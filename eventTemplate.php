<?php 
/**
 * Template page for event detail pages. Customize it with setting 
 * $agenda_title and $sparql_endpoint variables.
 *
 * Copyright 2016 Cristiano Longo, Alberto Berrittella, Michele Maresca
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
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title><?php echo $agenda_title; ?></title>
		<script type="text/javascript" src="http://opendatahacklab.org/sparql_suite3.0/sparql_processor.js"></script>
		<script type="text/javascript" src="http://opendatahacklab.org/sparql_suite3.0/event_sparql_processor.js"></script>
        <link rel="stylesheet" type="text/css" href="../mystyle.css"> 	
	</head>
<body>
	<header class="main-header">
		<h1 id="maintitle"></h1>
	</header>

	<section id="eventdetail">
		<p class="loading" id="loading">Loading ...</p>
	</section>
	<script type="text/javascript">
	
//the function responsible to print event information
var processEventFunction = function(event){
	
	//replace titles 
	var titleLower=event.eventName;
	document.getElementById("maintitle").appendChild(document.createTextNode(titleLower));
	//document.getElementById("eventinbreadcrumb").appendChild(
	//		document.createTextNode(titleLower));
    
	//put event details in the specified section
	var container = document.getElementById("eventdetail");
	container.removeChild(document.getElementById("loading"));
	
	var time = document.createElement("p");
	var timeStart = new Date(event.timeStart);
	time.className="eventtime";
	time.appendChild(document.createTextNode(timeStart.toLocaleDateString()+" - "+
		timeStart.toLocaleTimeString(navigator.language, {hour: '2-digit', minute:'2-digit'})));
	container.appendChild(time);

	var address = document.createElement("p");
	address.className="eventaddress";
	var addressTxt = event.eventPlace ==null ? event.address : event.eventPlace + " - "+event.address;
	address.appendChild(document.createTextNode(addressTxt));
	container.appendChild(address);	
	
	if(event.description != null){
		var description = document.createElement("p");
		description.className="eventdescription";
		description.appendChild(document.createTextNode(event.description));
		container.appendChild(description);
	}
	
	if (event.posts!=null && event.posts.lenght>0){
		var postsTitle=document.createElement("h2");
		h2.className="posts";
		postTitle.appendChild(document.createTextNode("Related Posts"));
		container.appendChild(postTitle);
		
		var postList=document.createElemet("ul");
		container.appendChild(postList);
		for(var i=0; i<event.posts.lenght; i++){
			var postValue = event.posts[i];
			var postItem=document.createElement("li");
			postList.appendChild(postItem);
			var a = document.createElement("a");
			a.href=postValue.URI;
			a.appendChild(document.createTextNode(postValue.title));
			postItem.appendChild(a);
		}
	}	
};

//retrieve the eventIRI from parameters
var eventIRI = location.search.split('?iri=')[1];

if (eventIRI==null)
	window.alert("No event IRI provided.");
else{
	var eventIRIdecoded=decodeURIComponent(eventIRI);
	var p = new SingleEventQueryProcessor(eventIRIdecoded, processEventFunction, function(event){
		window.alert("No such event!"+eventIRIdecoded);
	});

	sparql_query("<?php echo $sparql_endpoint;?>", p);
}
	</script>
	
<a class="back" href="index.php">Torna all'Agenda</a>
</body>
</html>
