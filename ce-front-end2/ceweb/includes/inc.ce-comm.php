<?php

///////////////////////////////////////////////////////////////
// WE NEED TO FINISH THE LAST OF THE API AND GET RID OF THIS //
///////////////////////////////////////////////////////////////
include '/etc/ceapi/inc.settings.php';

session_start();

global $g_coredomain;
global $g_simdomain;
global $g_masterauthemail;
global $g_masterapikey;
global $g_simapikey;

//$g_coredomain = "http://comm.dev:8080"; //"ce.controlpad.com";
//$g_simdomain = "http://comm.dev:8081"; // Point at the simulations server to run simulations //

// Only allow one or the other //
//$g_masterauthemail = "master@commissions.com";
//$g_masterapikey = "6257ceb47e093e1aee1a1e1a75adeb34bffd8288d4a4fcc4fc355fef62f94";
$g_simapikey = "NONE-YET"; // Do we even need this? //

// Handle POST submission
define('MASTER', 1);
define('CLIENT', 2);
define('AFFILIATE', 3);

// Define display output
define('SUCCESS_NOTHING', 1);
define('ADD_RECORD', 2);
define('EDIT_RECORD', 3);
define('DISABLE_RECORD', 4);
define('ENABLE_RECORD', 5);
define('DELETE_RECORD', 6);
define('AUTHORIZED_RECORD', 7);
define('UNAUTHORIZED_RECORD', 8);
define('BULK_AUTHORIZED', 9);

///////////////////////////
// Easily debug with Pre //
///////////////////////////
function Pre($array)
{
	echo "<pre>";
	print_r($array);
	echo "</pre>";
}

////////////////////////////////
// Build and POST data to url //
////////////////////////////////
function BuildAndPOST($acctype, $command, $fields, $pagvals)
{
	$headers = BuildHeader($acctype, $command, $fields, $pagvals, $_POST);
	$json = PostURL($headers, "false");

	return $json;
}

////////////////////////////////////
// Build the packet header easily //
////////////////////////////////////
function BuildHeader($acctype, $command, $fields, $pagvals, $values)
{
	global $g_masterauthemail;
	global $g_masterapikey;
	global $g_command;
	$g_command = $command;

	$headers = [];

	// Handle account type //
	if ($acctype == MASTER)
	{
		$headers[] = "authemail: ".$g_masterauthemail;
		$headers[] = "apikey: ".$g_masterapikey;
	}
	else if ((!empty($_SESSION['cp_token'])) && ($acctype == AFFILIATE))
	{
		$headers[] = "authorization: ".$_SESSION['cp_token'];
		if (empty($_SESSION['systemid']))
			$headers[] = "systemid: 1"; // Just force to system 1 for now if not found //
		else
			$headers[] = "systemid: ".$_SESSION['systemid'];

	}	
	else if ($acctype == CLIENT)
	{
		$headers[] = "authemail: ".$_SESSION['authemail'];
		$headers[] = "authpass: ".$_SESSION['authpass'];
	}
	else if ($acctype == AFFILIATE)
	{
		// Normal //
		if ((empty($_SESSION['authemail'])) && (empty($_SESSION['authpass'])))
		{
			$headers[] = "affiliateemail: ".$_SESSION['useremail'];
			$headers[] = "affiliatepass: ".$_SESSION['userpass'];
		}
		else // Override Account //
		{
			$headers[] = "affiliateemail: ".$_SESSION['useremail'];
			$headers[] = "affiliatepass: ".$_SESSION['userpass'];
			$headers[] = "authemail: ".$_SESSION['authemail'];
			$headers[] = "authpass: ".$_SESSION['authpass'];
		}
	}

	// Handle command //
	$headers[] = "command: ".$command;

	// Handle systemid if not empty //
	if (!empty($_SESSION['systemid']))
		$headers[] = "systemid: ".$_SESSION['systemid'];

	// Handle POST fields //
	foreach ($fields as $field)
	{
		$headers[] = $field.": ".$values[$field];
	} 

	// Handle pagination //
	$pagfields[] = "orderby";
	$pagfields[] = "orderdir";
	$pagfields[] = "offset";
	$pagfields[] = "limit";

	// Set defaults if empty //
	if (empty($pagvals["orderby"]))
		$pagvals["orderby"] = "id";
	if (empty($pagvals["orderdir"]))
		$pagvals["orderdir"] = "asc";
	if (empty($pagvals["offset"]))
		$pagvals["offset"] = "0";
	if (empty($pagvals["limit"]))
		$pagvals["limit"] = "10";

	foreach ($pagvals as $key => $value)
	{
    	$sort = $sort.$key."=".$value."&";
	} 
	$sort = rtrim($sort, "&");
	$sort = str_replace("::", "=", $sort);
	$sort = str_replace("@", "&", $sort);

	// Quick fix //
	$sort = str_replace("personalvolume", "mywholesalesales", $sort);
	$sort = str_replace("teamvolume", "teamwholesalesales", $sort);
	$sort = str_replace("enterprisevolume", "groupwholesalesales", $sort);

	$headers[] = "sort: ".$sort;

	// Handle search fields //
	if (!empty($_POST['search']))
	{
		$search = $_POST['search'];
	}
	else
	{
		foreach ($_POST as $key => $value)
		{
			if ((strstr($key, "search-") != FALSE) && (!empty($value)))
			{
				$varname = str_replace("search-", "", $key);
				$search .= $varname."=".$value."&";
			}
		}
		$search = rtrim($search, "&");
	}
	$headers[] = "search: ".$search; // Search as querystring style //

	return $headers;
}

