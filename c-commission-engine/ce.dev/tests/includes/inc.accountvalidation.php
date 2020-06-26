<?php

//////////////////////////////////////////////////
// Initiate the start of the validation process //
//////////////////////////////////////////////////
function InitiateValidation($systemid, $userid)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+InitiateValidation: </td><td>";

	// Define the commission structure //
	$headers = [];
	$headers[] = "command: initiatevalidation";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "userid: ".$userid;
	$retdata = PostURL($coredomain, $headers);
	$retarray = json_decode($retdata, true);
	if (JsonErrorCheck($retarray) == false)
	{
		if ($retarray['success']['status'] == 200)
			echo "<font color=green><b>Success</b></font>";
		else
			echo "<font color=red><b>Failure</b></font>";
	}

	echo "</td></tr></table>";

	return $retarray['validation'];
}

//////////////////////////////
// Validate the bankaccount //
//////////////////////////////
function ValidationAccount($systemid, $userid, $amount1, $amount2)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+ValidationAccount: </td><td>";

	// Define the commission structure //
	$headers = [];
	$headers[] = "command: validateaccount";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "userid: ".$userid;
	$headers[] = "amount1: ".$amount1;
	$headers[] = "amount2: ".$amount2;
	$retdata = PostURL($coredomain, $headers);
	$retarray = json_decode($retdata, true);
	if (JsonErrorCheck($retarray) == false)
	{
		if ($retarray['success']['status'] == 200)
			echo "<font color=green><b>Success</b></font>";
		else
			echo "<font color=red><b>Failure</b></font>";
	}

	echo "</td></tr></table>";
}

/////////////////////////////////////////
// Run the test for account validation //
/////////////////////////////////////////
function TestAccountValidation($systemid, $userid)
{
	echo "<br><b>Account Validation Tests:</b><br>";

	$record = InitiateValidation($systemid, $userid);
	//Pre($record);
	ValidationAccount($systemid, $userid, $record['amount1'], $record['amount2']);
}
?>