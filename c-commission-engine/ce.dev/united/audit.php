<?php

include_once("includes/inc.global.php");

function GetVal($query)
{
	//$starttime = time();

	$result = pg_query($query) or die('Query failed: ' . pg_last_error());	
	$line = pg_fetch_array($result, null, PGSQL_ASSOC);
	
	//$endtime = time();

	//echo "time=".($endtime-$starttime)."<br>";
	//echo $query."<br>";
	//echo "-----------------------------------------<br>";

	return array_values($line)[0];
}

function ShowAllRank($rank, $usertierall, $payoutamount, $batch_id)
{
	echo "<tr><td><b>Zone #".$rank.":</b></td><td align=right>$";
	$tierpay = GetVal("SELECT SUM(amount) FROM ce_ledger WHERE batch_id=".$batch_id." AND user_id IN (SELECT user_id FROM ce_users WHERE system_id=1 AND user_id IN (SELECT user_id FROM ce_ranks WHERE system_id=1 AND rank='".$rank."' AND batch_id=".$batch_id."))");
	echo $tierpay;
	echo "</td><td align=center>";
	echo round($tierpay/$payoutamount*100, 0);
	echo "%</td>";
	$usertier = GetVal("SELECT count(*) FROM ce_users WHERE system_id=1 AND usertype='1' AND user_id IN (SELECT user_id FROM ce_ranks WHERE system_id=1 AND rank='".$rank."' AND batch_id=".$batch_id.")");
	echo "<td align=center>(".$usertier." / ".$usertierall.") ";
	echo round($usertier/$usertierall*100, 0);
	echo "%</td></tr>";

	$tokensbuy = GetVal("SELECT SUM(amount) FROM ce_ledger WHERE ledger_type=3 AND batch_id=".$batch_id." AND user_id IN (SELECT user_id FROM ce_users WHERE system_id=1 AND user_id IN (SELECT user_id FROM ce_ranks WHERE system_id=1 AND batch_id=".$batch_id." AND rank='".$rank."'))");
	echo "<tr><td align=right>Purchased Comm:</td><td align=right>$".$tokensbuy."</td></tr>";
	$tokensplay = GetVal("SELECT SUM(amount) FROM ce_ledger WHERE ledger_type=4 AND batch_id=".$batch_id." AND user_id IN (SELECT user_id FROM ce_users WHERE system_id=1 AND user_id IN (SELECT user_id FROM ce_ranks WHERE system_id=1 AND batch_id=".$batch_id." AND rank='".$rank."'))");
	echo "<tr><td align=right>Played Comm:</td><td align=right>$".$tokensplay."</td></tr>";
	$tokenscmpay = GetVal("SELECT SUM(amount) FROM ce_ledger WHERE ledger_type=5 AND batch_id=".$batch_id." AND user_id IN (SELECT user_id FROM ce_users WHERE system_id=1 AND user_id IN (SELECT user_id FROM ce_ranks WHERE system_id=1 AND batch_id=".$batch_id." AND rank='".$rank."'))");
	echo "<tr><td align=right>CM Purchased:</td><td align=right>$".$tokenscmpay."</td></tr>";
	$tokenscmplay = GetVal("SELECT SUM(amount) FROM ce_ledger WHERE ledger_type=6 AND batch_id=".$batch_id." AND user_id IN (SELECT user_id FROM ce_users WHERE system_id=1 AND user_id IN (SELECT user_id FROM ce_ranks WHERE system_id=1 AND batch_id=".$batch_id." AND rank='".$rank."'))");
	echo "<tr><td align=right>CM Played:</td><td align=right>$".$tokenscmplay."</td></tr>";

	echo "<tr><td colspan=4><hr width='100%'></td></tr>";
}

$batch_id = 13;
$startdate = "2017-9-1";
$enddate = "2017-9-30";

echo "<b>batch_id = ".$batch_id."<br>";

// Display the receipt amount //
echo "<table>";
echo "<tr><td align=center><b>Type</b></td><td align=center><b>Amount</b></td><td align=center><b>Percent</b></td></tr>";

echo "<tr><td align=right><b>Receipt Amounts:</b></td><td align=right><b>$";
$receiptamount = GetVal("SELECT sum(amount) FROM ce_receipts WHERE system_id=1 AND purchase_date >= '".$startdate."' AND purchase_date <= '".$enddate."'");
echo $receiptamount;
echo "</b></td></tr>";

echo "<tr><td align=right><b>Tokens Played:</b></td><td align=right><b>$";
$tokensplayed = GetVal("SELECT sum(amount) FROM ce_receipts WHERE system_id!=1 AND purchase_date >= '".$startdate."' AND purchase_date <= '".$enddate."'");
echo $tokensplayed;
echo "</b></td></tr>";

