<?php

include "includes/inc.comm.php";

if (empty($_POST['command']))
	$_POST['command'] = "";

MenuStart();

// Define the system here //
$systemid = $systemid = $_SESSION['systemid'];
$system = "game.test.8";
$usermax = 20;
$receiptmax = 100;

/////////////////////////////
// Handle all system users //
/////////////////////////////
// Create a system user //
if ($_POST['command'] == "authsessionuser")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: authsessionuser";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "authpass: ".$authpass;
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "invalidcommand")
{
	$starttime = time();
	//while (time()-$starttime < 2)
	//{
		$curlstring = $coredomain;
		$headers = [];
		$headers[] = "command: invalidcommand";	
		$retdata = PostURL($curlstring, $headers);
	//}
}

// Create a system user //
if ($_POST['command'] == "addsystemuser")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: addsystemuser";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	
	$headers[] = "email: west3@iwestdev.com";
	$headers[] = "password: my.cooties.com";
	$retdata = PostURL($curlstring, $headers);
}

// Edit a system user //
if ($_POST['command'] == "editsystemuser")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: editsystemuser";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;

	$headers[] = "sysuserid: 2";
	$headers[] = "email: west@iwestdev.com";
	$headers[] = "password: my.cooties2.com";
	$retdata = PostURL($curlstring, $headers);
}

// Query System Users //
if ($_POST['command'] == "querysystemusers")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: querysystemusers";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;

	$retdata = PostURL($curlstring, $headers);
}

// Disable system user //
if ($_POST['command'] == "disablesystemuser")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: disablesystemuser";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;

	$headers[] = "sysuserid: 2";
	$retdata = PostURL($curlstring, $headers);
}

// Enable system user //
if ($_POST['command'] == "enablesystemuser")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: enablesystemuser";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;

	$headers[] = "sysuserid: 2";
	$retdata = PostURL($curlstring, $headers);
}

////////////////////////
// Handle all systems //
////////////////////////
// Create the system //
if ($_POST['command'] == "addsystem")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: addsystem";
	$headers[] = "authemail: ".$authemail;	
	$headers[] = "apikey: ".$apikey;

	$headers[] = "systemname: ".$system;
	$headers[] = "commtype: 1";
	//#define COMMRULE_HYBRIDUNI			1 // Meet criteria or get skipped over on commissions //
	//#define COMMRULE_BREAKAWAY			2 // A person breaks out of a downline to start their own branch //
	//#define COMMRULE_BINARY				3 // Take the two top legs. Pay the lesser of the two //

	$headers[] = "payouttype: 3"; // 1 - monthly, 2 - weekly, 3 - daily
	//$headers[] = "payoutmonthday: 5"; // 1-27. Stupid February and 30 vs 31 days, Just don't support stupid ideas //
	//$headers[] = "payoutweekday: 6"; // 1-7 starting with Sunday //

	//$headers[] = "rest_url: http://www.google.com";
	//$headers[] = "rest_user: lame";
	//$headers[] = "rest_pass: easypass";
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "editsystem")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: editsystem";
	$headers[] = "authemail: ".$authemail;	
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "systemname: ".$system;
	$headers[] = "commtype: 1";
	//#define COMMRULE_HYBRIDUNI			1 // Meet criteria or get skipped over on commissions //
	//#define COMMRULE_BREAKAWAY			2 // A person breaks out of a downline to start their own branch //
	//#define COMMRULE_BINARY				3 // Take the two top legs. Pay the lesser of the two //
	//$data[] = "sysuserid: 1";

	$headers[] = "payouttype: 1"; // 1 - monthly, 2 - weekly, 3 - daily
	$headers[] = "payoutmonthday: 1"; // 1-27. Stupid February and 30 vs 31 days, Just don't support stupid ideas //
	$headers[] = "payoutweekday: 2"; // 1-7 starting with Sunday //

	//$headers[] = "rest_url: http://www.google.com";
	//$headers[] = "rest_user: lame";
	//$headers[] = "rest_pass: easypass";
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "querysystems")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: querysystems";
	$headers[] = "authemail: ".$authemail;	
	$headers[] = "apikey: ".$apikey;

	$retdata = PostURL($curlstring, $headers, $headers);
}

