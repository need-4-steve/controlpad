<?php

////////////////
// Add a user //
////////////////
function AddUser($systemid, $userid, $parentid, $sponsorid, $usertype, $email, $address="", $city="", $state="", $zip="")
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$fields[] = "parentid";
	$fields[] = "sponsorid";
	$fields[] = "signupdate";
	$fields[] = "usertype";
	$fields[] = "email";
	
	// Optional Fields //
	if (!empty($address))
	{
		$fields[] = "address";
		$values["address"] = $address;
	}
	if (!empty($city))
	{
		$fields[] = "city";
		$values["city"] = $city;
	}
	if (!empty($state))
	{
		$fields[] = "state";
		$values["state"] = $state;
	}
	if (!empty($zip))
	{
		$fields[] = "zip";
		$values["zip"] = $zip;
	}

	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$values["parentid"] = $parentid;
	$values["sponsorid"] = $sponsorid;
	$values["signupdate"] = "2017-6-5";
	$values["usertype"] = $usertype;
	$values["email"] = $email;
	$headers = BuildHeader(CLIENT, "adduser", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["user"][0];
	}
	else
	{
		return "false";
	}
}

//////////////////////////////
// Update the users address //
//////////////////////////////
function UpdateUserAddress($systemid, $userid, $address, $city, $state, $zip)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$fields[] = "address";
	$fields[] = "city";
	$fields[] = "state";
	$fields[] = "zip";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$values["address"] = $address;
	$values["city"] = $city;
	$values["state"] = $state;
	$values["zip"] = $zip;
	$headers = BuildHeader(CLIENT, "updateuseraddr", $fields, "", $values);
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

/////////////////
// Edit a user //
/////////////////
function EditUser($systemid, $user)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$fields[] = "parentid";
	$fields[] = "sponsorid";
	$fields[] = "signupdate";
	$fields[] = "usertype";
	$fields[] = "firstname";
	$fields[] = "lastname";
	$fields[] = "email";
	$fields[] = "cell";
	$values["systemid"] = $systemid;
	$values["userid"] = $user["user_id"];
	$values["parentid"] = "0";
	$values["sponsorid"] = "0";
	$values["signupdate"] = "2017-6-5";
	$values["usertype"] = "1";
	$values["firstname"] = "Api";
	$values["lastname"] = "TesterEdit";
	$values["email"] = "testceapi@controlpad.com";
	$values["cell"] = "5555555554";
	$headers = BuildHeader(CLIENT, "edituser", $fields, "", $values);
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

/////////////////
// Query Users //
/////////////////
function QueryUsers($systemid)
{
	$fields[] = "systemid";
	$fields[] = "sort";
	$values["systemid"] = $systemid;
	$values["sort"] = "orderby=userid&orderdir=asc&offset=0&limit=1";
	$headers = BuildHeader(CLIENT, "queryuser", $fields, "", $values);
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

//////////////////
// Disable User //
//////////////////
function DisableUser($systemid, $userid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$headers = BuildHeader(CLIENT, "disableuser", $fields, "", $values);
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

//////////////////
// Disable User //
//////////////////
function EnableUser($systemid, $userid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$headers = BuildHeader(CLIENT, "enableuser", $fields, "", $values);
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

//////////////////
// Disable User //
//////////////////
function GetUser($systemid, $userid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$headers = BuildHeader(CLIENT, "getuser", $fields, "", $values);
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

/////////////////////////////////////////
// Seed users for later commission run //
/////////////////////////////////////////
function SeedUsers($systemid)
{
	// Add 100 users for commission run //
	for ($index=2; $index < 102; $index++)
	{
		$result = AddUser($systemid, $index, $index-1, $index-1, 1, "");
		if ($result == "false")
			return "false";
	}

	return "true";
}

?>