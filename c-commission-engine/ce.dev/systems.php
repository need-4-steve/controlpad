<?php

include "includes/inc.comm.php";

echo "<h2 align=center>Systems</h2>";
MenuStart();
$systemid = $_SESSION['systemid'];

/////////////////////////////////
// Handle adding of a rankrule //
/////////////////////////////////
if ($_POST['direction'] == 'add')
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: addsystem";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;

	$headers[] = "systemname: ".$_POST['systemname'];
	$headers[] = "commtype: ".$_POST['commtype'];
	//#define COMMRULE_HYBRIDUNI			1 // Meet criteria or get skipped over on commissions //
	//#define COMMRULE_BREAKAWAY			2 // A person breaks out of a downline to start their own branch //
	//#define COMMRULE_BINARY				3 // Take the two top legs. Pay the lesser of the two //
	$headers[] = "payouttype: ".$_POST['payouttype']; // 1 - monthly, 2 - weekly, 3 - daily
	$headers[] = "payoutmonthday: ".$_POST['payoutmonthday'];
	$headers[] = "payoutweekday: ".$_POST['payoutweekday']; 
	$headers[] = "autoauthgrand: false";
	$headers[] = "infinitycap: 0";
	$headers[] = "updatedurl: ".$_POST['updatedurl'];
	$headers[] = "updatedusername: ".$_POST['updatedusername'];
	$headers[] = "updatedpassword: ".$_POST['updatedpassword'];

	$retdata = PostURL($curlstring, $headers)."\n";

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
	$headers[] = "command: editsystem";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "systemname: ".$_POST['systemname'];
	$headers[] = "commtype: ".$_POST['commtype'];
	//#define COMMRULE_HYBRIDUNI			1 // Meet criteria or get skipped over on commissions //
	//#define COMMRULE_BREAKAWAY			2 // A person breaks out of a downline to start their own branch //
	//#define COMMRULE_BINARY				3 // Take the two top legs. Pay the lesser of the two //
	$headers[] = "payouttype: ".$_POST['payouttype']; // 1 - monthly, 2 - weekly, 3 - daily
	$headers[] = "payoutmonthday: ".$_POST['payoutmonthday'];
	$headers[] = "payoutweekday: ".$_POST['payoutweekday']; 
	$headers[] = "autoauthgrand: false";
	$headers[] = "infinitycap: 0";
	$headers[] = "updatedurl: ".$_POST['updatedurl'];
	$headers[] = "updatedusername: ".$_POST['updatedusername'];
	$headers[] = "updatedpassword: ".$_POST['updatedpassword'];
	$retdata = PostURL($curlstring, $headers)."\n";

	echo "<pre>";
	print_r($retdata);
	echo "</pre>";
}

if ($_POST['command'] == "disablesystem")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: disablesystem";
	$headers[] = "authemail: ".$authemail;	
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$_POST['systemid'];
	$retdata = PostURL($curlstring, $headers);
}

if ($_POST['command'] == "enablesystem")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: enablesystem";
	$headers[] = "authemail: ".$authemail;	
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$_POST['systemid'];
	$retdata = PostURL($curlstring, $headers);
}

/////////////////////////////////////////////////////////////
// Always run a query on load of page for most recent view //
/////////////////////////////////////////////////////////////
$curlstring = $coredomain;
$headers = [];
$headers[] = "command: querysystems";
$headers[] = "authemail: ".$authemail;
$headers[] = "apikey: ".$apikey;
$jsonrules = PostURL($curlstring, $headers);

echo "<b>Initial Json Response:</b><br><textarea cols=120 rows=5>".$jsonrules."</textarea><br><br>";
$retarray = json_decode($jsonrules, true);

//echo "<pre>";
//print_r($retarray);
//echo "</pre>";

// Display the fields //
echo "<table border=0>";
echo "<tr>";
echo "<td></td>";
echo "<td align=center><b>ID</b></td>";
echo "<td align=center><b>System Name</b></td>";
echo "<td align=center><b>CommType</b></td>";
echo "<td align=center><b>PayoutType</b></td>";
echo "<td align=center><b>PayoutMonthDay</b></td>";
echo "<td align=center><b>PayoutWeekdayDay</b></td>";

echo "<td align=center><b>Updated URL</b></td>";
echo "<td align=center><b>Updated Username</b></td>";
echo "<td align=center><b>Updated Password</b></td>";

echo "<td align=center><b>Disabled</b></td>";
echo "<td align=center><b>Created At</b></td>";
echo "<td align=center><b>Updated At</b></td>";
echo "</tr>";

// Loop through each rule //
foreach ($retarray['systems'] as $system)
{
	echo "<tr bgcolor='90C3D4'>";

	echo "<form method=POST action=''>";
	echo "<input type=hidden name='systemid' value='".$system['id']."'>";
	if ($system['disabled'] == 't')
	{
		echo "<input type=hidden name='command' value='enablesystem'>";
		echo "<td><input type=submit value='Enable'></td>";
	}
	else if ($system['disabled'] == 'f')
	{
		echo "<input type=hidden name='command' value='disablesystem'>";
		echo "<td><input type=submit value='Disable'></td>";
	}
	echo "</form>";

	echo "<form method=POST action=''>";
	echo "<input type=hidden name='sysuserid' value='".$system['id']."'>";
	echo "<input type=hidden name='direction' value='edit'>";

	echo "<td align=center>".$system['id']."</td>";
	echo "<td align=center><input size=18 type=edit name='systemname' value='".$system['systemname']."'></td>";
	//echo "<td align=center><input size=1 type=edit name='commtype' value='".$system['commtype']."'></td>";
	echo "<td align=center>".SelectCommType("commtype", $system['commtype'])."</td>";
	echo "<td align=center>".SelectPayoutType("payouttype", $system['payouttype'])."</td>";
	echo "<td align=center>".SelectNumber("payoutmonthday", $system['payoutmonthday'], 1, 28)."</td>";
	echo "<td align=center>".SelectNumber("payoutweekday", $system['payoutweekday'], 1, 7)."</td>";

	echo "<td align=center><input size=18 type=edit name='updatedurl' value='".$system['systemname']."'></td>";
	echo "<td align=center><input size=18 type=edit name='updatedusername' value='".$system['systemname']."'></td>";
	echo "<td align=center><input size=18 type=edit name='updatedpassword' value='".$system['systemname']."'></td>";

	echo "<td align=center>".$system['disabled']."</td>";
	echo "<td>".$system['createdat']."</td>";
	echo "<td>".$system['updatedat']."</td>";
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
echo "<td></td>";
echo "<td align=center><input size=18 type=edit name='systemname'></td>";
echo "<td align=center>".SelectCommType("commtype", "")."</td>";
echo "<td align=center>".SelectPayoutType("payouttype", "")."</td>";
echo "<td align=center>".SelectNumber("payoutmonthday", "", 1, 28)."</td>";
echo "<td align=center>".SelectNumber("payoutweekday", "", 1, 7)."</td>";
echo "<td></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td><input type=submit value='Add'></td>";

echo "</form>";

MenuEnd();
?>