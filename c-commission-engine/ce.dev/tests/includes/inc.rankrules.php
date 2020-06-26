<?php

///////////////////////
// Add a system user //
///////////////////////
function AddRankRule($systemid, $rank, $qualifytype, $qualifythreshold, $achvbonus, $breakage, $rulegroup, $maxdacleg, $display)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	if ($display != "false")
	{
		echo "<table>";
		echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+AddRankRule: </td><td>";
	}

	$headers = [];
	$headers[] = "command: addrankrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;

	$headers[] = "systemid: ".$systemid;
	$headers[] = "rank: ".$rank; // What level of rank we want to define for
	$headers[] = "qualifytype: ".$qualifytype; // PERSONAL_SALES = 1, GROUP_SALES = 2, SIGNUP_COUNT = 3, CUSTOMER_SALES = 4, RANK = 5
	$headers[] = "qualifythreshold: ".$qualifythreshold; // Dollar amount or number_count depending on the qualify_type
	$headers[] = "achvbonus: ".$achvbonus;  // One time dollar amount bonus on rank uplevel // 
	$headers[] = "breakage: ".$breakage; // Does the user max out at this level? To start their own branch? Prevent upline from mkaing commissions on them? 
	$headers[] = "rulegroup: ".$rulegroup; // Group rules together for INCLUSIVE apply //
	$headers[] = "maxdacleg: ".$maxdacleg; // Necessaty in getting into the higher ranks. Multiple strong legs. Not just one //

	$retdata = PostURL($coredomain, $headers);
	$retarray = json_decode($retdata, true);
	if ($display != "false")
	{
		if (JsonErrorCheck($retarray) == false)
		{
			if ($retarray['success']['status'] == 200)
				echo "<font color=green><b>Success</b></font>";
			else
			{
				echo "<font color=red><b>Failure</b></font>";
				exit;
			}
		}

		echo "</td></tr></table>";
	}
}

///////////////////
// Edit a system //
///////////////////
function EditRankRules($rankid, $systemid, $rank, $qualifytype, $qualifythreshold, $achvbonus, $breakage, $rulegroup, $maxdacleg)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+EditRankRule: </td><td>";

	$headers = [];
	$headers[] = "command: editrankrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;

	$headers[] = "rankid: ".$rankid;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "rank: ".$rank; // What level of rank we want to define for
	$headers[] = "qualifytype: ".$qualifytype; // PERSONAL_SALES = 1, GROUP_SALES = 2, SIGNUP_COUNT = 3
	$headers[] = "qualifythreshold: ".$qualifythreshold; // Dollar amount or number_count depending on the qualify_type
	$headers[] = "achvbonus: ".$achvbonus;  // One time dollar amount bonus on rank uplevel // 
	$headers[] = "breakage: ".$breakage; // Does the user max out at this level? To start their own branch? Prevent upline from mkaing commissions on them? 
	$headers[] = "rulegroup: ".$rulegroup; // Group rules together for INCLUSIVE apply //
	$headers[] = "maxdacleg: ".$maxdacleg; // Necessaty in getting into the higher ranks. Multiple strong legs. Not just one //

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
function QueryRankRules($display, $systemid, $rankruleid)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	if ($display == true)
	{
		echo "<table>";
		echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+QueryRankRules: </td><td>";
	}

	$headers = [];
	$headers[] = "command: queryrankrules";
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
	foreach ($retarray['rankrules'] as $record)
	{	
		if ($rankruleid == 0)
		{
			if ($display == false)
				return $record;
		}

		if ($rankruleid == $record['id'])
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
function DisableRankRules($systemid, $rankruleid)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+DisableRankRule: </td><td>";

	// Check Query on Before //
	$record = QueryRankRules(false, $systemid, $rankruleid);
	if ($record['disabled'] == 't')
	{
		echo "<font color=red><b>Failure #1</b></font>";
		echo "</td></tr></table>";
		return;
	}

	$headers = [];
	$headers[] = "command: disablerankrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "rankid: ".$rankruleid;
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
	$record = QueryRankRules(false, $systemid, $rankruleid);
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
function EnableRankRules($systemid, $rankruleid)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+EnableRankRule: </td><td>";

	// Check Query on Before //
	$record = QueryRankRules(false, $systemid, $rankruleid);
	if ($record['disabled'] == 'f')
	{
		echo "<font color=red><b>Failure #1</b></font>";
		echo "</td></tr></table>";
		return;
	}

	$headers = [];
	$headers[] = "command: enablerankrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "rankid: ".$record['id'];
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
	$record = QueryRankRules(false, $systemid, $rankruleid);
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
function TestRankRules($systemid)
{
	$qualifytype = 1;
	$rank = 1;
	$qualifytype = 1;
	$qualifythreshold = 2000; 
	$achvbonus = 500;
	$breakage = "false";
	$maxdacleg = 1000;
	$rulegroup = "0";

	$rankruleid = 1;
	echo "<br><b>Rank Rule Tests:</b><br>";

	AddRankRule($systemid, $rank, $qualifytype, $qualifythreshold, $achvbonus, $breakage, $rulegroup, $maxdacleg, 0);
	$record = QueryRankRules(false, $systemid, 0);
	EditRankRules($record['id'], $systemid, $rank, $qualifytype, $qualifythreshold, $achvbonus, $breakage, $rulegroup, $maxdacleg, 0);
	QueryRankRules(true, $systemid, $record['id']);
	DisableRankRules($systemid, $record['id']);
	EnableRankRules($systemid, $record['id']);
}

?>