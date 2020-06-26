<?php

include_once("includes/inc.global.php");

function GetVal($query)
{
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());	
	$line = pg_fetch_array($result, null, PGSQL_ASSOC);
	return array_values($line)[0];
}

/*
// Performing SQL query
$totalusers = GetVal("SELECT count(*) FROM ce_users WHERE usertype='1'");
$userrank0 = GetVal("SELECT count(*) FROM ce_users WHERE usertype='1' AND rank='0'");
$userrank1 = GetVal("SELECT count(*) FROM ce_users WHERE usertype='1' AND rank='1'");
$userrank2 = GetVal("SELECT count(*) FROM ce_users WHERE usertype='1' AND rank='2'");
$userrank3 = GetVal("SELECT count(*) FROM ce_users WHERE usertype='1' AND rank='3'");
$userrank4 = GetVal("SELECT count(*) FROM ce_users WHERE usertype='1' AND rank='4'");

echo "<table>";
echo "<tr><td></td><td><b>Count</b></td><td><b>Percent</b></td></tr>";
echo "<tr><td><b>Total Users:</b></td><td align=right>".$totalusers."</td></tr>";
echo "<tr><td align=right><b>Rank 0:</b></td><td align=right>".$userrank0."</td><td align=right>".round($userrank0/$totalusers*100)."%</td></tr>";
echo "<tr><td align=right><b>Rank 1:</b></td><td align=right>".$userrank1."</td><td align=right>".round($userrank1/$totalusers*100)."%</td></tr>";
echo "<tr><td align=right><b>Rank 2:</b></td><td align=right>".$userrank2."</td><td align=right>".round($userrank2/$totalusers*100)."%</td></tr>";
echo "<tr><td align=right><b>Rank 3:</b></td><td align=right>".$userrank3."</td><td align=right>".round($userrank3/$totalusers*100)."%</td></tr>";
echo "<tr><td align=right><b>Rank 4:</b></td><td align=right>".$userrank4."</td><td align=right>".round($userrank4/$totalusers*100)."%</td></tr>";
echo "</table>";
*/
echo "<br>";

/*
$sql = "UPDATE users SET rank='4' WHERE (";
// Find out how many affiliates have personally sponsored 20 affiliates //
$query = "SELECT sponsor_id, count(*) FROM users WHERE usertype='1' GROUP BY sponsor_id ORDER BY count(*) DESC";
$result2 = pg_query($query) or die('Query failed: ' . pg_last_error());
while ($line = pg_fetch_array($result2, null, PGSQL_ASSOC))
{
	$sql .= "user_id='".$line['sponsor_id']."' OR ";

	$index++;
	if ($line['count'] < 20)
	{
		echo "<b>affiliates sponsored over 20</b> = ".$index;
		break; // Exit out once limit is hit //
	}
}

echo "<br>";
$sql = rtrim($sql, "OR ");
$sql .= ")";
echo $sql;
*/

$sql = "SELECT user_id, group_sales FROM ce_userstats_month WHERE (";
$sql2 = "UPDATE users SET rank='4' WHERE (";
// Find out how many affiliates have personally sponsored 20 affiliates //
$query = "SELECT sponsor_id, count(*) FROM ce_users WHERE usertype='1' GROUP BY sponsor_id ORDER BY count(*) DESC";
$result2 = pg_query($query) or die('Query failed: ' . pg_last_error());
while ($line = pg_fetch_array($result2, null, PGSQL_ASSOC))
{
	if ($line['count'] < 20)
	{
		echo "<b>affiliates sponsored over 20</b> = ".$index;
		break; // Exit out once limit is hit //
	}
	$sql .= "user_id='".$line['sponsor_id']."' OR ";
	$sql2 .= "user_id='".$line['sponsor_id']."' OR ";
	$index++;
}

echo "<br>";
$sql2 = rtrim($sql2, "OR ");
$sql2 .= ")";
//echo $sql2;


