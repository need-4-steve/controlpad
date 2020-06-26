<?php

///////////////////////
// Add a system user //
///////////////////////
function AddPoolRule($systemid, $poolpotid, $startrank, $endrank, $qualifythreshold)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+AddPoolRule: </td><td>";

	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: addpoolrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "poolpotid: ".$poolpotid;
	$headers[] = "startrank: ".$startrank;
	$headers[] = "endrank: ".$endrank;
	$headers[] = "qualifythreshold: ".$qualifythreshold;
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
function EditPoolRule($poolruleid, $systemid, $poolpotid, $startrank, $endrank, $qualifythreshold)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+EditPoolRule: </td><td>";

	$headers = [];
	$headers[] = "command: editpoolrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "poolruleid: ".$poolruleid;
	$headers[] = "poolpotid: ".$poolpotid;
	$headers[] = "startrank: ".$startrank;
	$headers[] = "endrank: ".$endrank;
	$headers[] = "qualifythreshold: ".$qualifythreshold;
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
function QueryPoolRules($display, $systemid, $poolpotid, $poolruleid)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	if ($display == true)
	{
		echo "<table>";
		echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+QueryPoolRules: </td><td>";
	}

	$headers = [];
	$headers[] = "command: querypoolrules";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "poolpotid: ".$poolpotid;
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
	foreach ($retarray['poolrules'] as $record)
	{	
		if ($poolruleid == 0)
		{
			if ($display == false)
				return $record;
		}

		if ($poolruleid == $record['id'])
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
function DisablePoolRule($systemid, $poolpotid, $poolruleid)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+DisablePoolRule: </td><td>";

	// Check Query on Before //
	$record = QueryPoolRules(false, $systemid, $poolpotid, $poolruleid);
	if ($record['disabled'] == 't')
	{
		echo "<font color=red><b>Failure #1</b></font>";
		echo "</td></tr></table>";
		return;
	}

	$headers = [];
	$headers[] = "command: disablepoolrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "poolruleid: ".$poolruleid;
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
	$record = QueryPoolRules(false, $systemid, $poolpotid, $poolruleid);
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
function EnablePoolRule($systemid, $poolpotid, $poolruleid)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+EnablePoolRule: </td><td>";

	// Check Query on Before //
	$record = QueryPoolRules(false, $systemid, $poolpotid, $poolruleid);
	if ($record['disabled'] == 'f')
	{
		echo "<font color=red><b>Failure #1</b></font>";
		echo "</td></tr></table>";
		return;
	}

	$headers = [];
	$headers[] = "command: enablepoolrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "poolruleid: ".$record['id'];
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
	$record = QueryPoolRules(false, $systemid, $poolpotid, $poolruleid);
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
function TestPoolRules($systemid, $poolpotid)
{
	$startrank = 1;
	$endrank = 1; 
	$qualifythreshold = 500;
	
	echo "<br><b>Pool Rules Tests:</b><br>";

	AddPoolRule($systemid, $poolpotid, $startrank, $endrank, $qualifythreshold);
	$record = QueryPoolRules(false, $systemid, $poolpotid, 0);
	EditPoolRule($record['id'], $systemid, $poolpotid, $startrank, $endrank, $qualifythreshold);
	QueryPoolRules(true, $systemid, $poolpotid, $record['id']);
	DisablePoolRule($systemid, $poolpotid, $record['id']);
	EnablePoolRule($systemid, $poolpotid, $record['id']);

	return $record['id'];
}

?>