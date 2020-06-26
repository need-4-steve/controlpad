<?php

global $coredomain;
global $authemail;
global $apikey;
$coredomain = "https://nospy.mobi"; //"ce.controlpad.com"; // fastcgi.dev
//$coredomain = "https://nospy.mobi/ceapi";
$authemail = "master@commissions.com";
$apikey = "44cfb3d2a282a1268a2c68619f8adbe1934917239c82ceac49a8d2b58d8ae0";

// 742053e0f5cc38103819ce15aba2c02d5fc65047db2098d4795f187ae0b2b4c5

///////////////////////////////////
// Handle processing curl method //
///////////////////////////////////
function PostURL($url, $headers)
{
	// There was a speed performance issue I discovered with POST data
	// There was overhead of an extra packet sent back and forth //
	// Hence why it's all switched over to header vars //

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	//curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);

	// The quick work around for ssl //
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	// Use the proper fix for production //
	//http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/

	$data = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	//echo $data."<br>";
	curl_close($ch);
	
	return $data;
}

////////////////////////
// Start menu display //
////////////////////////
function MenuStart()
{
	global $coredomain;
	global $authemail;
	global $apikey;

	session_start();
	echo SelectSystem($coredomain, $authemail, $apikey);

	echo "<table border=1>";
	echo "<tr><td valign=top>";

	// Menu Links //
	echo "<table>";
	echo "<tr><td><a href='index.php'>Home</a></td></tr>";
	//echo "<tr><td><a href='auth.php'>Auth</a></td></tr>";
	echo "<tr><td><a href='systemusers.php'>System Users</a></td></tr>";
	echo "<tr><td><a href='systems.php'>Systems</a></td></tr>";
	echo "<tr><td><a href='rankrules.php'>Rank Rules</a></td></tr>";
	echo "<tr><td><a href='commissionrules.php'>Commission Rules</a></td></tr>";
	echo "<tr><td><a href='poolpots.php'>Pool Pots</a></td></tr>";
	echo "<tr><td><a href='poolrules.php'>Pool Rules</a></td></tr>";
	echo "<tr><td><a href='users.php'>Users</a></td></tr>";
	echo "<tr><td><a href='receipts.php'>Receipts</a></td></tr>";
	echo "<tr><td><a href='commissiontools.php'>Commission Tools</a></td></tr>";
	echo "<tr><td><a href='bankaccounts.php'>Bank Accounts</a></td></tr>";
	echo "<tr><td><a href='accountsvalidation.php'>Accounts Validation</a></td></tr>";
	echo "<tr><td><a href='payments.php'>Payments</a></td></tr>";
	echo "</table>";


	echo "</td><td valign=top>";
}

//////////////////////
// End menu display //
//////////////////////
function MenuEnd()
{
	echo "</td></tr></table>";
}

//////////////////////////////
// Display system to select //
//////////////////////////////
function SelectSystem()
{
	global $coredomain;
	global $authemail;
	global $apikey;

	if ($_POST['direction'] == 'selectsystem')
	{
		$_SESSION['systemid'] = $_POST['systemid'];
	}

	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: querysystems";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$jsonrules = PostURL($curlstring, $headers);
	$retarray = json_decode($jsonrules, true);

	$display = "<form method='POST' action=''>";
	$display .= "<input type=hidden name='direction' value='selectsystem'>";
	$display .= "System: <select name='systemid'>"; 
	$display .= "<option value='0'>";
	foreach ($retarray['systems'] as $system)
	{
		if ($system['id'] == $_SESSION['systemid'])
			$display .= "<option selected value='".$system['id']."'>".$system['id']." - ".$system['systemname'];
		else
			$display .= "<option value='".$system['id']."'>".$system['id']." - ".$system['systemname'];
	}
	$display .= "</select>";
	$display .= "<input type='submit' value='select'>";
	$display .= "</form>";

	return $display;
}

//////////////////////////
// Handle qualifty type //
//////////////////////////
function SelectQualifyType($name, $default)
{
	$display .= "<select name='".$name."'>"; 
	$display .= "<option value='0'>";
	
	if ($default == 1)
		$display .= "<option selected value='1'>Personal Sales";
	else
		$display .= "<option value='1'>Personal Sales";

	if ($default == 2)
		$display .= "<option selected value='2'>Group Sales";
	else
		$display .= "<option value='2'>Group Sales";

	if ($default == 3)
		$display .= "<option selected value='3'>Signup Count";
	else
		$display .= "<option value='3'>Signup Count";
	
	$display .= "</select>";

	return $display;
}

//////////////////////////////////
// Lookup text for qualify type //
//////////////////////////////////
function LookupQualifyType($typeid)
{
	if ($typeid == 1)
		return "Person Sales";
	if ($typeid == 2)
		return "Group Sales";
	if ($typeid == 3)
		return "Signup Count";	
}