if ($_POST['command'] == "disablesystem")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: disablesystem";
	$headers[] = "authemail: ".$authemail;	
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "enablesystem")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: enablesystem";
	$headers[] = "authemail: ".$authemail;	
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$retdata = PostURL($curlstring, $headers);
}

//////////////////////
// Handle the rules //
//////////////////////
if ($_POST['command'] == "addrankrule")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: addrankrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "rank: 1"; // What level of rank we want to define for
	$headers[] = "qualifytype: 1"; // PERSONAL_SALES = 1, GROUP_SALES = 2, SIGNUP_COUNT = 3
	$headers[] = "qualifythreshold: 1"; // Dollar amount or number_count depending on the qualify_type
	$headers[] = "achvbonus: 100"; // One time dollar amount bonus on rank uplevel // 
	$headers[] = "breakage: false"; // Does the user max out at this level? To start their own branch? Prevent upline from mkaing commissions on them? 
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "editrankrule")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: editrankrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "rankid: 1"; // The actual rankid of the database record //
	$headers[] = "rank: 1"; // What level of rank we want to define for
	$headers[] = "qualifytype: 1"; // PERSONAL_SALES = 1, GROUP_SALES = 2, SIGNUP_COUNT = 3
	$headers[] = "qualifythreshold: 20"; // Dollar amount or number_count depending on the qualify_type
	$headers[] = "achvbonus: 5000"; // One time dollar amount bonus on rank uplevel // 
	$headers[] = "breakage: false"; // Does the user max out at this level? To start their own branch? Prevent upline from mkaing commissions on them? 
	$retdata = PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "queryrankrules")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: queryrankrules";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$retdata = PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "disablerankrule")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: disablerankrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "rankid: 1"; // The actual rankid of the database record //
	$retdata = PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "enablerankrule")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: enablerankrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "rankid: 1"; // The actual rankid of the database record //
	$retdata = PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "addcommrule")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: addcommrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "startrank: 1";
	$headers[] = "endrank: 5";
	$headers[] = "startgen: 1";
	$headers[] = "endgen: 3";
	$headers[] = "qualifytype: 1";
	$headers[] = "qualifythreshold: 1";	
	$headers[] = "percent: 5";
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "editcommrule")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: editcommrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "commruleid: 1";
	$headers[] = "startrank: 1";
	$headers[] = "endrank: 5";
	$headers[] = "startgen: 1";
	$headers[] = "endgen: 3";
	$headers[] = "qualifytype: 1";
	$headers[] = "qualifythreshold: 100";	
	$headers[] = "percent: 5";
	$retdata = PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "querycommrules")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: querycommrules";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$retdata = PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "disablecommrule")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: disablecommrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "commruleid: 1";
	$retdata = PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "enablecommrule")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: enablecommrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "commruleid: 1";
	$retdata = PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "addpoolpot")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: addpoolpot";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "qualifytype: 1"; // Personal Sales, Group Sales, Signup count //
	$headers[] = "amount: 1005";
	$headers[] = "startdate: 2016-01-01";
	$headers[] = "enddate: 2016-10-01";
	$retdata = PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "editpoolpot")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: editpoolpot";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "poolpotid: 1";
	$headers[] = "qualifytype: 1"; // Personal Sales, Group Sales, Signup count //
	$headers[] = "amount: 1005";
	$headers[] = "startdate: 2016-01-01";
	$headers[] = "enddate: 2016-10-01";
	$retdata = PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "querypoolpots")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: querypoolpots";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$retdata = PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "disablepoolpot")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: disablepoolpot";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "poolpotid: 1";
	$retdata = PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "enablepoolpot")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: enablepoolpot";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "poolpotid: 1";
	$retdata = PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "addpoolrule")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: addpoolrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "poolpotid: 1";
	$headers[] = "startrank: 1";
	$headers[] = "endrank: 2";
	$headers[] = "startgen: 1";
	$headers[] = "endgen: 4";
	$headers[] = "qualifythreshold: 100";
	$retdata = PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "editpoolrule")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: editpoolrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "poolruleid: 1";
	$headers[] = "startrank: 1";
	$headers[] = "endrank: 2";
	$headers[] = "startgen: 1";
	$headers[] = "endgen: 4";
	$headers[] = "qualifythreshold: 100";
	$retdata = PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "querypoolrules")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: querypoolrules";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "poolpotid: 1";
	$retdata = PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "disablepoolrule")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: disablepoolrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "poolruleid: 1";
	$retdata = PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "enablepoolrule")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: enablepoolrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "poolruleid: 1";
	$retdata = PostURL($curlstring, $headers)."\n";
}

