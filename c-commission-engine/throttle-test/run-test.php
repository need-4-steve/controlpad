#!/usr/bin/php
<?php

include "includes/inc.ce-comm.php";

echo "Starting Throttle Test...\n";

//////////////////////////////////
// Create a system user account //
//////////////////////////////////
$sysuser_email = rand(1, 5000)."wanderson@controlpad.com";
$sysuser_password = "werdfdWERWE213213$##@&.com";

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
    echo "System User Added ".$sysuser_email."\n";
}
else
{
	Pre($json);
	echo "Test stopped because of error\n";
	exit(1);
}

/////////////
// #1 Test //
/////////////

// Create a system to work in //
// Build a list of input fields //
$fields[] = "systemname";
$fields[] = "commtype";
$fields[] = "payouttype";
$fields[] = "payoutmonthday";
$fields[] = "autoauthgrand";
$fields[] = "minpay";
$fields[] = "signupbonus";
$values["systemname"] = "ThrottleTest".rand(1, 10000);
$values["commtype"] = 1;
$values["payouttype"] = 1;
$values["payoutmonthday"] = "15";
$values["autoauthgrand"] = "false";
$values["minpay"] = "5";
$values["signupbonus"] = "40";
$headers = BuildHeader(CLIENT, "addsystem", $fields, "", $values);
$json = PostURL($headers);
echo "systemid = ".$json['system'][0]['id']."\n";
if (HandleResponse($json, SUCCESS_NOTHING) == true)
{
    echo "System Added ".$values["systemname"]."\n";
    $systemid = $json['system'][0]['id'];
}
else
{
	Pre($json);
	echo "Test stopped because of error\n";
	exit(1);
}

// Add 1000 users //
// Build a list of input fields //
$fields2[] = "systemid";
$fields2[] = "userid";
$fields2[] = "sponsorid";
$fields2[] = "parentid";
$fields2[] = "signupdate";
$fields2[] = "usertype";
for ($index=1; $index <= 500; $index++)
{
	$values["systemid"] = $systemid;
	$values["userid"] = $index;
	$values["sponsorid"] = $index-1;
	$values["parentid"] = $index-1;
	$values["signupdate"] = date("Y-m-d");
	$values["usertype"] = rand(1, 2);
	
	$headers = BuildHeader(CLIENT, "adduser", $fields2, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
	    echo "User Added ".$values["userid"]."\n";
	}
	else
	{
		Pre($json);
		echo "Test stopped because of error\n";
		exit(1);
	}
}


/*
// Exit Delay //
$headers = BuildHeader(CLIENT, "exit", $fields, "", $values);
$json = PostURL($headers);
if (HandleResponse($json, SUCCESS_NOTHING) == true)
{
    echo "EXIT HIT\n";
}
else
{
	echo "Test stopped because of error\n";
	exit(1);
}
*/

// Spawn a thread //

// Send a thread to run a simulation //

// Try to add a receipt while simulation is running //

/////////////
// #2 Test //
/////////////

// Try to run two simulations at the same time //

/////////////
// #3 Test //
/////////////

// How to test the server maxing out? //








?>