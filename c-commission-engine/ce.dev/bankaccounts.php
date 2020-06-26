<?php

include "includes/inc.comm.php";

echo "<h2 align=center>Bank Accounts</h2>";
MenuStart();
$systemid = $_SESSION['systemid'];

/////////////////////////////////
// Handle adding of a rankrule //
/////////////////////////////////
if ($_POST['direction'] == 'add')
{
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: addbankaccount";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "userid: ".$_POST['userid'];
	$headers[] = "accounttype: ".$_POST['accounttype'];
	$headers[] = "routingnumber: ".$_POST['routingnumber'];
	$headers[] = "accountnumber: ".$_POST['accountnumber'];
	$headers[] = "holdername: ".$_POST['holdername'];
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
	// Define the commission structure //
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: editbankaccount";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	
	$headers[] = "userid: ".$_POST['userid'];
	$headers[] = "accounttype: ".$_POST['accounttype'];
	$headers[] = "routingnumber: ".$_POST['routingnumber'];
	$headers[] = "accountnumber: ".$_POST['accountnumber'];
	$headers[] = "holdername: ".$_POST['holdername'];
	$retdata = PostURL($curlstring, $headers);

	echo "<pre>";
	print_r($retdata);
	echo "</pre>";
}

/////////////////////////////////////////////////////////////
// Always run a query on load of page for most recent view //
/////////////////////////////////////////////////////////////
$curlstring = $coredomain;
$headers = [];
$headers[] = "command: querybankaccounts";
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
echo "<td align=center><b>AccountID</b></td>";
echo "<td align=center><b>UserID</b></td>";
echo "<td align=center><b>AccountType</b></td>";
echo "<td align=center><b>RoutingNumber</b></td>";
echo "<td align=center><b>AccountNumber</b></td>";
echo "<td align=center><b>HolderName</b></td>";
echo "<td align=center><b>Disabled</b></td>";
echo "<td align=center><b>Created At</b></td>";
echo "<td align=center><b>Updated At</b></td>";
echo "</tr>";

// Loop through each rule //
foreach ($retarray['bankaccounts'] as $account)
{
	echo "<form method=POST action=''>";
	echo "<input type=hidden name='receiptid' value='".$account['id']."'>";
	echo "<input type=hidden name='direction' value='edit'>";

	echo "<tr bgcolor='90C3D4'>";
	echo "<td align=center>".$account['id']."</td>";
	echo "<td align=center>".SelectUser("userid", $account['userid'])."</td>";
	echo "<td align=center>".SelectBankAccountType("accounttype", $account['accounttype'])."</td>";
	echo "<td align=center><input size=10 type=edit name='routingnumber' value='".$account['routingnumber']."'></td>";
	echo "<td align=center><input size=10 type=edit name='accountnumber' value='".$account['accountnumber']."'></td>";
	echo "<td align=center><input size=10 type=edit name='holdername' value='".$account['holdername']."'></td>";

	echo "<td align=center>".$account['disabled']."</td>";
	echo "<td>".$account['createdat']."</td>";
	echo "<td>".$account['updatedat']."</td>";

	echo "<td><input type=submit value='Update'></td>";

	echo "</form>";
}

//////////////////////////
// Display the add form //
//////////////////////////
echo "<form method=POST action=''>";
echo "<input type=hidden name='direction' value='add'>";

echo "<tr bgcolor='F58C8C'>";

echo "<td align=center></td>";
echo "<td align=center>".SelectUser("userid", "")."</td>";
echo "<td align=center>".SelectBankAccountType("accounttype", "")."</td>";
echo "<td align=center><input size=10 type=edit name='routingnumber'></td>";
echo "<td align=center><input size=10 type=edit name='accountnumber'></td>";
echo "<td align=center><input size=10 type=edit name='holdername'></td>";

echo "<td></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td><input type=submit value='Add'></td>";

echo "</form>";

MenuEnd();
?>