<?php 
require('AgendaSheetParser.php');
$p = new AgendaSheetParser();
foreach ($p as $e)
{
	if ($e->start!=null)
		echo $e->name." ".$e->locationName."\n";
}


echo "LOCATIONS\n\n";
$locations=$p->getAllParsedLocations();
foreach ($locations as $n => $l)
{
	echo $l->name.", ".$l->city." ".$l->houseNumber." ".$l->address."\n";
}
?>