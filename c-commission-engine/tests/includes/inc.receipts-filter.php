<?php

/*
////////////////
// Add a Pool //
////////////////
function AddReceiptsFilter($systemid, $amount, $startdate, $enddate)
{
	$fields[] = "systemid";
	$fields[] = "amount";
	$fields[] = "startdate";
	$fields[] = "enddate";
	$values["systemid"] = $systemid;
	$values["amount"] = $amount;
	$values["startdate"] = $startdate;
	$values["enddate"] = $enddate;
	$headers = BuildHeader(CLIENT, "addpool", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['pool'][0];
	}
	else
	{
		return "false";
	}
}

////////////////
// Add a Pool //
////////////////
function EditReceiptsFilter($systemid, $id, $amount, $startdate, $enddate)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$fields[] = "amount";
	$fields[] = "startdate";
	$fields[] = "enddate";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$values["amount"] = $amount;
	$values["startdate"] = $startdate;
	$values["enddate"] = $enddate;
	$headers = BuildHeader(CLIENT, "editpool", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['pool'][0];
	}
	else
	{
		return "false";
	}
}

///////////////////////////////////
// Query pools in a given system //
///////////////////////////////////
function QueryReceiptsFilter($systemid)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	$headers = BuildHeader(CLIENT, "querypool", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['pool'];
	}
	else
	{
		return "false";
	}
}

////////////////////
// Disable a pool //
////////////////////
function DisableReceiptsFilter($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "disablepool", $fields, "", $values);
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
// Disable a pool //
////////////////////
function EnableReceiptsFilter($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "enablepool", $fields, "", $values);
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

////////////////
// Get a pool //
////////////////
function GetPool($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "getpool", $fields, "", $values);
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
*/
?>