// Commissions Purchased //
echo "<tr><td align=right>Commissions Purchased:</td><td align=right>$";
$commpurchased = GetVal("SELECT sum(amount) FROM ce_ledger WHERE system_id=1 AND batch_id=".$batch_id." AND ledger_type=3");
echo $commpurchased;
echo "</td><td align=right>".round($commpurchased*100/$receiptamount, 2)."%</td></tr>";

// Commissions Played //
echo "<tr><td align=right>Commissions Played:</td><td align=right>$";
$commplayed = GetVal("SELECT sum(amount) FROM ce_ledger WHERE system_id=1 AND batch_id=".$batch_id." AND ledger_type=4");
echo $commplayed;
echo "</td><td align=right>".round($commplayed*100/$receiptamount, 2)."%</td></tr>";

// CM Purchased //
echo "<tr><td align=right>CM Purchased:</td><td align=right>$";
$cmpurchased = GetVal("SELECT sum(amount) FROM ce_ledger WHERE system_id=1 AND batch_id=".$batch_id." AND ledger_type=5");
echo $cmpurchased;
echo "</td><td align=right>".round($cmpurchased*100/$receiptamount, 2)."%</td></tr>";

// CM Played //
echo "<tr><td align=right>CM Played:</td><td align=right>$";
$cmplayed = GetVal("SELECT sum(amount) FROM ce_ledger WHERE system_id=1 AND batch_id=".$batch_id." AND ledger_type=6");
echo $cmplayed;
echo "</td><td align=right>".round($cmplayed*100/$receiptamount, 2)."%</td></tr>";

//payout amount //
echo "<tr><td align=right><b>Total Payout Amounts:</b></td><td align=right><b>$";
$totalpayout = GetVal("SELECT sum(amount) FROM ce_ledger WHERE system_id=1 AND batch_id=".$batch_id);
echo $totalpayout;
echo "</b></td><td align=right><b>".round($totalpayout*100/$receiptamount, 2)."%</b></td></tr>";

echo "</table>";

//echo "<hr width='100%'>";
echo "<table width='65%'>";

echo "<tr><td align=center><b>Zone</b></td><td align=center><b>Payout</b></td><td align=center><b>Payout Percent</b></td><td align=center><b>Users / Percent</b></td></tr>";
$usertierall = GetVal("SELECT count(*) FROM ce_users WHERE system_id=1 AND usertype='1'");

ShowAllRank(1, $usertierall, $totalpayout, $batch_id);
ShowAllRank(2, $usertierall, $totalpayout, $batch_id);
ShowAllRank(3, $usertierall, $totalpayout, $batch_id);
ShowAllRank(4, $usertierall, $totalpayout, $batch_id);

echo "<hr width='100%'>";

echo "<table>";
echo "<tr><td align=right><b>Tokens Purchased Commissions:</b></td><td align=right>$";
$tokenspurchased = GetVal("SELECT sum(amount) FROM ce_ledger WHERE system_id=1 AND batch_id=".$batch_id." AND ledger_type=3");
echo $tokenspurchased;
echo "<tr><td align=right><b>Tokens Played Commissions:</b></td><td align=right>$";
$tokensplayed = GetVal("SELECT sum(amount) FROM ce_ledger WHERE system_id=1 AND batch_id=".$batch_id." AND ledger_type=4");
echo $tokensplayed;
echo "<tr><td align=right><b>Check Match Purchased:</b></td><td align=right>$";
$cmpurchased = GetVal("SELECT sum(amount) FROM ce_ledger WHERE system_id=1 AND batch_id=".$batch_id." AND ledger_type=5");
echo $cmpurchased;
echo "<tr><td align=right><b>Check Match Played:</b></td><td align=right>$";
$cmused = GetVal("SELECT sum(amount) FROM ce_ledger WHERE system_id=1 AND batch_id=".$batch_id." AND ledger_type=6");
echo $cmused;
echo "</table>";
/*
echo "<hr>";
echo "<h3>Top 10 earners</h3>";
echo "<table border=1>";
echo "<tr><td>User ID</td><td>Amount</td></tr>";
$query = "SELECT user_id, SUM(amount) FROM ce_ledger WHERE batch_id=".$batch_id." group BY user_id ORDER BY SUM(amount) DESC limit 10";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());	
while ($line = pg_fetch_array($result))
{
	echo "<tr><td>".$line['user_id']."</td><td>".$line['sum']."</td></tr>";
}

echo "</table>";
*/
?>