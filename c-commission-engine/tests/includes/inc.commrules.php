<?php
 
///////////////////////////
// Add a commission rule //
///////////////////////////
function AddCommRule($systemid, $rank, $generation, $infinitybonus, $percent, $invtype, $event, $paytype)
{
	$fields[] = "systemid";
	$fields[] = "rank";
	$fields[] = "generation";
	$fields[] = "infinitybonus";
	$fields[] = "percent";
	$fields[] = "invtype";
	$fields[] = "event";
	$fields[] = "paytype";
	$values["systemid"] = $systemid;
	$values["rank"] = $rank;
	$values["generation"] = $generation;
	$values["infinitybonus"] = $infinitybonus;
	$values["percent"] = $percent; // Group sales //
	$values["invtype"] = $invtype;
	$values["event"] = $event;
	$values["paytype"] = $paytype;
	$headers = BuildHeader(CLIENT, "addcommrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["commrule"][0];
	}
	else
	{
		return "false";
	}
}

//////////////////////////
// Edit Commission Rule //
//////////////////////////
function EditCommRule($systemid, $id, $rank, $generation, $infinitybonus, $percent, $invtype, $event, $paytype)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$fields[] = "rank";
	$fields[] = "generation";
	$fields[] = "infinitybonus";
	$fields[] = "percent";
	$fields[] = "invtype";
	$fields[] = "event";
	$fields[] = "paytype";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$values["rank"] = $rank;
	$values["generation"] = $generation;
	$values["infinitybonus"] = $infinitybonus;
	$values["percent"] = $percent; // Group sales //
	$values["invtype"] = $invtype;
	$values["event"] = $event;
	$values["paytype"] = $paytype;
	$headers = BuildHeader(CLIENT, "editcommrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["commrule"][0];
	}
	else
	{
		return "false";
	}
}

//////////////////////////
// Edit Commission Rule //
//////////////////////////
function QueryCommRule($systemid)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	$headers = BuildHeader(CLIENT, "querycommrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["commrule"][0];
	}
	else
	{
		return "false";
	}
}

/////////////////////////////
// Disable Commission Rule //
/////////////////////////////
function DisableCommRule($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "disablecommrule", $fields, "", $values);
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
// Enable Commission Rule //
////////////////////////////
function EnableCommRule($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "enablecommrule", $fields, "", $values);
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
// Enable Commission Rule //
////////////////////////////
function GetCommRule($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "getcommrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["commrule"][0];
	}
	else
	{
		return "false";
	}
}

?>