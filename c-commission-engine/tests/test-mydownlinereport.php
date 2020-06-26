#!/usr/bin/php
<?php
include "includes/inc.ce-comm.php";
include "includes/inc.tests.php";
include "includes/inc.my-affiliate.php";

$g_coredomain = "http://comm.dev:8080";

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

//$_SESSION['authemail'] = "superadmin@chalkcouture.com";
//$_SESSION['authpass'] = "asdfasdf1";

$_SESSION['useremail'] = "superadmin@chalkcouture.com";
$_SESSION['userpass'] = "asdfasdf1";

$user_id = 1;
$batch_id = 13;
$system["id"] = 1;

$loopcount = 0;
while (1)
{
	//$time_start = microtime_float();

	$mydownlinestatsfull = MyDownlineStatsFull($system["id"], $user_id, $batch_id);
	//Pre($mydownlinestatsfull);
	//TestCheck("true", $mydownlinestatsfull, "MyDownlineStatsFull");

	//$time_end = microtime_float();
	//$time = $time_end - $time_start;

	//echo "Execution Time: $time seconds\n";
	

	$loopcount++;
	echo "$loopcount = ".$loopcount."\n";
}

?>