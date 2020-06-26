<?php

///////////////////////
// Add a system user //
///////////////////////
function AddCommissionRule($systemid, $rank, $startgen, $endgen, $qualifytype, $qualifythreshold, $infinity, $percent, $display)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	if ($display != "false")
	{
		echo "<table>";
		echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+AddCommissionRule: </td><td>";
	}

	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: addcommrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "rank: ".$rank;
	$headers[] = "startgen: ".$startgen;
	$headers[] = "endgen: ".$endgen;
	$headers[] = "qualifytype: ".$qualifytype;
	$headers[] = "qualifythreshold: ".$qualifythreshold;	
	$headers[] = "infinitybonus: ".$infinity;
	$headers[] = "percent: ".$percent;
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
function EditCommissionRule($commruleid, $systemid, $rank, $startgen, $endgen, $qualifytype, $qualifythreshold, $infinity, $percent)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+EditCommissionRule: </td><td>";

	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: editcommrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "commruleid: ".$commruleid;
	$headers[] = "rank: ".$rank;
	$headers[] = "startgen: ".$startgen;
	$headers[] = "endgen: ".$endgen;
	$headers[] = "qualifytype: ".$qualifytype;
	$headers[] = "qualifythreshold: ".$qualifythreshold;	
	$headers[] = "infinitybonus: ".$infinity;
	$headers[] = "percent: ".$percent;
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
function QueryCommissionRules($display, $systemid, $commruleid)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	if ($display == true)
	{
		echo "<table>";
		echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+QueryCommissionRules: </td><td>";
	}

	$headers = [];
	$headers[] = "command: querycommrules";
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
	foreach ($retarray['commrules'] as $record)
	{	
		if ($commruleid == 0)
		{
			if ($display == false)
				return $record;
		}

		if ($commruleid == $record['id'])
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
function DisableCommissionRule($systemid, $commruleid)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+DisableCommissionRule: </td><td>";

	// Check Query on Before //
	$record = QueryCommissionRules(false, $systemid, $commruleid);
	if ($record['disabled'] == 't')
	{
		echo "<font color=red><b>Failure #1</b></font>";
		echo "</td></tr></table>";
		return;
	}

	$headers = [];
	$headers[] = "command: disablecommrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "commruleid: ".$commruleid;
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
	$record = QueryCommRules(false, $systemid, $commruleid);
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
function EnableCommissionRule($systemid, $commruleid)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+EnableCommissionRule: </td><td>";

	// Check Query on Before //
	$record = QueryCommissionRules(false, $systemid, $commruleid);
	if ($record['disabled'] == 'f')
	{
		echo "<font color=red><b>Failure #1</b></font>";
		echo "</td></tr></table>";
		return;
	}

	$headers = [];
	$headers[] = "command: enablecommrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "commruleid: ".$record['id'];
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
	$record = QueryCommRules(false, $systemid, $commruleid);
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
function TestCommissionRules($systemid)
{
	//$systemid = 3;
	$rank = 1;
	$startgen = 1;
	$endgen = 8;
	$qualifytype = ""; //1;
	$qualifythreshold = ""; //500;
	$percent = 5;
	$infinity = "false";

	echo "<br><b>Commission Rule Tests:</b><br>";

	// Without inifity bonus //
	AddCommissionRule($systemid, $rank, $startgen, $endgen, $qualifytype, $qualifythreshold, $infinity, $percent);
	$record = QueryCommissionRules($display, $systemid, 0);
	EditCommissionRule($record['id'], $systemid, $rank, $startgen, $endgen, $qualifytype, $qualifythreshold, $infinity, $percent);
	QueryCommissionRules(true, $systemid, $record['id']);
	DisableCommissionRule($systemid, $record['id']);
	EnableCommissionRule($systemid, $record['id']);

	// With inifity bonus //
	$percent = 2;
	$infinity = "true";
	AddCommissionRule($systemid, $rank, $startgen, $endgen, $qualifytype, $qualifythreshold, $infinity, $percent);
	$record = QueryCommissionRules($display, $systemid, 0);
	EditCommissionRule($record['id'], $systemid, $rank, $startgen, $endgen, $qualifytype, $qualifythreshold, $infinity, $percent);
	QueryCommissionRules(true, $systemid, $record['id']);
	DisableCommissionRule($systemid, $record['id']);
	EnableCommissionRule($systemid, $record['id']);
}

?>