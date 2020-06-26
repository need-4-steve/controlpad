#!/usr/bin/php
<?php

include "../../tests/includes/inc.ce-comm.php";
include "../../tests/includes/inc.tests.php";
include "../../tests/includes/inc.receipts.php";

global $g_failcount;
global $g_passcount;
$g_failcount = 0;
$g_passcount = 0;

$starttime = time();

// Generate Random Email //
$sysuser_email = "info@chalkcouture.com";
$sysuser_password = "Aasdfasdf1";
$_SESSION['authemail'] = $sysuser_email;
$_SESSION['authpass'] = $sysuser_password;

$system["id"] = 1;

$sort = "limit=1000&offset=0&orderby=wholesaleprice&orderdir=asc&orderby=wholesaleprice";
$receiptquery = QueryReceiptsFull($system["id"], $sort);

$count = 0;
$sql = "";

$prev_userid = "";
$prev_wholesale = "";
$prev_meta = "";
foreach ($receiptquery as $record)
{
	//echo $record['userid']."\n";

	if (($record['userid'] == $prev_userid) && 
		($record['wholesaleprice'] == $prev_wholesale) && 
		($record['metadataonadd'] == $prev_meta) &&
		($record['wholesaleprice'] < 0)) // Negative coupon amount //
	{
		$count++;
		//echo "id: ".$record['id'].", userid: ".$record['userid'].", wholesale: ".$record['wholesaleprice'].", metadataonadd: ".$record['metadataonadd']."\n";
		//echo "id: ".$prev_id.", userid: ".$prev_userid.", wholesale: ".$prev_wholesale.", metadataonadd: ".$prev_meta."\n\n";

		$sql .= "DELETE FROM ce_receipts WHERE id=".$record['id'].";\n";
    }
    $prev_id = $record['id'];
    $prev_userid = $record['userid'];
    $prev_wholesale = $record['wholesaleprice'];
    $prev_meta = $record['metadataonadd'];
}

echo "count = ".$count."\n";
file_put_contents("delete-duplicate-neg-receipts.sql", $sql);

//Pre($receiptquery);


//TestCheck("true", $receiptquery, "QueryReceipts");


?>