<?php

include '../includes/inc.ce-comm.php';
include '../includes/inc.display.php';

SystemSelectedCheck();
//header('Content-Type: application/json');
// Make sure a userid is passed in //
if (empty($_GET['userid']))
{
	echo '{"errors":{"status":"400","source":"API","title":"json::downline error","detail":"The userid is empty"}}';
	exit;
}

// Handle getting the downline level 1 json //
$_POST['userid'] = $_GET['userid'];
$fields[] = "userid";
$json = BuildAndPOST(AFFILIATE, "mydownlinelvlone", $fields);
//$text = json_encode($json);

// Add the id for D3 compatability //
$index = 0;
foreach ($json['users'] as $user) 
{
	$json['users'][$index]['id'] = $user['userid'];
	$index++;
}

// Convert json to text //
$retjson = json_encode($json['users']);

// Initial data //
// If my immediate downline, then append my data first //
if ($_GET['userid'] == $_SESSION['user_id'])
{
	$retjson = ltrim($retjson, "[");
	$retjson = '[{"id":"'.$_GET['userid'].'","userid":"'.$_GET['userid'].'","firstname":"Me","lastname":"Myself","parentid":"0","usertype":"1"},'.$retjson;
}

// Spit out the json needed //
echo $retjson;

//echo '[{"userid":"55","parentid":"0","firstname":"Oswald","lastname":"Cobblepot"},{"userid":"23","parentid":"55","firstname":"Sammy","lastname":"Hagar"},{"id":"33","parentid":"55","firstname":"Melvin"},{"id":"35","parentid":"23","firstname":"Snoopy","lastname":"Dog"},{"id":"656","parentid":"33","firstname":"John","lastname":"Doe"}]';
//echo '[{"id":"55","parentid":"0","firstname":"Oswald","lastname":"Cobblepot"},{"id":"23","parentid":"55","firstname":"Sammy","lastname":"Hagar"}]';
?>