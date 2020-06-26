<?php

include "includes/inc.comm.php";

echo "<h2 align=center>Accounts Validation</h2>";
MenuStart();
$systemid = $_SESSION['systemid'];

//////////////////////////////
// Start Validation Process //
//////////////////////////////
if ($_POST['direction'] == 'initiatevalidation')
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: initiatevalidation";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "userid: ".$_POST['userid'];
	$retdata = PostURL($curlstring, $headers);

	echo "<pre>";
	print_r($retdata);
	echo "</pre>";
}

if ($_POST['direction'] == "validateaccount")
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: validateaccount";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "userid: ".$_POST['userid'];
	$headers[] = "amount1: ".$_POST['amount1'];
	$headers[] = "amount2: ".$_POST['amount2'];
	$retdata = PostURL($curlstring, $headers);

	echo "<pre>";
	print_r($retdata);
	echo "</pre>";
}

echo "<b>Json Response:</b><br><textarea cols=120 rows=5>".$retdata."</textarea><br><br>";

echo "<table border=0>";
echo "<form method=POST action=''>";
echo "<tr><td></td><td align=center><b>User ID</b></td></tr>";
echo "<tr><td><input type='submit' value='Initiate Validation'></td>";
echo "<td align=center>".SelectUser("userid", "")."</td>";
echo "<input type='hidden' name='direction' value='initiatevalidation'>";
echo "</form>";
echo "</table>";

echo "<br>";

echo "<table border=0>";
echo "<form method=POST action=''>";
echo "<tr><td></td><td align=center><b>UserID</b></td><td align=center><b>Amount 1</b></td><td align=center><b>Amount 2</b></td></tr>";
echo "<tr><td><input type='submit' value='Validate Account'></td>";
echo "<td align=center>".SelectUser("userid", "")."</td>";
echo "<td><input type=edit size=3 name='amount1'></td>";
echo "<td><input type=edit size=3 name='amount2'></td>";
echo "<input type='hidden' name='direction' value='validateaccount'>";
echo "</form>";
echo "</table>";

MenuEnd();
?>