<?php

/////////////////
// Add a Bonus //
/////////////////
function AddRankGenBonusRule($systemid, $myrank, $userrank, $generation, $bonus)
{
	$fields[] = "systemid";
	$fields[] = "myrank";
	$fields[] = "userrank";
	$fields[] = "generation";
	$fields[] = "bonus";
	$values["systemid"] = $systemid;
	$values["myrank"] = $myrank;
	$values["userrank"] = $userrank;
	$values["generation"] = $generation;
	$values["bonus"] = $bonus;
	$headers = BuildHeader(CLIENT, "addrankgenbonusrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['rankgenbonusrules'][0];
	}
	else
	{
		return "false";
	}
}

//////////////////
// Edit a Bonus //
//////////////////
function EditRankGenBonusRule($systemid, $id, $myrank, $userrank, $generation, $bonus)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$fields[] = "myrank";
	$fields[] = "userrank";
	$fields[] = "generation";
	$fields[] = "bonus";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$values["myrank"] = $myrank;
	$values["userrank"] = $userrank;
	$values["generation"] = $generation;
	$values["bonus"] = $bonus;
	$headers = BuildHeader(CLIENT, "editrankgenbonusrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['rankgenbonusrules'][0];
	}
	else
	{
		return "false";
	}
}

///////////////////////////////////
// Query bonus in a given system //
///////////////////////////////////
function QueryRankGenBonusRule($systemid)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	$headers = BuildHeader(CLIENT, "queryrankgenbonusrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['rankgenbonusrules'];
	}
	else
	{
		return "false";
	}
}

/////////////////////
// Disable a Bonus //
/////////////////////
function DisableRankGenBonusRule($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "disablerankgenbonusrule", $fields, "", $values);
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

////////////////////
// Enable a Bonus //
////////////////////
function EnableRankGenBonusRule($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "enablerankgenbonusrule", $fields, "", $values);
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

/////////////////
// Get a bonus //
/////////////////
function GetRankGenBonusRule($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "getrankgenbonusrule", $fields, "", $values);
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

////////////////////////////
// Query the rankgenbonus //
////////////////////////////
function QueryRankGenBonus($systemid, $search, $sort)
{
	$fields[] = "systemid";
	$fields[] = "search";
	$fields[] = "sort";
	$values["systemid"] = $systemid;
	$values["search"] = $search;
	$values["sort"] = $sort;
	$headers = BuildHeader(CLIENT, "queryrankgenbonusrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['rankgenbonusrules'];
	}
	else
	{
		return "false";
	}
}
?>
