<?php

////////////////////////
// Query GrandPayouts //
////////////////////////
function QueryGrandPayouts($systemid, $authorized)
{
	$fields[] = "systemid";
	$fields[] = "authorized";
	$values["systemid"] = $systemid;
	$values["authorized"] = $authorized;
	$headers = BuildHeader(CLIENT, "querygrandpayout", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["grandtotals"];
	}
	else
	{
		return "false";
	}
}

///////////////////////
// Auth GrandPayouts //
///////////////////////
function AuthGrandPayouts($systemid, $id, $authorized)
{
	$fields[] = "systemid";
	$fields[] = "authorized";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$values["authorized"] = $authorized;
	$headers = BuildHeader(CLIENT, "authgrandpayout", $fields, "", $values);
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
// Auth GrandBulk //
////////////////////
function AuthGrandBulk($systemid)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	$headers = BuildHeader(CLIENT, "authgrandbulk", $fields, "", $values);
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

//////////////////////////
// Disable GrandPayouts //
//////////////////////////
function DisableGrandPayout($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "disablegrandpayout", $fields, "", $values);
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

/////////////////////////
// Enable GrandPayouts //
/////////////////////////
function EnableGrandPayout($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "enablegrandpayout", $fields, "", $values);
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