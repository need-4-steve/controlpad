<?php

include_once("includes/inc.global.php");

/////////////////////////
// Calc generation Sum //
/////////////////////////
function GenSum($user_id, $generation)
{
	$query = "SELECT sum(amount) FROM ce_breakdown WHERE system_id=1 AND generation=".$generation." AND user_id='".$user_id."'";
	$sum = GetDB($query);

	$query = "SELECT count(*) FROM ce_breakdown WHERE system_id=1 AND generation=".$generation." AND user_id='".$user_id."'";
	$count = GetDB($query);

	echo "<tr><td align=center>".$generation."</td><td align=right>$".$sum."</td><td align=center>".$count."</td></tr>";

	return $sum;
}

//////////////////////////////
// Handle checkmatch values //
//////////////////////////////
function CheckMatch($user_id, $generation, $ledger_type)
{
	$query = "SELECT sum(amount) FROM ce_ledger WHERE ledger_type=".$ledger_type." AND user_id='".$user_id."' AND generation='".$generation."'";
	$sum = GetDB($query);

	$query = "SELECT count(*) FROM ce_ledger WHERE ledger_type=".$ledger_type." AND user_id='".$user_id."' AND generation='".$generation."'";
	$count = GetDB($query);

	echo "<tr><td align=center>".$generation."</td><td align=right>$".$sum."</td><td align=center>".$count."</td></tr>";
	return $sum;
}

////////////////////
// All CheckMatch //
////////////////////
function AllCheckMatch($user_id, $generation, $ledger_type)
{
	$query = "SELECT system_id, batch_id, ref_id, user_id, amount, from_system_id, from_user_id, generation FROM ce_ledger WHERE ledger_type=".$ledger_type." AND user_id='".$user_id."' AND generation='".$generation."' ORDER BY from_user_id::INT4";
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());	
	while ($line = pg_fetch_array($result))
	{
		echo "<tr>";
		echo "<td>".$line['system_id']."</td>";
		echo "<td>".$line['batch_id']."</td>";
		echo "<td>".$line['ref_id']."</td>";
		echo "<td>".$line['user_id']."</td>";
		echo "<td>".$line['amount']."</td>";
		echo "<td>".$line['from_system_id']."</td>";
		echo "<td>".$line['from_user_id']."</td>";
		echo "<td>".$line['generation']."</td>";
		echo "</tr>";
	}
}

$user_id = 348016;

echo "<h3>Roger Commission Tokens Purchase</h3>";
echo "<table border=1>";
echo "<tr><td align=center><b>Generation</b></td><td align=center><b>SUM</b></td><td align=center><b>Count</b></td></tr>";

$total += GenSum($user_id, 1);
$total += GenSum($user_id, 2);
$total += GenSum($user_id, 3);
$total += GenSum($user_id, 4);
$total += GenSum($user_id, 5);
$total += GenSum($user_id, 6);
$total += GenSum($user_id, 7);
$total += GenSum($user_id, 8);
$total += GenSum($user_id, 9);

echo "<tr><td align=right><b>Total</b></td><td align=right>$".$total."</td></tr>";
echo "</table>";

echo "<hr>";

echo "<h3>Roger Checkmatch Tokens Purchased</h3>";
echo "<table border=1>";
echo "<tr><td align=center><b>Generation</b></td><td align=center><b>SUM</b></td><td align=center><b>Count</b></td></tr>";
$total = 0;
$total += CheckMatch($user_id, 1, 5);
$total += CheckMatch($user_id, 2, 5);
$total += CheckMatch($user_id, 3, 5);
$total += CheckMatch($user_id, 4, 5);
echo "<tr><td align=right><b>Total</b></td><td align=right>$".$total."</td></tr>";
echo "</table>";

echo "<hr>";

echo "<h3>Roger Checkmatch Tokens Played</h3>";
echo "<table border=1>";
echo "<tr><td align=center><b>Generation</b></td><td align=center><b>SUM</b></td><td align=center><b>Count</b></td></tr>";
$total = 0;
$total += CheckMatch($user_id, 1, 6);
$total += CheckMatch($user_id, 2, 6);
$total += CheckMatch($user_id, 3, 6);
$total += CheckMatch($user_id, 4, 6);
echo "<tr><td align=right><b>Total</b></td><td align=right>$".$total."</td></tr>";
echo "</table>";

echo "<hr>";

echo "<h3 align=center>Urlacker Played</h3>";

$user_id = 348016;

$comm_purchased = GetDB("SELECT SUM(amount) FROM ce_ledger WHERE ledger_type='3' AND user_id='".$user_id."'");

$comm_played = GetDB("SELECT SUM(amount) FROM ce_ledger WHERE ledger_type='4' AND user_id='".$user_id."'");

echo "comm_purchased = $".$comm_purchased."<br>";
echo "comm played = $".$comm_played."<br>";

$breakdown_total_1 = GetDB("SELECT sum(amount) FROM ce_breakdown WHERE user_id='".$user_id."' and system_id=1");
$breakdown_total = GetDB("SELECT sum(amount) FROM ce_breakdown WHERE user_id='".$user_id."' and system_id!=1");

echo "breakdown_total_1 = $".$breakdown_total_1."<br>";
echo "breakdown_total = $".$breakdown_total."<br>";


//"SELECT system_id FROM ce_users WHERE system_id!=1 AND user_id='".$user_id."' AND parent_id=0"

?>