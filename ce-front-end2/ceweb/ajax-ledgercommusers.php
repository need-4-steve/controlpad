<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.display.php';

SystemSelectedCheck();
//header('Content-Type: application/json');
// Make sure a userid is passed in //
if (empty($_GET['parentid']))
{
	echo '{"errors":{"status":"400","source":"API","title":"json::downline error","detail":"The parentid is empty"}}';
	exit;
}
else if (empty($_GET['systemid']))
{
	echo '{"errors":{"status":"400","source":"API","title":"json::downline error","detail":"The systemid is empty"}}';
	exit;
}
else if (empty($_GET['batchid']))
{
	echo '{"errors":{"status":"400","source":"API","title":"json::downline error","detail":"The batchid is empty"}}';
	exit;
}
else if (empty($_GET['generation']))
{
	//echo '{"errors":{"status":"400","source":"API","title":"json::downline error","detail":"The generation is empty"}}';
	//exit;
	$_GET['generation'] = "0";
}

$pagvals = "";

// Handle getting the downline level 1 json //
$_POST['parentid'] = $_GET['parentid'];
$_POST['systemid'] = $_GET['systemid'];
$_POST['batchid'] = $_GET['batchid'];
$_POST['generation'] = $_GET['generation'];
$fields[] = "parentid";
$fields[] = "systemid";
$fields[] = "batchid";
$fields[] = "generation";
$json = BuildAndPOST(AFFILIATE, "mybreakdownusers", $fields, $pagvals);
//$text = json_encode($json);

echo json_encode($json);
exit;
?>