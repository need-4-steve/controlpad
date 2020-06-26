<?php

function AddLedger($systemid, $batchid, $userid, $ledgertype, $amount, $eventdate)
{
	$fields[] = "systemid";
	$fields[] = "batchid";
	$fields[] = "userid";
	$fields[] = "ledgertype";
	$fields[] = "amount";
	$fields[] = "eventdate";
	$values["systemid"] = $systemid;
	$values["batchid"] = $batchid;
	$values["userid"] = $userid;
	$values["ledgertype"] = $ledgertype;
	$values["amount"] = $amount;
	$values["eventdate"] = $eventdate;
	$headers = BuildHeader(CLIENT, "addledger", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["ledger"];
	}
	else
	{
		return "false";
	}
}

/////////////////
// Edit Ledger //
/////////////////
function EditLedger($systemid, $id, $batchid, $userid, $ledgertype, $amount, $eventdate)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$fields[] = "batchid";
	$fields[] = "userid";
	$fields[] = "ledgertype";
	$fields[] = "amount";
	$fields[] = "eventdate";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$values["batchid"] = $batchid;
	$values["userid"] = $userid;
	$values["ledgertype"] = $ledgertype;
	$values["amount"] = $amount;
	$values["eventdate"] = $eventdate;
	$headers = BuildHeader(CLIENT, "editledger", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["ledger"];
	}
	else
	{
		return "false";
	}
}

//////////////////
// Query Ledger //
//////////////////
function QueryLedger($systemid)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	$headers = BuildHeader(CLIENT, "queryledger", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["ledger"];
	}
	else
	{
		return "false";
	}
}

///////////////////////
// Query Ledger User //
///////////////////////
function QueryLedgerUser($systemid, $userid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$headers = BuildHeader(CLIENT, "queryledgeruser", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["ledger"];
	}
	else
	{
		return "false";
	}
}

////////////////////////////
// Verify your stats lvl1 //
////////////////////////////
function VerifyLedgerUser($results, $jsonfile)
{
	$jsonstr = file_get_contents($jsonfile);
	$json = json_decode($jsonstr, true);

	foreach ($json as $record)
	{
		$stats = LookupLedgerUser($results, $record['ledgertype']);
		if (($record['userid'] != $stats['userid']) ||
			($record['ledgertype'] != $stats['ledgertype']) ||
			($record['amount'] != $stats['amount']))
		{
			echo "LookupLedgerUser() - userid: ".$record['userid']." - values don't match\n";
			Pre($record);
			Pre($stats);
			return "false";
		}
	}

	return "true";
}

////////////////////////////////////////////
// Lookup the user commission in the json //
////////////////////////////////////////////
function LookupLedgerUser($json, $ledgertype)
{
	foreach ($json as $record)
	{
		if ($record["ledgertype"] == $ledgertype)
			return $record;
	}

	echo "LookupLedgerUser() - record: userid(".$userid.") not found\n";
	Pre($json);
	die(1);
	return "None";
}







////////////////////////
// Query Ledger Batch //
////////////////////////
function QueryLedgerBatch($systemid, $batchid)
{
	$fields[] = "systemid";
	$fields[] = "batchid";
	$values["systemid"] = $systemid;
	$values["batchid"] = $batchid;
	$headers = BuildHeader(CLIENT, "queryledgerbatch", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["ledger"];
	}
	else
	{
		return "false";
	}
}

//////////////////////////
// Query Ledger Balance //
//////////////////////////
function QueryLedgerBalance($systemid)
{
	$fields[] = "systemid";
	$fields[] = "sort";
	$values["systemid"] = $systemid;
	$values["sort"] = "orderby=id&orderdir=asc&limit=201&offset=0";
	$headers = BuildHeader(CLIENT, "queryledgerbalance", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["ledger"];
	}
	else
	{
		return "false";
	}
}

////////////////////////////
// Verify the FULL ledger //
////////////////////////////
function VerifyLedgerBalance($result)
{
	$jsonstr = file_get_contents("json/ledger-balance.json");
	$json = json_decode($jsonstr, true);

	foreach ($json as $record)
	{
		$amount = LookupUserLedger($result, $record['userid']);
		if ($record['amount'] != $amount)
		{
			echo "VerifyLedgerBalance() - record: userid(".$record['userid'].") - record(".$record['amount'].") != ".$amount."\n";
			return "false";
		}
		//else
		//	echo "record[userid]=".$record['userid'].", record['amount']=".$record['amount'].", amount=".$amount."\n";
	}

	return "true";
}


////////////////////////////////////////////
// Lookup the user commission in the json //
////////////////////////////////////////////
function LookupUserLedger($json, $userid)
{
	foreach ($json as $record)
	{
		if ($record["userid"] == $userid)
			return $record["amount"];
	}

	echo "LookupUserBatchComm() - record: userid(".$userid.") not found\n";
	return "None";
}

?>