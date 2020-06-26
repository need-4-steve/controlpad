<?php

//////////////////////////
// Add external qualify //
//////////////////////////
function AddExtQualify($systemid, $userid, $varid, $value, $eventdate)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$fields[] = "varid";
	$fields[] = "value";
	$fields[] = "eventdate";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$values["varid"] = $varid;
	$values["value"] = $value;
	$values["eventdate"] = $eventdate; // Group sales //
	$headers = BuildHeader(CLIENT, "addextqualify", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["extqualify"][0];
	}
	else
	{
		return "false";
	}
}

///////////////////////////
// Edit external qualify //
///////////////////////////
function EditExtQualify($systemid, $id, $userid, $varid, $value, $eventdate)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$fields[] = "userid";
	$fields[] = "varid";
	$fields[] = "value";
	$fields[] = "eventdate";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$values["userid"] = $userid;
	$values["varid"] = $varid;
	$values["value"] = $value;
	$values["eventdate"] = $eventdate; // Group sales //
	$headers = BuildHeader(CLIENT, "editextqualify", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["extqualify"][0];
	}
	else
	{
		return "false";
	}
}

///////////////////////////
// Edit external qualify //
///////////////////////////
function QueryExtQualify($systemid)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	$headers = BuildHeader(CLIENT, "queryextqualify", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["extqualify"][0];
	}
	else
	{
		return "false";
	}
}

//////////////////////////////
// Disable external qualify //
//////////////////////////////
function DisableExtQualify($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "disableextqualify", $fields, "", $values);
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

/////////////////////////////
// Enable external qualify //
/////////////////////////////
function EnableExtQualify($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "enableextqualify", $fields, "", $values);
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

/////////////////////////////
// Enable external qualify //
/////////////////////////////
function GetExtQualify($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "getextqualify", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["extqualify"][0];
	}
	else
	{
		return "false";
	}
}

?>