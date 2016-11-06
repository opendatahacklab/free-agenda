<?php 
/**
 * Implementatios will be agenda specific parsers of source csv rows
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

interface AgendaEventParser{
	/**
	 * Create a location by parsing a row of the sheet.
	 *
	 * @param row an array of fields obtained by splitting a csv row
	 * @return the corresponding Location object or null if the location name is not provided.
	 */
	function parseLocation($row);

	/**
	 * Produce an event with the given location id by parsing a csv row
	 * @param row an array of fields obtained by splitting a csv row
	 * @param locationId a unique identifier for the location where the event take place
	 * or null if no location has been specified
	 * @return an Event
	 */
	function parseEvent($row, $locationId);
}
?>