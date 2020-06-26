<?php

////////////////////////
// Reissue the apikey //
////////////////////////
function ReIssueApiKey()
{
	$headers = BuildHeader(CLIENT, "reissueapikey", NULL, "", NULL);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["apikey"];
	}
	else
	{
		return "false";
	}
}

///////////////////
// Add an ApiKey //
///////////////////
function AddApiKey($systemid)
{
	$fields[] = "systemid";
	$fields[] = "label";
	$values["systemid"] = $systemid;
	$values["label"] = "TestingApiKey";
	$headers = BuildHeader(CLIENT, "addapikey", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return "false"; // Swap true/false cause commands aren't finished //
	}
	else
	{
		return "true";
	}
}

////////////////////
// Edit an ApiKey //
////////////////////
function EditApiKey($systemid)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$fields[] = "label";
	$values["systemid"] = $systemid;
	$values["id"] = 0; // Try to break it when we finish //
	$values["label"] = "TestingApiKey";
	$headers = BuildHeader(CLIENT, "editapikey", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return "false"; // Swap true/false cause commands aren't finished //
	}
	else
	{
		return "true";
	}
}

/////////////////////
// Query an ApiKey //
/////////////////////
function QueryApiKey($systemid)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	$headers = BuildHeader(CLIENT, "queryapikey", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return "false"; // Swap true/false cause commands aren't finished //
	}
	else
	{
		return "true";
	}
}

///////////////////////
// Disable an ApiKey //
///////////////////////
function DisableApiKey($systemid)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = 0; // Try to break it when we finish //
	$headers = BuildHeader(CLIENT, "disableapikey", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return "false"; // Swap true/false cause commands aren't finished //
	}
	else
	{
		return "true";
	}
}

///////////////////////
// Disable an ApiKey //
///////////////////////
function EnableApiKey($systemid)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = 0; // Try to break it when we finish //
	$headers = BuildHeader(CLIENT, "enableapikey", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return "false"; // Swap true/false cause commands aren't finished //
	}
	else
	{
		return "true";
	}
}

?>