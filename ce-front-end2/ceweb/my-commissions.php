<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.affiliate.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

SystemSelectedCheck();

// Handle query //
$_POST["userid"] = $_SESSION['user_id'];
$fields[] = "userid";
$pagvals = PagValidate("id", "desc");
$json = BuildAndPOST(AFFILIATE, "mycommissions", $fields, $pagvals);
HandleResponse($json, SUCCESS_NOTHING);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>My Commissions</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">

<?php
// Build search parameters //
$size["id"] = 5;
$size["systemid"] = 5;
$size["batchid"] = 5;
$size["userid"] = 5;
$size["amount"] = 5;
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "ID";
$sort["systemid"] = "System ID";
$sort["batchid"] = "Batch ID";
$sort["userid"] = "User ID";
$sort["amount"] = "Amount";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['commissions'] as $commission)
{
	echo "<tr>";
	echo "<td></td>";
	echo "<td align=center>".$commission['id']."</td>";
	echo "<td align=center>".$commission['systemid']."</td>";
	echo "<td align=center>".$commission['batchid']."</td>";
	echo "<td align=center>".$commission['userid']."</td>";
	echo "<td align=center>$".number_format($commission['amount'], 2)."</td>";
	//echo "<td align=center>".$commission['disabled']."</td>";
	echo "<td align=center>".DispTimestamp($commission['createdat'])."</td>";
	echo "<td align=center>".DispTimestamp($commission['updatedat'])."</td>";
	echo "</tr>";
}

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>