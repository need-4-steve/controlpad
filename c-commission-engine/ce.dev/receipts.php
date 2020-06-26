<?php

include "includes/inc.comm.php";

echo "<h2 align=center>Receipts</h2>";
MenuStart();
$systemid = $_SESSION['systemid'];

/////////////////////////////////
// Handle adding of a rankrule //
/////////////////////////////////
if ($_POST['direction'] == 'add')
{
	if (empty($_POST['commissionable']))
		$_POST['commissionable'] = "false";

	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: addreceipt";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "receiptid: ".$_POST['receiptid'];
	$headers[] = "userid: ".$_POST['userid'];
	$headers[] = "amount: ".$_POST['amount'];
	$headers[] = "purchasedate: ".$_POST['purchasedate'];
	$headers[] = "commissionable: ".$_POST['commissionable'];
	$retdata .= PostURL($curlstring, $headers);

	echo "<pre>";
	print_r($retdata);
	echo "</pre>";
}

///////////////////////////////////
// Handle update of the rankrule //
///////////////////////////////////
if ($_POST['direction'] == 'edit')
{
	if (empty($_POST['commissionable']))
		$_POST['commissionable'] = "false";

	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: editreceipt";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "receiptid: ".$_POST['receiptid'];
	$headers[] = "userid: ".$_POST['userid'];
	$headers[] = "amount: ".$_POST['amount'];
	$headers[] = "purchasedate: ".$_POST['purchasedate'];
	$headers[] = "commissionable: ".$_POST['commissionable'];
	$retdata .= PostURL($curlstring, $headers);

	echo "<pre>";
	print_r($retdata);
	echo "</pre>";
}

/////////////////////////////////////////////////////////////
// Always run a query on load of page for most recent view //
/////////////////////////////////////////////////////////////
$curlstring = $coredomain;
$headers = [];
$headers[] = "command: queryreceipts";
$headers[] = "authemail: ".$authemail;
$headers[] = "apikey: ".$apikey;
$headers[] = "systemid: ".$systemid;

$headers[] = "startdate: 2015-01-01";
$headers[] = "enddate: 2020-01-01";
$jsonrules = PostURL($curlstring, $headers);

echo "<b>Initial Json Response:</b><br><textarea cols=120 rows=5>".$jsonrules."</textarea><br><br>";
$retarray = json_decode($jsonrules, true);

//echo "<pre>";
//print_r($retarray);
//echo "</pre>";

// Display the fields //
echo "<table border=0>";
echo "<tr>";
echo "<td align=center><b>ReceiptID</b></td>";
echo "<td align=center><b>UserID</b></td>";
echo "<td align=center><b>Amount</b></td>";
echo "<td align=center><b>PurchaseDate</b></td>";
echo "<td align=center><b>Commissionable</b></td>";
echo "<td align=center><b>Disabled</b></td>";
echo "<td align=center><b>Created At</b></td>";
echo "<td align=center><b>Updated At</b></td>";
echo "</tr>";

// Loop through each rule //
foreach ($retarray['receipts'] as $receipt)
{
	$checked = "";
	if ($receipt['commissionable'] == "t")
		$checked = " checked ";

	echo "<form method=POST action=''>";
	echo "<input type=hidden name='receiptid' value='".$receipt['id']."'>";
	echo "<input type=hidden name='direction' value='edit'>";

	echo "<tr bgcolor='90C3D4'>";
	echo "<td align=center>".$receipt['id']."</td>";
	echo "<td align=center>".SelectUser("userid", $receipt['userid'])."</td>";
	echo "<td align=center><input size=1 type=edit name='amount' value='".$receipt['amount']."'></td>";
	echo "<td align=center><input size=18 type=edit name='purchasedate' value='".$receipt['purchasedate']."'></td>";
	echo "<td align=center><input type=checkbox checked name='commissionable' ".$checked." value='true'></td>";

	echo "<td align=center>".$receipt['disabled']."</td>";
	echo "<td>".$receipt['createdat']."</td>";
	echo "<td>".$receipt['updatedat']."</td>";

	echo "<td><input type=submit value='Update'></td>";
	echo "</form>";

	echo "<form method=POST action='breakdown.php'>";
	echo "<input type=hidden name='receiptid' value='".$receipt['id']."'>";
	echo "<td><input type=submit value='Breakdown'></td></tr>";
	echo "</form>";
}

//////////////////////////
// Display the add form //
//////////////////////////
echo "<form method=POST action=''>";
echo "<input type=hidden name='direction' value='add'>";

echo "<tr bgcolor='F58C8C'>";
echo "<td align=center><input size=1 type=edit name='receiptid'></td>";
echo "<td align=center>".SelectUser("userid", "")."</td>";
echo "<td align=center><input size=1 type=edit name='amount'></td>";
echo "<td align=center><input size=18 type=edit name='purchasedate'></td>";
echo "<td align=center><input type=checkbox name='commissionable' value='true'></td>";
echo "<td align=center></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td><input type=submit value='Add'></td>";

echo "</form>";

MenuEnd();
?>