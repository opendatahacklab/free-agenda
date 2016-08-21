<?php 
define('AGEND_UNICA_URL','https://docs.google.com/spreadsheets/d/1bzVASM5_JjCgvNp3Vs0GJ4vDgYsKo_ig5NHU1QI5USc/export?format=tsv&exportFormat=tsv&ndplr=1');

require('AgendaSheetParser.php');
$p = new AgendaSheetParser(AGEND_UNICA_URL);
foreach ($p as $e)
{
	if ($e->start!=null)
		echo $e->name." location id=$e->locationId ; Description=$e->description\n";
}


echo "LOCATIONS\n\n";
$locations=$p->getAllParsedLocations();
foreach ($locations as $n => $l)
{
	echo "$n >> ".$l->city." ".$l->houseNumber." ".$l->address."\n";
}
?>