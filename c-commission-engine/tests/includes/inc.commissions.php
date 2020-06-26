<?php

/////////////////////////
// Predict Commissions //
/////////////////////////
function PredictCommissions($systemid, $startdate, $enddate)
{
	$fields[] = "systemid";
	$fields[] = "startdate";
	$fields[] = "enddate";
	$values["systemid"] = $systemid;
	$values["startdate"] = $startdate;
	$values["enddate"] = $enddate;
	$headers = BuildHeader(CLIENT, "predictcommissions", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["payouts"];
	}
	else
	{
		return "false";
	}
}

//////////////////////////////////
// Compare Full Predict results //
//////////////////////////////////
function PredictFullCompare($result)
{
	$jsonstr = file_get_contents("json/predict-payout.json");
	$json = json_decode($jsonstr, true);

	foreach ($json as $record)
	{
		if (($record['commission']) != LookupUserCommission($result, $record['userid']))
		{
			echo "PredictFullCompare() - record: userid(".$record['userid'].") - record(".$record['commission'].") != (".LookupUserCommission($result, $record['userid']).")\n";
			return "false";
		}
	}

	return "true";
}

////////////////////////////////////////////
// Lookup the user commission in the json //
////////////////////////////////////////////
function LookupUserCommission($json, $userid)
{
	foreach ($json as $record)
	{
		if ($record["userid"] == $userid)
			return $record["commission"];
	}
	echo "LookupUserCommission() - record: userid(".$userid.") not found\n";
	return "None";
}

/////////////////////////
// Predict GrandTotals //
/////////////////////////
function PredictGrandTotals($systemid, $startdate, $enddate)
{
	$fields[] = "systemid";
	$fields[] = "startdate";
	$fields[] = "enddate";
	$values["systemid"] = $systemid;
	$values["startdate"] = $startdate;
	$values["enddate"] = $enddate;
	$headers = BuildHeader(CLIENT, "predictgrandtotal", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["grandpayouts"];
	}
	else
	{
		return "false";
	}
}

///////////////////////////////
// Do compare on grand total //
///////////////////////////////
function PredictGrandCompare($result)
{
	if ($result['receiptswholesale'] != 51571.06)
	{
		echo "receiptswholesale != 51571.06\n";
		echo "receiptswholesale == ".$result['receiptswholesale']."\n";
		return "false";
	}

	if ($result['receiptsretail'] != 257643.37)
	{
		echo "receiptsretail != 257643.37\n";
		echo "receiptsretail == ".$result['receiptsretail']."\n";
		return "false";
	}

	if ($result['commissions'] != 21741.40) //9241.40)
	{
		echo "commissions != 9241.40\n";
		echo "commissions == ".$result['commissions']."\n";
		return "false";
	}

	if ($result['achvbonuses'] != 1219.00)
	{
		echo "achvbonuses != 1219.00\n";
		echo "achvbonuses == ".$result['achvbonuses']."\n";
		return "false";
	}

	if ($result['bonuses'] != 99.24)
	{
		echo "bonuses != 0\n";
		echo "bonuses == ".$result['bonuses']."\n";
		return "false";
	}

	if ($result['signupbonuses'] != 4000.00)
	{
		echo "signupbonuses != 4000.00\n";
		echo "signupbonuses == ".$result['signupbonuses']."\n";
		return "false";
	}

	return "true";
}

/////////////////////////////////
// Do an actual commission run //
/////////////////////////////////
function CalcCommissions($systemid, $startdate, $enddate)
{
	$fields[] = "systemid";
	$fields[] = "startdate";
	$fields[] = "enddate";
	$values["systemid"] = $systemid;
	$values["startdate"] = $startdate;
	$values["enddate"] = $enddate;
	$headers = BuildHeader(CLIENT, "calccommissions", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["grandpayouts"];
	}
	else
	{
		return "false";
	}
}

///////////////////
// Query Batches //
///////////////////
function QueryBatches($systemid)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	$headers = BuildHeader(CLIENT, "querybatches", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["batches"][0];
	}
	else
	{
		return "false";
	}
}

