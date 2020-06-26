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

function ShowAllRank($rank, $usertierall, $payoutamount)
{
	echo "<tr><td><b>Zone #".$rank.":</b></td><td align=right>$";
	$tierpay = GetVal("SELECT SUM(amount) FROM ce_ledger WHERE user_id IN (SELECT user_id FROM users WHERE system_id=1 AND user_id IN (SELECT user_id FROM ranks WHERE system_id=1 AND rank='".$rank."'))");
	echo $tierpay;
	echo "</td><td align=center>";
	echo round($tierpay/$payoutamount*100, 0);
	echo "%</td>";
	$usertier = GetVal("SELECT count(*) FROM users WHERE system_id=1 AND usertype='1' AND user_id IN (SELECT user_id FROM ranks WHERE system_id=1 AND rank='".$rank."')");
	echo "<td align=center>(".$usertier." / ".$usertierall.") ";
	echo round($usertier/$usertierall*100, 0);
	echo "%</td></tr>";

	$tokensbuy = GetVal("SELECT SUM(amount) FROM ledger WHERE ledger_type=3 AND user_id IN (SELECT user_id FROM users WHERE system_id=1 AND user_id IN (SELECT user_id FROM ranks WHERE system_id=1 AND rank='".$rank."'))");
	echo "<tr><td align=right>Purchased Comm:</td><td align=right>$".$tokensbuy."</td></tr>";
	$tokensplay = GetVal("SELECT SUM(amount) FROM ledger WHERE ledger_type=4 AND user_id IN (SELECT user_id FROM users WHERE system_id=1 AND user_id IN (SELECT user_id FROM ranks WHERE system_id=1 AND rank='".$rank."'))");
	echo "<tr><td align=right>Played Comm:</td><td align=right>$".$tokensplay."</td></tr>";
	$tokenscmpay = GetVal("SELECT SUM(amount) FROM ledger WHERE ledger_type=5 AND user_id IN (SELECT user_id FROM users WHERE system_id=1 AND user_id IN (SELECT user_id FROM ranks WHERE system_id=1 AND rank='".$rank."'))");
	echo "<tr><td align=right>CM Purchased:</td><td align=right>$".$tokenscmpay."</td></tr>";
	$tokenscmplay = GetVal("SELECT SUM(amount) FROM ledger WHERE ledger_type=6 AND user_id IN (SELECT user_id FROM users WHERE system_id=1 AND user_id IN (SELECT user_id FROM ranks WHERE system_id=1 AND rank='".$rank."'))");
	echo "<tr><td align=right>CM Played:</td><td align=right>$".$tokenscmplay."</td></tr>";

	echo "<tr><td colspan=4><hr width='100%'></td></tr>";
}

// Display the receipt amount //
echo "<table>";
echo "<tr><td align=right><b>Receipt Amounts:</b></td><td align=right>$";
$receiptamount = GetVal("SELECT sum(amount) FROM receipts WHERE system_id=1");
echo $receiptamount;
echo "</td></tr>";

//payout amount //
echo "<tr><td align=right><b>Payout Amounts:</b></td><td align=right>$";
$payoutamount = GetVal("SELECT sum(amount) FROM ledger WHERE system_id=1");
echo $payoutamount;
echo "</td></tr>";

// Percentages //
echo "<tr><td align=right><b>Percentage:</b></td><td>";
$payoutamount = GetVal("SELECT sum(amount) FROM ledger WHERE system_id=1");
echo round($payoutamount/$receiptamount*100, 2);
echo "%</td></tr>";

echo "</table>";

//echo "<hr width='100%'>";
echo "<table width='45%'>";

echo "<tr><td align=center><b>Zone</b></td><td align=center><b>Payout</b></td><td align=center><b>Payout Percent</b></td><td align=center><b>Users / Percent</b></td></tr>";
$usertierall = GetVal("SELECT count(*) FROM users WHERE system_id=1 AND usertype='1'");

ShowAllRank(1, $usertierall, $payoutamount);
ShowAllRank(2, $usertierall, $payoutamount);
ShowAllRank(3, $usertierall, $payoutamount);
ShowAllRank(4, $usertierall, $payoutamount);

echo "<hr width='100%'>";

echo "<table>";
echo "<tr><td align=right><b>Tokens Purchased Commissions:</b></td><td align=right>$";
$tokenspurchased = GetVal("SELECT sum(amount) FROM ledger WHERE system_id=1 AND ledger_type=3");
echo $tokenspurchased;
echo "<tr><td align=right><b>Tokens Played Commissions:</b></td><td align=right>$";
$tokensplayed = GetVal("SELECT sum(amount) FROM ledger WHERE system_id=1 AND ledger_type=4");
echo $tokensplayed;
echo "<tr><td align=right><b>Check Match Purchased:</b></td><td align=right>$";
$cmpurchased = GetVal("SELECT sum(amount) FROM ledger WHERE system_id=1 AND ledger_type=5");
echo $cmpurchased;
echo "<tr><td align=right><b>Check Match Played:</b></td><td align=right>$";
$cmused = GetVal("SELECT sum(amount) FROM ledger WHERE system_id=1 AND ledger_type=6");
echo $cmused;
