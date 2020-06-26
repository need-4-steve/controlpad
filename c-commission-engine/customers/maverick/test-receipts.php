#!/usr/bin/php
<?php
include "includes/inc.global.php";
include "includes/inc.database.php";

global $g_dbconn;

ConnectDB();

// Do inital query of broken receipts //
$query = "SELECT DISTINCT metadata_onadd, wholesale_price, wholesale_date, receipt_id FROM ce_receipts WHERE wholesale_date IS NOT null AND receipt_id IN (SELECT receipt_id FROM ce_receipts WHERE wholesale_date IS null)";
$count = 0;
$result = pg_exec($g_dbconn, $query); 
while ($row = pg_fetch_array($result)) 
{ 
	// Grab metadata_onadd to related receipts //
	$query = "UPDATE ce_receipts SET wholesale_date='".$row['wholesale_date']."', wholesale_price='".$row['wholesale_price']."' WHERE wholesale_date IS NULL AND receipt_id='".$row['receipt_id']."'";

	echo "metadata_onadd = ".$row['metadata_onadd']."\n";
	echo "receipt_id = ".$row['receipt_id']."\n";
	echo "date = ".$row['wholesale_date']."\n";
	echo "price = ".$row['wholesale_price']."\n";
	echo "---------------\n";

/*
	echo "---------------\n";
	echo $row['receipt_id']."\n";

	$data = QueryDB("SELECT wholesale_date, wholesale_price FROM ce_receipts WHERE receipt_id='".$row['receipt_id']."' AND wholesale_price IS NOT NULL order by id");

	$price = $data['wholesale_price'];
	$date = $data['wholesale_date'];
	
	echo $price."\n";
	echo $date."\n";
	echo "\n";
*/

//	$query = "UPDATE ce_receipts SET wholesale_date='".$date."', wholesale_price='".$price."' WHERE receipt_id='".$row['receipt_id']."' AND wholesale_date IS null";
//	ExecDB($query);

	//$count++;
	//if ($count > 15)
	//	break;
	if (empty(trim($row['wholesale_date'])))
		break;
}

DisconnectDB();
?>
