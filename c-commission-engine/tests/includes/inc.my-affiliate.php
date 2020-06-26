<?php

////////////////////////////////////////////
// Check for a valid email already in use //
////////////////////////////////////////////
function MyUserValidCheck($systemid, $email)
{
	$fields[] = "systemid";
	$fields[] = "email";
	$values["systemid"] = $systemid;
	$values["email"] = $email;
	$headers = BuildHeader(CLIENT, "myuservalidcheck", $fields, "", $values);
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

//////////////////////////////////////////////
// Password reset generate a hash for email //
//////////////////////////////////////////////
function MyPassHashGen($systemid, $email, $remoteaddress)
{
	$fields[] = "systemid";
	$fields[] = "email";
	$fields[] = "remoteaddress";
	$values["systemid"] = $systemid;
	$values["email"] = $email;
	$values["remoteaddress"] = $remoteaddress;
	$headers = BuildHeader(CLIENT, "mypasshashgen", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['hashgen']['hash'];
	}
	else
	{
		return "false";
	}
}

////////////////////////////////
// Is the password Hash Valid //
////////////////////////////////
function MyPassHashValid($hash)
{
	$fields[] = "hash";
	$values["hash"] = $hash;
	$headers = BuildHeader(CLIENT, "mypasshashvalid", $fields, "", $values);
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

///////////////////////////////////
// Update the hash has been used //
///////////////////////////////////
function MyPassHashUpdate($hash)
{
	$fields[] = "hash";
	$values["hash"] = $hash;
	$headers = BuildHeader(CLIENT, "mypasshashupdate", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['hashupdate']['userid'];
	}
	else
	{
		return "false";
	}
}

/////////////////////////
// Update the password //
/////////////////////////
function MyPassReset($systemid, $userid, $password)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$fields[] = "password";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$values["password"] = $password;
	$headers = BuildHeader(CLIENT, "mypassreset", $fields, "", $values);
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

//////////////
// MyLogout //
//////////////
function MyLogout($systemid, $email)
{
	$fields[] = "systemid";
	$fields[] = "email";
	$values["systemid"] = $systemid;
	$values["email"] = $email;
	$headers = BuildHeader(CLIENT, "mylogoutlog", $fields, "", $values);
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

/////////////
// MyLogin //
/////////////
function MyLogin($systemid, $affiliateemail, $affiliatepass, $remoteaddress)
{
	$_SESSION["useremail"] = $affiliateemail;
	$_SESSION["userpass"] = $affiliatepass;

	$fields[] = "systemid";
	$fields[] = "affiliateemail";
	$fields[] = "remoteaddress";
	$values["systemid"] = $systemid;
	$values["affiliateemail"] = $affiliateemail;
	$values["remoteaddress"] = $remoteaddress;
	$headers = BuildHeader(AFFILIATE, "mylogin", $fields, "", $values);
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

///////////////////
// MyProjections //
///////////////////
function MyProjections($systemid, $userid, $startdate, $enddate)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$fields[] = "startdate";
	$fields[] = "enddate";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$values["startdate"] = $startdate;
	$values["enddate"] = $enddate;
	$headers = BuildHeader(AFFILIATE, "myprojections", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["payouts"][0];
	}
	else
	{
		return "false";
	}
}

///////////////////////////
// Handle my commissions //
///////////////////////////
function MyCommissions($systemid, $userid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$headers = BuildHeader(AFFILIATE, "mycommissions", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["commissions"][0];
	}
	else
	{
		return "false";
	}
}

/////////////////////////
// Handle my achvbonus //
/////////////////////////
function MyAchvBonus($systemid, $userid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$headers = BuildHeader(AFFILIATE, "myachvbonus", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["achvbonus"][0];
	}
	else
	{
		return "false";
	}
}

////////////////////
// Handle MyBonus //
////////////////////
function MyBonus($systemid, $userid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$headers = BuildHeader(AFFILIATE, "mybonus", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["bonus"][0];
	}
	else
	{
		return "false";
	}
}

/////////////////////
// Handle MyLedger //
/////////////////////
function MyLedger($systemid, $userid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$headers = BuildHeader(AFFILIATE, "myledger", $fields, "", $values);
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

/////////////////////////////
// Verify My Ledger Values //
/////////////////////////////
function VerifyMyLedger($results, $jsonfile)
{
	$jsonstr = file_get_contents($jsonfile);
	$json = json_decode($jsonstr, true);

	foreach ($json as $record)
	{
		$ledger = LookupMyLedger($results, $record['ledgertype']);
		if (($record['userid'] != $ledger['userid']) ||
			($record['ledgertype'] != $ledger['ledgertype']) ||
			($record['amount'] != $ledger['amount']))
		{
			echo "VerifyMyLedger() - record: amount(".$record['amount']." != ".$ledger['amount'].") - values don't match\n";
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
function LookupMyLedger($json, $ledgertype)
{
	foreach ($json as $record)
	{
		if ($record["ledgertype"] == $ledgertype)
			return $record;
	}

	echo "LookupMyLedger() - record: ledgertype(".$ledgertype.") not found\n";
	return "None";
}

////////////////////
// Handle MyStats //
////////////////////
function MyStats($systemid, $userid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$headers = BuildHeader(AFFILIATE, "mystats", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["userstats"][0];
	}
	else
	{
		return "false";
	}
}

////////////////////////
// Handle MyStatsLvl1 //
////////////////////////
function MyStatsLvl1($systemid, $userid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$headers = BuildHeader(AFFILIATE, "mystatslvl1", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["userstatslvl1"];
	}
	else
	{
		return "false";
	}
}

///////////////////////
// My Downline Stats //
///////////////////////
function MyDownlineStats($systemid, $userid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$headers = BuildHeader(AFFILIATE, "mydownstats", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["userstats"];
	}
	else
	{
		return "false";
	}
}

///////////////////////
// My Downline Stats //
///////////////////////
function MyDownlineStatsLvl1($systemid, $userid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$fields[] = "sort";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$values["sort"] = "limit=10&offset=0&orderby=userid&orderdir=asc&qstring=limit=10&orderby=userid";
	$headers = BuildHeader(AFFILIATE, "mydownstatslvl1", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["userstatslvl1"];
	}
	else
	{
		return "false";
	}
}

////////////////////////////
// Verify your stats lvl1 //
////////////////////////////
function VerifyMyDownStatsLvl1($results, $jsonfile)
{
	$jsonstr = file_get_contents($jsonfile);
	$json = json_decode($jsonstr, true);

	foreach ($json as $record)
	{
		$stats = LookupMyDownStatsLvl1($results, $record['userid']);
		if (($record['personalsales'] != $stats['personalsales']) ||
			($record['signupcount'] != $stats['signupcount']) ||
			($record['customercount'] != $stats['customercount']) ||
			($record['affiliatecount'] != $stats['affiliatecount']) ||
			($record['mywholesalesales'] != $stats['mywholesalesales'])||
			($record['myretailsales'] != $stats['myretailsales']))
		{
			echo "LookupMyDownStatsLvl1() - record: userid(".$record['userid'].") - values don't match\n";
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
function LookupMyDownStatsLvl1($json, $userid)
{
	foreach ($json as $record)
	{
		if ($record["userid"] == $userid)
			return $record;
	}

	echo "LookupMyDownStatsLvl1() - record: userid(".$userid.") not found\n";
	Pre($json);
	return "None";
}

///////////////////////
// My Downline Stats //
///////////////////////
function MyDownlineStatsFull($systemid, $userid, $batchid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$fields[] = "batchid";
	$fields[] = "sort";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$values["batchid"] = $batchid;
	$values["sort"] = "limit=10&offset=0&orderby=userid&orderdir=asc&qstring=limit=10&orderby=userid";
	$headers = BuildHeader(AFFILIATE, "mydownstatsfull", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["userstatsfull"];
	}
	else
	{
		return "false";
	}
}


//////////////////
// My Breakdown //
//////////////////
function MyBreakdown($systemid, $userid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$headers = BuildHeader(AFFILIATE, "mybreakdown", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["breakdown"];
	}
	else
	{
		return "false";
	}
}

////////////////////////
// My Breakdown Users //
////////////////////////
function MyBreakdownGen($systemid, $parentid, $batchid)
{
	$fields[] = "systemid";
	$fields[] = "parentid";
	$fields[] = "batchid";
	$values["systemid"] = $systemid;
	$values["parentid"] = $parentid;
	$values["batchid"] = $batchid;
	$headers = BuildHeader(AFFILIATE, "mybreakdowngen", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["breakdowngen"];
	}
	else
	{
		return "false";
	}
}

////////////////////////
// My Breakdown Users //
////////////////////////
function MyBreakdownUsers($systemid, $parentid, $generation, $batchid)
{
	$fields[] = "systemid";
	$fields[] = "parentid";
	$fields[] = "generation";
	$fields[] = "batchid";
	$values["systemid"] = $systemid;
	$values["parentid"] = $parentid;
	$values["generation"] = $generation;
	$values["batchid"] = $batchid;
	$headers = BuildHeader(AFFILIATE, "mybreakdownusers", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["breakdownusers"];
	}
	else
	{
		return "false";
	}
}

/////////////////////////
// My Breakdown Orders //
/////////////////////////
function MyBreakdownOrders($systemid, $parentid, $userid, $batchid)
{
	$fields[] = "systemid";
	$fields[] = "parentid";
	$fields[] = "userid";
	$fields[] = "batchid";
	$values["systemid"] = $systemid;
	$values["parentid"] = $parentid;
	$values["userid"] = $userid;
	$values["batchid"] = $batchid;
	$headers = BuildHeader(AFFILIATE, "mybreakdownorders", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["breakdownorders"];
	}
	else
	{
		return "false";
	}
}

/////////////////////////
// My Downline Level 1 //
/////////////////////////
function MyDownlineLvl1($systemid, $userid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$headers = BuildHeader(AFFILIATE, "mydownlinelvlone", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["users"];
	}
	else
	{
		return "false";
	}
}

///////////////
// My Upline //
///////////////
function MyUpline($systemid, $userid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$headers = BuildHeader(AFFILIATE, "myupline", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["upline"];
	}
	else
	{
		return "false";
	}
}

/////////////////
// My TopClose //
/////////////////
function MyTopClose($systemid, $userid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$headers = BuildHeader(AFFILIATE, "mytopclose", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["rankmissed"];
	}
	else
	{
		return "false";
	}
}

/////////////////////////////////
function StatCompare($result, $jsonfile)
{
	$jsonstr = file_get_contents($jsonfile);
	$json = json_decode($jsonstr, true);

	foreach ($json as $record)
	{
		$lookupuser = LookupStatUser($result, $record['userid']);
		
		if ($lookupuser == "None")
			return "false";

		if (($record['groupwholesalesales'] != $lookupuser['groupwholesalesales']) ||
			($record['groupused'] != $lookupuser['groupused']) ||
			($record['customerwholesalesales'] != $lookupuser['customerwholesalesales']) ||
			($record['affiliatewholesalesales'] != $lookupuser['affiliatewholesalesales']) ||
			($record['signupcount'] != $lookupuser['signupcount']) ||
			($record['affiliatecount'] != $lookupuser['affiliatecount']) ||
			($record['customercount'] != $lookupuser['customercount']))
		{
			echo "StatCompare() - userid: ".$record['userid']."\n"; 
			echo "StatCompare() - groupwholesale: ".$record['groupwholesalesales']." != ".$lookupuser['groupwholesalesales']."\n";
			echo "StatCompare() - groupused: ".$record['groupused']." != ".$lookupuser['groupused']."\n";
			echo "StatCompare() - customerwhole: ".$record['customerwholesalesales']." != ".$lookupuser['customerwholesalesales']."\n";
			echo "StatCompare() - affiliatewhole: ".$record['affiliatewholesalesales']." != ".$lookupuser['affiliatewholesalesales']."\n";
			
			echo "StatCompare() - resellerwholesale: ".$record['resellerwholesalesales']." != ".$lookupuser['resellerwholesalesales']."\n";

			echo "StatCompare() - signupcount: ".$record['signupcount']." != ".$lookupuser['signupcount']."\n";
			echo "StatCompare() - affiliatecount: ".$record['affiliatecount']." != ".$lookupuser['affiliatecount']."\n";
			echo "StatCompare() - resellercount: ".$record['resellercount']." != ".$lookupuser['resellercount']."\n";
			echo "StatCompare() - customercount: ".$record['customercount']." != ".$lookupuser['customercount']."\n";
			

			return "false";
		}
	}

	return "true";
}


////////////////////////////////////////////
// Lookup the user commission in the json //
////////////////////////////////////////////
function LookupStatUser($json, $userid)
{
	foreach ($json as $record)
	{
		if ($record["userid"] == $userid)
			return $record;
	}

	echo "LookupUserBatchComm() - record: userid(".$userid.") not found\n";
	return "None";
}

///////////////////////
// MyDownRankSumLvl1 //
///////////////////////
function MyDownRankSumLvl1($systemid, $userid, $batchid, $rank)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$fields[] = "batchid";
	$fields[] = "rank";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$values["batchid"] = $batchid;
	$values["rank"] = $rank;
	$headers = BuildHeader(AFFILIATE, "mydownranksumlvl1", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["ranksumlvl1"];
	}
	else
	{
		return "false";
	}
}

///////////////////
// MyDownRankSum //
///////////////////
function MyDownRankSum($systemid, $userid, $batchid, $rank, $generation)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$fields[] = "batchid";
	$fields[] = "rank";
	$fields[] = "generation";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$values["batchid"] = $batchid;
	$values["rank"] = $rank;
	$values["generation"] = $generation;
	$headers = BuildHeader(AFFILIATE, "mydownranksum", $fields, "", $values);
	$json = PostURL($headers);

	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["ranksum"];
	}
	else
	{
		return "false";
	}
}

///////////////////
// MyDownRankSum //
///////////////////
function MyTitle($systemid, $userid, $batchid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$fields[] = "batchid";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$values["batchid"] = $batchid;
	$headers = BuildHeader(AFFILIATE, "mytitle", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["mytitle"];
	}
	else
	{
		return "false";
	}
}
?>