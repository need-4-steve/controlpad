<?php

///////////////////
// Add a receipt //
///////////////////
function AddReceipt($systemid, $userid, $receiptid, $wholesaleprice, $retailsaleprice)
{
	$fields[] = "systemid";
	$fields[] = "receiptid";
	$fields[] = "userid";
	$fields[] = "wholesaleprice";
	$fields[] = "retailprice";
	$fields[] = "wholesaledate";
	$fields[] = "retaildate";
	$fields[] = "invtype";
	$fields[] = "commissionable";
	$values["systemid"] = $systemid;
	$values["receiptid"] = $receiptid;
	$values["userid"] = $userid;
	$values["wholesaleprice"] = $wholesaleprice;
	$values["retailprice"] = $retailsaleprice;
	$values["wholesaledate"] = "2017-6-15 01:01:01";
	$values["retaildate"] = "2017-6-20";
	$values["invtype"] = "1";
	$values["commissionable"] = "true";
	$headers = BuildHeader(CLIENT, "addreceipt", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["receipt"][0];
	}
	else
	{
		return "false";
	}
}

////////////////////
// Edit a receipt //
////////////////////
function EditReceipt($systemid, $userid, $receiptid, $wholesalesprice, $retailprice)
{
	$fields[] = "systemid";
	$fields[] = "receiptid";
	$fields[] = "userid";
	$fields[] = "wholesaleprice";
	$fields[] = "retailprice";
	$fields[] = "wholesaledate";
	$fields[] = "retaildate";
	$fields[] = "invtype";
	$fields[] = "commissionable";
	$values["systemid"] = $systemid;
	$values["receiptid"] = $receiptid;
	$values["userid"] = $userid;
	$values["wholesaleprice"] = $wholesalesprice;
	$values["retailprice"] = $retailprice;
	$values["wholesaledate"] = "2017-6-15";
	$values["retaildate"] = "2017-6-20";
	$values["invtype"] = "1";
	$values["commissionable"] = "true";
	$headers = BuildHeader(CLIENT, "editreceipt", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["receipt"][0];
	}
	else
	{
		return "false";
	}
}

////////////////////////////
// Edit a receipt With ID //
////////////////////////////
function EditReceiptWID($systemid, $userid, $receiptcoreid)
{
	$fields[] = "systemid";
	$fields[] = "receiptid";
	$fields[] = "userid";
	$fields[] = "wholesaleprice";
	$fields[] = "retailprice";
	$fields[] = "wholesaledate";
	$fields[] = "retaildate";
	$fields[] = "invtype";
	$fields[] = "commissionable";
	$values["systemid"] = $systemid;
	$values["id"] = $receiptcoreid;
	$values["receiptid"] = "1";
	$values["userid"] = $userid;
	$values["wholesaleprice"] = "11.06";
	$values["retailprice"] = "22.17";
	$values["wholesaledate"] = "2017-6-21";
	$values["retaildate"] = "2017-6-22";
	$values["invtype"] = "1";
	$values["commissionable"] = "true";
	$headers = BuildHeader(CLIENT, "editreceipt", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["receipt"][0];
	}
	else
	{
		return "false";
	}
}

////////////////////
// Query Receipts //
////////////////////
function QueryReceipts($systemid)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	$headers = BuildHeader(CLIENT, "queryreceipt", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["receipt"][0];
	}
	else
	{
		return "false";
	}
}

////////////////////
// Query Receipts //
////////////////////
function QueryReceiptsFull($systemid, $sort)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	if (!empty($sort))
	{
		$fields[] = "sort";
		$values["sort"] = $sort;
	}
	$headers = BuildHeader(CLIENT, "queryreceipt", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["receipt"];
	}
	else
	{
		return "false";
	}
}

///////////////////////
// QuerySum Receipts //
///////////////////////
function QueryReceiptSum($systemid)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	$headers = BuildHeader(CLIENT, "queryreceiptsum", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["receipt"][0];
	}
	else
	{
		// Return true if there are not records //
		// Call again after a COMMISSION RUN and verify results //
		if ($json["errors"]["detail"] == "There are no records")
			return "true";

		return "false";
	}
}

/////////////////////
// Disable Receipt //
/////////////////////
function DisableReceipt($systemid, $receiptcoreid)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $receiptcoreid;
	$headers = BuildHeader(CLIENT, "disablereceipt", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		// Call again after a COMMISSION RUN and verify results were affected //
		return "true";
	}
	else
	{
		return "false";
	}
}

////////////////////
// Enable Receipt //
////////////////////
function EnableReceipt($systemid, $receiptcoreid)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $receiptcoreid;
	$headers = BuildHeader(CLIENT, "enablereceipt", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		// Call again after a COMMISSION RUN and verify results were affected //
		return "true";
	}
	else
	{
		return "false";
	}
}

/////////////////
// Get Receipt //
/////////////////
function GetReceipt($systemid, $receiptcoreid)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $receiptcoreid;
	$headers = BuildHeader(CLIENT, "getreceipt", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		// Call again after a COMMISSION RUN and verify results were affected //
		return $json["receipt"][0];
	}
	else
	{
		return "false";
	}
}

