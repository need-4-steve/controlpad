<?php

/////////////////////
// Add a Pool Rule //
/////////////////////
function AddPoolRule($systemid, $poolid, $startrank, $endrank, $qualifytype, $qualifythreshold)
{
	$fields[] = "systemid";
	$fields[] = "poolid";
	$fields[] = "startrank";
	$fields[] = "endrank";
	$fields[] = "qualifytype";
	$fields[] = "qualifythreshold";
	$values["systemid"] = $systemid;
	$values["poolid"] = $poolid;
	$values["startrank"] = $startrank;
	$values["endrank"] = $endrank;
	$values["qualifytype"] = $qualifytype;
	$values["qualifythreshold"] = $qualifythreshold;
	$headers = BuildHeader(CLIENT, "addpoolrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['poolrule'][0];
	}
	else
	{
		return "false";
	}
}

//////////////////////
// Edit a Pool Rule //
//////////////////////
function EditPoolRule($systemid, $id, $poolid, $startrank, $endrank, $qualifytype, $qualifythreshold)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$fields[] = "poolid";
	$fields[] = "startrank";
	$fields[] = "endrank";
	$fields[] = "qualifytype";
	$fields[] = "qualifythreshold";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$values["poolid"] = $poolid;
	$values["startrank"] = $startrank;
	$values["endrank"] = $endrank;
	$values["qualifytype"] = $qualifytype;
	$values["qualifythreshold"] = $qualifythreshold;
	$headers = BuildHeader(CLIENT, "editpoolrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['poolrule'][0];
	}
	else
	{
		return "false";
	}
}

//////////////////////////
// Query the pool rules //
//////////////////////////
function QueryPoolRules($systemid, $poolid)
{
	$fields[] = "systemid";
	$fields[] = "poolid";
	$values["systemid"] = $systemid;
	$values["poolid"] = $poolid;
	$headers = BuildHeader(CLIENT, "querypoolrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['poolrule'][0];
	}
	else
	{
		return "false";
	}
}

/////////////////////////
// Disable a pool rule //
/////////////////////////
function DisablePoolRule($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "disablepoolrule", $fields, "", $values);
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
// Enable a pool rule //
////////////////////////
function EnablePoolRule($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "enablepoolrule", $fields, "", $values);
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
// Enable a pool rule //
////////////////////////
function GetPoolRule($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "getpoolrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['poolrule'][0];
	}
	else
	{
		return "false";
	}
}

?>