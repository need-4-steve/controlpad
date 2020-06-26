<?php
//#!/usr/bin/php

$dbconn = pg_connect("host=localhost dbname=chalk-live user=root password=53eqRpYtQPP94apf")
    or die('Could not connect: ' . pg_last_error());

$query = "SELECT id, batch_id, user_id, amount, bonus_date FROM ce_bonus WHERE system_id='2' ORDER BY user_id, bonus_date, amount, batch_id";

$result = pg_query($query) or die('Query failed: ' . pg_last_error());	

$prev_userid = "";
$prev_amount = "";
$prev_date = "";

while ($row = pg_fetch_row($result))
{
	//echo "id: $row[0]  batch_id: $row[1]";
	//echo "<br />\n";

	if (($prev_userid == $row[2]) && 
		($prev_amount == $row[3]) &&
		($prev_date == $row[4]))
	{
		echo "DELETE FROM ce_bonus WHERE id=".$row[0].";<br>\n";
	}

	$prev_userid = $row[2];
	$prev_amount = $row[3];
	$prev_date = $row[4];
}

/////////////////////////////////////
// Handle cleaning ce_ledger table //
/////////////////////////////////////
$query = "SELECT id, batch_id, user_id, ledger_type, amount, event_date FROM ce_ledger WHERE system_id='2' ORDER BY user_id, batch_id, ledger_type, event_date, amount";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());	

$prev_userid = "";
$prev_ledgertype = "";
$prev_amount = "";
$prev_date = "";

while ($row = pg_fetch_row($result))
{
	//echo "id: $row[0]  batch_id: $row[1]";
	//echo "<br />\n";

	if (($prev_userid == $row[2]) && 
		($prev_ledgertype == $row[3]) && 
		($prev_amount == $row[4]) &&
		($prev_date == $row[5]))
	{
		echo "DELETE FROM ce_ledger WHERE id=".$row[0].";<br>\n";
	}

	$prev_userid = $row[2];
	$prev_ledgertype = $row[3];
	$prev_amount = $row[4];
	$prev_date = $row[5];
}

?>
