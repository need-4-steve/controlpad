<?php

include_once("includes/inc.global.php");

echo "<h3 align=center>Receipt Diff Report</h3>";

function GetDB($query)
{
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());	
	$line = pg_fetch_array($result, null, PGSQL_ASSOC);
	
	return array_values($line)[0];
}

$query = "SELECT * INTO TEMP tmp_receipts3 FROM ce_receipts WHERE system_id='1' AND user_id IN (SELECT user_id FROM users WHERE system_id='1' AND parent_id IN (SELECT user_id FROM users WHERE system_id='1' AND parent_id IN (SELECT user_id FROM users WHERE system_id=1 AND parent_id='57')))";
pg_query($query);
$query = "SELECT * INTO TEMP tmp_receipts_ce FROM ce_receipts WHERE system_id='1' AND receipt_id IN (SELECT receipt_id FROM ce_breakdown WHERE system_id='1' AND user_id='57' AND generation=3)";
pg_query($query);

$query = "SELECT count(*) FROM tmp_receipts3 WHERE receipt_id NOT IN (SELECT receipt_id FROM tmp_receipts_ce)";
$diff1 = GetDB($query);

$query = "SELECT count(*) FROM tmp_receipts_ce WHERE receipt_id NOT IN (SELECT receipt_id FROM tmp_receipts3)";
$diff2 = GetDB($query);

echo "diff1 = ".$diff1."<br>";
echo "diff2 = ".$diff2."<br>";

$total1 = GetDB("SELECT count(*) FROM tmp_receipts3");
$total2 = GetDB("SELECT count(*) FROM tmp_receipts_ce");

echo "total1 = ".$total1."<br>";
echo "total2 = ".$total2."<br>";
?>