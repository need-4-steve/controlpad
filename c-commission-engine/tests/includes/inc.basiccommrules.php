<?php

///////////////////////////
// Add a commission rule //
///////////////////////////
function AddBasicCommRule($systemid, $generation, $qualifytype, $startthreshold, $endthreshold, $invtype, $event, $percent, $modulus, $paylimit, $pvoverride, $paytype, $rank=null)
{
	$fields[] = "systemid"; 
	$fields[] = "generation";
	$fields[] = "qualifytype";
	$fields[] = "startthreshold";
	$fields[] = "endthreshold";
	$fields[] = "invtype";
	$fields[] = "event";
	$fields[] = "percent";
	$fields[] = "paylimit";
	$fields[] = "pvoverride";
	$fields[] = "paytype";
	if (!empty($rank))
		$fields[] = "rank";
	$values["systemid"] = $systemid;
	$values["generation"] = $generation;
	$values["qualifytype"] = $qualifytype;
	$values["startthreshold"] = $startthreshold;
	$values["endthreshold"] = $endthreshold;
	$values["invtype"] = $invtype;
	$values["event"] = $event;
	$values["percent"] = $percent; 
	$values["paylimit"] = $paylimit; 
	$values["pvoverride"] = $pvoverride;
	$values["paytype"] = $paytype;
	if (!empty($rank))
		$values["rank"] = $rank;
	if (strlen($modulus) != 0)
	{
		$fields[] = "modulus";
		$values["modulus"] = $modulus;
	}
	$headers = BuildHeader(CLIENT, "addbasiccommrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["basiccommrule"][0];
	}
	else
	{
		Pre($json);
		return "false";
	}
}

//////////////////////////
// Edit Commission Rule //
//////////////////////////
function EditBasicCommRule($systemid, $id, $generation, $qualifytype, $startthreshold, $endthreshold, $invtype, $event, $percent, $modulus, $paylimit, $pvoverride, $paytype)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$fields[] = "generation";
	$fields[] = "qualifytype";
	$fields[] = "startthreshold";
	$fields[] = "endthreshold";
	$fields[] = "invtype";
	$fields[] = "event";
	$fields[] = "percent";
	$fields[] = "paylimit";
	$fields[] = "pvoverride";
	$fields[] = "paytype";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$values["generation"] = $generation;
	$values["qualifytype"] = $qualifytype;
	$values["startthreshold"] = $startthreshold;
	$values["endthreshold"] = $endthreshold;
	$values["invtype"] = $invtype;
	$values["event"] = $event;
	$values["percent"] = $percent; 
	$values["paylimit"] = $paylimit; 
	$values["pvoverride"] = $pvoverride;
	$values["paytype"] = $paytype; 
	if (strlen($modulus) != 0)
	{
		$fields[] = "modulus";
		$values["modulus"] = $modulus;
	}
	$headers = BuildHeader(CLIENT, "editbasiccommrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["basiccommrule"][0];
	}
	else
	{
		return "false";
	}
}

//////////////////////////
// Edit Commission Rule //
//////////////////////////
function QueryBasicCommRule($systemid)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	$headers = BuildHeader(CLIENT, "querybasiccommrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["basiccommrule"][0];
	}
	else
	{
		return "false";
	}
}

/////////////////////////////
// Disable Commission Rule //
/////////////////////////////
function DisableBasicCommRule($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "disablebasiccommrule", $fields, "", $values);
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
function EnableBasicCommRule($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "enablebasiccommrule", $fields, "", $values);
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
function GetBasicCommRule($systemid, $id)
{
	$fields[] = "systemid";
	$fields[] = "id";
	$values["systemid"] = $systemid;
	$values["id"] = $id;
	$headers = BuildHeader(CLIENT, "getbasiccommrule", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["basiccommrule"][0];
	}
	else
	{
		return "false";
	}
}

?>