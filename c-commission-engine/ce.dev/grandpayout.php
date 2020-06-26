<?php

include "includes/inc.comm.php";

echo "<h2 align=center>Grand Payout</h2>";
MenuStart();
$systemid = $_SESSION['systemid'];

if ($_POST['command'] == "authgrandpayout")
{
	if (empty($_POST['authorized']))
		$_POST['authorized'] = "false";

	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: authgrandpayout";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "grandid: ".$_POST['grandid'];
	$headers[] = "authorized: ".$_POST['auth'];
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "authgrandbulk")
{
	if (empty($_POST['authorized']))
		$_POST['authorized'] = "false";

	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: authgrandbulk";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$retdata = PostURL($curlstring, $headers);
}

if (empty($_POST['authorized']))
	$_POST['authorized'] = "false";

// Define the commission structure //
$curlstring = $coredomain;
$headers = [];
$headers[] = "command: querygrandpayout";
$headers[] = "authemail: ".$authemail;
$headers[] = "apikey: ".$apikey;
$headers[] = "systemid: ".$systemid;
	
$headers[] = "authorized: ".$_POST['authorized'];
$retdata = PostURL($curlstring, $headers);

$grandarray = json_decode($retdata, true);

echo "<b>Json Response:</b><br><textarea cols=120 rows=5>".$retdata."</textarea><br><br>";

echo "<form method='POST' action=''>";
echo "<input type='hidden' name='command' value='authgrandbulk'>";
echo "<input type='hidden' name='authorized' value='".$_POST['authorized']."'>";
echo "<input type='submit' value='Authorize All'>";
echo "</form>";

// Display the fields //
echo "<table border=0 width='100%'>";
echo "<tr>";
echo "<td align=center><b>ID</b></td>";
echo "<td align=center><b>SystemID</b></td>";
echo "<td align=center><b>UserID</b></td>";
echo "<td align=center><b>Amount</b></td>";
echo "<td align=center><b>Authorized</b></td>";
echo "<td align=center><b>SyncdPayman</b></td>";
echo "<td align=center><b>Disabled</b></td>"; // Disable is a completely different command //
echo "<td align=center><b>Created At</b></td>";
echo "<td align=center><b>Updated At</b></td>";
echo "</tr>";

foreach ($grandarray['grandtotals'] as $record)
{
	echo "<tr>";
	echo "<tr bgcolor='90C3D4'>";
	echo "<td align=center>".$record['id']."</td>";
	echo "<td align=center>".$record['systemid']."</td>";
	echo "<td align=center>".$record['userid']."</td>";
	echo "<td align=center>".$record['amount']."</td>";
	echo "<td align=center>".$record['authorized']."</td>";
	echo "<td align=center>".$record['syncdpayman']."</td>";
	echo "<td align=center>".$record['disabled']."</td>";
	echo "<td align=center>".$record['createdat']."</td>";
	echo "<td align=center>".$record['updatedat']."</td>";

	if ($_POST['authorized'] == "false")
	{
		echo "<form method='POST' action=''>";
		echo "<input type='hidden' name='grandid' value='".$record['id']."'>";
		echo "<input type='hidden' name='command' value='authgrandpayout'>";
		echo "<input type='hidden' name='auth' value='true'>";
		echo "<input type='hidden' name='authorized' value='".$_POST['authorized']."'>";
		echo "<td align=center><input type='submit' value='Authorize'></td>";
		echo "</form>";
	}
	else if ($_POST['authorized'] == "true")
	{
		echo "<form method='POST' action=''>";
		echo "<input type='hidden' name='grandid' value='".$record['id']."'>";
		echo "<input type='hidden' name='command' value='authgrandpayout'>";
		echo "<input type='hidden' name='auth' value='false'>";
		echo "<input type='hidden' name='authorized' value='".$_POST['authorized']."'>";
		echo "<td align=center><input type='submit' value='Un-Authorize'></td>";
		echo "</form>";
	}

	echo "</tr>";
}
echo "</table>";

MenuEnd();
?>