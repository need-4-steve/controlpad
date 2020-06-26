<?php

include_once("includes/inc.global.php");

/////////////////////////
// Calc generation Sum //
/////////////////////////
function GenSum($generation, $batch_id)
{
	$query = "SELECT sum(amount) FROM ce_breakdown WHERE system_id=1 AND batch_id=".$batch_id." AND generation=".$generation;
	$sum = GetDB($query);

	$query = "SELECT count(*) FROM ce_breakdown WHERE system_id=1 AND batch_id=".$batch_id." AND generation=".$generation;
	$count = GetDB($query);

	$query = "SELECT sum(amount) FROM ce_receipts WHERE receipt_id IN (SELECT receipt_id FROM ce_breakdown WHERE system_id=1 AND batch_id=".$batch_id." AND generation=".$generation.")";
	$dollars = GetDB($query);

	$percent = $sum*100/$dollars;

	// Tokens Purchased //
	echo "<tr><td align=center>".$generation."</td><td align=right>$".$sum."</td><td align=center>".$count."</td></tr>";
	//echo "<td align=right>$".$dollars."</td><td align=right>".$percent."%</td>";

	// Tokens Played //

	// Checkmatch Purchased //

	// Checkmatch Played //

	echo "</tr>";

	$retval['sum'] = $sum;
	$retval['count'] = $count;
	$retval['dollars'] = $dollars;

	return $retval;
}

$batch_id = "2";

echo "<h3>Generation Report</h3>";

echo "<table border=1 width='50%'>";
echo "<tr><td align=center><b>Generation</b></td><td align=center><b>Payout</b></td><td align=center><b>Count</b></td></tr>";
//echo "<td align=center><b>Receipts</b></td><td align=center><b>Percent</b></td></tr>";
$retval = GenSum(1, $batch_id);
$total += $retval['sum'];
$count += $retval['count'];
$retval = GenSum(2, $batch_id);
$total += $retval['sum'];
$count += $retval['count'];
$retval = GenSum(3, $batch_id);
$total += $retval['sum'];
$count += $retval['count'];
$retval = GenSum(4, $batch_id);
$total += $retval['sum'];
$count += $retval['count'];
$retval = GenSum(5, $batch_id);
$total += $retval['sum'];
$count += $retval['count'];
$retval = GenSum(6, $batch_id);
$total += $retval['sum'];
$count += $retval['count'];
$retval = GenSum(7, $batch_id);
$total += $retval['sum'];
$count += $retval['count'];
$retval = GenSum(8, $batch_id);
$total += $retval['sum'];
$count += $retval['count'];
$retval = GenSum(9, $batch_id);
$total += $retval['sum'];
$count += $retval['count'];

echo "<tr><td align=right><b>Total</b></td><td align=right>$".$total."</td><td align=center>".$count."</td></tr>";

echo "</table>";


?>