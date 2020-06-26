<?php

//////////////////////////////
// Query Audit Ranks Report //
//////////////////////////////
function QueryAuditRanksReport($systemid, $batchid)
{
	$fields[] = "systemid";
	$fields[] = "batchid";
	$values["systemid"] = $systemid;
	$values["batchid"] = $batchid;
	$headers = BuildHeader(CLIENT, "queryauditranks", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["auditranks"];
	}
	else
	{
		return "false";
	}
}

///////////////////////
// Query Audit Users //
///////////////////////
function QueryAuditUsersReport($systemid, $batchid)
{
	$fields[] = "systemid";
	$fields[] = "batchid";
	$values["systemid"] = $systemid;
	$values["batchid"] = $batchid;
	$headers = BuildHeader(CLIENT, "queryauditusers", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["auditusers"];
	}
	else
	{
		return "false";
	}
}

/////////////////////
// Query Audit Gen //
/////////////////////
function QueryAuditGenReport($systemid, $batchid)
{
	$fields[] = "systemid";
	$fields[] = "batchid";
	$values["systemid"] = $systemid;
	$values["batchid"] = $batchid;
	$headers = BuildHeader(CLIENT, "queryauditgen", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["auditgen"];
	}
	else
	{
		return "false";
	}
}

/////////////////////
// Query Audit Gen //
/////////////////////
function QueryAuditRanks($systemid, $batchid)
{
	$fields[] = "systemid";
	$fields[] = "batchid";
	$values["systemid"] = $systemid;
	$values["batchid"] = $batchid;
	$headers = BuildHeader(CLIENT, "queryranks", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["ranks"];
	}
	else
	{
		return "false";
	}
}

//////////////////////
// Query Achv Bonus //
//////////////////////
function QueryAchvBonus($systemid, $batchid)
{
	$fields[] = "systemid";
	$fields[] = "batchid";
	$values["systemid"] = $systemid;
	$values["batchid"] = $batchid;
	$headers = BuildHeader(CLIENT, "queryachvbonus", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["achvbonus"];
	}
	else
	{
		return "false";
	}
}

///////////////////////
// Query Commissions //
///////////////////////
function QueryCommissions($systemid, $batchid)
{
	$fields[] = "systemid";
	$fields[] = "batchid";
	$values["systemid"] = $systemid;
	$values["batchid"] = $batchid;
	$headers = BuildHeader(CLIENT, "querycommissions", $fields, "", $values);
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

/////////////////////
// Query UserStats //
/////////////////////
function QueryUserStats($systemid, $batchid)
{
	$fields[] = "systemid";
	$fields[] = "batchid";
	$values["systemid"] = $systemid;
	$values["batchid"] = $batchid;
	$headers = BuildHeader(CLIENT, "queryuserstats", $fields, "", $values);
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

/////////////////////
// Query UserStats //
/////////////////////
function QueryUserStatsLvl1($systemid, $batchid)
{
	$fields[] = "systemid";
	$fields[] = "batchid";
	$values["systemid"] = $systemid;
	$values["batchid"] = $batchid;
	$headers = BuildHeader(CLIENT, "queryuserstatslvl1", $fields, "", $values);
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

?>