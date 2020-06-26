<?php

include "includes/inc.comm.php";

echo "<h2 align=center>Pool Rules</h2>";
MenuStart();
$systemid = $_SESSION['systemid'];

echo SelectPoolPot($coredomain, $authemail, $apikey);

/////////////////////////////////
// Handle adding of a rankrule //
/////////////////////////////////
if ($_POST['direction'] == 'add')
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: addpoolrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "poolpotid: ".$_SESSION['poolpotid'];
	$headers[] = "startrank: ".$_POST['startrank'];
	$headers[] = "endrank: ".$_POST['endrank'];
	$headers[] = "qualifythreshold: ".$_POST['qualifythreshold'];
	$retdata = PostURL($curlstring, $headers);

	print_r($retdata);
}

///////////////////////////////////
// Handle update of the rankrule //
///////////////////////////////////
if ($_POST['direction'] == 'edit')
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: editpoolrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "poolruleid: ".$_POST['poolruleid'];
	$headers[] = "poolpotid: ".$_SESSION['poolpotid'];
	$headers[] = "startrank: ".$_POST['startrank'];
	$headers[] = "endrank: ".$_POST['endrank'];
	$headers[] = "qualifythreshold: ".$_POST['qualifythreshold'];
	$retdata = PostURL($curlstring, $headers)."\n";

	print_r($retdata);
}

/////////////////////////////////////////////////////////////
// Always run a query on load of page for most recent view //
/////////////////////////////////////////////////////////////
$curlstring = $coredomain;
$headers = [];
$headers[] = "command: querypoolrules";
$headers[] = "authemail: ".$authemail;
$headers[] = "apikey: ".$apikey;
$headers[] = "systemid: 1";
$headers[] = "poolpotid: ".$_SESSION['poolpotid'];
$jsonrules = PostURL($curlstring, $headers);

echo "<br><b>Initial Json Response:</b><br><textarea cols=120 rows=5>".$jsonrules."</textarea><br><br>";
$poolrulearray = json_decode($jsonrules, true);

// Display the fields //
echo "<table border=0>";
echo "<tr>";
echo "<td align=center><b>ID</b></td>";
echo "<td align=center><b>StartRank</b></td>";
echo "<td align=center><b>EndRank</b></td>";
echo "<td align=center><b>QualifyThreshold</b></td>";
echo "<td align=center><b>Disabled</b></td>";
echo "<td align=center><b>Created At</b></td>";
echo "<td align=center><b>Updated At</b></td>";
echo "</tr>";

// Loop through each rule //
foreach ($poolrulearray['poolrules'] as $poolrule)
{
	echo "<form method=POST action=''>";
	echo "<input type=hidden name='poolruleid' value='".$poolrule['id']."'>";
	echo "<input type=hidden name='direction' value='edit'>";
	echo "<tr bgcolor='90C3D4'>";

	echo "<td>".$poolrule['id']."</td>";
	echo "<td align=center>".SelectRank("startrank", $poolrule['startrank'])."</td>";
	echo "<td align=center>".SelectRank("endrank", $poolrule['endrank'])."</td>";
	echo "<td align=center><input size=1 type=edit name='qualifythreshold' value='".$poolrule['qualifythreshold']."'></td>";
	echo "<td align=center>".$poolrule['disabled']."</td>";
	echo "<td>".$poolrule['createdat']."</td>";
	echo "<td>".$poolrule['updatedat']."</td>";

	echo "<td><input type=submit value='Update'></td></tr>";

	echo "</form>";
}

//////////////////////////
// Display the add form //
//////////////////////////
echo "<form method=POST action=''>";
echo "<input type=hidden name='direction' value='add'>";

echo "<tr bgcolor='F58C8C'>";
echo "<td>".$poolrule['id']."</td>";
echo "<td align=center>".SelectRank("startrank", "")."</td>";
echo "<td align=center>".SelectRank("endrank", "")."</td>";
echo "<td align=center><input size=1 type=edit name='qualifythreshold'></td>";
echo "<td align=center>".$poolrule['disabled']."</td>";
echo "<td>".$poolrule['createdat']."</td>";
echo "<td>".$poolrule['updatedat']."</td>";
echo "<td><input type=submit value='Add'></td></tr>";

echo "</form>";
MenuEnd();
?>