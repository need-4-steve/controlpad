<?php

////////////////////////
// Query Signup bonus //
////////////////////////
function QuerySignupBonus($systemid)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	$headers = BuildHeader(CLIENT, "querysignupbonus", $fields, "", $values);
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

?>