///////////////////////
// Copy Array Values //
///////////////////////
function CopyArrayValues($fields, $from)
{
	foreach ($fields as $field)
	{
    	$retval[$field] = $from[$field];
	} 

	return $retval;
}

///////////////////////////////////
// Handle processing curl method //
///////////////////////////////////
function PostURL($headers, $forcesim = "false")
{ 
	global $g_coredomain;
	global $g_simdomain;
	global $g_command;

	// Handle sim override for CHALKCOUTURE //
	if ($forcesim == "true") // For password update //
		$domain = $g_simdomain;
	else if (($_SESSION['userloggedin'] == "true") || ($_SESSION['override'] == "true")) // User Accounts or Override of User Account //
		$domain = $g_simdomain;
	else if (($g_command == "copyseedsim") || ($g_command == "runsim"))
		$domain = $g_coredomain;
	else if ($_SESSION['simulations'] == "true") // Admin can see sim database on their side //
		$domain = $g_simdomain;
	else
		$domain = $g_coredomain;

	// There was a speed performance issue I discovered with POST data
	// There was overhead of an extra packet sent back and forth //
	// Hence why it's all switched over to header vars //

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $domain);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	//curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20000);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20000);

	// The quick work around for ssl //
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	// Use the proper fix for production //
	//http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/

	$data = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	//echo $data."<br>";
	curl_close($ch);

	//echo "<pre>";
	//$resulterere = json_decode($data, JSON_UNESCAPED_SLASHES);
	//print_r($resulterere);
	//echo "</pre>";

	//Pre($data);

	//return json_decode($data, true);
	return json_decode($data, JSON_UNESCAPED_SLASHES);

	// Return json //
	//return $data;
}

////////////////////////////////////////////////////
// Build the select page for all Query page types //
////////////////////////////////////////////////////
function BuildQueryPage($acctype, $recordtype, $idcolumn, $pagvals, $fields = "")
{
	if (empty($fields))
		$fields = array(); // Fix stupid nginx error //

	////////////////////
	// Handle Disable //
	////////////////////
	if ($_GET['direction'] == 'disable')
	{
		if ($recordtype == "user")
			$fields[] = "userid";
		else
			$fields[] = "id"; //$idcolumn;
		$json = BuildAndPOST($acctype, "disable".$recordtype, $fields, $pagvals);
		if (HandleResponse($json, DISABLE_RECORD) == false)
		{
			$values = CopyArrayValues($fields, $_POST);
		}
	}

	///////////////////
	// Handle Enable //
	///////////////////
	if ($_GET['direction'] == 'enable')
	{
		if ($recordtype == "user")
			$fields[] = "userid";
		else
			$fields[] = "id"; //$idcolumn;
		$json = BuildAndPOST($acctype, "enable".$recordtype, $fields, $pagvals);
		if (HandleResponse($json, ENABLE_RECORD) == false)
		{
			$values = CopyArrayValues($fields, $_POST);
		}
	}

	/////////////////////////////////////////////////////////////
	// Always run a query on load of page for most recent view //
	/////////////////////////////////////////////////////////////
	$json = BuildAndPOST(CLIENT, "query".$recordtype, $fields, $pagvals);
	HandleResponse($json, SUCCESS_NOTHING);

	return $json; // Return the json records //
}

