<?php
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
<title>Agenda Unica del Movimento - Catania</title>
<script type="text/javascript"
	src="http://opendatahacklab.org/sparql_suite/sparql_processor.js"></script>
<script type="text/javascript"
	src="http://opendatahacklab.org/sparql_suite/event_sparql_processor.js"></script>
<link rel="stylesheet" type="text/css" href="mystyle.css">
</head>

<body>
	<header class="main-header">
		<h1>Agenda Unica del Movimento Catanese</h1>
		<nav>
<a href="https://github.com/opendatahacklab/agenda-unica.git"
				title="Source Code"> <img
				src="../commons/imgs/GitHub-Mark-64px.png" />
			</a>
			<a href="#data" title="dati"><img src="../commons/imgs/rdf.png" /></a> 	
			<a href="#info" title="informazioni"><img src="../commons/imgs/Info_Simple_bw.svg.png" /></a>
			<a href="https://docs.google.com/forms/d/1QL7C46f4Csc_uAKVjkyWIgXEi3C2hE0GSAXCaX_dPpE/viewform?ndplr=1" title="aggiungi evento" target="_blank"><img src="../commons/imgs/plus.png" /></a>
		</nav>
	</header>

	<table id="events">
	</table>

	<section id="data">
		<header>
			<h2>Accesso ai Dati</h2>
		</header>
		<p>I dati dell'agenda unica sono rilasciati come <a href="http://opendefinition.org/">open
		data</a> con licenza <a href="https://creativecommons.org/licenses/by/4.0/">CC BY 4.0</a>,
		ossia possono essere scaricati e riutilizzati in qualsiasi modo e a qualsiasi fine con
		l'unica limitazione di dover citare questa pagina come fonte.
		Sono accessibili nelle seguenti modalit&agrave;:</p>
		<ul>
			<li><a target="_blank" href="https://docs.google.com/spreadsheets/d/1bzVASM5_JjCgvNp3Vs0GJ4vDgYsKo_ig5NHU1QI5USc/view?ndplr=1">google sheet</a></li>
			<li><a href="agenda.owl" type="application/rdf+xml">ontologia OWL in RDF/XML</a></li>
			<li><a href="http://dydra.com/cristianolongo/agenda-unica-ct/sparql">SPARQL endpoint</a>.</li>
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
				src="../commons/imgs/dydra-logo-24pt.png" /></a>
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
				src="../commons/imgs/logo_cog4_ter.png" />
			</a>
		</p>

		<p>Riportiamo nel seguito i riconoscimenti per le immagini
			utilizzate nel sito.</p>
		<ol class="iconlist">
			<li><img src="../commons/imgs/logo_cog4_ter.png" /> &egrave;
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
			<li><img src="../commons/imgs/GitHub-Mark-64px.png" /> <a
				href="https://github.com/logos">GitHub Mark</a>;</li>
			<li><img src="../commons/imgs/rdf.png" /> derivata da <a href="http://www.w3.org/RDF/icons/">W3C RDF Resource
					Description Framework Icons</a>;</li>				
			<li><img src="../commons/imgs/Info_Simple_bw.svg.png" /> via <a href="https://commons.wikimedia.org/wiki/File:Info_Simple_bw.svg">Wikimedia Commons</a>;
			<li><img src="../commons/imgs/plus.png" /> 
			by see <a href="https://commons.wikimedia.org/wiki/File%3AHigh-contrast-list-add.svg">Talk:GNOME High contrast icons</a> (download.gnome.org) [<a href="http://www.gnu.org/licenses/lgpl.html">LGPL</a>], via Wikimedia Commons.
			</li>			
		</ol>
	</section>

	<script type="text/javascript">
		var container = document.getElementById("events");
		var processEventFunction = function(event, isNext) {

			function insertZero(x) {

				if (x == "0" || x == "1" || x == "2" || x == "3" || x == "4"
						|| x == "5" || x == "6" || x == "7" || x == "8"
						|| x == "9") {
					x = "0" + x;
				}
				return x;
			}
			;

			var trevents = document.createElement("tr");
			container.appendChild(trevents);

			var tdData = document.createElement("td");
			var data = new Date(event.timeStart);
			var giorno = data.getDate();
			giorno = insertZero(giorno);
			var mese = data.getMonth() + 1;
			mese = insertZero(mese);
			var stringaData = document.createTextNode(giorno + "/" + mese + "/"
					+ data.getFullYear());
			tdData.appendChild(stringaData);
			tdData.setAttribute('class', 'date');
			trevents.appendChild(tdData);

			var tdOra = document.createElement("td");
			var data = new Date(event.timeStart);
			var minuti = data.getMinutes();
			minuti = insertZero(minuti);
			var ore = data.getHours();
			ore = insertZero(ore);
			var ora = document.createTextNode(ore + ":" + minuti);
			tdOra.appendChild(ora);
			tdOra.setAttribute('class', 'time');
			trevents.appendChild(tdOra);

			var tdTitolo = document.createElement("td");
			var tdTestoTitolo = document.createTextNode(event.eventName);
			tdTitolo.appendChild(tdTestoTitolo);
			trevents.appendChild(tdTitolo);

			var tdLink = document.createElement("td");
			var a = document.createElement("a");
			a.href = "eventdetails.html?iri=" + encodeURIComponent(event.URI);
			var lente = document.createElement("img");
			lente.src = "https://upload.wikimedia.org/wikipedia/commons/thumb/4/48/Loupe.svg/58px-Loupe.svg.png";
			lente.style.height = "1em";
			a.appendChild(lente);
			tdLink.appendChild(a);
			trevents.appendChild(tdLink);

		};

		function EventProcessor() {
			this.processPast = function(event) {
				processEventFunction(event, false);
			};
			this.processNext = function(event) {
				processEventFunction(event, true);
			};
			this.processFuture = function(event) {
				processEventFunction(event, false);
			};
			this.flush = function() {
			};

		}

		var p = new EventQueryProcessor(new EventProcessor(), new Date());

		sparql_query("http://dydra.com/cristianolongo/agenda-unica-ct/sparql",
				p);
	</script>
</body>
</html>