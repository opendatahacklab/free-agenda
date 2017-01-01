#!/bin/sh
#
# Script to publish a set of knowledge bases on their sparql endpoint in
# its dydra.com repositories. It reads ontology URL, sparql service URL 
# and user to access the sparql service from standard input, each field separated by space.
#
# @author Cristiano Longo
#
# Copyright 2016 Cristiano Longo
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Lesser General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Lesser General Public License for more details.
#
# You should have received a copy of the GNU Lesser General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

while read line; do
	ontology="$(echo $line | cut -d " " -f 1)";
	sparql="$(echo $line | cut -d " " -f 2)";
	user="$(echo $line | cut -d " " -f 3)";
	echo "Sending $ontology to $sparql begin";
	echo "Sending $ontology to $sparql end";
	java -jar ~/semanticoctopus/0.1.1/semanticoctopus-0.1.1.jar $ontology >tmp.owl
	curl -X PUT -H "Content-Type: application/rdf+xml" -u "$user"  --data-binary @tmp.owl $sparql
done </dev/stdin
sleep 5
rm tmp.owl
