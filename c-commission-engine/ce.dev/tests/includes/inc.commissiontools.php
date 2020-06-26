<?php

///////////////////////////////////////////
// Do a Prediction of commissions payout //
///////////////////////////////////////////
function CalcPredictCommissions($systemid, $startdate, $enddate, $display)
{
	global $coredomain;
	global $authemail;
	global $apikey;

	if ($display != "false")
	{
		echo "<table>";
		echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+CalcPredictCommissions: </td><td>";
	}

	// Define the commission structure //
	$headers = [];
	$headers[] = "command: predictcommissions";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;

	$headers[] = "systemid: ".$systemid;
	$headers[] = "startdate: ".$startdate;
	$headers[] = "enddate: ".$enddate;
	$retdata = PostURL($coredomain, $headers);
	$retarray = json_decode($retdata, true);

	if ($display == "false")
	{
		return $retarray;
	}
	else
	{
		if (JsonErrorCheck($retarray) == false)
		{
			if (count($retarray['payouts']) == 0)
			{
				echo "<font color=red><b>Failure: No Payouts</b></font>";
			}
			else
			{
				echo "<font color=green><b>Success</b></font>";
			}
			echo "</td></tr></table>";
			return $retarray;
		}
	}
	return $retarray;
}

///////////////////////////////////////////
// Do a Prediction of commissions payout //
///////////////////////////////////////////
function CalcPredictGrandTotal($systemid, $startdate, $enddate, $receipttotal, $display)
{
	global $coredomain;
	global $authemail;
	global $apikey;

	if ($display != "false")
	{
		echo "<table>";
		echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+PredictGrandTotal: </td><td>";
	}

	// Define the commission structure //
	$headers = [];
	$headers[] = "command: predictgrandtotal";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;

	$headers[] = "systemid: ".$systemid;
	$headers[] = "startdate: ".$startdate;
	$headers[] = "enddate: ".$enddate;
	$retdata = PostURL($coredomain, $headers);
	$retarray = json_decode($retdata, true);
	if ($display == "false")
	{
		return $retarray;
	}
	else
	{
		if (JsonErrorCheck($retarray) == false)
		{
			if ($retarray['success']['status'] == 200)
			{
				if (strcmp($receipttotal, $retarray['grandpayouts']['receipts']) == 0)
					echo "<font color=green><b>Success</b></font>";
				else
					echo "<font color=red><b>Failure #1: ".$receipttotal." != ".$retarray['grandpayouts']['receipts']."</b></font>";
			}
			else
			{
				echo "<font color=red><b>Failure #2</b></font>";
			}
			echo "</td></tr></table>";
			return $retarray;
		}
	}
	return $retarray;
}


///////////////////////////////////////////
// Do a Prediction of commissions payout //
///////////////////////////////////////////
function CalcCommissions($systemid, $startdate, $enddate, $receipttotal, $display)
{
	global $coredomain;
	global $authemail;
	global $apikey;

	if ($display != "false")
	{
		echo "<table>";
		echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+CalCommissions: </td><td>";
	}

	// Define the commission structure //
	$headers = [];
	$headers[] = "command: calccommissions";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;

	$headers[] = "systemid: ".$systemid;
	$headers[] = "startdate: ".$startdate;
	$headers[] = "enddate: ".$enddate;
	$retdata = PostURL($coredomain, $headers);
	$retarray = json_decode($retdata, true);
	if ($display == "false")
	{
		return $retarray;
	}
	else
	{
		if (JsonErrorCheck($retarray) == false)
		{
			if ($retarray['success']['status'] == 200)
			{
				if (strcmp($receipttotal, $retarray['grandpayouts']['receipts']) == 0)
					echo "<font color=green><b>Success</b></font>";
				else
					echo "<font color=red><b>Failure #1: ".$receipttotal." != ".$retarray['grandpayouts']['receipts']."</b></font>";
			}
			else
			{
				echo "<font color=red><b>Failure #2</b></font>";
			}
			echo "</td></tr></table>";
			return $retarray;
		}
	}
	return $retarray;
}

/////////////////////
// Get the batches //
/////////////////////
function CalcQueryBatches($systemid, $display)
{
	global $coredomain;
	global $authemail;
	global $apikey;

	if ($display != "false")
	{
		echo "<table>";
		echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+CalQueryBatches: </td><td>";
	}

	$headers = [];
	$headers[] = "command: querybatches";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$retdata = PostURL($coredomain, $headers);
	$retarray = json_decode($retdata, true);
	if ($display == "false")
	{
		return $retarray;
	}
	else
	{
		if (JsonErrorCheck($retarray) == false)
		{
			if ($retarray['success']['status'] == 200)
			{
				if (count($retarray['batches']) == 1)
					echo "<font color=green><b>Success</b></font>";
				else
					echo "<font color=red><b>Failure #1</b></font>";
			}
			else
			{
				echo "<font color=red><b>Failure #2</b></font>";
			}
			echo "</td></tr></table>";
			return $retarray;
		}
	}
	return $retarray;
}

//////////////////////////////
// Get the user commissions //
//////////////////////////////
function CalcQueryUserComm($systemid, $userid, $display)
{
	global $coredomain;
	global $authemail;
	global $apikey;

	if ($display != "false")
	{
		echo "<table>";
		echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+CalcQueryUserComm: </td><td>";
	}
	
	$headers = [];
	$headers[] = "command: queryusercomm";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "userid: ".$userid;
	$retdata = PostURL($coredomain, $headers);
	$retarray = json_decode($retdata, true);

	if ($display == "false")
	{
		return $retarray;
	}
	else
	{
		if (JsonErrorCheck($retarray) == false)
		{
			if ($retarray['success']['status'] == 200)
			{
				if (count($retarray['commission']) == 1)
					echo "<font color=green><b>Success</b></font>";
				else
					echo "<font color=red><b>Failure #1</b></font>";
			}
			else
			{
				echo "<font color=red><b>Failure #2</b></font>";
			}
			echo "</td></tr></table>";
			return $retarray;
		}
	}
	return $retarray;
}

////////////////////////////////////
// Query all records from a batch //
////////////////////////////////////
function CalcQueryBatchComm($systemid, $batchid, $display)
{
	global $coredomain;
	global $authemail;
	global $apikey;

	if ($display != "false")
	{
		echo "<table>";
		echo "<tr><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;+CalcQueryBatchComm: </td><td>";
	}

	if (!is_numeric($batchid))
	{
		echo "<font color=red><b>Failure - Empty batchid</b></font>";
		echo "</td></tr></table>";
		return;
	}

	$headers = [];
	$headers[] = "command: querybatchcomm";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "batchid: ".$batchid;
	$retdata = PostURL($coredomain, $headers);
	$retarray = json_decode($retdata, true);
	if ($display == "false")
	{
		return $retarray;
	}
	else
	{
		if (JsonErrorCheck($retarray) == false)
		{
			if ($retarray['success']['status'] == 200)
			{
				if (count($retarray['commissions']) == 1)
					echo "<font color=green><b>Success</b></font>";
				else
					echo "<font color=red><b>Failure #1</b></font>";
			}
			else
			{
				echo "<font color=red><b>Failure #2</b></font>";
			}
			echo "</td></tr></table>";
			return $retarray;
		}
	}
	return $retarray;
}
?>