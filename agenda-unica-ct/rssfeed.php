<?php 
/**
 * Generate the calendar file from the ontology.
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
require('../RDFEvents2RSS.php');
require('constants.php');

(new RDFEvents2RSS(SPARQL_ENDPOINT, TITLE, 'http://www.opendatahacklab.org/free-agenda/agenda-unica-ct/', 'http://opendatahacklab.org/free-agenda/agenda-unica-ct/agenda.atom','Agenda Unica', 
		null, null, null))->generate();
?>
