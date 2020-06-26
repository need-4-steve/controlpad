<?php

/////////////////
// Add a Bonus //
/////////////////
function AddBonus($systemid, $userid, $amount, $bonusdate)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$fields[] = "amount";
	$fields[] = "bonusdate";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$values["amount"] = $amount;
	$values["bonusdate"] = $bonusdate;
	$headers = BuildHeader(CLIENT, "addbonus", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['bonus'][0];
	}
	else
	{
		return "false";
	}
}

//////////////////
// Edit a Bonus //
//////////////////
function EditBonus($systemid, $id, $userid, $amount, $bonusdate)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$fields[] = "userid";
	$fields[] = "amount";
	$fields[] = "bonusdate";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$values["userid"] = $userid;
	$values["amount"] = $amount;
	$values["bonusdate"] = $bonusdate;
	$headers = BuildHeader(CLIENT, "editbonus", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['bonus'][0];
	}
	else
	{
		return "false";
	}
}

///////////////////////////////////
// Query bonus in a given system //
///////////////////////////////////
function QueryBonus($systemid)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	$headers = BuildHeader(CLIENT, "querybonus", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['bonus'];
	}
	else
	{
		return "false";
	}
}

///////////////////////////////////
// Query bonus in a given system //
///////////////////////////////////
function QueryUserBonus($systemid, $userid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$headers = BuildHeader(CLIENT, "queryuserbonus", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['bonus'];
	}
	else
	{
		return "false";
	}
}

/////////////////////
// Disable a Bonus //
/////////////////////
function DisableBonus($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "disablebonus", $fields, "", $values);
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
function EnableBonus($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "enablebonus", $fields, "", $values);
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
function GetBonus($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "getbonus", $fields, "", $values);
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