////////////////////////////////////////////////
// Build an edit page for all edit type pages //
////////////////////////////////////////////////
function BuildEditPage($acctype, $recordtype, $fields)
{
	///////////////////////////////
	// Handle update of a system //
	///////////////////////////////
	if ($_POST['direction'] == 'edit')
	{
		if ($recordtype == "receipt")
			$command = "edit".$recordtype."wid"; // Allow editing with id for receipts //
		else
			$command = "edit".$recordtype;

		$json = BuildAndPOST($acctype, $command, $fields, $pagvals);
		if (HandleResponse($json, EDIT_RECORD) == false)
		{
			$values = CopyArrayValues($fields, $_POST);
			return $values;
		}
	}

	///////////////////////////////
	// Handle adding of a system //
	///////////////////////////////
	if ($_POST['direction'] == 'add')
	{
		$json = BuildAndPOST($acctype, "add".$recordtype, $fields, $pagvals);
		if (HandleResponse($json, ADD_RECORD) == false)
		{
			$values = CopyArrayValues($fields, $_POST);
			$values['id'] = $_POST['id'];
			return $values;
		}
	}

	//////////////////////////////////////
	// Prepare to edit passed in system //
	//////////////////////////////////////
	if (($_GET['edit'] == "true") || ($_POST['edit'] == "true"))
	{
		// Grab the record from the database to edit //
		$json = BuildAndPOST($acctype, "get".$recordtype, $fields, $pagvals);
		HandleResponse($json, SUCCESS_NOTHING);
		$values = CopyArrayValues($fields, $json[$recordtype][0]);
		$values['id'] = $json[$recordtype][0]['id']; // Add cause not part of fields //

		return $values;
	}
}

///////////////////////////
// Handle showing errors //
///////////////////////////
function ShowError($errortext)
{
	echo '<div class="col-md-12 col-sm-12 col-xs-12"><div class="x_panel">';
	echo "<font color=red><b>".$errortext."</b></font>";
	echo "</div></div>";
	return true;
}

//////////////////////////////
// Handle an error response //
//////////////////////////////
function HandleResponse($json, $display)
{
	if ($json['errors'])
	{
		/*
		echo '<div class="col-md-12 col-sm-12 col-xs-12"><div class="x_panel">';

		$code = $json['errors'];
		//$text = "Status: ".$code['status'].", source: ".$code['source'].", title: ".$code['title'].", detail: ".$code['detail'];
		$text = $code['detail'];

		echo "<font color=red><b>".$text."</b></font>";
		echo "</div></div>";
		*/

		$code = $json['errors'];
		$text = $code['detail'];
		ShowError($text);

		return false;
	}
	else if (($json["success"]["status"] == 200) && ($display == SUCCESS_NOTHING))
	{
		// Show nothing. Needed for selection type pages //
		return true;
	}
	else if ($json["success"]["status"] == 200)
	{
		if ($display == ADD_RECORD)
			$text = "The record has been added";
		if ($display == EDIT_RECORD)
			$text = "The record has been updated";
		if ($display == DISABLE_RECORD)
			$text = "The record has been disabled";
		if ($display == ENABLE_RECORD)
			$text = "The record has been enabled";
		if ($display == DELETE_RECORD)
			$text = "The record has been deleted";
		if ($display == AUTHORIZED_RECORD)
			$text = "The record has been authorized";
		if ($display == UNAUTHORIZED_RECORD)
			$text = "The record has been unauthorized";
		if ($display == BULK_AUTHORIZED)
			$text = "All the records have been bulk authorized";
		if ($display == PASSWORD_RESET)
			$text = "The password has been updated";

		ShowBannerMessage($text, "green", "white");
		return true;
	}
	else
	{	
		$text = "There is reason to believe the API server is down";
		ShowBannerMessage($text, "red", "white");
		return false;
	}

	return false;
}

/////////////////////////////
// Show a standard message //
/////////////////////////////
function ShowMessage($text, $textcolor)
{
	echo '<div class="col-md-12 col-sm-12 col-xs-12"><div class="x_panel">';
	echo "<font color='".$textcolor."'><b>".$text."</b></font>";
	echo "</div></div>";
}

/////////////////////////////////////
// Show a banner message on screen //
/////////////////////////////////////
function ShowBannerMessage($text, $bgcolor, $textcolor)
{
	echo '<div class="col-md-12 col-sm-12 col-xs-12"><div class="x_panel">';
	echo "<table width=100%><tr bgcolor='".$bgcolor."'><td><font color='".$textcolor."'><b><h2>&nbsp;&nbsp; ".$text." &nbsp;&nbsp;</h2></b></font></td></tr></table>";
	echo "</div></div>";
}

/////////////////////////////
// Check for single system //
/////////////////////////////
function SingleSystemCheck()
{
	// Grab the default systemid //
	$fields[] = "varname";
    $_POST['varname'] = "defaultsystem";
	$json = BuildAndPOST(CLIENT, "settingsget", $fields, $pagvals);
	$defaultsystemid = $json['settings'][0]['value'];

	// Grab all systems //
	$json = BuildAndPOST(CLIENT, "querysystem", "", $pagvals);
	foreach ($json['system'] as $record)
	{
		if ($defaultsystemid == $record['id'])
		{
			$_SESSION['systemid'] = $defaultsystemid;
			$_SESSION['systemname'] = $record['systemname'];
			$_SESSION['commtype'] = $record['commtype']; // We change form display if binary is selected //
		}
	}
}

