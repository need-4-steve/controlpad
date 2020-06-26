<?php

$dbconn = pg_connect("host=localhost dbname=ce user=root password=53eqRpYtQPP94apf")
//$dbconn = pg_connect("host=localhost dbname=ce user=west password=gypsey")
    or die('Could not connect: ' . pg_last_error());

$query = 'SELECT user_id, rank FROM users WHERE usertype=\'1\' AND system_id=1';
$result = pg_query($query) or die('Query failed: ' . pg_last_error());

$sqlout1 = "CREATE TABLE tmp_users (user_id TEXT, rank VARCHAR(2));\r\n";
$sqlout2 = "INSERT INTO tmp_users (user_id, rank) VALUES ";
while ($line = pg_fetch_array($result, null, PGSQL_ASSOC))
{
	$sqlout2 .= "('".$line['user_id']."', ".$line['rank']."),";
}
$sqlout2 = rtrim($sqlout2, ",");

file_put_contents("/tmp/UPDATE-tmp.sql", $sqlout1.$sqlout2);

echo "DONE!!!";
?>