// Add users into the system //
if ($_POST['command'] == "adduser")
{
	$starttime = time();
	$index = rand(3342, 500000);
	while (time()-$starttime < 30)
	{
		$curlstring = $coredomain;
		$headers = [];
		$headers[] = "command: adduser";
		$headers[] = "authemail: ".$authemail;
		$headers[] = "apikey: ".$apikey;
		$headers[] = "systemid: ".$systemid;

		$headers[] = "userid: ".$index;
		$headers[] = "sponsorid: 1"; //rand(0, 600); // Make sure the sponsor comes before //
		$headers[] = "signupdate: ".date("Y-m-d");
		$retdata .= PostURL($curlstring, $headers)."\n";

		$index++;
	}

	echo "index = ".$index."<br>";

	/*
	for ($index=304; $index < 604; $index++)
	{
		$curlstring = $coredomain;
		$headers = [];
		$headers[] = "command: adduser";
		$headers[] = "authemail: ".$authemail;
		$headers[] = "apikey: ".$apikey;
		$headers[] = "systemid: ".$systemid;

		$headers[] = "userid: ".$index;
		$headers[] = "sponsorid: ".rand(0, $index-1); // Make sure the sponsor comes before //
		$headers[] = "signupdate: ".date("Y-m-d");

		$retdata .= PostURL($curlstring, $headers)."\n";
	}
	*/
}

if ($_POST['command'] == "edituser")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: edituser";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "userid: 2"; //.$index;
	$headers[] = "sponsorid: 1"; //.rand(0, $index-1); // Make sure the sponsor comes before //
	$headers[] = "signupdate: ".date("Y-m-d");

	$retdata .= PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "queryusers")
{
	// Add a user into the system //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: queryusers";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$retdata .= PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "disableuser")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: disableuser";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "userid: 2"; //.$index;
	$retdata .= PostURL($curlstring, $headers);
}

if ($_POST['command'] == "enableuser")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: enableuser";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "userid: 2"; //.$index;
	$retdata .= PostURL($curlstring, $headers, $data);
}

// Add receipts into the system //
if ($_POST['command'] == "addreceipt")
{
	$starttime = microtime(true);
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: addreceipt";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "receiptid: 1";
	$headers[] = "userid: 2"; //.rand(1, $usermax); 
	$headers[] = "amount: ".rand(0, 100).".".rand(0, 99); // Random dollar amounts //
	$headers[] = "purchasedate: ".date("Y-m-d");
	$headers[] = "commissionable: true";
	$retdata .= PostURL($curlstring, $headers);
}

