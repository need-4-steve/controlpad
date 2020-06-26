<?php

include_once("includes/inc.global.php");

/*
function FindParentID($last)
{
	//echo $ancestry;

	$retval = strtok("/");

	//echo $retval."<br>";

	if (strlen($retval) == 0)
		return $last;

	return FindParentID($retval);
}

$ancestry = "1/3/189/724/1670/11049/12603/15031/19764/34589/35211/36361/40509/113509/131191";
$first = strtok($ancestry, "/");
$last = FindParentID($first);

echo "last=".$last."<br>";
*/

$query = "SELECT batch3_user_id, difference FROM tmp_ledger_repair WHERE difference > 0 ORDER BY difference DESC";

$result = pg_query($query);
$data = "INSERT INTO ce_ledger (system_id, batch_id, user_id, ledger_type, amount, event_date) VALUES ";
while ($line = pg_fetch_array($result))
{
	$count++;

	if ($count >= 5000)
	{
		$count = 0;
		$data = rtrim($data, ", ");
		$data .= ";\n";
		file_put_contents("/tmp/repair_ledger.sql", $data, FILE_APPEND);
		$data = "INSERT INTO ce_ledger (system_id, batch_id, user_id, ledger_type, amount, event_date) VALUES ";
	}

	$data .= "(1, 2, '".$line['batch3_user_id']."', 9, '".$line['difference']."', '2017-2-14'), ";
}

if ($count != 0)
{
	$data = rtrim($data, ", ");
	$data .= ";\n";
	file_put_contents("/tmp/repair_ledger.sql", $data, FILE_APPEND);
}

// 185696.7693
// 192381.4172

?>