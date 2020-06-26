<?php

include_once("includes/inc.global.php");

echo "<h3 align=center>Cross Reference Report</h3>";

function GetDB($query)
{
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());	
	$line = pg_fetch_array($result, null, PGSQL_ASSOC);
	
	return array_values($line)[0];
}

function Upline($user_id)
{
	$query = "SELECT parent_id FROM users WHERE user_id='".$user_id."'";
	return GetDB($query);
}

echo "<table border=1>";
echo "<tr><td>user_id</td><td>parent_id</td><td>usertype</td><td>generation</td></tr>";
$user_id = $_GET['user_id'];
$generation = 0;
$count = 0;
while (1)
{
	$parent_id = Upline($user_id);
	$usertype = GetDB("SELECT usertype FROM users WHERE user_id='".$user_id."'");
	if (($usertype == 1) && ($count!=0))
		$generation++;
	$count++;
	echo "<tr><td>".$user_id."</td><td>".$parent_id."</td><td>".$usertype."</td><td>".$generation."</td></tr>";
	if (($parent_id == "0") || ($parent_id == ""))
		break;
	else
		$user_id = $parent_id;
}
echo "</table>";

?>