///////////////////
// Query Batches //
///////////////////
function QueryBatchesMore($type, $systemid, $search, $sort)
{
	$fields[] = "systemid";
	if (!empty($search))
	{
		$fields[] = "search";
		$values["search"] = $search;
	}
	if (!empty($sort))
	{
		$fields[] = "sort";
		$values["sort"] = $sort;
	}

	$values["systemid"] = $systemid;
	$headers = BuildHeader($type, "querybatches", $fields, "", $values);
	$json = PostURL($headers);
	return $json;
}

//////////////////////////
// Compare batch values //
//////////////////////////
function BatchCompare($batch)
{
	if ($batch['receiptswholesale'] != 51571.1000)
	{
		echo "receiptswholesale != 51571.1000\n";
		echo "receiptswholesale == ".$batch['receiptswholesale']."\n";
		return "false";
	}

	if ($batch['commissions'] != 21741.40)
	{
		echo "commissions != 21741.40\n";
		echo "commissions == ".$batch['commissions']."\n";
		return "false";
	}

	if ($batch['achvbonuses'] != 1219.0000)
	{
		echo "achvbonuses != 1219.0000\n";
		echo "achvbonuses == ".$batch['achvbonuses']."\n";
		return "false";
	}

	if ($batch['bonuses'] != 99.2400)
	{
		echo "bonuses != 99.2400\n";
		echo "bonuses == ".$batch['bonuses']."\n";
		return "false";
	}

	return "true";
}

////////////////////
// Query UserComm //
////////////////////
function QueryUserComm($systemid, $userid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$headers = BuildHeader(CLIENT, "queryusercomm", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["commission"][0];
	}
	else
	{
		return "false";
	}
}

////////////////////////////////////////
// Handle compare of user commissions //
////////////////////////////////////////
function UserCommCompare($usercomm)
{
	if ($usercomm['user_id'] != 50)
	{
		echo "user_id != 50\n";
		echo "user_id == ".$usercomm['user_id']."\n";
		return "false";
	}

	if ($usercomm['amount'] != 213.0000)
	{
		echo "amount != 213.0000\n";
		echo "amount == ".$usercomm['amount']."\n";
		return "false";
	}

	return "true";
}

/////////////////////
// Query BatchComm //
/////////////////////
function QueryBatchComm($systemid, $batchid)
{
	$fields[] = "systemid";
	$fields[] = "batchid";
	$values["systemid"] = $systemid;
	$values["batchid"] = $batchid;
	$headers = BuildHeader(CLIENT, "querybatchcomm", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["commissions"];
	}
	else
	{
		return "false";
	}
}

/////////////////////////////////
// Batch Commission Comparison //
// Spot Check three records    //
/////////////////////////////////
function BatchCommCompare($result)
{
	$jsonstr = file_get_contents("json/predict-payout.json");
	$json = json_decode($jsonstr, true);

	foreach ($json as $record)
	{
		$commission = LookupUserBatchComm($result, $record['userid']);
		if ($record['commission'] != $commission)
		{
			echo "BatchCommCompare() - record: userid(".$userid.") - record(".$record['commission'].") != ".$commission."\n";
			return "false";
		}
	}

	return "true";
}


////////////////////////////////////////////
// Lookup the user commission in the json //
////////////////////////////////////////////
function LookupUserBatchComm($json, $userid)
{
	foreach ($json as $record)
	{
		if ($record["userid"] == $userid)
			return $record["amount"];
	}

	echo "LookupUserBatchComm() - record: userid(".$userid.") not found\n";
	return "None";
}


/*
<pre>Array
(
    [id] => 2917
    [system_id] => 86
    [batch_id] => 33
    [user_id] => 23
    [amount] => 8745.7000
)
</pre><pre>Array
(
    [id] => 2945
    [system_id] => 86
    [batch_id] => 33
    [user_id] => 49
    [amount] => 7006.3000
)
</pre><pre>Array
(
    [id] => 2994
    [system_id] => 86
    [batch_id] => 33
    [user_id] => 93
    [amount] => 1290.7000
)
*/

?>