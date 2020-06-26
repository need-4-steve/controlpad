<?php

////////////////////////////////////////////////
// Add 1 million customers underneath someone //
////////////////////////////////////////////////
function AddMillionCustomers($system_id, $sponsor_id)
{
	$filedata = "";

	$index = 0;
	for ($count=0; $count < 1000000; $count++)
	{
		if ($index == 0)
		{
			$filedata = rtrim($filedata, ",");
			$filedata .= ";\nINSERT INTO users (system_id, user_id, usertype, sponsor_id, parent_id, signup_date) VALUES ";
		}
		
		$user_id = $sponsor_id."-".$count;
		$filedata .= "(".$system_id.", '".$user_id."', 2, '".$sponsor_id."', '".$sponsor_id."', '2016-8-15'),";

		// Only allow 5000 horizontal inserts //
		$index++;
		if ($index == 5000)
			$index = 0;
	}

	$filedata = rtrim($filedata, ",");
	$filedata .= ";\n";

	return $filedata;
}

//$dbconn = pg_connect("host=localhost dbname=united user=root password=53eqRpYtQPP94apf")
//$dbconn = pg_connect("host=localhost dbname=united user=west password=gypsey")
//   or die('Could not connect: ' . pg_last_error());

// Add for system 1 //
$filedata .= AddMillionCustomers(1, 372);
file_put_contents("/tmp/5-mill-sim.sql", $filedata, FILE_APPEND);
$filedata = "";
$filedata .= AddMillionCustomers(1, 66513);
file_put_contents("/tmp/5-mill-sim.sql", $filedata, FILE_APPEND);
$filedata = "";
$filedata .= AddMillionCustomers(1, 43196);
file_put_contents("/tmp/5-mill-sim.sql", $filedata, FILE_APPEND);
$filedata = "";
//$filedata .= AddMillionCustomers(1, 452332);
//file_put_contents("/tmp/5-mill-sim.sql", $filedata, FILE_APPEND);
//$filedata = "";
$filedata .= AddMillionCustomers(1, 165806);
file_put_contents("/tmp/5-mill-sim.sql", $filedata, FILE_APPEND);
$filedata = "";
$filedata .= AddMillionCustomers(1, 119236);
file_put_contents("/tmp/5-mill-sim.sql", $filedata, FILE_APPEND);
$filedata = "";

// Add for system 2 //
$filedata .= AddMillionCustomers(2, 372);
file_put_contents("/tmp/5-mill-sim.sql", $filedata, FILE_APPEND);
$filedata = "";
$filedata .= AddMillionCustomers(2, 66513);
file_put_contents("/tmp/5-mill-sim.sql", $filedata, FILE_APPEND);
$filedata = "";
$filedata .= AddMillionCustomers(2, 43196);
file_put_contents("/tmp/5-mill-sim.sql", $filedata, FILE_APPEND);
$filedata = "";
//$filedata .= AddMillionCustomers(2, 452332);
//file_put_contents("/tmp/5-mill-sim.sql", $filedata, FILE_APPEND);
//$filedata = "";
$filedata .= AddMillionCustomers(2, 165806);
file_put_contents("/tmp/5-mill-sim.sql", $filedata, FILE_APPEND);
$filedata = "";
$filedata .= AddMillionCustomers(2, 119236);
file_put_contents("/tmp/5-mill-sim.sql", $filedata, FILE_APPEND);
$filedata = "";

echo "Done 5-Million!";

// Free resultset
//pg_free_result($result);

// Closing connection
//pg_close($dbconn);


?>