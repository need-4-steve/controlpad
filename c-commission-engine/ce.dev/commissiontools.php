<?php

include "includes/inc.comm.php";

echo "<h2 align=center>Commission Rules</h2>";
MenuStart();
$systemid = $_SESSION['systemid'];

if ($_POST['command'] == "predictcommissions")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: predictcommissions";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "startdate: ".$_POST['startdate'];
	$headers[] = "enddate: ".$_POST['enddate'];
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "predictgrandtotal")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: predictgrandtotal";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "startdate: ".$_POST['startdate'];
	$headers[] = "enddate: ".$_POST['enddate'];
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "calccommissions")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: calccommissions";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "startdate: ".$_POST['startdate'];
	$headers[] = "enddate: ".$_POST['enddate'];
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "querybatches")
{
	if (empty($_POST['authorized']))
		$_POST['authorized'] = "false";

	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: querybatches";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "queryusercomm")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: queryusercomm";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "userid: ".$_POST['userid'];
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "querybatchcomm")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: querybatchcomm";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "batchid: ".$_POST['batchid'];
	$retdata = PostURL($curlstring, $headers);
}

echo "<b>Json Response:</b><br><textarea cols=120 rows=5>".$retdata."</textarea><br><br>";

echo "<table border=0>";
echo "<tr><td></td><td align=center><b>Start Date</b></td><td align=center><b>End Date</b></td></tr>";
echo "<form method=POST action=''>";
echo "<tr><td><input type='submit' value='Predict Commissions'></td>";
echo "<td><input type=edit name='startdate' value='2010-01-01'></td>";
echo "<td><input type=edit name='enddate' value='2020-01-01'></td></tr>";
echo "<input type='hidden' name='command' value='predictcommissions'>";
echo "</form>";

echo "<form method=POST action=''>";
echo "<tr><td><input type='submit' value='Predict Grand Total'></td>";
echo "<input type='hidden' name='command' value='predictgrandtotal'>";
echo "<td><input type=edit name='startdate' value='2010-01-01'></td>";
echo "<td><input type=edit name='enddate' value='2020-01-01'></td></tr>";
echo "</form>";

echo "<form method=POST action=''>";
echo "<tr><td><input type='submit' value='Calc Commissions'></td>";
echo "<input type='hidden' name='command' value='calccommissions'>";
echo "<td><input type=edit name='startdate' value='2010-01-01'></td>";
echo "<td><input type=edit name='enddate' value='2020-01-01'></tr>";
echo "</form>";

echo "<tr><td>&nbsp;</td></tr>";

echo "<form method=POST action='grandpayout.php'>";
echo "<tr><td><input type='submit' value='Query Grand Payout'></td>";
echo "<td><input type='checkbox' name='authorized' value='true'></td></tr>";
echo "<input type='hidden' name='command' value='querygrandpayout'>";
echo "</form>";

echo "<form method=POST action=''>";
echo "<tr><td><input type='submit' value='Query Batches'></td>";
echo "<input type='hidden' name='command' value='querybatches'>";
echo "</form>";

echo "<form method=POST action=''>";
echo "<tr><td><input type='submit' value='Query User Commissions'></td>";
echo "<td align=right><b>UserID:</b></td>";
echo "<td align=center>".SelectUser("userid", $receipt['userid'])."</td>";
echo "<input type='hidden' name='command' value='queryusercomm'>";
echo "</form>";

echo "<form method=POST action=''>";
echo "<tr><td><input type='submit' value='Query Batch Commissions'></td>";
echo "<td align=right><b>BatchID:</b></td>";
echo "<td><input type='edit' name='batchid'></td>";
echo "<input type='hidden' name='command' value='querybatchcomm'>";
echo "</form>";

MenuEnd();
?>