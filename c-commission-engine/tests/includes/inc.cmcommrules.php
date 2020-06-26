<?php

////////////////////////////////////
// Add CheckMatch Commission Rule //
////////////////////////////////////
function AddCMCommRule($systemid, $rank, $generation, $percent)
{
	$fields[] = "systemid";
	$fields[] = "rank";
	$fields[] = "generation";
	$fields[] = "percent";
	$values["systemid"] = $systemid;
	$values["rank"] = $rank;
	$values["generation"] = $generation;
	$values["percent"] = $percent; // Group sales //
	$headers = BuildHeader(CLIENT, "addcmcommrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["cmcommrule"][0];
	}
	else
	{
		return "false";
	}
}

/////////////////////////////////////
// Edit CheckMatch Commission Rule //
/////////////////////////////////////
function EditCMCommRule($systemid, $commruleid, $rank, $generation, $percent)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$fields[] = "rank";
	$fields[] = "generation";
	$fields[] = "percent";
	$values["systemid"] = $systemid;
	$values["id"] = $commruleid;
	$values["rank"] = $rank;
	$values["generation"] = $generation;
	$values["percent"] = $percent; // Group sales //
	$headers = BuildHeader(CLIENT, "editcmcommrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["cmcommrule"][0];
	}
	else
	{
		return "false";
	}
}

///////////////////////////////////////////
// Query the Checkmatch Commission Rules //
///////////////////////////////////////////
function QueryCMCommRule($systemid)
{
	$fields[] = "systemid";
	$fields[] = "sort";
	$values["systemid"] = $systemid;
	$values["sort"] = "orderby=id&orderdir=asc&offset=0&limit=5";
	$headers = BuildHeader(CLIENT, "querycmcommrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["cmcommrule"][0];
	}
	else
	{
		return "false";
	}
}

////////////////////////////////////////////
// Disable the checkmatch commission rule //
////////////////////////////////////////////
function DisableCMCommRule($systemid, $cmcommruleid)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $cmcommruleid;
	$headers = BuildHeader(CLIENT, "disablecmcommrule", $fields, "", $values);
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

///////////////////////////////////////////
// Enable the checkmatch commission rule //
///////////////////////////////////////////
function EnableCMCommRule($systemid, $cmcommruleid)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $cmcommruleid;
	$headers = BuildHeader(CLIENT, "enablecmcommrule", $fields, "", $values);
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