if ($_POST['command'] == "editreceipt")
{	
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: editreceipt";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "receiptid: 1";
	$headers[] = "userid: 2"; //.rand(1, $usermax); 
	$headers[] = "amount: ".rand(0, 100).".".rand(0, 99); // Random dollar amounts //
	$headers[] = "purchasedate: ".date("Y-m-d");
	$headers[] = "commissionable: true";
	$retdata .= PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "queryreceipts")
{	
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: queryreceipts";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "startdate: ".date("Y-m-d");
	$headers[] = "enddate: ".date("Y-m-d");
	$retdata .= PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "disablereceipt")
{	
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: disablereceipt";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "receiptid: 1";
	$retdata .= PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "enablereceipt")
{	
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: enablereceipt";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "receiptid: 1";
	$retdata .= PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "predictcommissions")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: predictcommissions";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "startdate: 2016-3-1";
	$headers[] = "enddate: 2016-3-31";
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "predictgrandtotal")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: predictgrandtotal";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "startdate: 2016-3-1";
	$headers[] = "enddate: 2016-3-31";
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "calccommissions")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: calccommissions";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "startdate: 2016-04-01";
	$headers[] = "enddate: 2016-05-31";
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "querybatches")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: querybatches";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "authorized: false";
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "queryusercomm")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: queryusercomm";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "userid: 1";
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "querybatchcomm")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: querybatchcomm";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "batchid: 1";
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "addbankaccount")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: addbankaccount";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "userid: 1";
	$headers[] = "accounttype: 1"; // CHECKING	= 1, SAVINGS = 2
	$headers[] = "routingnumber: 555444332";
	$headers[] = "accountnumber: 12345678901234567"; 
	$headers[] = "holdername: Tucker T2 Fudpucker";
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "editbankaccount")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: editbankaccount";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "userid: 1";
	$headers[] = "accounttype: 1"; // CHECKING	= 1, SAVINGS = 2
	$headers[] = "routingnumber: 555444332";
	$headers[] = "accountnumber: 12345678901234567"; 
	$headers[] = "holdername: Tucker T2 Fudpucker";
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "querybankaccounts")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: querybankaccounts";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "disablebankaccount")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: disablebankaccount";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "userid: 1";
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "enablebankaccount")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: enablebankaccount";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "userid: 1";
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "initiatevalidation")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: initiatevalidation";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "userid: 1";
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "validateaccount")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: validateaccount";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "userid: 1";
	$headers[] = "amount1: 0.17";
	$headers[] = "amount2: 0.54";
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "processpayments")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: processpayments";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "batchid: 1";
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "queryuserpayments")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: queryuserpayments";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "userid: 2";
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "querybatchpayments")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: querybatchpayments";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "batchid: 1";
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "querynopayusers")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: querynopayusers";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "batchid: 1";
	$retdata = PostURL($curlstring, $headers);
}

if (empty($retdata))
	$retdata = "";
echo "<b>Initial Json Response:</b><br><textarea cols=120 rows=5 >".$retdata."</textarea>";

?>

<table border=0>

<tr><td align=right><b>Auth:</b></td>

<!-- Authenticate a system user -->
<form method=POST action=''>
<td><input type='submit' value='Authenticate System User'></td>
<input type='hidden' name='command' value='authsessionuser'>
</form>

<!-- Test invalid command -->
<form method=POST action=''>
<td><input type='submit' value='Test Invalid Command'></td><tr>
<input type='hidden' name='command' value='invalidcommand'>
</form>

<tr><td align=right><b>System Users:</b></td>

<!-- Add a system user -->
<form method=POST action=''>
<td><input type='submit' value='Add System User'></td>
<input type='hidden' name='command' value='addsystemuser'>
</form>

<!-- Edit a system user -->
<form method=POST action=''>
<td><input type='submit' value='Edit System User'></td>
<input type='hidden' name='command' value='editsystemuser'>
</form>

<!-- Query system users -->
<form method=POST action=''>
<td><input type='submit' value='Query System Users'></td>
<input type='hidden' name='command' value='querysystemusers'>
</form>

