<?php

include "includes/inc.comm.php";

echo "<h2 align=center>Receipt Breakdown</h2>";
MenuStart();
$systemid = $_SESSION['systemid'];

/////////////////////////////////////////////////////////////
// Always run a query on load of page for most recent view //
/////////////////////////////////////////////////////////////
$curlstring = $coredomain;
$headers = [];
$headers[] = "command: querybreakdown";
$headers[] = "authemail: ".$authemail;
$headers[] = "apikey: ".$apikey;
$headers[] = "systemid: ".$systemid;

$headers[] = "receiptid: ".$_POST['receiptid'];
$jsonrules = PostURL($curlstring, $headers);

echo "<b>Initial Json Response:</b><br><textarea cols=120 rows=5>".$jsonrules."</textarea><br><br>";
$retarray = json_decode($jsonrules, true);

// Display the fields //
echo "<table border=0>";
echo "<tr>";
echo "<td align=center><b>ID</b></td>";
echo "<td align=center><b>BatchID</b></td>";
echo "<td align=center><b>UserID</b></td>";
echo "<td align=center><b>PayType</b></td>";
echo "<td align=center><b>Amount</b></td>";
echo "<td align=center><b>Created At</b></td>";
echo "<td align=center><b>Updated At</b></td>";
echo "</tr>";

foreach ($retarray['breakdown'] as $item)
{
	echo "<tr bgcolor='90C3D4'>";
	echo "<td align=center>".$item['id']."</td>";
	echo "<td align=center>".$item['batchid']."</td>";
	echo "<td align=center>".$item['userid']."</td>";
	echo "<td align=center>".$item['paytype']."</td>";
	echo "<td>$".$item['amount']."</td>";
	echo "<td align=center>".$item['createdat']."</td>";
	echo "<td align=center>".$item['updatedat']."</td>";
	echo "</tr>";
}
echo "</table>";

MenuEnd();
?>