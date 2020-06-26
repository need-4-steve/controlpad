<?php

///////////////////////
// Add a system user //
///////////////////////
function AddReceipt($systemid, $receiptid, $userid, $amount, $purchasedate, $commissionable, $display)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	if ($display != "false")
	{
		echo "<table>";
		echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+AddReceipt: </td><td>";
	}

	$headers = [];
	$headers[] = "command: addreceipt";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "receiptid: ".$receiptid;
	$headers[] = "userid: ".$userid;
	$headers[] = "amount: ".$amount;
	$headers[] = "purchasedate: ".$purchasedate;
	$headers[] = "commissionable: ".$commissionable;
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
function EditReceipt($systemid, $receiptid, $userid, $amount, $purchasedate, $commissionable)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+EditReceipt: </td><td>";

	$headers = [];
	$headers[] = "command: editreceipt";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "receiptid: ".$receiptid;
	$headers[] = "userid: ".$userid;
	$headers[] = "amount: ".$amount;
	$headers[] = "purchasedate: ".$purchasedate;
	$headers[] = "commissionable: ".$commissionable;
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
function QueryReceipts($display, $systemid, $startdate, $enddate, $receiptid)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	if ($display == true)
	{
		echo "<table>";
		echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+QueryReceipts: </td><td>";
	}

	$headers = [];
	$headers[] = "command: queryreceipts";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "startdate: ".$startdate;
	$headers[] = "enddate: ".$enddate;
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
	foreach ($retarray['receipts'] as $record)
	{	
		if ($receiptid == 0)
		{
			if ($display == false)
				return $record;
		}

		if ($receiptid == $record['receiptid'])
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
function DisableReceipt($systemid, $receiptid)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+DisableReceipt: </td><td>";

	// Check Query on Before //
	//$record = QueryReceipts(false, $systemid, $userid);
	$record = QueryReceipts(false, $systemid, "2010-1-1", "2100-1-1", $receiptid);
	if ($record['disabled'] == 't')
	{
		echo "<font color=red><b>Failure #1</b></font>";
		echo "</td></tr></table>";
		return;
	}

	$headers = [];
	$headers[] = "command: disablereceipt";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "receiptid: ".$receiptid;
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
	$record = QueryReceipts(false, $systemid, "2010-1-1", "2100-1-1", $receiptid);
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
function EnableReceipt($systemid, $receiptid)
{
	global $coredomain;
	global $authemail;
	global $apikey; 

	echo "<table>";
	echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+EnableReceipt: </td><td>";

	// Check Query on Before //
	$record = QueryReceipts(false, $systemid, "2010-1-1", "2100-1-1", $receiptid);
	if ($record['disabled'] == 'f')
	{
		echo "<font color=red><b>Failure #1</b></font>";
		echo "</td></tr></table>";
		return;
	}

	$headers = [];
	$headers[] = "command: enablereceipt";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "receiptid: ".$receiptid;
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
	$record = QueryReceipts(false, $systemid, "2010-1-1", "2100-1-1", $receiptid);
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
function TestReceipts($systemid, $userid)
{
	$receiptid = rand(1, 100000);
	$amount = rand(1, 3000).".".rand(0, 99);
	$purchasedate = date("Y-m-d");
	$commissionable = "true";

	$maxreceipt = 4;

	echo "<br><b>Receipt Tests:</b><br>";

	for ($index=0; $index < $maxreceipt; $index++)
	{
		$receipttotal += $amount;
		AddReceipt($systemid, $receiptid+$index, $userid, $amount, $purchasedate, $commissionable);
	}
	
	$record = QueryReceipts(false, $systemid, "2010-1-1", "2100-1-1", $record['receiptid']);
	EditReceipt($systemid, $record['receiptid'], $userid, $amount, $purchasedate, $commissionable);
	QueryReceipts(true, $systemid, "2010-1-1", "2100-1-1", $record['receiptid']);
	DisableReceipt($systemid, $record['receiptid']);
	EnableReceipt($systemid, $record['receiptid']);

	$record['total'] = $receipttotal;
	return $record;
}

?>