<?php
$out = array();
/*
$out[] = array(
				"id" => "0",
                "name" => 'Google I/O',
                "location" => 'San Francisco, CA',
                "startDate" => "new Date(2016, 4, 28)",
                "endDate" => "new Date(2016, 4, 29)", 
				"color" => "#ff0000"

);
$out[] = array(
				"id" => "1",
                "name" => 'Prova',
                "location" => 'San Francisco, CA',
                "startDate" => "new Date(2016, 2, 16)",
                "endDate" => "new Date(2016, 2, 19)", 
				"color" => "#ff0000"

);
*/
$out[] = array(
				"sYear" => "2016",
                "sMonth" => '4',
                "sDay" => '28',
				"eYear" => "2016",
                "eMonth" => '4',
                "eDay" => '29', 
				"color" => "#ff0000"

);
$out[] = array(
				"sYear" => "2016",
                "sMonth" => '2',
                "sDay" => '16',
				"eYear" => "2016",
                "eMonth" => '2',
                "eDay" => '19',
				"color" => "#00ff00"

);


$result['dates'] = $out;
echo json_encode($result);

?>