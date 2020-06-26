<?php

include "includes/inc.comm.php";

echo "<h2 align=center>Commission Rules</h2>";
MenuStart();
$systemid = $_SESSION['systemid'];

/////////////////////////////////
// Handle adding of a rankrule //
/////////////////////////////////
if ($_POST['direction'] == 'add')
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: addcommrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "rank: ".$_POST['rank'];
	$headers[] = "startgen: ".$_POST['startgen'];
	$headers[] = "endgen: ".$_POST['endgen'];
	$headers[] = "qualifytype: ".$_POST['qualifytype'];
	$headers[] = "qualifythreshold: ".$_POST['qualifythreshold'];	
	$headers[] = "infinitybonus: false";
	$headers[] = "percent: ".$_POST['percent'];
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
	$headers[] = "command: editcommrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "commruleid: ".$_POST['commruleid'];
	$headers[] = "rank: ".$_POST['rank'];
	$headers[] = "startgen: ".$_POST['startgen'];
	$headers[] = "endgen: ".$_POST['endgen'];
	$headers[] = "qualifytype: ".$_POST['qualifytype'];
	$headers[] = "qualifythreshold: ".$_POST['qualifythreshold'];	
	$headers[] = "infinitybonus: false";
	$headers[] = "percent: ".$_POST['percent'];
	$retdata = PostURL($curlstring, $headers);

	echo "<pre>";
	print_r($retdata);
	echo "</pre>";
}

if ($_POST['command'] == "disablecommrule")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: disablecommrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "commruleid: ".$_POST['commruleid'];
	$retdata = PostURL($curlstring, $headers)."\n";
}

if ($_POST['command'] == "enablecommrule")
{
	$curlstring = $coredomain;
	$headers = [];
	$headers[] = "command: enablecommrule";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "commruleid: ".$_POST['commruleid'];
	$retdata = PostURL($curlstring, $headers)."\n";
}

/////////////////////////////////////////////////////////////
// Always run a query on load of page for most recent view //
/////////////////////////////////////////////////////////////
$curlstring = $coredomain;
$headers = [];
$headers[] = "command: querycommrules";
$headers[] = "authemail: ".$authemail;
$headers[] = "apikey: ".$apikey;
$headers[] = "systemid: ".$systemid;
$jsonrules = PostURL($curlstring, $headers);

echo "<b>Initial Json Response:</b><br><textarea cols=120 rows=5>".$jsonrules."</textarea><br><br>";
$rulearray = json_decode($jsonrules, true);

// Display the fields //
echo "<table border=0 width='100%'>";
echo "<tr>";
echo "<td></td>";
echo "<td align=center><b>ID</b></td>";
echo "<td align=center><b>Rank</b></td>";
echo "<td align=center><b>StartGen</b></td>";
echo "<td align=center><b>EndGen</b></td>";
echo "<td align=center><b>QualifyType</b></td>";
echo "<td align=center><b>QualifyThreshold</b></td>";
echo "<td align=center><b>Percent</b></td>";
echo "<td align=center><b>Disabled</b></td>"; // Disable is a completely different command //
echo "<td align=center><b>Created At</b></td>";
echo "<td align=center><b>Updated At</b></td>";
echo "</tr>";

// Loop through each rule //
foreach ($rulearray['commrules'] as $rule)
{
	if ($rule['breakage'] == "t")
		$checked = " checked ";

	echo "<tr bgcolor='90C3D4'>";

	echo "<form method=POST action=''>";
	echo "<input type=hidden name='commruleid' value='".$rule['id']."'>";
	if ($rule['disabled'] == 't')
	{
		echo "<input type=hidden name='command' value='enablecommrule'>";
		echo "<td><input type=submit value='Enable'></td>";
	}
	else if ($rule['disabled'] == 'f')
	{
		echo "<input type=hidden name='command' value='disablecommrule'>";
		echo "<td><input type=submit value='Disable'></td>";
	}
	echo "</form>";

	echo "<form method=POST action=''>";
	echo "<input type=hidden name='commruleid' value='".$rule['id']."'>";
	echo "<input type=hidden name='direction' value='edit'>";

	echo "<td align=center>".$rule['id']."</td>";
	echo "<td align=center>".SelectRank("rank", $rule['rank'])."</td>";
	echo "<td align=center><input size=1 type=edit name='startgen' value='".$rule['startgen']."'></td>";
	echo "<td align=center><input size=1 type=edit name='endgen' value='".$rule['endgen']."'></td>";
	echo "<td align=center>".SelectQualifyType("qualifytype", $rule['qualifytype'])."</td>";
	echo "<td align=center><input size=1 type=edit name='qualifythreshold' value='".$rule['qualifythreshold']."'></td>";
	echo "<td align=center><input size=1 type=edit name='percent' value='".$rule['percent']."'></td>";
	echo "<td align=center>".$rule['disabled']."</td>";
	echo "<td>".$rule['createdat']."</td>";
	echo "<td>".$rule['updatedat']."</td>";
	echo "<td><input type=submit value='Update'></td>";

	echo "</form>";
}

//////////////////////////
// Display the add form //
//////////////////////////
echo "<form method=POST action=''>";
echo "<input type=hidden name='commruleid' value='".$rule['id']."'>";
echo "<input type=hidden name='direction' value='add'>";

echo "<tr bgcolor='F58C8C'>";
echo "<td></td>";
echo "<td></td>";
echo "<td align=center><input size=1 type=edit name='startrank'></td>";
echo "<td align=center><input size=1 type=edit name='endrank'></td>";
echo "<td align=center><input size=1 type=edit name='startgen'></td>";
echo "<td align=center><input size=1 type=edit name='endgen'></td>";
echo "<td align=center>".SelectQualifyType("qualifytype", "")."</td>";
echo "<td align=center><input size=1 type=edit name='qualifythreshold'></td>";
echo "<td align=center><input size=1 type=edit name='percent'></td>";
echo "<td align=center></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td><input type=submit value='Add'></td>";

echo "</form>";

MenuEnd();

?>