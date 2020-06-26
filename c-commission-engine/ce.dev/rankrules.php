<?php

include "includes/inc.comm.php";

echo "<h2 align=center>Rank Rules</h2>";
MenuStart();
$systemid = $_SESSION['systemid'];

/////////////////////////////////
// Handle adding of a rankrule //
/////////////////////////////////
if ($_POST['direction'] == 'add')
{
	if (empty($_POST['breakage']))
		$_POST['breakage'] = "false";
	else
		$_POST['breakage'] = "true";

	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: addrankrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "rank: ".$_POST['rank']; // What level of rank we want to define for
	$headers[] = "qualifytype: ".$_POST['qualifytype']; // PERSONAL_SALES = 1, GROUP_SALES = 2, SIGNUP_COUNT = 3, CUSTOMER_SALES = 4, RANK = 5
	$headers[] = "qualifythreshold: ".$_POST['qualifythreshold']; // Dollar amount or number_count depending on the qualify_type
	$headers[] = "achvbonus: ".$_POST['achvbonus'];  // One time dollar amount bonus on rank uplevel // 
	$headers[] = "breakage: ".$_POST['breakage']; // Does the user max out at this level? To start their own branch? Prevent upline from mkaing commissions on them? 
	//$headers[] = "rulegroup: ".$rulegroup; // Group rules together for INCLUSIVE apply //
	//$headers[] = "maxdacleg: ".$maxdacleg; // Necessaty in getting into the higher ranks. Multiple strong legs. Not just one //
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
	if (empty($_POST['breakage']))
		$_POST['breakage'] = "false";
	else
		$_POST['breakage'] = "true";

	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: editrankrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: 1";
	$headers[] = "rankid: ".$_POST['rankid']; // The actual rankid of the database record //
	$headers[] = "rank: ".$_POST['rank']; // What level of rank we want to define for
	$headers[] = "qualifytype: ".$_POST['qualifytype']; // PERSONAL_SALES = 1, GROUP_SALES = 2, SIGNUP_COUNT = 3
	$headers[] = "qualifythreshold: ".$_POST['qualifythreshold']; // Dollar amount or number_count depending on the qualify_type
	$headers[] = "achvbonus: ".$_POST['achvbonus']; // One time dollar amount bonus on rank uplevel // 
	$headers[] = "breakage: ".$_POST['breakage']; // Does the user max out at this level? To start their own branch? Prevent upline from mkaing commissions on them? 
	//$headers[] = "rulegroup: ".$rulegroup; // Group rules together for INCLUSIVE apply //
	//$headers[] = "maxdacleg: ".$maxdacleg; // Necessaty in getting into the higher ranks. Multiple strong legs. Not just one //
	$retdata = PostURL($curlstring, $headers);

	echo "<pre>";
	print_r($retdata);
	echo "</pre>";
}

if ($_POST['command'] == "enablerankrule")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: enablerankrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "rankid: ".$_POST['rankid'];
	$retdata = PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "disablerankrule")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: disablerankrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "rankid: ".$_POST['rankid'];
	$retdata = PostURL($curlstring, $headers)."\n";
}

/////////////////////////////////////////////////////////////
// Always run a query on load of page for most recent view //
/////////////////////////////////////////////////////////////
$curlstring = $coredomain;
$headers = [];
$headers[] = "command: queryrankrules";
$headers[] = "authemail: ".$authemail;
$headers[] = "apikey: ".$apikey;
$headers[] = "systemid: ".$systemid;
$jsonrules = PostURL($curlstring, $headers);

echo "<b>Initial Json Response:</b><br><textarea cols=120 rows=5>".$jsonrules."</textarea><br><br>";

// Build a table display looping through json formatted rules //
$rulearray = json_decode($jsonrules, true);

// Display the fields //
echo "<table border=0 width='100%'>";
echo "<tr>";
echo "<td></td>";
echo "<td align=center><b>ID</b></td>";
echo "<td align=center><b>Rank</b></td>";
echo "<td align=center><b>QualifyType</b></td>";
echo "<td align=center><b>QualifyThreshold</b></td>";
echo "<td align=center><b>AchvBonus</b></td>";
echo "<td align=center><b>Breakage</b></td>";
//echo "<td align=center><b>disabled</b></td>"; // Disable is a completely different command //
echo "<td align=center><b>Created At</b></td>";
echo "<td align=center><b>Updated At</b></td>";
echo "</tr>";

// Loop through each rule //
foreach ($rulearray['rankrules'] as $rule)
{
	$checked = "";
	if ($rule['breakage'] == "t")
		$checked = " checked ";

	echo "<tr bgcolor='90C3D4'>";

	echo "<form method=POST action=''>";
	echo "<input type=hidden name='rankid' value='".$rule['id']."'>";
	if ($rule['disabled'] == 't')
	{
		echo "<input type=hidden name='command' value='enablerankrule'>";
		echo "<td><input type=submit value='Enable'></td>";
	}
	else if ($rule['disabled'] == 'f')
	{
		echo "<input type=hidden name='command' value='disablerankrule'>";
		echo "<td><input type=submit value='Disable'></td>";
	}
	echo "</form>";

	echo "<form method=POST action=''>";
	echo "<input type=hidden name='rankid' value='".$rule['id']."'>";
	echo "<input type=hidden name='direction' value='edit'>";

	echo "<td align=center>".$rule['id']."</td>";
	echo "<td align=center><input size=1 type=edit name='rank' value='".$rule['rank']."'></td>";
	echo "<td align=center>".SelectQualifyType("qualifytype", $rule['qualifytype'])."</td>";
	echo "<td align=center><input size=1 type=edit name='qualifythreshold' value='".$rule['qualifythreshold']."'></td>";
	echo "<td align=center><input size=1 type=edit name='achvbonus' value='".$rule['achvbonus']."'></td>";
	echo "<td align=center><input type=checkbox name='breakage' ".$checked." value='true'></td>";
	echo "<td>".$rule['createdat']."</td>";
	echo "<td>".$rule['updatedat']."</td>";
	echo "<td><input type=submit value='Update'></td>";

	echo "</form>";
}

//////////////////////////
// Display the add form //
//////////////////////////
echo "<form method=POST action=''>";
echo "<input type=hidden name='rankruleid' value='".$rule['id']."'>";
echo "<input type=hidden name='direction' value='add'>";

echo "<tr bgcolor='F58C8C'>";
//echo "<td></td>";
echo "<td></td>";
echo "<td align=center></td>";
echo "<td align=center><input size=1 type=edit name='rank'></td>";
echo "<td align=center>".SelectQualifyType("qualifytype", "")."</td>";
echo "<td align=center><input size=1 type=edit name='qualifythreshold'></td>";
echo "<td align=center><input size=1 type=edit name='achvbonus'></td>";
echo "<td align=center><input type=checkbox name='breakage'></td>";
echo "<td>".$rule['createdat']."</td>";
echo "<td>".$rule['updatedat']."</td>";
echo "<td><input type=submit value='Add'></td>";

echo "</form>";

MenuEnd();

?>