//////////////////////////////////////////
// Make sure a system has been selected //
//////////////////////////////////////////
function SystemSelectedCheck()
{
	if (empty($_SESSION['systemid']))
	{
		$text = "<b><u><a href='systems-select.php'>A System need to be selected</a></u></b>";
		ShowMessage($text, "blue");
		include 'includes/inc.footer.php';
		exit();
	}
}

////////////////////////
// Start menu display //
////////////////////////
function MenuStart()
{
	global $g_coredomain;
	global $authemail;
	global $apikey;

	session_start();
	echo SelectSystem($g_coredomain, $authemail, $apikey);

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

/////////////////////////
// Get the system name //
/////////////////////////
function GetSystem($systemid)
{
	$fields[] = "id";
	$json = BuildAndPOST("getsystem", $systemid, $fields, $pagvals);
	return $json['systems'][0];
}

//////////////////////////////
// Display system to select //
//////////////////////////////
function SelectSystem()
{
	global $g_coredomain;
	global $authemail;
	global $apikey;

	if ($_POST['direction'] == 'selectsystem')
	{
		$_SESSION['systemid'] = $_POST['systemid'];
	}

	$curlstring = $g_coredomain;
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

////////////////////////////////////
// Select the pot you want to use //
////////////////////////////////////
function SelectPoolPot()
{
	global $g_coredomain;
	global $authemail;
	global $apikey;

	if ($_POST['direction'] == 'selectpot')
	{
		$_SESSION['poolpotid'] = $_POST['poolpotid'];
	}

	$curlstring = $g_coredomain;
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
			$display .= "<option selected value='".$pots['id']."'>".$pots['id']." - ".$pots['qualifytype']." - $".$pots['amount'];
		else
			$display .= "<option value='".$pots['id']."'>".$pots['id']." - ".$pots['qualifytype']." - $".$pots['amount'];
	}
	$display .= "</select>";
	$display .= "<input type='submit' value='select'>";
	$display .= "</form>";

	return $display;
}

/*
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
*/

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
	//echo "account default = ".$default."<br>";

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
	global $g_coredomain;
	global $authemail;
	global $apikey;

	$curlstring = $g_coredomain;
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
	global $g_coredomain;
	global $authemail;
	global $apikey;

	// Allow caching and correct selected view //
	// Skip for now to get ready for demo //

	$curlstring = $g_coredomain;
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

//////////////////////////
// Handle default batch //
//////////////////////////
function DefaultBatch()
{
	global $g_BatchJson;

	$batchpagvals['limit'] = 1000;
	$batchpagvals['offset'] = 0;
	$batchpagvals['orderby'] = "id";
	$batchpagvals['orderdir'] = "desc";
	$batchpagvals['qstring'] = "limit=1000&orderby=id";
	$fields = "";

	if ($_SESSION['sysuserloggedin'] == "true")
		$g_BatchJson = BuildAndPOST(CLIENT, "querybatches", $fields, $batchpagvals);
	else
		$g_BatchJson = BuildAndPOST(AFFILIATE, "querybatches", $fields, $batchpagvals);
	
	$defaultbatchid = $g_BatchJson['batches'][0]['id'];
	
	return $defaultbatchid;
}

///////////////////////////////////////
// Get the dates from the batch json //
///////////////////////////////////////
function GetBatchDates($batchid, $batchesjson)
{
	foreach ($batchesjson['batches'] as $batch)
	{
		if ($batchid == $batch['id'])
		{
			return $batch;
		}
	}
}

// Check to see if we use the jwt URL //
function GetJwtURL()
{
	///////////////
    // loginsite //
    ///////////////
    $fields[] = "varname";
    $_POST['varname'] = "loginsite";
    $retvaljson = BuildAndPOST(MASTER, "settingsget", $fields, $pagvals);  
//    Pre($retvaljson);
    if (($retvaljson['errors'][status] == "400") && ($retvaljson['errors']['detail'] == "There are no records"))
    {
        return "";
    }
    else
    {
    	return $retvaljson['settings'][0]['value'];
    }	
}

function GetJwtURLLogout()
{
	///////////////
    // loginsite //
    ///////////////
    $fields[] = "varname";
    $_POST['varname'] = "logoutsite";
    $retvaljson = BuildAndPOST(MASTER, "settingsget", $fields, $pagvals);  
//    Pre($retvaljson);
    if (($retvaljson['errors'][status] == "400") && ($retvaljson['errors']['detail'] == "There are no records"))
    {
        return "";
    }
    else
    {
    	return $retvaljson['settings'][0]['value'];
    }	
}
?>
