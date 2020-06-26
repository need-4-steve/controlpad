<?php

/////////////////////////////
// Set a settings variable //
/////////////////////////////
function SettingsSet($acctype, $webpage, $userid, $varname, $value)
{
	$fields[] = "webpage";
	$fields[] = "userid";
	$fields[] = "varname";
	$fields[] = "value";
	$values["webpage"] = $webpage;
	$values["userid"] = $userid;
	$values["varname"] = $varname;
	$values["value"] = $value;
	$headers = BuildHeader($acctype, "settingsset", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['settings'][0];
	}
	else
	{
		return "false";
	}
}

/////////////////////////////
// Set a settings variable //
/////////////////////////////
function SettingsSetSystem($acctype, $systemid, $webpage, $userid, $varname, $value)
{
	$fields[] = "systemid";
	$fields[] = "webpage";
	$fields[] = "userid";
	$fields[] = "varname";
	$fields[] = "value";
	$values["systemid"] = $systemid;
	$values["webpage"] = $webpage;
	$values["userid"] = $userid;
	$values["varname"] = $varname;
	$values["value"] = $value;
	//$headers = BuildHeader(CLIENT, "settingssetsystem", $fields, "", $values);
	$headers = BuildHeader($acctype, "settingssetsystem", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['settings'][0];
	}
	else
	{
		return "false";
	}
}

////////////////////
// Query Settings //
////////////////////
function SettingsQuerySystem($acctype, $systemid, $search, $sort)
{
	$fields[] = "systemid"; 
	$fields[] = "search";
	$fields[] = "sort";
	$values["systemid"] = $systemid;
	$values["search"] = $search;
	$values["sort"] = $sort;
	//$headers = BuildHeader(CLIENT, "settingsquerysystem", $fields, "", $values);
	$headers = BuildHeader($acctype, "settingsquerysystem", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['settings'][0];
	}
	else
	{
		return "false";
	}
}

////////////////////
// Query Settings //
////////////////////
function SettingsQuery($acctype, $search, $sort)
{
	$fields[] = "search";
	$fields[] = "sort";
	$values["search"] = $search;
	$values["sort"] = $sort;
	$headers = BuildHeader($acctype, "settingsquery", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json['settings'];
	}
	else
	{
		return "false";
	}
}

/////////////////////////////
// Set a settings variable //
/////////////////////////////
function SettingsGetTimezones($acctype, $sort)
{
	//$fields[] = "systemid";
	$fields[] = "sort";
	//$values["systemid"] = $systemid;
	$values["sort"] = $sort;
	$headers = BuildHeader($acctype, "settingsgettz", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json; //['settings'][0];
	}
	else
	{
		return "false";
	}
}
