<?php

/////////////////////
// Add a rank rule //
/////////////////////
function AddCMRankRule($systemid, $label, $rank, $qualifytype, $threshold, $achvbonus, $rulegroup, $sumrankstart, $sumrankend)
{
	$fields[] = "systemid";
	$fields[] = "label";
	$fields[] = "rank";
	$fields[] = "qualifytype";
	$fields[] = "qualifythreshold";
	$fields[] = "achvbonus";
	$fields[] = "breakage";
	$fields[] = "rulegroup";
	$fields[] = "sumrankstart";
	$fields[] = "sumrankend";
	//$fields[] = "maxdacleg";
	$values["systemid"] = $systemid;
	$values["label"] = $label;
	$values["rank"] = $rank;
	$values["qualifytype"] = $qualifytype; // Group sales //
	$values["qualifythreshold"] = $threshold;
	$values["achvbonus"] = $achvbonus;
	$values["breakage"] = "false";
	$values["rulegroup"] = $rulegroup;
	$values["sumrankstart"] = $sumrankstart;
	$values["sumrankend"] = $sumrankend;

	//$values["rulegroup"] = "1";
	//$values["maxdacleg"] = "100";
	$headers = BuildHeader(CLIENT, "addcmrankrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["cmrankrule"][0];
	}
	else
	{
		return "false";
	}
}

//////////////////////
// Edit a rank rule //
//////////////////////
function EditCMRankRule($systemid, $rankid, $rank, $label)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$fields[] = "label";
	$fields[] = "rank";
	$fields[] = "qualifytype";
	$fields[] = "qualifythreshold";
	$fields[] = "achvbonus";
	$fields[] = "breakage";
	//$fields[] = "rulegroup";
	//$fields[] = "maxdacleg";
	$values["systemid"] = $systemid;
	$values["id"] = $rankid;
	$values["label"] = $label;
	$values["rank"] = $rank;
	$values["qualifytype"] = 1;
	$values["qualifythreshold"] = "5";
	$values["achvbonus"] = "1";
	$values["breakage"] = "false";
	//$values["rulegroup"] = "1";
	//$values["maxdacleg"] = "100";
	$headers = BuildHeader(CLIENT, "editcmrankrule", $fields, "", $values);
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

//////////////////////
// Query rank rules //
//////////////////////
function QueryCMRankRule($systemid)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	$headers = BuildHeader(CLIENT, "querycmrankrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["cmrankrule"][0];
	}
	else
	{
		return "false";
	}
}

////////////////////////
// Disable rank rules //
////////////////////////
function DisableCMRankRule($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "disablecmrankrule", $fields, "", $values);
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

////////////////////////
// Disable rank rules //
////////////////////////
function EnableCMRankRule($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "enablecmrankrule", $fields, "", $values);
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

////////////////////////
// Disable rank rules //
////////////////////////
function GetCMRankRule($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "getcmrankrule", $fields, "", $values);
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

?>