<!-- Disable system user -->
<form method=POST action=''>
<td><input type='submit' value='Disable System User'></td>
<input type='hidden' name='command' value='disablesystemuser'>
</form>

<!-- Enable system user -->
<form method=POST action=''>
<td><input type='submit' value='Enable System User'></td></tr>
<input type='hidden' name='command' value='enablesystemuser'>
</form>

<tr><td align=right><b>Systems:</b></td>

<!-- Initialize a system -->
<form method=POST action=''>
<td><input type='submit' value='Add System'></td>
<input type='hidden' name='command' value='addsystem'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Edit System'></td>
<input type='hidden' name='command' value='editsystem'>
</form>

<!-- Query systems -->
<form method=POST action=''>
<td><input type='submit' value='Query Systems'></td>
<input type='hidden' name='command' value='querysystems'>
</form>

<!-- Disable system -->
<form method=POST action=''>
<td><input type='submit' value='Disable Systems'></td>
<input type='hidden' name='command' value='disablesystem'>
</form>

<!-- Enable system -->
<form method=POST action=''>
<td><input type='submit' value='Enable Systems'></td></tr>
<input type='hidden' name='command' value='enablesystem'>
</form>

<tr><td align=right><b>Rank Rules:</b></td>

<!-- Define some rank rules -->
<form method=POST action=''>
<td><input type='submit' value='Add Rank Rule'></td>
<input type='hidden' name='command' value='addrankrule'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Edit Rank Rules'></td>
<input type='hidden' name='command' value='editrankrule'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Query Rank Rules'></td>
<input type='hidden' name='command' value='queryrankrules'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Disable Rank Rule'></td>
<input type='hidden' name='command' value='disablerankrule'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Enable Rank Rule'></td></tr>
<input type='hidden' name='command' value='enablerankrule'>
</form>

<tr><td align=right><b>Commission Rules:</b></td>

<!-- Define some commission rules -->
<form method=POST action=''>
<td><input type='submit' value='Add Commission Rule'></td>
<input type='hidden' name='command' value='addcommrule'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Edit Commission Rule'></td>
<input type='hidden' name='command' value='editcommrule'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Query Commission Rules'></td>
<input type='hidden' name='command' value='querycommrules'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Disable Commission Rule'></td>
<input type='hidden' name='command' value='disablecommrule'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Enable Commission Rule'></td></tr>
<input type='hidden' name='command' value='enablecommrule'>
</form>

<tr><td align=right><b>Pool Pots:</b></td>

<!-- Define pool pots -->
<form method=POST action=''>
<td><input type='submit' value='Add Pool Pot'></td>
<input type='hidden' name='command' value='addpoolpot'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Edit Pool Pot'></td>
<input type='hidden' name='command' value='editpoolpot'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Query Pool Pot'></td>
<input type='hidden' name='command' value='querypoolpots'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Disable Pool Pot'></td>
<input type='hidden' name='command' value='disablepoolpot'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Enable Pool Pot'></td></tr>
<input type='hidden' name='command' value='enablepoolpot'>
</form>

<tr><td align=right><b>Pool Rules:</b></td>

<!-- Define pool pots -->
<form method=POST action=''>
<td><input type='submit' value='Add Pool Rule'></td>
<input type='hidden' name='command' value='addpoolrule'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Edit Pool Rule'></td>
<input type='hidden' name='command' value='editpoolrule'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Query Pool Rule'></td>
<input type='hidden' name='command' value='querypoolrules'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Disable Pool Rule'></td>
<input type='hidden' name='command' value='disablepoolrule'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Enable Pool Rule'></td></tr>
<input type='hidden' name='command' value='enablepoolrule'>
</form>

<tr><td align=right><b>Users:</b></td>

<!-- Add User -->
<form method=POST action=''>
<td><input type='submit' value='Add User'></td>
<input type='hidden' name='command' value='adduser'>
</form>

