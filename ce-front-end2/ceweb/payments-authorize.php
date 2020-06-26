<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

SystemSelectedCheck();

if (empty($_POST['authorizedset']))
	$_POST['authorizedset'] = "false";

//////////////////////////////
// Authorize single payment //
//////////////////////////////
if ($_POST['direction'] == "authorize")
{
	if ($_POST['authorized'] == true)
		$record_type = AUTHORIZED_RECORD;
	else if ($_POST['authorized'] == false)
		$record_type = UNAUTHORIZED_RECORD;

	$fields[] = "id";
	$fields[] = "authorized";
	$json = BuildAndPOST(CLIENT, "authgrandpayout", $fields, $_POST);
	if (HandleResponse($json, $record_type) == false)
	{
		$values = CopyArrayValues($fields, $_POST);
	}
}

///////////////////////////////
// Authorize bulk everything //
///////////////////////////////
if ($_POST['direction'] == "bulkauthorize")
{
	$fields[] = "authorized";
	$json = BuildAndPOST(CLIENT, "authgrandbulk", $fields, $_POST);
	if (HandleResponse($json, BULK_AUTHORIZED) == false)
	{
		$values = CopyArrayValues($fields, $_POST);
	}
}

////////////////////
// Handle Disable //
////////////////////
if ($_POST['direction'] == 'disable')
{
	$fields[] = "id";
	$json = BuildAndPOST(CLIENT, "disablegrandpayout", $fields, $_POST);
	if (HandleResponse($json, DISABLE_RECORD) == false)
	{
		$values = CopyArrayValues($fields, $_POST);
	}
}

////////////////////
// Handle Enable //
////////////////////
if ($_POST['direction'] == 'enable')
{
	$fields[] = "id";
	$json = BuildAndPOST(CLIENT, "enablegrandpayout", $fields, $_POST);
	if (HandleResponse($json, ENABLE_RECORD) == false)
	{
		$values = CopyArrayValues($fields, $_POST);
	}
}

// Handle query //
$pagvals = PagValidate("amount", "desc");
$fields[] = "authorized";
$_POST["authorized"] = $_POST['authorizedset'];
$json = BuildAndPOST(CLIENT, "querygrandpayout", $fields, $pagvals);
HandleResponse($json, SUCCESS_NOTHING);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
<?php
if ($_POST['authorizedset'] == "false")
	echo "<h2>Authorize Payments</small></h2>";
else if ($_POST['authorizedset'] == "true")
	echo "<h2>UnAuthorize Payments</small></h2>";
?>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">

<?php
// Build search parameters //
$size["id"] = 5;
$size["userid"] = 5;
$size["amount"] = 5;
$size["authorized"] = 5;
$size["disabled"] = 5;
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "ID";
$sort["userid"] = "User ID";
$sort["amount"] = "Amount";
$sort["authorized"] = "Authorized";
$sort["disabled"] = "Disabled";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['grandtotals'] as $grand)
{
	echo "<tr>";
	
	if ($grand['disabled'] == "f")
	{
		echo "<form method=POST action=''>";
		echo "<input type='hidden' name='authorizedset' value='".$_POST['authorizedset']."'>";
		echo "<input type='hidden' name='direction' value='disable'>";
		echo "<input type='hidden' name='id' value='".$grand['id']."'>";
		echo "<td align=center><input type='submit' value='Disable'></td>";
		echo "</form>";
	}
	else if ($grand['disabled'] == "t")
	{
		echo "<form method=POST action=''>";
		echo "<input type='hidden' name='authorizedset' value='".$_POST['authorizedset']."'>";
		echo "<input type='hidden' name='direction' value='enable'>";
		echo "<input type='hidden' name='id' value='".$grand['id']."'>";
		echo "<td align=center><input type='submit' value='Enable'></td>";
		echo "</form>";
	}

	echo "<td align=center>".$grand['id']."</td>";
	echo "<td align=center>".$grand['firstname']." ".$grand['lastname']." (".$grand['userid'].")</td>";
	echo "<td align=center>$".$grand['amount']."</td>";
	echo "<td align=center>".DispBoolN($grand['authorized'])."</td>";
	echo "<td align=center>".DispBoolY($grand['disabled'])."</td>";
	echo "<td><a href='".$link."'>".DispTimestamp($grand['createdat'])."</td>";
	echo "<td><a href='".$link."'>".DispTimestamp($grand['updatedat'])."</td>";
	
	// Handle Authorized/UnAuthorize payment of each record //
	if ($_POST['authorizedset'] == "false")
	{
		echo "<form method=POST action=''>";
		echo "<input type='hidden' name='authorizedset' value='false'>";
		echo "<input type='hidden' name='direction' value='authorize'>";
		echo "<input type='hidden' name='authorized' value='true'>";
		echo "<input type='hidden' name='id' value='".$grand['id']."'>";
		echo "<td align=center><input type='submit' value='Authorize'></td>";
		echo "</form>";
	}
	else if ($_POST['authorizedset'] == "true")
	{
		echo "<form method=POST action=''>";
		echo "<input type='hidden' name='authorizedset' value='true'>";
		echo "<input type='hidden' name='direction' value='authorize'>";
		echo "<input type='hidden' name='authorized' value='false'>";
		echo "<input type='hidden' name='id' value='".$grand['id']."'>";
		echo "<td align=center><input type='submit' value='UnAuthorize'></td>";
		echo "</form>";
	}

	echo "</tr>";
}

echo "</table>";

PagBottom($pagvals, $json['count']);

echo "<br>";
echo "<table>";
echo "<tr><td>";

if ($_POST['authorizedset'] == "false")
{
	echo "<form method=POST action=''>";
	echo "<input type='hidden' name='authorizedset' value='true'>";
	echo "<td align=center><input type='submit' value='Switch View Authorized'></td>";
	echo "</form>";
}
else
{
	echo "<form method=POST action=''>";
	echo "<input type='hidden' name='authorizedset' value='false'>";
	echo "<td align=center><input type='submit' value='Switch View UnAuthorized'></td>";
	echo "</form>";
}
echo "</td>";

echo "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";

echo "<td>";
if ($_POST['authorizedset'] == "false")
{
	echo "<form method=POST action=''>";
	echo "<input type='hidden' name='direction' value='bulkauthorize'>";
	echo "<input type='hidden' name='authorize' value='true'>";
	echo "<td align=center><input type='submit' value='Bulk Authorize'></td>";
	echo "</form>";
}
else
{
	echo "<form method=POST action=''>";
	echo "<input type='hidden' name='direction' value='bulkauthorize'>";
	echo "<input type='hidden' name='authorize' value='false'>";
	echo "<td align=center><input type='submit' value='Bulk UnAuthorize'></td>";
	echo "</form>";
}
echo "</td></tr>";

include 'includes/inc.footer.php';

?>