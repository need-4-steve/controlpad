<?php

///////////////////////
// Add a system user //
///////////////////////
function AddPoolPot($systemid, $qualifytype, $amount, $startdate, $enddate)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+AddPoolPot: </td><td>";

	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: addpoolpot";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;

	$headers[] = "systemid: ".$systemid;
	$headers[] = "qualifytype: ".$qualifytype; // Personal Sales, Group Sales, Signup count //
	$headers[] = "amount: ".$amount;
	$headers[] = "startdate: ".$startdate;
	$headers[] = "enddate: ".$enddate;
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

///////////////////
// Edit a system //
///////////////////
function EditPoolPot($poolpotid, $systemid, $qualifytype, $amount, $startdate, $enddate)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+EditPoolPot: </td><td>";

	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: editpoolpot";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;

	$headers[] = "poolpotid: ".$poolpotid;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "qualifytype: ".$qualifytype; // Personal Sales, Group Sales, Signup count //
	$headers[] = "amount: ".$amount;
	$headers[] = "startdate: ".$startdate;
	$headers[] = "enddate: ".$enddate;
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

////////////////////////
// Query System Users //
////////////////////////
function QueryPoolPots($display, $systemid, $poolpotid)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	if ($display == true)
	{
		echo "<table>";
		echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+QueryPoolPots: </td><td>";
	}

	$headers = [];
	$headers[] = "command: querypoolpots";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$retdata = PostURL($coredomain, $headers);
	$retarray = json_decode($retdata, true);

	if ($display == true)
	{
		if ($retarray['errors']['source'] == "API")
		{
			echo "<font color=red><b>Failed</b></font>";
			echo "<table cellpadding=0 cellspacing=0>";
			echo "<tr><td align=right>status:</td><td><font color=red>&nbsp;&nbsp;".$retarray['errors']['status']."</font></td></tr>";
			echo "<tr><td align=right>title:</td><td><font color=red>&nbsp;&nbsp;".$retarray['errors']['title']."</font></td></tr>";
			echo "<tr><td align=right>detail:</td><td><font color=red>&nbsp;&nbsp;".$retarray['errors']['detail']."</font></td></tr>";
			echo "</table>";
			echo "</td></tr></table>";
			return;
		} 
	}

	// Loop through and find email of new sysuser //
	foreach ($retarray['poolpots'] as $record)
	{	
		if ($poolpotid == 0)
		{
			if ($display == false)
				return $record;
		}

		if ($poolpotid == $record['id'])
		{
			$recordfound = true;
			if ($display == false)
				return $record;
		}
	}

	if ($display == true)
	{
		if ($recordfound == true)
			echo "<font color=green><b>Success</b></font>";
		else
			echo "<font color=red><b>Failure</b></font>";
		echo "</td></tr></table>";
	}
}

///////////////////////////
// Disable a system user //
///////////////////////////
function DisablePoolPot($systemid, $poolpotid)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+DisablePoolPots: </td><td>";

	// Check Query on Before //
	$record = QueryPoolPots(false, $systemid, $poolpotid);
	if ($record['disabled'] == 't')
	{
		echo "<font color=red><b>Failure #1</b></font>";
		echo "</td></tr></table>";
		return;
	}

	$headers = [];
	$headers[] = "command: disablepoolpot";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "poolpotid: ".$poolpotid;
	$retdata = PostURL($coredomain, $headers);
	$retarray = json_decode($retdata, true);
	if (JsonErrorCheck($retarray) == false)
	{
		if ($retarray['success']['status'] == 200)
			echo "<font color=green><b>Success</b></font>";
		else
			echo "<font color=red><b>Failure</b></font>";
		echo "</td></tr></table>";
		return;
	}

	// Check Query on After //
	$record = QueryPoolPots(false, $systemid, $poolpotid);
	if ($record['disabled'] == 't')
	{
		echo "<font color=green><b>Success</b></font>";
		echo "</td></tr></table>";
		return;
	}

	echo "<font color=red><b>Failure #2</b></font>";
	echo "</td></tr></table>";
	return;
}

///////////////////////////
// Enable a system user //
///////////////////////////
function EnablePoolPot($systemid, $poolpotid)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+EnablePoolPots: </td><td>";

	// Check Query on Before //
	$record = QueryPoolPots(false, $systemid, $poolpotid);
	if ($record['disabled'] == 'f')
	{
		echo "<font color=red><b>Failure #1</b></font>";
		echo "</td></tr></table>";
		return;
	}

	$headers = [];
	$headers[] = "command: enablepoolpot";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "poolpotid: ".$record['id'];
	$retdata = PostURL($coredomain, $headers);
	$retarray = json_decode($retdata, true);
	if (JsonErrorCheck($retarray) == false)
	{
		if ($retarray['success']['status'] == 200)
			echo "<font color=green><b>Success</b></font>";
		else
			echo "<font color=red><b>Failure</b></font>";
		echo "</td></tr></table>";
		return;
	}

	// Check Query on After //
	$record = QueryPoolPots(false, $systemid, $poolpotid);
	if ($record['disabled'] != 'f')
	{
		echo "<font color=red><b>Failure</b></font>";
		echo "</td></tr></table>";
		return;
	}
	else
	{
		echo "<font color=green><b>Success</b></font>";
		echo "</td></tr></table>";
		return;
	}
}

///////////////////////////
// Do System users tests //
///////////////////////////
function TestPoolPots($systemid)
{
	$qualifytype = 1; // 1 - Person, 2 - Group, 3 - SignUp // 
	$amount = 10000;
	$startdate = date("Y-m-d");
	$enddate =  date("Y-m-d");

	echo "<br><b>Pool Pots Tests:</b><br>";

	AddPoolPot($systemid, $qualifytype, $amount, $startdate, $enddate);
	$record = QueryPoolPots(false, $systemid, 0);
	EditPoolPot($record['id'], $systemid, $qualifytype, $amount, $startdate, $enddate);
	QueryPoolPots(true, $systemid, $record['id']);
	DisablePoolPot($systemid, $record['id']);
	EnablePoolPot($systemid, $record['id']);

	return $record['id'];
}

?>