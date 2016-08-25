<?php 
/**
 * Read the accessess of the specified day
 */
$today=new DateTimeImmutable();
$currentYear=$today->format('Y');
$currentMonth=$today->format('m');
$currentDay=$today->format('d');
$year=isset($_GET['year']) ? $_GET['year'] : $currentYear;
$month=isset($_GET['month']) ? $_GET['month'] : $currentMonth;
$day=isset($_GET['day']) ? $_GET['day'] : $currentDay;

if (!($handle = fopen("access.log", "r")))
	die("Unable to read access.log");

$count=0;
while (($row = fgetcsv($handle, 1000, "\t")) !== FALSE)
	if (strcmp($year, $row[0])==0 && 
			strcmp($month, $row[1])==0 &&
			strcmp($day, $row[2])==0)
		$count++;

echo "Found $count visits for $year/$month/$day";
?>