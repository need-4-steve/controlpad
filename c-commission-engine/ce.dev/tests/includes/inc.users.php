<?php

///////////////////////
// Add a system user //
///////////////////////
function AddUser($systemid, $userid, $sponsorid, $signupdate, $usertype, $display)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	if ($display != "false")
	{
		echo "<table>";
		echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+AddUser: </td><td>";
	}

	$headers = [];
	$headers[] = "command: adduser";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "userid: ".$userid;
	$headers[] = "sponsorid: ".$sponsorid;
	$headers[] = "parentid: ".$sponsorid;
	$headers[] = "signupdate: ".$signupdate;
	$headers[] = "usertype: ".$usertype; // 1 = affiliate, 2 = customer //
	$retdata = PostURL($coredomain, $headers);
	$retarray = json_decode($retdata, true);

	if ($display != "false")
	{
		if (JsonErrorCheck($retarray) == false)
		{
			if ($retarray['success']['status'] == 200)
				echo "<font color=green><b>Success</b></font>";
			else
				echo "<font color=red><b>Failure</b></font>";
		}

		echo "</td></tr></table>";
	}
}

///////////////////
// Edit a system //
///////////////////
function EditUser($systemid, $userid, $sponsorid, $signupdate)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+EditUser: </td><td>";

	$headers = [];
	$headers[] = "command: edituser";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "userid: ".$userid;
	$headers[] = "sponsorid: ".$sponsorid;
	$headers[] = "signupdate: ".$signupdate;
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
function QueryUsers($display, $systemid, $userid)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	if ($display == true)
	{
		echo "<table>";
		echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+QueryUsers: </td><td>";
	}

	$headers = [];
	$headers[] = "command: queryusers";
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
	foreach ($retarray['users'] as $record)
	{	
		if ($userid == 0)
		{
			if ($display == false)
				return $record;
		}

		if ($userid == $record['userid'])
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
function DisableUser($systemid, $userid)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+DisableUser: </td><td>";

	// Check Query on Before //
	$record = QueryUsers(false, $systemid, $userid);
	if ($record['disabled'] == 't')
	{
		echo "<font color=red><b>Failure #1</b></font>";
		echo "</td></tr></table>";
		return;
	}

	$headers = [];
	$headers[] = "command: disableuser";
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
		echo "</td></tr></table>";
		return;
	}

	// Check Query on After //
	$record = QueryUsers(false, $systemid, $userid);
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
function EnableUser($systemid, $userid)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+EnableUser: </td><td>";

	// Check Query on Before //
	$record = QueryUsers(false, $systemid, $userid);
	if ($record['disabled'] == 'f')
	{
		echo "<font color=red><b>Failure #1</b></font>";
		echo "</td></tr></table>";
		return;
	}

	$headers = [];
	$headers[] = "command: enableuser";
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
		echo "</td></tr></table>";
		return;
	}

	// Check Query on After //
	$record = QueryUsers(false, $systemid, $userid);
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
function TestUsers($systemid)
{
	$sponsorid = rand(1, 1000);
	$userid = $sponsorid."-".rand(1,9);
	$signupdate = date("Y-m-d");

	echo "<br><b>Users Tests:</b><br>";

	// Add Sponsor //
	AddUser($systemid, $sponsorid, "0", $signupdate, 1);

	// Add User //
	AddUser($systemid, $userid, $sponsorid, $signupdate, 1);
	$record = QueryUsers(false, $systemid, $userid);
	EditUser($systemid, $record['userid'], $sponsorid, $signupdate, 1);
	QueryUsers(true, $systemid, $record['userid']);
	DisableUser($systemid, $record['userid']);
	EnableUser($systemid, $record['userid']);

	return $record['userid'];
}

?>