<?php

include "includes/inc.comm.php";

echo "<h2 align=center>System Users</h2>";
MenuStart();
$systemid = $_SESSION['systemid'];

/////////////////////////////////
// Handle adding of a rankrule //
/////////////////////////////////
if ($_POST['direction'] == 'add')
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: addsystemuser";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;

	$headers[] = "email: ".$_POST['email'];
	$headers[] = "password: ".$_POST['password'];
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
	
	$headers[] = "sysuserid: ".$_POST['sysuserid'];
	$headers[] = "email: ".$_POST['email'];
	$headers[] = "password: ".$_POST['password'];
	$retdata = PostURL($curlstring, $headers)."\n";

	echo "<pre>";
	print_r($retdata);
	echo "</pre>";
}

if ($_POST['direction'] == 'enablesystemuser')
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: enablesystemuser";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "sysuserid: ".$_POST['sysuserid'];
	$retdata = PostURL($curlstring, $headers);

	echo "<pre>";
	print_r($retdata);
	echo "</pre>";
}

if ($_POST['direction'] == 'disablesystemuser')
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: disablesystemuser";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "sysuserid: ".$_POST['sysuserid'];
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
$headers[] = "command: querysystemusers";
$headers[] = "authemail: ".$authemail;
$headers[] = "apikey: ".$apikey;
$jsonrules = PostURL($curlstring, $headers);

echo "<b>Initial Json Response:</b><br><textarea cols=120 rows=5>".$jsonrules."</textarea><br><br>";
$retarray = json_decode("[".$jsonrules."]", true);

// Display the fields //
echo "<table border=0>";
echo "<tr>";
echo "<td></td>";
echo "<td align=center><b>ID</b></td>";
echo "<td align=center><b>Email</b></td>";
echo "<td align=center><b>Password</b></td>";
echo "<td align=center><b>Disabled</b></td>";
echo "<td align=center><b>Created At</b></td>";
echo "<td align=center><b>Updated At</b></td>";
echo "</tr>";

// Loop through each rule //
foreach ($retarray[0]['systemusers'] as $sysusers)
{
	echo "<tr bgcolor='90C3D4'>";

	if ($sysusers['id'] != 1)
	{
		echo "<form method=POST action=''>";
		echo "<input type=hidden name='sysuserid' value='".$sysusers['id']."'>";
		if ($sysusers['disabled'] == 't')
		{
			echo "<input type=hidden name='direction' value='enablesystemuser'>";
			echo "<td><input type=submit value='Enable'></td>";
		}
		else if ($sysusers['disabled'] == 'f')
		{
			echo "<input type=hidden name='direction' value='disablesystemuser'>";
			echo "<td><input type=submit value='Disable'></td>";
		}
		echo "</form>";
	}
	else
		echo "<td></td>";

	echo "<form method=POST action=''>";
	echo "<input type=hidden name='sysuserid' value='".$sysusers['id']."'>";
	echo "<input type=hidden name='direction' value='edit'>";
	echo "<td align=center>".$sysusers['id']."</td>";
	echo "<td align=center><input size=18 type=edit name='email' value='".$sysusers['email']."'></td>";
	echo "<td align=center>********</td>";
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
echo "<td></td>";
echo "<td></td>";
echo "<td align=center><input size=18 type=edit name='email'></td>";
echo "<td align=center><input size=18 type=edit name='password'></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td><input type=submit value='Add'></td>";

echo "</form>";

MenuEnd();
?>