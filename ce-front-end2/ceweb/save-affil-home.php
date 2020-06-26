 <?php


include 'includes/inc.ce-comm.php';
include 'includes/inc.affiliate.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

SystemSelectedCheck();

file_put_contents('/tmp/test.txt', file_get_contents('php://input'));

$json = file_get_contents('php://input');

// Save to the ce_settings table //
$fields[] = "varname";
$fields[] = "value";
$_POST['varname'] = "affiliatehome";
$_POST['value'] = $json;
$retvaljson = BuildAndPOST(CLIENT, "settingsset", $fields, $pagvals);

//file_put_contents('/tmp/retvaljson.txt', $retvaljson);

//return $retvaljson;
return '{"success":true}';

?>