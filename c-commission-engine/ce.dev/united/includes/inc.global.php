<?php

//$connstr = "host=localhost dbname=ce user=root password=gypsey";
//$dbconn = pg_connect($connstr)
$dbconn = pg_connect("host=localhost dbname=united user=root password=53eqRpYtQPP94apf")
    or die('Could not connect: ' . pg_last_error());

/////////////
// Run SQL //
/////////////
function ExecDB($query)
{
	pg_query($query) or die('Query failed: ' . pg_last_error());
}

//////////////////////////
// Get a database value //
//////////////////////////
function GetDB($query)
{
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());	
	$line = pg_fetch_array($result, null, PGSQL_ASSOC);
	
	return array_values($line)[0];
}

//////////////////////////////////////////
// Grab the top 10 user_id based payout //
//////////////////////////////////////////
function BuildTopTen()
{
	$query = "SELECT user_id, SUM(amount) FROM ce_ledger WHERE system_id=1 GROUP BY user_id ORDER BY SUM(amount) DESC LIMIT 10";
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());	
	$index = 0;
	while ($line = pg_fetch_array($result, null, PGSQL_ASSOC))
	{
		$retval[$index] = $line['user_id'];
		$index++;
	}

	return $retval;
}	

?>