////////////////////
// Cancel Receipt //
////////////////////
function CancelReceipt($systemid, $receiptid, $metadataonadd)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	if (!empty($receiptid))
	{
		$fields[] = "receiptid";
		$values["receiptid"] = $receiptid;
	}
	if (!empty($metadataonadd))
	{
		$fields[] = "metadataonadd";
		$values["metadataonadd"] = $metadataonadd;
	}
	$headers = BuildHeader(CLIENT, "cancelreceipt", $fields, "", $values);
	$json = PostURL($headers);
	Pre($json);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		// Call again after a COMMISSION RUN and verify results were affected //
		return $json["receipt"][0];
	}
	else
	{
		return "false";
	}
}

//////////////////////////////////////
// Query the breakdown of a receipt //
//////////////////////////////////////
function QueryBreakdownReceipt($systemid)
{
	$fields[] = "systemid"; 
	$fields[] = "sort";
	$values["systemid"] = $systemid;
	$values["sort"] = "orderby=id&orderdir=asc&limit=5000&offset=0"; // 375 is the real total, but buffer for possible changes //
	$headers = BuildHeader(CLIENT, "querybreakdown", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['breakdown'];
	}
	else
	{
		// Return true if there are not records //
		// Call again after a COMMISSION RUN and verify results //
		if ($json["errors"]["detail"] == "There are no records")
			return "true";

		return "false";
	}
}

//////////////////////////////////////////////
// Verify ALL the breakdown of the receipts //
//////////////////////////////////////////////
function VerifyBreakdownReceipts($results, $filename)
{
	$jsonstr = file_get_contents($filename);
	$json = json_decode($jsonstr, true);

	foreach ($json as $record)
	{
		// CommType #1 //
		$amount = LookupBreakdownReceipt($results, $record['receiptid'], $record['userid'], $record['generation'], $record['commtype']);
		if ($record['amount'] != $amount)
		{
			echo "LookupBreakdownReceipt(#1) - record: userid(".$record['userid'].") - record(".$record['amount'].") != ".$amount.", commtype==".$record['commtype']."\n";
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
function LookupBreakdownReceipt($json, $receiptid, $userid, $generation, $commtype)
{
	foreach ($json as $record)
	{
		if (($record["receiptid"] == $receiptid) && ($record["userid"] == $userid) && ($record["generation"] == $generation) && ($record["commtype"] == $commtype))
			return $record["amount"];
	}

	echo "LookupBreakdownReceipt() - record: receiptid(".$receiptid."), userid(".$userid.") not found\n";
	return "None";
}

/////////////////////////////////////////////////
// Add a receipt for multiple quantity entries //
/////////////////////////////////////////////////
function AddReceiptBulk($systemid, $receiptid, $userid, $qty, $wholesaleprice, $wholesaledate, $invtype, $commissionable, $metadata)
{
	$fields[] = "systemid";
	$fields[] = "receiptid";
	$fields[] = "userid";
	$fields[] = "qty";
	$fields[] = "wholesaleprice";
	$fields[] = "wholesaledate";
	$fields[] = "invtype";
	$fields[] = "commissionable";
	$fields[] = "metadata";
	$values["systemid"] = $systemid;
	$values["receiptid"] = $receiptid;
	$values["userid"] = $userid;
	$values["qty"] = $qty;
	$values["wholesaleprice"] = $wholesaleprice;
	$values["wholesaledate"] = $wholesaledate;
	$values["invtype"] = $invtype;
	$values["commissionable"] = $commissionable;
	$values["metadata"] = $metadata;
	$headers = BuildHeader(CLIENT, "addreceiptbulk", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return "true";
	}
	else
	{
		return "false";
	}
}

/////////////////////////////////////////////////////
// Update a receipt from multiple quantity entries //
/////////////////////////////////////////////////////
function UpdateReceiptBulk($systemid, $receiptid, $userid, $qty, $retailprice, $retaildate, $metadata)
{
	$fields[] = "systemid";
	$fields[] = "receiptid";
	$fields[] = "userid";
	$fields[] = "qty";
	$fields[] = "retailprice";
	$fields[] = "retaildate";
	$fields[] = "metadata";
	$values["systemid"] = $systemid;
	$values["receiptid"] = $receiptid;
	$values["userid"] = $userid;
	$values["qty"] = $qty;
	$values["retailprice"] = $retailprice;
	$values["retaildate"] = $retaildate;
	$values["metadata"] = $metadata;
	$headers = BuildHeader(CLIENT, "updatereceiptbulk", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return "true";
	}
	else
	{
		return "false";
	}
}

//////////////////////////////////////////
// Seed the receipts for commission run //
//////////////////////////////////////////
function SeedReceipts($systemid)
{
	// Add 100 receipts for commission run //
	for ($index=2; $index < 102; $index++)
	{
		$receiptid = $index;
		$result = AddReceipt($systemid, $index, $receiptid, ($index*10), (50*$index));
		if ($result == "false")
			return "false";
	}

	return "true";
}

?>