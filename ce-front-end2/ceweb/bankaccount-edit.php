<?php

include "includes/inc.ce-comm.php";
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';
include 'includes/inc.validate.php';

SystemSelectedCheck();

if (!empty($_POST['direction']))
{
	if (!empty($_POST['userid']) && (is_userid($_POST['userid']) == false))
		$error = ShowError("There userid is invalid");
}

$pagvals = PagValidate("user_id", "desc");

if ($error != true)
{
	////////////////////
	// Handle Disable //
	////////////////////
	if ($_GET['direction'] == 'disable')
	{
		$fields[] = "userid";
		$json = BuildAndPOST(CLIENT, "disablebankaccount", $fields, "");
		if (HandleResponse($json, DISABLE_RECORD) == false)
		{
			$values = CopyArrayValues($fields, $_POST);
		}
	}

	///////////////////
	// Handle Enable //
	///////////////////
	if ($_GET['direction'] == 'enable')
	{
		$fields[] = "userid";
		$json = BuildAndPOST(CLIENT, "enablebankaccount", $fields, "");
		if (HandleResponse($json, ENABLE_RECORD) == false)
		{
			$values = CopyArrayValues($fields, $_POST);
		}
	}

	////////////////////////////////////////////////
	// Handle initiate validation of bank account //
	////////////////////////////////////////////////
	if ($_POST['initiatevalidation'] == "true")
	{
		$fields[] = "userid";
		$headers = BuildHeader(CLIENT, "initiatevalidation", $fields, $pagvals, $_POST);
		$json = PostURL($headers, "false");
		HandleResponse($json, SUCCESS_NOTHING);
	}
}

// Handle query //
$json = BuildAndPOST(CLIENT, "querybankaccounts", $fields, $pagvals);
HandleResponse($json, SUCCESS_NOTHING);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Edit Bank Accounts</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">

<?php
// Build search parameters //
$size["id"] = 5;
$size["userid"] = 5;
$size["accounttype"] = 5;
$size["routingnumber"] = 5;
$size["accountnumber"] = 5;
$size["holdername"] = 5;
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "ID";
$sort["userid"] = "User ID";
$sort["accounttype"] = "AccountType";
$sort["routingnumber"] = "Routing";
$sort["accountnumber"] = "Account";
$sort["holdername"] = "Holder Name";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['bankaccount'] as $account)
{
	echo "<tr>";

	// Disable/Enable //
	if ($account['disabled'] == "t")
	{
		echo "<form method=POST action='?direction=enable'>";
		echo "<input type=hidden name='userid' value='".$account['userid']."'>";
		echo "<td align=center><input type=submit value='Enable'></td>";
		echo "</form>";
	}
	else if ($account['disabled'] == "f")
	{
		echo "<form method=POST action='?direction=disable'>";
		echo "<input type=hidden name='userid' value='".$account['userid']."'>";
		echo "<td align=center><input type=submit value='Disable'></td>";
		echo "</form>";
	}

	//echo "<td align=center>".$account['systemid']."</td>";
	echo "<td align=center>".$account['id']."</td>";
	echo "<td align=center>".$account['firstname']." ".$account['lastname']." (".$account['userid'].")</td>";
	echo "<td align=center>".DispBankAccountType($account['accounttype'])."</td>";
	echo "<td align=center>".$account['routingnumber']."</td>";
	echo "<td align=center>".$account['accountnumber']."</td>";
	echo "<td align=center>".$account['holdername']."</td>";
	echo "<td>".DispTimestamp($account['createdat'])."</td>";
	echo "<td>".DispTimestamp($account['updatedat'])."</td>";

	echo "<form method='POST' action='bankaccount-add.php'>";
	echo "<input type='hidden' name='edit' value='true'>";
	echo "<input type='hidden' name='userid' value='".$account['userid']."'>";
	echo "<td><input type='submit' value='Edit'></td>";
	echo "</form>";

	echo "<form method='POST' action=''>";
	echo "<input type='hidden' name='initiatevalidation' value='true'>";
	echo "<input type='hidden' name='userid' value='".$account['userid']."'>";
	echo "<td><input type='submit' value='Initiate Validation'></td>";
	echo "</form>";

	if ($account['validated'] == "f")
	{
		echo "<form method='POST' action='bankaccount-validate.php'>";
		echo "<input type='hidden' name='userid' value='".$account['userid']."'>";
		echo "<td><input type='submit' value='Validate'></td>";
		echo "</form>";
	}

	echo "</tr>";
}
echo "</table>";

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>