<?php

include_once("includes/inc.global.php");

echo "<h3 align=center>Cross Reference Report</h3>";

//////////////////////////
// Get a database value //
//////////////////////////
function GetDB($query)
{
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());	
	$line = pg_fetch_array($result, null, PGSQL_ASSOC);
	
	return array_values($line)[0];
}

function GetUserGenVals($user_id, $generation)
{
	// Get commission engine already calculated commissions //
	$retval['ce_comm'] = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE system_id=1 AND user_id='".$user_id."' AND generation=".$generation);

	if (!empty($retval['ce_comm']))
	{
		echo "ce_comm: user_id=".$user_id.", generation=".$generation."<br>";
	}

	// Build the temp tables //
	$query = "SELECT * INTO TEMP tmp_breakdown".$generation." FROM ce_breakdown WHERE system_id=1 AND user_id='".$user_id."' AND generation=".$generation;
	//echo "<font color=green>".$query."</font><br><br>";
	pg_query($query);
	//$query = "SELECT * INTO TEMP tmp_receipts".$generation." FROM receipts WHERE system_id='1' AND user_id IN (SELECT user_id FROM users WHERE system_id='1' AND parent_id IN (SELECT user_id FROM users WHERE parent_id='".$user_id."'))";
	
	if ($generation == 1)
	{
		$query = "SELECT * INTO TEMP tmp_receipts".$generation." FROM ce_receipts WHERE system_id='1' AND user_id IN (SELECT user_id FROM ce_users WHERE system_id='1' AND parent_id='".$user_id."')";
	}
	else
	{
		$query = "SELECT * INTO TEMP tmp_receipts".$generation." FROM ce_receipts WHERE system_id='1' AND user_id IN ";
		for ($index=1; $index <= $generation-1; $index++)
			$query .= "(SELECT user_id FROM ce_users WHERE system_id='1' AND parent_id IN ";

		$query .= "(SELECT user_id FROM ce_users WHERE system_id=1 AND parent_id='".$user_id."')";
		for ($index=1; $index <= $generation-1; $index++)
			$query .= ")";
	}

	//echo "<font color=red>".$query."</font><br><br>";

	pg_query($query);
	$query = "SELECT * INTO TEMP missing_receipts".$generation." FROM ce_receipts WHERE receipt_id IN (SELECT receipt_id FROM tmp_breakdown".$generation." WHERE receipt_id NOT IN (SELECT receipt_id FROM tmp_receipts".$generation."))";
	//echo "<font color=blue>".$query."</font><br><br>";
	pg_query($query);

	// Finally grab compression values //
	$retval['compression'] = GetDB("SELECT SUM(amount) FROM missing_receipts".$generation);

	// Grab the receipts totals //
	$retval['x_bought'] = GetDB("SELECT SUM(amount) FROM tmp_receipts".$generation);
	$retval['pretotal'] = number_format($retval['x_bought']+$retval['compression'], 4, '.', '');

	$retval['percent'] = GetDB("SELECT distinct percent FROM ce_commrules WHERE start_gen=".$generation);

	$query = "SELECT SUM(amount) FROM ce_receipts WHERE system_id='1' AND receipt_id IN (SELECT receipt_id FROM ce_breakdown WHERE system_id='1' AND user_id='".$user_id."' AND generation=".$generation.")";
	//echo "<font color=green>".$query."</font><br><br>";
	$retval['ce_receipts'] = GetDB("SELECT SUM(amount) FROM ce_receipts WHERE system_id='1' AND receipt_id IN (SELECT receipt_id FROM ce_breakdown WHERE system_id='1' AND user_id='".$user_id."' AND generation=".$generation.")");
	$retval['receipt_diff'] = $retval['ce_receipts']-$retval['pretotal'];
	$retval['x_comm'] = number_format(($retval['x_bought']+$retval['compression']) * $retval['percent'] * 0.01, 4, '.', '');

	$retval['comm_diff'] = $retval['ce_comm']-$retval['x_comm'];

	// More temp table and totals for debugging //
	$query = "CREATE TEMP TABLE tmp_union".$generation." (system_id INT4, receipt_id BIGINT, user_id TEXT, amount DECIMAL(37,4))";
	pg_query($query);
	$query = "INSERT INTO tmp_union".$generation." SELECT system_id, receipt_id, user_id, amount FROM missing_receipts".$generation."";
	pg_query($query);
	$query = "INSERT INTO tmp_union".$generation." SELECT system_id, receipt_id, user_id, amount FROM tmp_receipts".$generation."";
	pg_query($query);
	$query = "DELETE FROM tmp_union".$generation." WHERE receipt_id IN (SELECT receipt_id FROM tmp_breakdown".$generation.")";
	pg_query($query);

	$retval['br_count'] = GetDB("SELECT count(*) FROM tmp_breakdown".$generation);
	$retval['rc_count'] = GetDB("SELECT count(*) FROM tmp_receipts".$generation);
	$retval['mi_count'] = GetDB("SELECT count(*) FROM missing_receipts".$generation);
	$retval['un_count'] = GetDB("SELECT count(*) FROM tmp_union".$generation);

	$retval['br_sum'] = GetDB("SELECT SUM(amount) FROM tmp_breakdown".$generation);
	$retval['rc_sum'] = GetDB("SELECT SUM(amount) FROM tmp_receipts".$generation);
	$retval['mi_sum'] = GetDB("SELECT SUM(amount) FROM missing_receipts".$generation);
	$retval['un_sum'] = GetDB("SELECT SUM(amount) FROM tmp_union".$generation);

	$retval['generation'] = $generation;

	return $retval;
}

