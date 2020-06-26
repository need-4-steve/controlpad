<?php

include_once("includes/inc.global.php");

echo "<h3 align=center>Level Report</h3>";

////////////////////////////////
// Show each individual level //
////////////////////////////////
function ShowLevel($level, $ancestor_id)
{
	$query = "SELECT sum(amount) FROM ce_receipts WHERE system_id=1 AND user_id IN (SELECT user_id FROM ce_levels WHERE system_id=1 AND ancestor_id='".$ancestor_id."' AND level='".$level."')";
	$sum = GetDB($query);
	
	echo "<tr><td>".$level."</td><td>".$sum."</td></tr>";
}

////////////////////////////
// Show all of the levels //
////////////////////////////
function ShowAllLevels($ancestor_id)
{
	echo "<table>";

	$query = "SELECT DISTINCT level FROM ce_levels WHERE system_id='1' AND ancestor_id='".$ancestor_id."' ORDER BY level";
	$result = pg_query($query);	
	while ($line = pg_fetch_array($result))
	{
		ShowLevel($line['level'], $ancestor_id);
	}
	echo "</table>";
}

ShowAllLevels(3);

?>
