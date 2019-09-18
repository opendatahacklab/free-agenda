<?php 
/**
 * Agenda for all events in Catania
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
require('../FBPageEvents2Calendar.php');


(new FBPageEvents2Calendar(array('associazione.lagirandola.ct',
	'Comitiva-di-via-Nizzeti-112386252115636',
	'zocentroculturecontemporanee',
	'AssociazioneGammazita',
	'occhiohorus',
	'teatrocoppolateatrodeicittadini',
	'viadelprincipe',
	'whitegaragegallery',
	'laboratoriourbanopopolare',
	'TabareFMS',
	'officinarebelde',
	'olisticaetnea',
	'AnimeinArte',
	'midulla.centropolifunzionale.5',
	'laviadelcinabro',
	'ilgiardinodiscida',
	'ComunitaResistentePiazzetta',
	'youthhubcatania',
	'vulcanic.incubatore',
	'cittainsiemect',
	'Dirittodiaccesso.GY',
	'Partito-Comunista-Italiano-Catania-1110154402389049',
	'fridaysforfuturecatania',
	'tifonecrew',
	'Mamma-Africa-1676805999243933',
	'BARACCIOBABANGIDA',
	'collettivored.militant'), 'eventi-catania'))->generate();
?>