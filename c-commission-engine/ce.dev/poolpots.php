<?php

include "includes/inc.comm.php";

echo "<h2 align=center>Pool Pots</h2>";
MenuStart();
$systemid = $_SESSION['systemid'];

/////////////////////////////////
// Handle adding of a rankrule //
/////////////////////////////////
if ($_POST['direction'] == 'add')
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: addpoolpot";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: 1";

	$headers[] = "qualifytype: ".$_POST['qualifytype']; // Personal Sales, Group Sales, Signup count //
	$headers[] = "amount: ".$_POST['amount'];
	$headers[] = "startdate: ".$_POST['startdate'];
	$headers[] = "enddate: ".$_POST['enddate'];
	$retdata = PostURL($curlstring, $headers);

	echo "<pre>";
	print_r($retdata);
	echo "</pre>";
}

///////////////////////////////////
// Handle update of the rankrule //
///////////////////////////////////
if ($_POST['direction'] == 'edit')
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: editpoolpot";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: 1";

	$headers[] = "qualifytype: ".$_POST['qualifytype']; // Personal Sales, Group Sales, Signup count //
	$headers[] = "poolpotid: ".$_POST['potid'];
	$headers[] = "amount: ".$_POST['amount'];
	$headers[] = "startdate: ".$_POST['startdate'];
	$headers[] = "enddate: ".$_POST['enddate'];
	$retdata = PostURL($curlstring, $headers)."\n";

	echo "<pre>";
	print_r($retdata);
	echo "</pre>";
}

/////////////////////////////////////////////////////////////
// Always run a query on load of page for most recent view //
/////////////////////////////////////////////////////////////
$curlstring = $coredomain;
$headers = [];
$headers[] = "command: querypoolpots";
$headers[] = "authemail: ".$authemail;
$headers[] = "apikey: ".$apikey;
$headers[] = "systemid: 1";
$jsonrules = PostURL($curlstring, $headers);

echo "<b>Initial Json Response:</b><br><textarea cols=120 rows=5>".$jsonrules."</textarea><br><br>";
$potarray = json_decode($jsonrules, true);

echo "<pre>";
print_r($poolarray);
echo "</pre>";

// Display the fields //
echo "<table border=0>";
echo "<tr>";
echo "<td align=center><b>ID</b></td>";
echo "<td align=center><b>Qualify Type</b></td>";
echo "<td align=center><b>Amount</b></td>";
echo "<td align=center><b>Start Date</b></td>";
echo "<td align=center><b>End Date</b></td>";
echo "<td align=center><b>Disabled</b></td>";
echo "<td align=center><b>Created At</b></td>";
echo "<td align=center><b>Updated At</b></td>";
echo "</tr>";

// Loop through each rule //
foreach ($potarray['poolpots'] as $pot)
{
	echo "<form method=POST action=''>";
	echo "<input type=hidden name='potid' value='".$pot['id']."'>";
	echo "<input type=hidden name='direction' value='edit'>";

	echo "<tr bgcolor='90C3D4'>";
	echo "<td>".$pot['id']."</td>";
	echo "<td align=center>".SelectQualifyType("qualifytype", $pot['qualifytype'])."</td>";
	//echo "<td align=center><input size=1 type=edit name='qualifytype' value='".$pot['qualifytype']."'></td>";
	echo "<td align=center><input size=1 type=edit name='amount' value='".$pot['amount']."'></td>";
	echo "<td align=center><input size=3 type=edit name='startdate' value='".$pot['startdate']."'></td>";
	echo "<td align=center><input size=3 type=edit name='enddate' value='".$pot['enddate']."'></td>";
	echo "<td align=center>".$pot['disabled']."</td>";
	echo "<td>".$pot['createdat']."</td>";
	echo "<td>".$pot['updatedat']."</td>";
	echo "<td><input type=submit value='Update'></td>";

	echo "</form>";
}

//////////////////////////
// Display the add form //
//////////////////////////
echo "<form method=POST action=''>";
echo "<input type=hidden name='direction' value='add'>";

echo "<tr bgcolor='F58C8C'>";
echo "<td></td>";
//echo "<td align=center><input size=1 type=edit name='qualifytype'></td>";
echo "<td align=center>".SelectQualifyType("qualifytype", "")."</td>";
echo "<td align=center><input size=1 type=edit name='amount'></td>";
echo "<td align=center><input size=3 type=edit name='startdate'></td>";
echo "<td align=center><input size=3 type=edit name='enddate'></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td><input type=submit value='Add'></td>";

echo "</form>";

MenuEnd();
?>