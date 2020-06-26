<?php

include_once("includes/inc.global.php");

echo "<h3 align=center>Verify CheckMatch Report</h3>";

///////////////////////
// CheckMatch Amount //
///////////////////////
function TokensPlayedCM($user_id, $batch_id, $level)
{
	return GetDB("SELECT sum(amount) FROM ce_ledger WHERE batch_id=".$batch_id." AND ledger_type=4 AND user_id IN (SELECT user_id FROM ce_levels WHERE ancestor_id='".$user_id."' AND level=".$level.")");
}

$batch_id = "2";
$user_id = "3";

$amount[1] = TokensPlayedCM($user_id, $batch_id, 1);
$amount[2] = TokensPlayedCM($user_id, $batch_id, 2);
$amount[3] = TokensPlayedCM($user_id, $batch_id, 3);
$amount[4] = TokensPlayedCM($user_id, $batch_id, 4);

echo "<table>";
echo "<tr><td align=center><b>Tier</b></td><td align=center><b>Amount</b></td><td align=center><b>Cut</b></td></tr>";
echo "<tr><td align=center>1</td><td align=center>$".$amount[1]."</td><td align=center>$".round($amount[1]*.1, 2)."</td></tr>";
echo "<tr><td align=center>2</td><td align=center>$".$amount[2]."</td><td align=center>$".round($amount[1]*.05, 2)."</td></tr>";
echo "<tr><td align=center>3</td><td align=center>$".$amount[3]."</td><td align=center>$".round($amount[1]*.03, 2)."</td></tr>";
echo "<tr><td align=center>4</td><td align=center>$".$amount[4]."</td><td align=center>$".round($amount[1]*.02, 2)."</td></tr>";
?>