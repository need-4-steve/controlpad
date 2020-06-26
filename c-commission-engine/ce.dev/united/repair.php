<?php

include_once("includes/inc.global.php");

$duplicount = 0;

pg_query("SELECT system_id, count(*) INTO TEMP tmp_cp FROM ce_checkpoint GROUP BY system_id");
pg_query("DELETE FROM tmp_cp WHERE count!=2");

echo "</table>";
echo "<tr>";
echo "<td><b>ID</b></td>";
echo "<td><b>System ID</b></td>";
echo "<td><b>Ref ID</b></td>";
echo "<td><b>User ID</b></td>";
echo "<td><b>Ledger Type</b></td>";
echo "<td><b>Amount</b></td>";
echo "<td><b>From Sys ID</b></td>";
echo "<td><b>From User ID</b></td>";
echo "<td><b>Created At</b></td>";
echo "</tr>";

$query = "SELECT id, system_id, ref_id, user_id, ledger_type, amount, from_system_id, from_user_id, created_at FROM ce_ledger WHERE from_system_id IN (SELECT system_id FROM tmp_cp) ORDER BY from_system_id";
$result = pg_query($query);
while ($line = pg_fetch_array($result))
{
	echo "<tr>";
	echo "<td>".$line['id']."</td>";
	echo "<td>".$line['system_id']."</td>";
	echo "<td>".$line['ref_id']."</td>";
	echo "<td>".$line['user_id']."</td>";
	echo "<td>".$line['ledger_type']."</td>";
	echo "<td>".$line['amount']."</td>";
	echo "<td>".$line['from_system_id']."</td>";
	echo "<td>".$line['from_user_id']."</td>";
	echo "<td>".$line['created_at']."</td>";
	echo "</tr>";
}
echo "</table>";
?>