<?php

///////////////////////
// Add a system user //
///////////////////////
function AddSystem($systemname, $commtype, $payouttype, $payoutmonthday, $payoutweekday, $autoauthgrand, $infinitycap, $updatedurl, $updatedusername, $updatedpassword, $display)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	if ($display != "false")
	{
		echo "<table>";
		echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+AddSystem: </td><td>";
	}

	$headers = [];
	$headers[] = "command: addsystem";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;

	$headers[] = "systemname: ".$systemname;
	$headers[] = "commtype: ".$commtype;
	$headers[] = "payouttype: ".$payouttype;
	$headers[] = "payoutmonthday: ".$payoutmonthday;
	$headers[] = "payoutweekday: ".$payoutweekday; 
	$headers[] = "autoauthgrand: ".$autoauthgrand;
	$headers[] = "infinitycap: ".$infinitycap;
	$headers[] = "updatedurl: ".$updatedurl;
	$headers[] = "updatedusername: ".$updatedusername;
	$headers[] = "updatedpassword: ".$updatedpassword;

	$retdata = PostURL($coredomain, $headers);
	$retarray = json_decode($retdata, true);
	if ($display != "false")
	{
		if (JsonErrorCheck($retarray) == false)
		{
			if (is_numeric($retarray['system'][0]['id']))
				echo "<font color=green><b>Success</b></font>";
			else
				echo "<font color=red><b>Failure</b></font>";
		}
	}

	echo "</td></tr></table>";

	// Return the new systemid //
	return $retarray['system'][0]['id'];
}

///////////////////
// Edit a system //
///////////////////
function EditSystem($systemid, $systemname, $commtype, $payouttype, $payoutmonthday, $payoutweekday, $autoauthgrand, $infinitycap, $updatedurl, $updatedusername, $updatedpassword)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+EditSystem: </td><td>";

	$headers = [];
	$headers[] = "command: editsystem";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;

	$headers[] = "systemid: ".$systemid;
	$headers[] = "systemname: ".$systemname;
	$headers[] = "commtype: ".$commtype;
	$headers[] = "payouttype: ".$payouttype;
	$headers[] = "payoutmonthday: ".$payoutmonthday;
	$headers[] = "payoutweekday: ".$payoutweekday; 
	$headers[] = "autoauthgrand: ".$autoauthgrand;
	$headers[] = "infinitycap: ".$infinitycap;
	$headers[] = "updatedurl: ".$updatedurl;
	$headers[] = "updatedusername: ".$updatedusername;
	$headers[] = "updatedpassword: ".$updatedpassword;

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
function QuerySystems($display, $systemname)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	if ($display == true)
	{
		echo "<table>";
		echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+QuerySystems: </td><td>";
	}

	$headers = [];
	$headers[] = "command: querysystems";
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
	foreach ($retarray['systems'] as $sysrecord)
	{	
		if ($systemname == $sysrecord['systemname'])
		{
			$systemfound = true;

			if ($display == false)
				return $sysrecord;
		}
	}

	if ($display == true)
	{
		if ($systemfound == true)
			echo "<font color=green><b>Success</b></font>";
		else
			echo "<font color=red><b>Failure</b></font>";
		echo "</td></tr></table>";
	}
}

///////////////////////////
// Disable a system user //
///////////////////////////
function DisableSystem($systemname)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+DisableSystem: </td><td>";

	// Check Query on Before //
	$record = QuerySystems(false, $systemname);
	if ($record['disabled'] == 't')
	{
		echo "<font color=red><b>Failure #1</b></font>";
		echo "</td></tr></table>";
		return;
	}

	$headers = [];
	$headers[] = "command: disablesystem";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$record['id'];
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
	$record = QuerySystems(false, $systemname);
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
function EnableSystem($systemname)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+EnableSystem: </td><td>";

	// Check Query on Before //
	$record = QuerySystems(false, $systemname);
	if ($record['disabled'] == 'f')
	{
		echo "<font color=red><b>Failure #1</b></font>";
		echo "</td></tr></table>";
		return;
	}

	$headers = [];
	$headers[] = "command: enablesystem";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$record['id'];
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
	$record = QuerySystems(false, $systemname);

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
function TestSystems()
{
	$systemname = "game.".strtolower(GenRanStr(12));
	$commtype = 1;
	$payouttype = 3;
	$payoutmonthday = '15';
	$payoutweekday = '5';
	$autoauthgrand = "false";
	$infinitycap = '2'; // No more than 2% of commission on sales volume for pay period //
	$updatedurl = "http://".$_SERVER['SERVER_NAME']."/testupdatedurl.php";
	$updatedusername = "testusername";
	$updatedpassword = "testpassword";

	echo "<br><b>Systems Tests:</b><br>";
	echo "<i>System Name Used: ".$systemname."</i><br>";
	AddSystem($systemname, $commtype, $payouttype, $payoutmonthday, $payoutweekday, $autoauthgrand, $infinitycap, $updatedurl, $updatedusername, $updatedpassword);
	$record = QuerySystems(false, $systemname); // Grab the record back out //
	EditSystem($record['id'], $systemname, $commtype, $payouttype, $payoutmonthday, $payoutweekday, $autoauthgrand, $infinitycap, $updatedurl, $updatedusername, $updatedpassword);
	QuerySystems(true, $systemname); // Grab the record back out //
	DisableSystem($systemname);
	EnableSystem($systemname);

	return $record['id'];
}

//#define COMMRULE_HYBRIDUNI			1 // Meet criteria or get skipped over on commissions //
//#define COMMRULE_BREAKAWAY			2 // A person breaks out of a downline to start their own branch //
//#define COMMRULE_BINARY				3 // Take the two top legs. Pay the lesser of the two //
//$headers[] = "payouttype: ".$_POST['payouttype']; // 1 - monthly, 2 - weekly, 3 - daily

?>