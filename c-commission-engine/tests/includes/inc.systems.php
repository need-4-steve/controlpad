<?php

//////////////////
// Add a system //
//////////////////
function AddSystem($systemname, $commtype, $payouttype, $payoutmonthday, $minpay, $signupbonus, $teamgenmax, $piggyid, $psqlimit, $compression)
{
	// Create a system to work in //
	// Build a list of input fields //
	$fields[] = "systemname";
	$fields[] = "stacktype";
	$fields[] = "commtype";
	$fields[] = "payouttype";
	$fields[] = "payoutmonthday";
	$fields[] = "autoauthgrand";
	$fields[] = "minpay";
	$fields[] = "signupbonus";
	$fields[] = "teamgenmax";
	$fields[] = "piggyid";
	$fields[] = "psqlimit";
	$fields[] = "compression";
	$values["systemname"] = $systemname;
	$values["stacktype"] = 1;
	$values["commtype"] = $commtype;
	$values["payouttype"] = $payouttype;
	$values["payoutmonthday"] = $payoutmonthday;
	$values["autoauthgrand"] = "false";
	$values["minpay"] = $minpay;
	$values["signupbonus"] = $signupbonus;
	$values["teamgenmax"] = $teamgenmax;
	$values["piggyid"] = $piggyid;
	$values["psqlimit"] = $psqlimit;
	$values["compression"] = $compression;
	$headers = BuildHeader(CLIENT, "addsystem", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
	    $system = $json['system'][0];
	    return $system;
	}
	else
	{
		return "false";
	}
}

///////////////////
// Edit a system //
///////////////////
function EditSystem($systemid)
{
	// Create a system to work in //
	// Build a list of input fields //
	$fields[] = "systemid";
	$fields[] = "systemname";
	$fields[] = "stacktype";
	$fields[] = "commtype";
	$fields[] = "payouttype";
	$fields[] = "payoutmonthday";
	$fields[] = "autoauthgrand";
	$fields[] = "minpay";
	$fields[] = "signupbonus";
	$fields[] = "teamgenmax";
	$fields[] = "psqlimit";
	$fields[] = "compression";
	$values["systemid"] = $systemid;
	$values["systemname"] = "APITestEdit".rand(1, 10000);
	$values["stacktype"] = 1;
	$values["commtype"] = 1;
	$values["payouttype"] = 1;
	$values["payoutmonthday"] = "15";
	$values["autoauthgrand"] = "false";
	$values["minpay"] = "5";
	$values["signupbonus"] = "40";
	$values["teamgenmax"] = "3";
	$values["psqlimit"] = "100";
	$values["compression"] = "true";
	$headers = BuildHeader(CLIENT, "editsystem", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
	    $system = $json['system'][0];
	    return $system;
	}
	else
	{
		return "false";
	}
}

////////////////////
// Query a system //
////////////////////
function QuerySystem($systemid)
{
	// Create a system to work in //
	// Build a list of input fields //
	$headers = BuildHeader(CLIENT, "querysystem", NULL, "", NULL);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		foreach ($json["system"] as $system)
		{
			// Check to see if the sysuserid is in the dataset //
			if ($system["id"] == $systemid)
			{
				return "true";
			}
		}

	    return "false";
	}
	else
	{
		return "false";
	}
}

//////////////////////
// Disable a system //
//////////////////////
function DisableSystem($systemid)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	$headers = BuildHeader(CLIENT, "disablesystem", $fields, "", $values);
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

/////////////////////
// Enable a system //
/////////////////////
function EnableSystem($systemid)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	$headers = BuildHeader(CLIENT, "enablesystem", $fields, "", $values);
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

///////////////////////
// Get system values //
///////////////////////
function GetSystemVals($systemid)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	$headers = BuildHeader(CLIENT, "getsystem", $fields, "", $values);
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

////////////////////////////////////////
// Get count of systems for sysuserid //
////////////////////////////////////////
function CountSystem($sysuserid)
{
	$fields[] = "sysuserid";
	$values["sysuserid"] = $sysuserid;
	$headers = BuildHeader(CLIENT, "countsystem", $fields, "", $values);
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
// Get Stats for a system //
////////////////////////////
function StatsSystem($systemid)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	$headers = BuildHeader(CLIENT, "statssystem", $fields, "", $values);
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