// List all affilliate information
echo "<br>";
echo "<table>";
echo "<tr><td><b>User ID</td></td><td align=center><b>Group Sales</b></td><td><b>Commissions</b></td><td><b>Rank</b></td><td><b>Level</b></td></tr>";
$sql = rtrim($sql, "OR ");
$sql .= ") ORDER BY group_sales DESC";
//echo $sql;
$result3 = pg_query($sql) or die('Query failed: ' . pg_last_error());
while ($line = pg_fetch_array($result3, null, PGSQL_ASSOC))
{
	echo "<tr><td align=right>".$line['user_id']."</td><td align=right>".$line['group_sales']."</td>";

	$commission = GetVal("SELECT amount FROM ce_commissions WHERE user_id='".$line['user_id']."'");
	echo "<td align=right>".$commission."</td>";

	$rank = GetVal("SELECT rank FROM ce_users WHERE user_id='".$line['user_id']."'");
	echo "<td align=right>".$rank."</td>";

	$level = GetVal("SELECT DISTINCT level FROM ce_levels WHERE ancestor_id='".$line['user_id']."' ORDER BY level DESC");
	echo "<td align=right>".$level."</td>";

	echo "</tr>";
}
echo "</table>";

/*
// Do cross audit on groupsales //
echo "<br><br>";
echo "<table>";
echo "<tr><td><b>UserID</b></td><td><b>GroupSales #1</b></td><td><b>GroupSales #2</b></td><td align=center><b>Affiliate Sales</b></td><td align=center><b>Customer Sales</b></td></tr>";
$query = "SELECT user_id, group_sales, affiliate_sales, customer_sales FROM userstats_month WHERE system_id=1 ORDER BY group_sales DESC LIMIT 5";
$result2 = pg_query($query) or die('Query failed: ' . pg_last_error());
while ($line = pg_fetch_array($result2, null, PGSQL_ASSOC))
{
	//print_r($line);

	$amount = GetVal("SELECT sum(amount) FROM receipts WHERE system_id=1 AND purchase_date>='2016-8-1' AND purchase_date <='2016-8-31' AND user_id IN (SELECT user_id FROM levels WHERE system_id=1 AND ancestor_id='".$line['user_id']."')");

	echo "<tr><td align=right>".$line['user_id']."</td><td align=right>$".$line['group_sales']."</td>";

	if ($amount == $line['group_sales'])
		echo "<td align=right>$".$amount."</td>";
	else
		echo "<td align=right><font color=red><b>$".$amount."</b></font></td>";

	echo "<td align=right>$".$line['affiliate_sales']."</td><td align=right>$".$line['customer_sales']."</td></tr>";
} 
echo "</table>";
*/

/*
CREATE INDEX idx_breakdown_system_id ON breakdown(system_id);
CREATE INDEX idx_breakdown_user_id ON breakdown(user_id);
SELECT * INTO tmp_breakdown FROM breakdown WHERE user_id='57' AND system_id=2;
SELECT * INTO tmp_receipts FROM receipts WHERE receipt_id IN (SELECT receipt_id FROM tmp_breakdown WHERE generation=1);
SELECT * INTO tmp_users FROM users WHERE parent_id='57' AND system_id=2;

SELECT * FROM tmp_users WHERE user_id NOT IN (SELECT user_id FROM tmp_receipts);

select user_id, count(*) FROM users WHERE system_id>2 AND usertype='1' AND user_id in (select parent_id from users where usertype='1' and system_id>1) 

SELECT parent_id, count(*) FROM users WHERE parent_id IN (SELECT user_id FROM users WHERE usertype='1' AND system_id>2) group by parent_id ORDER BY count(*) DESC;

selereceipt+ from tmp_breakdown where user_id='57' AND receipt_id in (SELECT receipt_id FROM tmp_receipts WHERE usertype='2') AND generation

SELECT * FROM users WHERE parent_id!='57' AND user_id IN (SELECT user_id FROM tmp_receipts WHERE usertype='1' AND receipt_id IN (SELECT receipt_id FROM tmp_breakdown WHERE generation='1'))

*/
?>