////////////////////////////////////
// Select the pot you want to use //
////////////////////////////////////
function SelectPoolPot()
{
	global $coredomain;
	global $authemail;
	global $apikey;

	if ($_POST['direction'] == 'selectpot')
	{
		$_SESSION['poolpotid'] = $_POST['poolpotid'];
	}

	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: querypoolpots";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$_SESSION['systemid'];
	$jsonrules = PostURL($curlstring, $headers);
	$retarray = json_decode($jsonrules, true);

	$display = "<form method='POST' action=''>";
	$display .= "<input type=hidden name='direction' value='selectpot'>";
	$display .= "Pool Pot: <select name='poolpotid'>"; 
	$display .= "<option value='0'>";
	foreach ($retarray['poolpots'] as $pots)
	{
		if ($pots['id'] == $_SESSION['systemid'])
			$display .= "<option selected value='".$pots['id']."'>".$pots['id']." - ".LookupQualifyType($pots['qualifytype'])." - $".$pots['amount'];
		else
			$display .= "<option value='".$pots['id']."'>".$pots['id']." - ".LookupQualifyType($pots['qualifytype'])." - $".$pots['amount'];
	}
	$display .= "</select>";
	$display .= "<input type='submit' value='select'>";
	$display .= "</form>";

	return $display;
}

///////////////////////////////////
// Select commission system type //
///////////////////////////////////
function SelectCommType($name, $default)
{
	$display .= "<select name='".$name."'>"; 
	$display .= "<option value='0'>";
	
	if ($default == 1)
		$display .= "<option selected value='1'>Hybrid Uni";
	else
		$display .= "<option value='1'>Hybrid Uni";

	if ($default == 2)
		$display .= "<option selected value='2'>Breakaway";
	else
		$display .= "<option value='2'>Breakaway";

	if ($default == 3)
		$display .= "<option selected value='3'>Binary";
	else
		$display .= "<option value='3'>Binary";
	
	$display .= "</select>";

	return $display;
}

///////////////////////////////////
// Select commission system type //
///////////////////////////////////
function SelectPayoutType($name, $default)
{
	$display .= "<select name='".$name."'>"; 
	$display .= "<option value='0'>";

	if ($default == 1)
		$display .= "<option selected value='1'>Monthly";
	else
		$display .= "<option value='1'>Monthly";

	if ($default == 2)
		$display .= "<option selected value='2'>Weekly";
	else
		$display .= "<option value='2'>Weekly";

	if ($default == 3)
		$display .= "<option selected value='3'>Daily";
	else
		$display .= "<option value='3'>Daily";

	if ($default == 4)
		$display .= "<option selected value='4'>External";
	else
		$display .= "<option value='4'>External";
	
	$display .= "</select>";

	return $display;
}

/////////////////////////////
// Select a numeric number //
/////////////////////////////
function SelectNumber($name, $default, $start, $end)
{
	$display .= "<select name='".$name."'>"; 
	$display .= "<option value='0'>";

	for ($index=$start; $index <= $end; $index++)
	{
		if ($default == $index)
			$display .= "<option selected value='".$index."'>".$index;
		else
			$display .= "<option value='".$index."'>".$index;
	}

	$display .= "</select>";

	return $display;
}

///////////////////////////////////
// Select commission system type //
///////////////////////////////////
function SelectBankAccountType($name, $default)
{
	$display .= "<select name='".$name."'>"; 
	$display .= "<option value='0'>";

	if ($default == 1)
		$display .= "<option selected value='1'>Checking";
	else
		$display .= "<option value='1'>Checking";

	if ($default == 2)
		$display .= "<option selected value='2'>Savings";
	else
		$display .= "<option value='2'>Savings";
	
	$display .= "</select>";

	return $display;
}

/////////////////////
// Select the user //
/////////////////////
function SelectUser($name, $default)
{
	global $coredomain;
	global $authemail;
	global $apikey;

	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: queryusers";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$_SESSION['systemid'];
	$jsonrules = PostURL($curlstring, $headers);
	$retarray = json_decode($jsonrules, true);

	$display .= "<select name='".$name."'>"; 
	$display .= "<option value='0'>";
	foreach ($retarray['users'] as $user)
	{
		if ($user['id'] == $default)
			$display .= "<option selected value='".$user['id']."'>".$user['id'];
		else
			$display .= "<option value='".$user['id']."'>".$user['id'];
	}
	$display .= "</select>";

	return $display;
}

///////////////////
// Select a rank //
///////////////////
function SelectRank($name, $default)
{
	global $coredomain;
	global $authemail;
	global $apikey;

	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: queryrankrules";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$_SESSION['systemid'];
	$jsonrules = PostURL($curlstring, $headers);
	$retarray = json_decode($jsonrules, true);

	$display .= "<select name='".$name."'>"; 
	$display .= "<option value='0'>";
	foreach ($retarray['rankrules'] as $rank)
	{
		if ($rank['id'] == $default)
			$display .= "<option selected value='".$rank['id']."'>".$rank['id'];
		else
			$display .= "<option value='".$rank['id']."'>".$rank['id'];
	}
	$display .= "</select>";

	return $display;
}

////////////////////
// Select a batch //
////////////////////
function SelectBatch($name, $default)
{
	global $coredomain;
	global $authemail;
	global $apikey;

	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: querybatches";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$_SESSION['systemid'];
	$headers[] = "authorized: false";
	$jsonrules = PostURL($curlstring, $headers);
	$retarray = json_decode($jsonrules, true);

	$display .= "<select name='".$name."'>"; 
	$display .= "<option value='0'>";
	foreach ($retarray['batches'] as $batch)
	{
		if ($rank['id'] == $default)
			$display .= "<option selected value='".$batch['id']."'>".$batch['id']." - From:".$batch['startdate']." To:".$batch['enddate'];
		else
			$display .= "<option value='".$batch['id']."'>".$batch['id']." - From:".$batch['startdate']." To:".$batch['enddate'];
	}
	$display .= "</select>";

	return $display;
}
?>