function GetAllUserVals($user_id)
{
	$user['id'] = $user_id;
	$user['ce_bought_total'] = GetDB("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND ledger_type='3' AND user_id='".$user_id."'");
	$user['ce_played_total'] = GetDB("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND ledger_type='4' AND user_id='".$user_id."'");
	$user['ce_cm_bought'] = GetDB("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND ledger_type='5' AND user_id='".$user_id."'");
	$user['ce_cm_played'] = GetDB("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND ledger_type='6' AND user_id='".$user_id."'");

	$user['maxgen'] = GetDB("SELECT distinct generation FROM ce_breakdown WHERE user_id='".$user_id."' AND system_id='1' ORDER BY generation DESC");

	for ($index=1; $index <= $user['maxgen']; $index++)
	{
		$user[$index] = GetUserGenVals($user_id, $index);
	}

	//CleanAllReceipts(57, 3);

	//$user['x_comm'][2] = number_format(($user['x_bought'][2]+$user['compression'][2]) * $percent[2] * 0.01, 4);
	//$user['x_comm'][3] = number_format(($user['x_bought'][3]+$user['compression'][3]) * $percent[3] * 0.01, 4);
	//$user['x_comm'][4] = number_format(($user['x_bought'][4]+$user['compression'][4]) * $percent[4] * 0.01, 4);

	//$user['t_play_gen1'] = GetDB("SELECT SUM(amount) FROM breakdown WHERE system_id!=1 AND user_id='".$user_id."' AND generation=1");
	//$user['t_play_gen2'] = GetDB("SELECT SUM(amount) FROM breakdown WHERE system_id!=1 AND user_id='".$user_id."' AND generation=2");
	//$user['t_play_gen3'] = GetDB("SELECT SUM(amount) FROM breakdown WHERE system_id!=1 AND user_id='".$user_id."' AND generation=3");
	//$user['t_play_gen4'] = GetDB("SELECT SUM(amount) FROM breakdown WHERE system_id!=1 AND user_id='".$user_id."' AND generation=4");

	// How to calc Check Match //

	return $user;
}

//////////////////////////////////
// Handle the row values easily //
//////////////////////////////////
function RowValues($heading, $user, $key, $color)
{
	echo "<tr><td align=right><b>".$heading."</b></td>";
	for ($index=1; $index <= $user['maxgen']; $index++)
	{
		echo "<td align=right><font color=".$color.">".$user[$index][$key]."</font></td>";
	}
	echo "</tr>";
}

// Specific users to spot check //

