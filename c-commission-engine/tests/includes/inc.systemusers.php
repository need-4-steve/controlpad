<?php

//////////////////////
// Add a systemuser //
//////////////////////
function AddSystemUser($email)
{
	global $_SESSION;

	//////////////////////////////////
	// Create a system user account //
	//////////////////////////////////
	$sysuser_email = $email;
	$sysuser_password = "###testsystemuserpassword.com###";
	$_SESSION['authemail'] = $sysuser_email;
	$_SESSION['authpass'] = $sysuser_password;

	// Add a system user //
	$fields[] = "firstname";
	$fields[] = "lastname";
	$fields[] = "email";
	$fields[] = "password";
	$fields[] = "remoteaddress";
	$values["firstname"] = "Throttle";
	$values["lastname"] = "Test";
	$values["email"] = $sysuser_email;
	$values["password"] = $sysuser_password;
	$values["remoteaddress"] = "127.0.0.1";
	$headers = BuildHeader(MASTER, "addsystemuser", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
	    //echo "System User Added ".$sysuser_email."\n";
	    return $json['systemuser'][0];
	}
	else
	{
		//Pre($json);
		return "false";
	}
}

////////////////////////
// Edit a system user //
////////////////////////
function EditSystemUser($sysuserid, $sysuser_password)
{
	global $_SESSION;

	// Slightly alter system user password //
	//$sysuser_password = "###testsystemuserpassword.com/GUESS###";

	// Add a system user //
	$fields[] = "sysuserid";
	$fields[] = "email";
	$fields[] = "password";
	$values["sysuserid"] = $sysuserid;
	$values["email"] = $_SESSION['authemail']; // Keep email the same //
	$values["password"] = $sysuser_password;
	$headers = BuildHeader(CLIENT, "editsystemuser", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
	    $_SESSION['authpass'] = $sysuser_password;
	    return "true";
	}
	else
	{
		//Pre($json);
		return "false";
	}
}

/////////////////////////
// Query a system user //
/////////////////////////
function QuerySystemUser($sysuserid)
{
	$headers = BuildHeader(MASTER, "querysystemuser", NULL, "", NULL);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		// Find the systemuserid in the query //
		foreach ($json["systemusers"] as $sysuser)
		{
			// Check to see if the sysuserid is in the dataset //
			if ($sysuser["id"] == $sysuserid)
			{
				return "true";
			}
		}

	    return "false";
	}
	else
	{
		//Pre($json);
		return "false";
	}
}

///////////////////////////
// Disable a system user //
///////////////////////////
function DisableSystemUser($sysuserid)
{
	// Add a system user //
	$fields[] = "sysuserid";
	$values["sysuserid"] = $sysuserid;
	$headers = BuildHeader(MASTER, "disablesystemuser", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
	    return "true";
	}
	else
	{
		//Pre($json);
		return "false";
	}
}

//////////////////////////
// Enable a system user //
//////////////////////////
function EnableSystemUser($sysuserid)
{
	// Add a system user //
	$fields[] = "sysuserid";
	$values["sysuserid"] = $sysuserid;
	$headers = BuildHeader(MASTER, "enablesystemuser", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
	    return "true";
	}
	else
	{
		//Pre($json);
		return "false";
	}
}

////////////////////////////////////////////////////////
// Check to see if email already used for system user //
////////////////////////////////////////////////////////
function ValidCheckSysUser($email)
{
	// Add a system user //
	$fields[] = "email";
	$values["email"] = $email;
	$headers = BuildHeader(MASTER, "validchecksysuser", $fields, "", $values);
	$json = PostURL($headers);

	//if (HandleResponse($json, SUCCESS_NOTHING) == true)
	if (!empty($json['success']) && ($json['success']['status'] == "200"))
	{
		return "true";
	}
	else if ($json['errors']['detail'] == "No systemuser found in system for given systemid and email")
	{
	    return "true";
	}
	else
	{
		//Pre($json);
		return "false";
	}
}

///////////////////////////////////////////////////
// The following below is for resetting password //
///////////////////////////////////////////////////

////////////////////////////////////
// Generate a hash for systemuser //
////////////////////////////////////
function PassHashSysUserGen($email)
{
	// Add a system user //
	$fields[] = "email";
	$fields[] = "remoteaddress";
	$values["email"] = $email;
	$values["remoteaddress"] = "0.0.0.0";
	$headers = BuildHeader(MASTER, "passhashsysusergen", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
	    return $json["hashgen"]["hash"];
	}
	else
	{
		//Pre($json);
		return "false";
	}
}

////////////////////////////////
// Verify hash for systemuser //
////////////////////////////////
function PassHashSysUserValid($hash)
{
	// Add a system user //
	$fields[] = "hash";
	$values["hash"] = $hash;
	$headers = BuildHeader(MASTER, "passhashsysuservalid", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
	    return "true";
	}
	else
	{
		//Pre($json);
		return "false";
	}
}

////////////////////////////////
// Verify hash for systemuser //
////////////////////////////////
function PassHashSysUserUpdate($hash)
{
	// Add a system user //
	$fields[] = "hash";
	$values["hash"] = $hash;
	$headers = BuildHeader(MASTER, "passhashsysuserupdate", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
	    return "true";
	}
	else
	{
		//Pre($json);
		return "false";
	}
}

////////////////////////////////
// Verify hash for systemuser //
////////////////////////////////
function PassResetSysUser($sysuserid, $password)
{
	// Add a system user //
	$fields[] = "sysuserid";
	$fields[] = "password";
	$values["sysuserid"] = $sysuserid;
	$values["password"] = $password;
	$headers = BuildHeader(MASTER, "passresetsysuser", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		$_SESSION['authpass'] = $password; // Update to the new password //
	    return "true";
	}
	else
	{
		return "false";
	}
}

////////////////////////////////
// Verify hash for systemuser //
////////////////////////////////
function LogoutSysUserLog($email)
{
	// Add a system user //
	$fields[] = "email";
	$values["email"] = $email;
	$headers = BuildHeader(MASTER, "logoutsysuserlog", $fields, "", $values);
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