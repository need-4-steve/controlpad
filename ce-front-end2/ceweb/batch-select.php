<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

SystemSelectedCheck();

if ($_POST['batchfind'] == "selected")
{
	if (empty($_POST['batchid']))
		$error = ShowError("There batchid is empty");
	else if (is_numeric($_POST['batchid']) == false)
		$error = ShowError("The batchid is invalid");
	else
		$_SESSION['batchid'] = $_POST['batchid'];
}

// Handle query //
$pagvals = PagValidate("id", "desc");
$json = BuildAndPOST(CLIENT, "querybatches", $fields, $pagvals);
HandleResponse($json, SUCCESS_NOTHING);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>View Batches</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">

<?php
// Build search parameters //
$size["id"] = 3;
$size["startdate"] = "NULL";
$size["enddate"] = "NULL";
$size["receipts"] = 5;
$size["commissions"] = 5;
$size["achvbonuses"] = 5;
$size["bonuses"] = 5;
$size["pools"] = 5;
$size["percent"] = "NULL";
$size["createdat"] = "NULL";
//$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "Batch ID";
$sort["startdate"] = "StartDate";
$sort["enddate"] = "EndDate";
$sort["receipts"] = "Receipts";
$sort["commissions"] = "Commissions";
$sort["achvbonuses"] = "Achv Bonuses";
$sort["bonuses"] = "Bonuses";
$sort["pools"] = "Pools";
$sort["percent"] = "Percent";
$sort["createdat"] = "Created";
//$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

//setlocale(LC_MONETARY, 'en_US');

// Loop through each rule //
foreach ($json['batches'] as $batch)
{
	if (empty($batch['bonuses']))
		$batch['bonuses'] = "0";

	$percent = round(($batch['commissions']+$batch['achvbonuses']+$batch['bonuses'])/$batch['receiptswholesale']*100, 2);

	echo "<tr>";
	echo "<td></td>";
	echo "<td align=center>".$batch['id']."</td>";
	echo "<td align=center>".DispDate($batch['startdate'])."</td>";
	echo "<td align=center>".DispDate($batch['enddate'])."</td>";
	echo "<td align=center>$".number_format($batch['receiptswholesale'])."</td>";
	echo "<td align=center>$".number_format($batch['commissions'])."</td>";
	echo "<td align=center>$".number_format($batch['achvbonuses'])."</td>";
	echo "<td align=center>$".number_format($batch['bonuses'])."</td>";
	echo "<td align=center>$".number_format($batch['pools'])."</td>";
	echo "<td align=center>".$percent."%</td>";
	echo "<td align=center>".DispTimestamp($batch['createdat'])."</td>";
	//echo "<td align=center>".DispTimestamp($batch['updatedat'])."</td>";
	
	echo "<form method='POST' action=''>";
	echo "<td align=center><input type='submit' value='Select'></td>";
	echo "<input type='hidden' name='batchid' value='".$batch['id']."'>";
	echo "<input type='hidden' name='batchfind' value='selected'>";
	echo "</form>";

	echo "</tr>";
}

echo "</table>";

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>