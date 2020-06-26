<?php

include_once("includes/inc.global.php");

///////////////////////////////
// Handle the user breakdown //
///////////////////////////////
function UserBreakdown($user_id)
{
	$system_id = 1;

	// Users Breakdown //
	$breakdown_user1 = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE user_id='".$user_id."' AND generation='1' AND system_id=".$system_id);
	$breakdown_user2 = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE user_id='".$user_id."' AND generation='2' AND system_id=".$system_id);
	$breakdown_user3 = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE user_id='".$user_id."' AND generation='3' AND system_id=".$system_id);
	$breakdown_user4 = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE user_id='".$user_id."' AND generation='4' AND system_id=".$system_id);

	$count_user1 = GetDB("SELECT count(*) FROM ce_breakdown WHERE user_id='".$user_id."' AND generation='1' AND system_id=".$system_id);
	$count_user2 = GetDB("SELECT count(*) FROM ce_breakdown WHERE user_id='".$user_id."' AND generation='2' AND system_id=".$system_id);
	$count_user3 = GetDB("SELECT count(*) FROM ce_breakdown WHERE user_id='".$user_id."' AND generation='3' AND system_id=".$system_id);
	$count_user4 = GetDB("SELECT count(*) FROM ce_breakdown WHERE user_id='".$user_id."' AND generation='4' AND system_id=".$system_id);

	echo "user_id = ".$user_id."<br>";
	echo "<table border=1>";
	echo "<tr><td>Generation</td><td align=center>User(".$user_id.") Count</td><td align=center>User(".$user_id.") Dollar</td></tr>";
	echo "<tr><td align=center>1</td><td align=center>".$count_user1."</td><td align=right>$".$breakdown_user1."</td></tr>";
	echo "<tr><td align=center>2</td><td align=center>".$count_user2."</td><td align=right>$".$breakdown_user2."</td></tr>";
	echo "<tr><td align=center>3</td><td align=center>".$count_user3."</td><td align=right>$".$breakdown_user3."</td></tr>";
	echo "<tr><td align=center>4</td><td align=center>".$count_user4."</td><td align=right>$".$breakdown_user4."</td></tr>";
	echo "</table>";
	echo "<hr>";
}

echo "<h3 align=center>Compression Audit</h3>";

$system_id = 1;
$user_id = "3";

// Show percentage breakdown //
$receipt_total = GetDB("SELECT SUM(amount) FROM ce_receipts WHERE system_id=".$system_id);
$payout_total = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE system_id=".$system_id);
$receipt_count = GetDB("SELECT count(*) FROM ce_receipts WHERE system_id=".$system_id);
$breakdown_count = GetDB("SELECT count(*) FROM ce_breakdown WHERE system_id=".$system_id);

//echo "user_id = ".$user_id."<br>";
echo "receipt_total = $".$receipt_total."<br>";
echo "payout_total = $".$payout_total."<br>";
echo "receipt_count = ".$receipt_count."<br>";
echo "breakdown_count = ".$breakdown_count."<br>";
echo "<hr>";

$breakdown_gen1 = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE generation='1' AND system_id=".$system_id);
$breakdown_gen2 = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE generation='2' AND system_id=".$system_id);
$breakdown_gen3 = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE generation='3' AND system_id=".$system_id);
$breakdown_gen4 = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE generation='4' AND system_id=".$system_id);

$count_gen1 = GetDB("SELECT count(*) FROM ce_breakdown WHERE generation='1' AND system_id=".$system_id);
$count_gen2 = GetDB("SELECT count(*) FROM ce_breakdown WHERE generation='2' AND system_id=".$system_id);
$count_gen3 = GetDB("SELECT count(*) FROM ce_breakdown WHERE generation='3' AND system_id=".$system_id);
$count_gen4 = GetDB("SELECT count(*) FROM ce_breakdown WHERE generation='4' AND system_id=".$system_id);

echo "<b>Overall</b><br>";
echo "<table border=1>";
echo "<tr><td>Generation</td><td align=center>Dollars</td><td align=center>Percent Of Receipts</td><td align=center>Percent Of Payout</td><td align=center>Breakdown Count</td></tr>";
echo "<tr><td align=center>1</td><td align=right>$".$breakdown_gen1."</td><td align=center>".round($breakdown_gen1/$receipt_total*100, 2)."%</td><td align=center>".round($breakdown_gen1/$payout_total*100, 2)."%</td><td align=center>".$count_gen1."</td></tr>";
echo "<tr><td align=center>2</td><td align=right>$".$breakdown_gen2."</td><td align=center>".round($breakdown_gen2/$receipt_total*100, 2)."%</td><td align=center>".round($breakdown_gen2/$payout_total*100, 2)."%</td><td align=center>".$count_gen2."</td></tr>";
echo "<tr><td align=center>3</td><td align=right>$".$breakdown_gen3."</td><td align=center>".round($breakdown_gen3/$receipt_total*100, 2)."%</td><td align=center>".round($breakdown_gen3/$payout_total*100, 2)."%</td><td align=center>".$count_gen3."</td></tr>";
echo "<tr><td align=center>4</td><td align=right>$".$breakdown_gen4."</td><td align=center>".round($breakdown_gen4/$receipt_total*100, 2)."%</td><td align=center>".round($breakdown_gen4/$payout_total*100, 2)."%</td><td align=center>".$count_gen4."</td></tr>";
echo "</table>";
echo "<hr>";

// Top 10 //
echo "<b>Top 10 earners</b><br>";
echo "<table border=1>";
echo "<tr><td>User ID</td><td align=center>Amount</td><td align=center>Percent of Payout</td></tr>";
$query = "SELECT user_id, SUM(amount) FROM ce_breakdown GROUP BY user_id ORDER BY SUM(amount) DESC LIMIT 10";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());
while ($line = pg_fetch_array($result, null, PGSQL_ASSOC))
{
	echo "<tr><td align=center>".$line['user_id']."</td><td align=right>$".$line['sum']."</td><td align=right>".round($line['sum']/$payout_total*100, 2)."%</td></tr>";
}
echo "</table>";
echo "<hr>";

UserBreakdown(3);
UserBreakdown(596228);
UserBreakdown(1);
UserBreakdown(569049);
UserBreakdown(421103);

/*
echo "<table border=1>";
echo "<tr><td>ID</td><td>Receipt ID</td><td>User ID</td><td>Amount</td><td>Pay Count</td></tr>";
$query = "SELECT id, receipt_id, user_id, usertype, amount FROM ce_receipts WHERE system_id='".$system_id."' ORDER BY user_id::INT4";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());
while ($line = pg_fetch_array($result, null, PGSQL_ASSOC))
{
	echo "<tr><td>".$line['id']."</td><td>".$line['receipt_id']."</td><td>".$line['user_id']."</td><td>".$line['amount']."</td>";

	$break_count = GetDB("SELECT count(*) FROM ce_breakdown WHERE system_id='".$system_id."' AND receipt_id=".$line['receipt_id']);
	echo "<td align=center>".$break_count."</td>";

	$query2 = "SELECT user_id, generation FROM ce_breakdown WHERE receipt_id=".$line['receipt_id'];
	$result2 = pg_query($query2) or die('Query failed: ' . pg_last_error());
	while ($line2 = pg_fetch_array($result2, null, PGSQL_ASSOC))
	{
		echo "<td>user_id=".$line2['user_id'].", gen=".$line2['generation']."</td>";
	}
	echo "</tr>";
}
echo "</table>";

*/
?>