<?php 
/**
 * A generator to produce the ontology from the parsed csv.
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
require('../AgendaRDFGenerator.php');
require('LecceEventParser.php');

define('ONTOLOGY_URL', 'http://opendatahacklab.org/free-agenda/lecce/ontology');
define('SRC_URL','https://goo.gl/ha4kiU/export?format=tsv&exportFormat=tsv&ndplr=1');


(new AgendaRDFGenerator(ONTOLOGY_URL, SRC_URL, new LecceEventParser()))->generate();
?>