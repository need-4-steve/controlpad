<?php

/////////////////////
// Add a rank rule //
/////////////////////
function AddRankRule($systemid, $label, $rank, $qualifytype, $threshold, $achvbonus, $rulegroup, $sumrankstart, $sumrankend)
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
	$headers = BuildHeader(CLIENT, "addrankrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["rankrule"][0];
	}
	else
	{
		return "false";
	}
}

//////////////////////
// Edit a rank rule //
//////////////////////
function EditRankRule($systemid, $rankid, $rank, $label)
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
	$headers = BuildHeader(CLIENT, "editrankrule", $fields, "", $values);
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
function QueryRankRule($systemid)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	$headers = BuildHeader(CLIENT, "queryrankrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["rankrule"][0];
	}
	else
	{
		return "false";
	}
}

////////////////////////
// Disable rank rules //
////////////////////////
function DisableRankRule($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "disablerankrule", $fields, "", $values);
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
function EnableRankRule($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "enablerankrule", $fields, "", $values);
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
function GetRankRule($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "getrankrule", $fields, "", $values);
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

#define POST_GETRANKRULE			"getrankrule"


?>