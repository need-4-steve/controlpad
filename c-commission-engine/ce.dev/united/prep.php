<?php

function FindParentID($last)
{
	$retval = strtok("/");
	if (strlen($retval) == 0)
		return $last;

	return FindParentID($retval);
}

// Performing SQL query
//$query = 'SELECT id, sponsor_id, braintree_customer_id, ancestry_depth, created_at::DATE FROM users WHERE sponsor_id < 500 ORDER BY id LIMIT 500';
//$query = 'SELECT id, sponsor_id, braintree_customer_id, ancestry_depth, created_at::DATE FROM users WHERE sponsor_id < 4000 ORDER BY id limit 4000';

function SystemPrep($system_id, $filename)
{
	$query = 'SELECT id, sponsor_id, cc_vault_id, ancestry_depth, ancestry, created_at::DATE FROM users';
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	//$system_id = "1";
	$index = 0;
	while ($line = pg_fetch_array($result, null, PGSQL_ASSOC))
	{
		if ($index == 0)
		{
			$filedata = rtrim($filedata, ",");
			$filedata .= ";\nINSERT INTO ce_users (system_id, user_id, usertype, sponsor_id, parent_id, signup_date) VALUES ";
		}

		// Find the parent_id //
		$first = strtok($line['ancestry'], "/");
		$parent_id = FindParentID($first);

		// Prepare the bulk insert //
		$filedata .= "('".$system_id."', '".$line['id']."', ";
		if (!empty($line['cc_vault_id']))
	    	$filedata .= "1";
	   	else 
	   		$filedata .= "2";
		$filedata .= ", '".$line['sponsor_id']."', '".$parent_id."', '".$line['created_at']."'),"; 

		// Only allow 5000 horizontal inserts //
		$index++;
		if ($index == 5000)
			$index = 0;
	}

	$filedata = rtrim($filedata, ",");
	$filedata .= ";";
	file_put_contents($filename, $filedata);
	//file_put_contents("/tmp/united-users-9.sql", $filedata);

	// Free resultset
	pg_free_result($result);
}

$dbconn = pg_connect("host=localhost dbname=united user=root password=53eqRpYtQPP94apf")
//$dbconn = pg_connect("host=localhost dbname=united user=west password=gypsey")
    or die('Could not connect: ' . pg_last_error());

SystemPrep(1, "/tmp/united-users-9.sql");
SystemPrep(2, "/tmp/united-users-10.sql");

echo "Done!";

// Closing connection
pg_close($dbconn);


/*
UPDATE users
SET rank=subquery.rank
FROM (SELECT user_id, rank
      FROM  tmp_users WHERE rank=1) AS subquery
WHERE users.user_id=subquery.user_id;
*/
?>