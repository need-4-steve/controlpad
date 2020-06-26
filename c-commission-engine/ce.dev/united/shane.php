<?php

///////////////////
// Shanes report //
///////////////////


// Only complete credit card transactions //
// cc_vault_id //

// #1 - Affiliates with 1 player //

// #2 - Affiliates invited an affiliate or a player //

// #3 - Affiliates invited 5 or more affiliates //


// Percentage payout total on each zone //


// Show comparison of tokens purchased (receipts) VS commissions paid (ledger) //

include_once("includes/inc.global.php");

echo "<h3 align=center>Shane Report</h3>";

//////////////////////////
// Get a database value //
//////////////////////////
function GetDB($query)
{
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());	
	$line = pg_fetch_array($result, null, PGSQL_ASSOC);
	
	return array_values($line)[0];
}

function UserGenStats($generation, $user_id)
{
	// Generation, count, dollar /
	$breakdown_count = GetDB("SELECT count(*) FROM ce_breakdown WHERE user_id='".$user_id."' AND generation=".$generation);
	$breakdown_sum = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE user_id='".$user_id."' AND generation=".$generation);

	echo "<tr><td>".$generation."</td><td align=center>".$breakdown_count."</td><td align=right>$".$breakdown_sum."</td></tr>";
}

function UserStats($user_id)
{
	echo "user_id=".$user_id."<br>";
	echo "<table border=1>";
	echo "<tr><td>Generation</td><td>Count</td><td>SUM(amount)</td></tr>";
	UserGenStats(1, $user_id);
	UserGenStats(2, $user_id);
	UserGenStats(3, $user_id);
	UserGenStats(4, $user_id);
	UserGenStats(5, $user_id);
	UserGenStats(6, $user_id);
	UserGenStats(7, $user_id);
	UserGenStats(8, $user_id);
	UserGenStats(9, $user_id);
	echo "</table>";
	echo "<br>";
}

// Possible fraud purchases //
echo "<h1>Top 10 $70 purchases</h1>";
echo "<table border=1>";
echo "<tr><td>User ID</td><td>Count</td></tr>";
$query = "SELECT user_id, count(*) FROM ce_receipts WHERE amount='70' AND system_id='1' GRoUP BY user_id ORDER by count(*) DESC LIMIT 10";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());	
while ($line = pg_fetch_array($result))
{
	echo "<tr><td align=right>".$line['user_id']."</td><td align=center>".$line['count']."</td></tr>";
}
echo "</table>";

echo "<hr>";

// Top 5 payout //
UserStats(3);
UserStats(596228);
UserStats(1);
UserStats(569049);
UserStats(421103);

// Drill down for roger //
echo "<hr>";
echo "<h3>Roger Drill down</h3>";
echo "<table border=1>";
echo "<tr><td>User ID</td><td>Receipt ID</td><td>Amount</td><td>Generatoon</td><td>Percent</td>";
echo "<td></td><td>Receipt User ID</td><td>Receipt Amount</td>";

echo "</tr>";
$query = "SELECT user_id, receipt_id, amount, generation, percent FROM ce_breakdown WHERE user_id='140589'";
$result2 = pg_query($query) or die('Query failed: ' . pg_last_error());	
while ($line2 = pg_fetch_array($result2))
{
	echo "<tr><td align=center>".$line2['user_id']."</td><td align=center>".$line2['receipt_id']."</td><td align=right>$".$line2['amount']."</td><td align=center>".$line2['generation']."</td><td align=center>".$line2['percent']."</td>";

	echo "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";

	// More in depth receipts //
	$query = "SELECT user_id FROM ce_receipts WHERE receipt_id=".$line2['receipt_id'];
	$receipt_user_id = GetDB($query);
	$query = "SELECT amount FROM ce_receipts WHERE receipt_id=".$line2['receipt_id'];
	$receipt_amount = GetDB($query);
	echo "<td align=center>".$receipt_user_id."</td><td align=right>$".$receipt_amount."</td>";
	echo "</tr>";
}
echo "</table>";

// SELECT parent_id, count(*) FROM ce_users WHERE system_id=1 AND user_id IN (SELECT user_id FROM ce_receipts WHERE system_id=1 AND receipt_id IN (SELECT receipt_id FROM ce_breakdown WHERE system_id='1' AND user_id='3' AND generation=3)) GROUP BY parent_id ORDER BY count(*) DESC;
?> 