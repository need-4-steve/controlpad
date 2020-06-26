<?php

include "includes/inc.comm.php";

echo "<h2 align=center>Users</h2>";
MenuStart();
$systemid = $_SESSION['systemid'];

/////////////////////////////////
// Handle adding of a rankrule //
/////////////////////////////////
if ($_POST['direction'] == 'add')
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: adduser";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "userid: ".$_POST['userid'];
	$headers[] = "sponsorid: ".$_POST['sponsorid'];
	$headers[] = "parentid: ".$_POST['parentid'];
	$headers[] = "signupdate: ".$_POST['signupdate'];
	$retdata .= PostURL($curlstring, $headers)."\n";

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
	$headers[] = "command: edituser";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "userid: ".$_POST['userid'];
	$headers[] = "sponsorid: ".$_POST['sponsorid'];
	$headers[] = "parentid: ".$_POST['parentid'];
	$headers[] = "signupdate: ".$_POST['signupdate'];
	$retdata .= PostURL($curlstring, $headers)."\n";

	echo "<pre>";
	print_r($retdata);
	echo "</pre>";
}

/////////////////////////////////////////////////////////////
// Always run a query on load of page for most recent view //
/////////////////////////////////////////////////////////////
$curlstring = $coredomain;
$headers = [];
$headers[] = "command: queryusers";
$headers[] = "authemail: ".$authemail;
$headers[] = "apikey: ".$apikey;
$headers[] = "systemid: ".$systemid;
$jsonrules = PostURL($curlstring, $headers);

echo "<b>Initial Json Response:</b><br><textarea cols=120 rows=5>".$jsonrules."</textarea><br><br>";
$retarray = json_decode($jsonrules, true);

//echo "<pre>";
//print_r($retarray);
//echo "</pre>";

// Display the fields //
echo "<table border=0>";
echo "<tr>";
echo "<td align=center><b>UserID</b></td>";
echo "<td align=center><b>SponsorID</b></td>";
echo "<td align=center><b>SignupDate</b></td>";
echo "<td align=center><b>Disabled</b></td>";
echo "<td align=center><b>Created At</b></td>";
echo "<td align=center><b>Updated At</b></td>";
echo "</tr>";

// Loop through each rule //
foreach ($retarray['users'] as $sysusers)
{
	echo "<form method=POST action=''>";
	echo "<input type=hidden name='sysuserid' value='".$sysusers['id']."'>";
	echo "<input type=hidden name='direction' value='edit'>";

	echo "<tr bgcolor='90C3D4'>";
	echo "<td align=center><input size=1 type=edit name='userid' value='".$sysusers['userid']."'></td>";
	echo "<td align=center>".SelectUser("sponsorid", $sysusers['sponsorid'])."</td>";
	echo "<td align=center><input size=18 type=edit name='signupdate' value='".$sysusers['signupdate']."'></td>";
	echo "<td align=center>".$sysusers['disabled']."</td>";
	echo "<td>".$sysusers['createdat']."</td>";
	echo "<td>".$sysusers['updatedat']."</td>";

	echo "<td><input type=submit value='Update'></td>";

	echo "</form>";
}

//////////////////////////
// Display the add form //
//////////////////////////
echo "<form method=POST action=''>";
echo "<input type=hidden name='direction' value='add'>";

echo "<tr bgcolor='F58C8C'>";
echo "<td align=center><input size=1 type=edit name='userid'></td>";
echo "<td align=center>".SelectUser("sponsorid", "")."</td>";
echo "<td align=center><input size=18 type=edit name='signupdate'></td>";
echo "<td align=center></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td><input type=submit value='Add'></td>";

echo "</form>";

MenuEnd();
?>