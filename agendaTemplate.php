<?php
/**
 * This is a template used for each agenda page. Customize it with setting 
 * the variables
 * 
 * $title for the agenda page title
 * $sparql for the URL of the sparql endpoint
 * $sheet for the URL of the google sheet.
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

/**
 * store on a file access.log date and time of accessing this code.
 */
define('CSV_TIME_FORMAT',"Y\tm\td\tH\ti\ts\n");
$handle = fopen("access.log", "a");
fwrite($handle, date(CSV_TIME_FORMAT));
fflush($handle);
fclose($handle);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $title?></title>
<script type="text/javascript"
	src="../../sparql_suite/sparql_processor.js"></script>
<script type="text/javascript"
	src="../../sparql_suite/event_sparql_processor.js"></script>
<link rel="stylesheet" type="text/css" href="../mystyle.css">
</head>

<body>
	<header class="main-header">
		<h1><?php echo $title; ?></h1>
		<nav>
<a href="https://github.com/opendatahacklab/free-agenda.git"
				title="Source Code"> <img
				src="../../commons/imgs/GitHub-Mark-64px.png" />
			</a>
			<a href="#data" title="dati"><img src="../../commons/imgs/rdf.png" /></a> 	
			<a href="#info" title="informazioni"><img src="../../commons/imgs/Info_Simple_bw.svg.png" /></a>
			<a title="Internet Calendar" href="calendar.php" type="text/calendar"><img src="../../commons/imgs/Octicons-calendar.svg.png" /></a>
			<a title="Atom Feed" href="rssfeed.php" type="application/atom+xml"><img src="../../commons/imgs/rss-feed-icon.png" /></a>
<?php 
	if (isset($form))
		echo ("<a href=\"$form\" title=\"aggiungi evento\" target=\"_blank\"><img src=\"../../commons/imgs/plus.png\" /></a>\n");
?>			
		</nav>
	</header>

	<p><a href="../index.html">Vedi altre agende</a></p>
	
	<section id="events">
	</section>

	<section id="data">
		<header>
			<h2>Accesso ai Dati</h2>
		</header>
		<p>I dati di questa agenda sono rilasciati come <a href="http://opendefinition.org/">open
		data</a> con licenza <a href="https://creativecommons.org/licenses/by/4.0/">CC BY 4.0</a>,
		ossia possono essere scaricati e riutilizzati in qualsiasi modo e a qualsiasi fine con
		l'unica limitazione di dover citare questa pagina come fonte.
		Sono accessibili nelle seguenti modalit&agrave;:</p>
		<ul>
			<li><a target="_blank" href="<?php echo $sheet;?>">google sheet</a></li>
			<li><a href="calendar.php">Internet Calendar</a></li>
			<li><a href="rssfeed.php">Feed RSS Atom</a></li>
			<li><a href="ontology.php" type="application/rdf+xml">ontologia OWL in RDF/XML</a></li>
			<li><a href="<?php echo $sparql?>">SPARQL endpoint</a>.</li>
		</ul>
	</section>
	<section id="info">
		<header>
			<h2>Riconoscimenti</h2>
		</header>

		<p>
			Il sito risiede su un <em>hosting</em> gentilmente offerto da <a
				class="wt-tech" target="_blank" href="http://wt-tech.it">WT-TECH</a>
		</p>
		<p>
			La base di conoscenza che contiene l'elenco degli eventi &egrave;
			gentilmente offerta da <a href="http://dydra.com" target="_blank"><img
				id="dydralogo" alt="dydra.com"
				src="../../commons/imgs/dydra-logo-24pt.png" /></a>
		</p>
		<p>
			Questo sito &egrave; stato realizzato da <a
				href="http://hackspacecatania.it" target="_blank">Hackspace
				Catania</a> nell'ambito del progetto
			<code>
				<a href="http://opendatahacklab.org" title="opendatahacklab"
					target="_blank">opendatahacklab</a>
			</code>
			<a href="http://opendatahacklab.org" title="opendatahacklab"
				target="_blank"> <img id="odhllogo"
				src="../../commons/imgs/logo_cog4_ter.png" />
			</a>
		</p>

		<p>Il modulo di inserimento eventi &egrave; stato realizzato in collaborazione col <em>Google Developer Group</em>
		di Catania.
		</p>
		<p>Riportiamo nel seguito i riconoscimenti per le immagini
			utilizzate nel sito.</p>
		<ol class="iconlist">
			<li><img src="../../commons/imgs/logo_cog4_ter.png" /> &egrave;
				stato realizzato a partire dal logo di <a
				href="http://opendatasicilia.it">Open Data Sicilia</a> (vedi il <em>communication
					kit</em> nel <a
				href="https://github.com/SiciliaHub/opendatasicilia-blog">repository
					di Open Data Sicilia</a>) ed il file <a
				href="https://commons.wikimedia.org/wiki/File%3ACog%2C_Web_Fundamentals.svg">Cog,
					Web Fundamentals.svg</a> by Google (Google Web Fundamentals) [Apache
				License 2.0 (http://www.apache.org/licenses/LICENSE-2.0)], via
				Wikimedia Commons;</li>
			<li><img
				src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/48/Loupe.svg/58px-Loupe.svg.png">
				by <a href="https://commons.wikimedia.org/wiki/Magnifying_glass">Wikimedia
					commons</a>;</li>
			<li><img src="../../commons/imgs/Octicons-calendar.svg.png" /> by Stefania Servidio (The Noun Project) <a href="http://creativecommons.org/licenses/by/3.0/us/deed.en">CC BY 3.0</a>, 
				via <a href="https://commons.wikimedia.org/wiki/File%3ANoun_project_-_Calendar.svg">Wikimedia Commons</a>;</li>
			<li><img src="../../commons/imgs/GitHub-Mark-64px.png" /> <a
				href="https://github.com/logos">GitHub Mark</a>;</li>
			<li><img src="../../commons/imgs/rdf.png" /> derivata da <a href="http://www.w3.org/RDF/icons/">W3C RDF Resource
					Description Framework Icons</a>;</li>				
			<li><img src="../../commons/imgs/Info_Simple_bw.svg.png" /> via <a href="https://commons.wikimedia.org/wiki/File:Info_Simple_bw.svg">Wikimedia Commons</a>;
			<li><img src="../../commons/imgs/plus.png" /> 
			by see <a href="https://commons.wikimedia.org/wiki/File%3AHigh-contrast-list-add.svg">Talk:GNOME High contrast icons</a> (download.gnome.org) [<a href="http://www.gnu.org/licenses/lgpl.html">LGPL</a>], via Wikimedia Commons.
			</li>			
		</ol>
	</section>

	<script type="text/javascript">
		var container = document.getElementById("events");
		var currentTime = new Date();
		var futureEventsProcessor = new EventQueryProcessor(new DrawTableEventProcessorAsc(container, "Prossimi Eventi", "event"), currentTime, currentTime);
		sparql_query("<?php echo $sparql;?>",
				futureEventsProcessor);
		
 		var pastEventsProcessor = new EventQueryProcessor(new DrawTableEventProcessorDec(container, "Eventi Passati", "event"), currentTime, null, currentTime);
		sparql_query("<?php echo $sparql;?>",
				pastEventsProcessor);
		
	</script>
</body>
</html>