<?php

//$userid = '2';

$pgconn = pg_connect("dbname=ce user=root password=53eqRpYtQPP94apf host=127.0.0.1 port=5432");
//echo "pgconn = ".$pgconn."<br>";
//$result = pg_query($pgconn, "SELECT count(*) from users");
// echo "result = ".$result."<br>";
//$count = pg_fetch_result($result, 0, 0);
//echo "count = ".$count."<br>";

$query = "SELECT count(*) FROM users WHERE user_id::INT4 <= 10 AND usertype='1'";
$result = pg_query($pgconn, $query);
$usercount = pg_fetch_result($result, 0, 0);

echo "<font color=red>red=All</font><br>";
echo "<font color=green>green=Receipts</font><br>";

echo "<table border=1>";
echo "<tr><td>UserID</td><td>Type</td><td>Aff #1</td><td>Aff #2</td><td>Aff #3</td><td>Cust #1</td><td>Cust #2</td><td>Cust #3</td><td>Aff Total</td><td>Cust Total</td><td>Grand Total</td></tr>";

$query = "SELECT user_id, usertype FROM users WHERE user_id IN (SELECT DISTINCT sponsor_id FROM users) AND usertype='1' ORDER BY user_id::int4";
$result = pg_query($pgconn, $query);
for ($index=0; $index < $usercount; $index++)
{
	$afftotal = 0;
	$afftotalr = 0;
	$custtotal = 0;
	$custtotalr = 0;
	$grandtotal = 0;
	$grandtotalr = 0;

	$userid = pg_fetch_result($result, $index, 0);
	$usertype = pg_fetch_result($result, $index, 1);

	$query = "SELECT count(*) FROM users WHERE sponsor_id='".$userid."' AND usertype='1'";
	$result2 = pg_query($pgconn, $query);
	$affilcount[1] = pg_fetch_result($result2, 0, 0);

	$query = "SELECT count(*) FROM users WHERE sponsor_id='".$userid."' AND usertype='1' AND user_id IN (SELECT user_id FROM receipts)";
	$result2 = pg_query($pgconn, $query);
	$affilcountr[1] = pg_fetch_result($result2, 0, 0);

	$query = "SELECT count(*) FROM users WHERE sponsor_id='".$userid."' AND usertype='2'";
	$result2 = pg_query($pgconn, $query);
	$custcount[1] = pg_fetch_result($result2, 0, 0);

	$query = "SELECT count(*) FROM users WHERE sponsor_id='".$userid."' AND usertype='2' AND user_id IN (SELECT user_id FROM receipts)";
	$result2 = pg_query($pgconn, $query);
	$custcountr[1] = pg_fetch_result($result2, 0, 0);

	$query = "SELECT count(*) FROM users WHERE sponsor_id IN (SELECT user_id FROM users WHERE sponsor_id='".$userid."') AND usertype='1'";
	$result2 = pg_query($pgconn, $query);
	$affilcount[2] = pg_fetch_result($result2, 0, 0);

	$query = "SELECT count(*) FROM users WHERE sponsor_id IN (SELECT user_id FROM users WHERE sponsor_id='".$userid."') AND usertype='1' AND user_id IN (SELECT user_id FROM receipts)";
	
	$result2 = pg_query($pgconn, $query);
	$affilcountr[2] = pg_fetch_result($result2, 0, 0);

	$query = "SELECT count(*) FROM users WHERE sponsor_id IN (SELECT user_id FROM users WHERE sponsor_id='".$userid."') AND usertype='2'";
	$result2 = pg_query($pgconn, $query);
	$custcount[2] = pg_fetch_result($result2, 0, 0);

	$query = "SELECT count(*) FROM users WHERE sponsor_id IN (SELECT user_id FROM users WHERE sponsor_id='".$userid."') AND usertype='2' AND user_id IN (SELECT user_id FROM receipts)";
	$result2 = pg_query($pgconn, $query);
	$custcountr[2] = pg_fetch_result($result2, 0, 0);

	$query = "SELECT count(*) FROM users WHERE sponsor_id IN (SELECT user_id FROM users WHERE sponsor_id IN (SELECT user_id FROM users WHERE sponsor_id='".$userid."') AND usertype='1')";
	$result2 = pg_query($pgconn, $query);
	$affilcount[3] = pg_fetch_result($result2, 0, 0);

	$query = "SELECT count(*) FROM users WHERE sponsor_id IN (SELECT user_id FROM users WHERE sponsor_id IN (SELECT user_id FROM users WHERE sponsor_id='".$userid."') AND usertype='1') AND user_id IN (SELECT user_id FROM receipts)";
	$result2 = pg_query($pgconn, $query);
	$affilcountr[3] = pg_fetch_result($result2, 0, 0);

	$query = "SELECT count(*) FROM users WHERE sponsor_id IN (SELECT user_id FROM users WHERE sponsor_id IN (SELECT user_id FROM users WHERE sponsor_id='".$userid."') AND usertype='2')";
	$result2 = pg_query($pgconn, $query);
	$custcount[3] = pg_fetch_result($result2, 0, 0);

	$query = "SELECT count(*) FROM users WHERE sponsor_id IN (SELECT user_id FROM users WHERE sponsor_id IN (SELECT user_id FROM users WHERE sponsor_id='".$userid."') AND usertype='2') AND user_id IN (SELECT user_id FROM receipts)";
	$result2 = pg_query($pgconn, $query);
	$custcountr[3] = pg_fetch_result($result2, 0, 0);

	echo "<tr><td align=center>".$userid."</td><td align=center>".$usertype."</td>";
	echo"<td align=center><font color=red>".$affilcount[1]."</font> (<font color=green>".$affilcountr[1]."</font>)</td>";
	echo "<td align=center><font color=red>".$affilcount[2]."</font> (<font color=green>".$affilcountr[2]."</font>)</td>";
	echo "<td align=center><font color=red>".$affilcount[3]."</font> (<font color=green>".$affilcountr[3]."</font>)</td>";
	
	echo "<td align=center><font color=red>".$custcount[1]."</font> (<font color=green>".$custcountr[1]."</font>)</td>";
	echo "<td align=center><font color=red>".$custcount[2]."</font> (<font color=green>".$custcountr[2]."</font>)</td>";
	echo "<td align=center><font color=red>".$custcount[3]."</font> (<font color=green>".$custcountr[3]."</font>)</td>";

	// Totals //
	$afftotal = $affilcount[1]+$affilcount[2]+$affilcount[3];
	$afftotalr = $affilcountr[1]+$affilcountr[2]+$affilcountr[3];
	$custtotal = $custcount[1]+$custcount[2]+$custcount[3];
	$custtotalr = $custcountr[1]+$custcountr[2]+$custcountr[3];
	$grandtotal = $afftotal+$custtotal;
	$grandtotalr = $afftotalr+$custtotalr;

	echo "<td align=center><font color=red>".$afftotal."</font> (<font color=green>".$afftotalr."</font>)</td>";
	echo "<td align=center><font color=red>".$custtotal."</font> (<font color=green>".$custtotalr."</font>)</td>";
	echo "<td align=center><font color=red>".$grandtotal."</font> (<font color=green>".$grandtotalr."</font>)</td>";

	echo "</tr>";
}

echo "</table>";

?>