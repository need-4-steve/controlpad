<?php

///////////////////////
// Add a system user //
///////////////////////
function AddSystemUser($email, $password)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+AddSystemUsers: </td><td>";

	$headers = [];
	$headers[] = "command: addsystemuser";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;

	$headers[] = "email: ".$email;
	$headers[] = "password: ".$password;

	$retdata = PostURL($coredomain, $headers);
	$retarray = json_decode($retdata, true);
	if (JsonErrorCheck($retarray) == false)
	{
		if (is_numeric($retarray['systemuser'][0]['id']))
			echo "<font color=green><b>Success</b></font>";
		else
			echo "<font color=red><b>Failure</b></font>";
	}

	echo "</td></tr></table>";

	$newuser['authemail'] = $email;
	$newuser['apikey'] = $retarray['systemuser'][0]['apikey'];
	return $newuser;
}

////////////////////////
// Edit a system user //
////////////////////////
function EditSystemUser($sysuserid, $email, $password)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+EditSystemUsers: </td><td>";

	$headers = [];
	$headers[] = "command: editsystemuser";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;

	$headers[] = "sysuserid: ".$sysuserid;
	$headers[] = "email: ".$email;
	$headers[] = "password: ".$password;

	$retdata = PostURL($coredomain, $headers);
	$retarray = json_decode($retdata, true);
	if (JsonErrorCheck($retarray) == false)
	{
		if (is_numeric($retarray['systemuser'][0]['id']))
			echo "<font color=green><b>Success</b></font>";
		else
			echo "<font color=red><b>Failure</b></font>";
	}

	echo "</td></tr></table>";

	$newuser['authemail'] = $email;
	$newuser['apikey'] = $retarray['systemuser'][0]['apikey'];
	return $newuser;
}

////////////////////////
// Query System Users //
////////////////////////
function QuerySystemUser($display, $checkemail)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	if ($display == true)
	{
		echo "<table>";
		echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+QuerySystemUsers: </td><td>";
	}

	$headers = [];
	$headers[] = "command: querysystemusers";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
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
	foreach ($retarray['systemusers'] as $sysrecord)
	{	
		if ($checkemail == $sysrecord['email'])
		{
			$emailfound = true;

			if ($display == false)
				return $sysrecord;
		}
	}

	if ($display == true)
	{
		if ($emailfound == true)
			echo "<font color=green><b>Success</b></font>";
		else
			echo "<font color=red><b>Failure</b></font>";
		echo "</td></tr></table>";
	}
}

///////////////////////////
// Disable a system user //
///////////////////////////
function DisableSystemUser($email)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+DisableSystemUsers: </td><td>";

	// Check Query on Before //
	$record = QuerySystemUser(false, $email);
	if ($record['disabled'] == 't')
	{
		echo "<font color=red><b>Failure #1</b></font>";
		echo "</td></tr></table>";
		return;
	}

	$headers = [];
	$headers[] = "command: disablesystemuser";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "sysuserid: ".$record['id'];
	$retdata = PostURL($coredomain, $headers);
	$retarray = json_decode($retdata, true);

	// Check Query on After //
	$record = QuerySystemUser(false, $email);
	if ($record['disabled'] == true)
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
function EnableSystemUser($email)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+EnableSystemUsers: </td><td>";

	// Check Query on Before //
	$record = QuerySystemUser(false, $email);
	if ($record['disabled'] == 'f')
	{
		echo "<font color=red><b>Failure #1</b></font>";
		echo "</td></tr></table>";
		return;
	}

	$headers = [];
	$headers[] = "command: enablesystemuser";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "sysuserid: ".$record['id'];
	$retdata = PostURL($coredomain, $headers);
	$retarray = json_decode($retdata, true);
	if ($retarray['success']['status'] != 200)
	{
		echo "<font color=red><b>Failure #2</b></font>";
		echo "</td></tr></table>";
		return;
	}

	// Check Query on After //
	$record = QuerySystemUser(false, $email);
	if ($record['disabled'] != 'f')
	{
		echo "<font color=red><b>Failure #3</b></font>";
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

///////////////////////////////////////////
// Test Session of Authorization of user //
///////////////////////////////////////////
function AuthSessionUser($authemail, $authpass)
{
	global $coredomain;

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+AuthSessionUser: </td><td>";

	$headers = [];
	$headers[] = "command: authsessionuser";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "authpass: ".$authpass;
	$retdata = PostURL($coredomain, $headers);
	$retarray = json_decode($retdata, true);
	if (strlen($retarray['authuser']['sessionkey']) > 0)
	{
		echo "<font color=green><b>Success</b></font>";
		echo "</td></tr></table>";
	}
	else
	{
		echo "<font color=red><b>Failure</b></font>";
		echo "</td></tr></table>";
	}

	return $retarray['authuser']['sessionkey'];
}

///////////////////////////
// Do System users tests //
///////////////////////////
function TestSystemUsers()
{
	$acct_email = strtolower(GenRanNumStr(12))."@".strtolower(GenRanStr(10)).".com"; // Make this random generated not in system? //
	$password = "easypassword";

	echo "<b>System Users Tests:</b><br>";
	echo "<i>Account Email Used: ".$acct_email."</i><br>";
	AddSystemUser($acct_email, $password);
	$record = QuerySystemUser(false, $acct_email); // Grab the record back out //
	$newuser = EditSystemUser($record['id'], $acct_email, $password);
	QuerySystemUser(true, $acct_email);
	DisableSystemUser($acct_email);
	EnableSystemUser($acct_email);

	// Test 24 minutes login //
	$sessionkey = AuthSessionUser($acct_email, $password);

	// Set to newuser to communicate with the API //
	global $authemail;
	global $apikey;
	$authemail = $newuser['authemail'];
	$apikey = $newuser['apikey'];
}

?>