<!-- Edit User -->
<form method=POST action=''>
<td><input type='submit' value='Edit User'></td>
<input type='hidden' name='command' value='edituser'>
</form>

<!-- Query users -->
<form method=POST action=''>
<td><input type='submit' value='Query Users'></td>
<input type='hidden' name='command' value='queryusers'>
</form>

<!-- Disable user -->
<form method=POST action=''>
<td><input type='submit' value='Disable User'></td>
<input type='hidden' name='command' value='disableuser'>
</form>

<!-- Enable user -->
<form method=POST action=''>
<td><input type='submit' value='Enable User'></td>
<input type='hidden' name='command' value='enableuser'>
</form>

<tr><td align=right><b>Receipts:</b></td>

<!-- Add receipt -->
<form method=POST action=''>
<td><input type='submit' value='Add Receipt'></td>
<input type='hidden' name='command' value='addreceipt'>
</form>

<!-- Edit receipt -->
<form method=POST action=''>
<td><input type='submit' value='Edit Receipt'></td>
<input type='hidden' name='command' value='editreceipt'>
</form>

<!-- Query receipts -->
<form method=POST action=''>
<td><input type='submit' value='Query Receipt'></td>
<input type='hidden' name='command' value='queryreceipts'>
</form>

<!-- Disable receipt -->
<form method=POST action=''>
<td><input type='submit' value='Disable Receipt'></td>
<input type='hidden' name='command' value='disablereceipt'>
</form>

<!-- Enable receipt -->
<form method=POST action=''>
<td><input type='submit' value='Enable Receipt'></td></tr>
<input type='hidden' name='command' value='enablereceipt'>
</form>

<tr><td align=right><b>Commission Calc:</b></td>

<form method=POST action=''>
<td><input type='submit' value='Predict Commissions'></td>
<input type='hidden' name='command' value='predictcommissions'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Predict Grand Total'></td>
<input type='hidden' name='command' value='predictgrandtotal'>
</form>

<!-- Calculate Commissions -->
<form method=POST action=''>
<td><input type='submit' value='Calc Commissions'></td>
<input type='hidden' name='command' value='calccommissions'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Query Batches'></td>
<input type='hidden' name='command' value='querybatches'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Query User Commissions'></td>
<input type='hidden' name='command' value='queryusercomm'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Query Batch Commissions'></td></tr>
<input type='hidden' name='command' value='querybatchcomm'>
</form>

<tr><td align=right><b>Bank Accounts:</b></td>

<form method=POST action=''>
<td><input type='submit' value='Add Bank Account'></td>
<input type='hidden' name='command' value='addbankaccount'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Edit Bank Account'></td>
<input type='hidden' name='command' value='editbankaccount'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Query Bank Accounts'></td>
<input type='hidden' name='command' value='querybankaccounts'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Disable Bank Account'></td>
<input type='hidden' name='command' value='disablebankaccount'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Enable Bank Account'></td>
<input type='hidden' name='command' value='enablebankaccount'>
</form>

<tr><td align=right><b>Accounts Validation:</b></td>

<form method=POST action=''>
<td><input type='submit' value='Initiate Validation'></td>
<input type='hidden' name='command' value='initiatevalidation'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Validate Account'></td></tr>
<input type='hidden' name='command' value='validateaccount'>
</form>

<tr><td align=right><b>Payments:</b></td>

<form method=POST action=''>
<td><input type='submit' value='Process Payments'></td>
<input type='hidden' name='command' value='processpayments'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Query User Payments'></td>
<input type='hidden' name='command' value='queryuserpayments'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Query Batch Payments'></td>
<input type='hidden' name='command' value='querybatchpayments'>
</form>

<form method=POST action=''>
<td><input type='submit' value='Query No Pay Users'></td></tr>
<input type='hidden' name='command' value='querynopayusers'>
</form>

</table>

<?php
MenuEnd();

?>