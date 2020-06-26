<?php

include "includes/inc.comm.php";

echo "<h2 align=center>Payments</h2>";
MenuStart();
$systemid = $_SESSION['systemid'];

if ($_POST['command'] == "processpayments")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: processpayments";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "batchid: ".$_POST['batchid'];
	$retdata = PostURL($curlstring, $headers);
}


echo "<b>Json Response:</b><br><textarea cols=120 rows=5>".$retdata."</textarea><br><br>";

echo "<table border=0>";
echo "<form method=POST action=''>";
echo "<tr><td><input type='submit' value='Process Payments'></td>";
echo "<td align=center>".SelectBatch("batchid", "")."</td></tr>";
echo "<input type='hidden' name='command' value='processpayments'>";
echo "</form>";

MenuEnd();
?>