/*
$percent[1] = 10;
$percent[2] = 5;
$percent[3] = 3;
$percent[4] = 2;
$percent[5] = 1;
$percent[6] = 1;
$percent[7] = 1;
$percent[8] = 0.5;
$percent[9] = 0.5;
*/

// Shane = 57 //
//echo "<pre>";
$user = GetAllUserVals(3);
//print_r($result);
//echo "</pre>";

// Cross reference table //
echo "<table border=1>";
echo "<tr><td></td><td colspan=".$user['maxgen']." align=center><b>Tokens Purchased</b></td></tr>";
//echo "<tr><td align=right><b>Generation</b></td><td align=center><b>1</b></td><td align=center><b>2</b></td><td align=center><b>3</b></td><td align=center><b>4</b></td></tr>";
//echo "<tr><td align=right><b>Percent</b></td><td align=center>".$percent[1]."%</td><td align=center>".$percent[2]."%</td><td align=center>".$percent[3]."%</td><td align=center>".$percent[4]."%</td></tr>";

RowValues("Generation", $user, "generation", "black");
RowValues("Percent", $user, "percent", "black");
RowValues("Receipts (XRef)", $user, "x_bought", "blue");
RowValues("Compression (XRef)", $user, "compression", "blue");
RowValues("Pretotal (XRef)", $user, "pretotal", "blue");
RowValues("Receipts (CE)", $user, "ce_receipts", "green");
RowValues("Receipts Diff", $user, "receipt_diff", "red");

//echo "<tr><td><a href='receiptdiff.php'>Receipt Diff</a></td></tr>";

echo "<tr><td colspan=5>&nbsp;</td></tr>";

RowValues("Commissions (XRef)", $user, "x_comm", "orange");
RowValues("Commissions (CE)", $user, "ce_comm", "green");
RowValues("Commission Diff", $user, "comm_diff", "red");

echo "<tr><td colspan=5>&nbsp;</td></tr>";

RowValues("Breakdown (count)", $user, "br_count", "green");
RowValues("Receipt (count)", $user, "rc_count", "blue");
RowValues("Missing Receipt (count)", $user, "mi_count", "blue");
RowValues("Union (count)", $user, "un_count", "red");

RowValues("Breakdown (sum)", $user, "br_sum", "green");
RowValues("Receipt (sum)", $user, "rc_sum", "blue");
RowValues("Missing Receipt (sum)", $user, "mi_sum", "blue");
RowValues("Union (sum)", $user, "un_sum", "red");

echo "</table>";

// Union Receipts //
echo "<h3>Union Receipts</h3>";
echo "<table border=1>";
echo "<tr><td align=center><b>system_id</b></td><td align=center><b>receipt_id</b></td><td align=center><b>user_id</b></td><td><b>amount</b></td></tr>";
$query = "SELECT system_id, receipt_id, user_id, amount FROM tmp_union3";
//$query = "SELECT count(*), receipt_id FROM tmp_union3 GROUP BY receipt_id ORDER BY count(*) DESC";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());	
while ($line = pg_fetch_array($result))
{
	//$user_id = GetDB("SELECT user_id FROM receipts WHERE receipt_id=".$line["receipt_id"]);


	//echo "<tr><td align=center>".$line["count"]."</td><td align=center>".$line["receipt_id"]."</td><td><a href='upline.php?user_id=".$user_id."'>".$user_id."</a></td></tr>";

	//$amount += $line['amount'];
    echo "<tr><td align=center>".$line["system_id"]."</td><td>".$line["receipt_id"]."</td><td><a href='upline.php?user_id=".$line["user_id"]."'>".$line["user_id"]."</a></td><td>".$line["amount"]."</td></tr>";
} 
//echo "<tr><td>Amount:</td><td>".$amount."</td></tr>";

echo "</table>";

// Count how many generation level 1 //
// Count how many generation level 2 //
// Count how many generation level 3 //
// Count how many generation level 4 //

// Multiply by the commrule //

// Compare to breakdown values. Do they match? //

// Tokens Played //
// Tokens Spent //
// Check Match Played //
// Check Match Spent //

// 10% generation=1 //
// 5% generation=2 //

// 3% generation=3 //
// 2% generation=4 //
?>