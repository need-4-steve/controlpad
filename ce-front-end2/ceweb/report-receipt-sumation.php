<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.display.php';
include 'includes/inc.select.php';
include 'includes/inc.pagination.php';

SystemSelectedCheck();

$pagvals = PagValidate("count", "desc");
$json = BuildAndPOST(CLIENT, "queryreceiptsum", "", $pagvals);
HandleResponse($json, SUCCESS_NOTHING);
?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>View Receipt Sumation</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">

<?php
// Build search parameters //
$size["id"] = 5;
$size["userid"] = 5;
$size["amount"] = 5;
$size["count"] = 5;
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "ID";
$sort["userid"] = "User ID";
$sort["amount"] = "Amount";
$sort["count"] = "Count";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['receipts'] as $user)
{
	echo "<tr>";
	echo "<td></td>";
	echo "<td align=center>".$user['id']."</td>";
	echo "<td align=center>".$user['firstname']." ".$user['lastname']." (".$user['userid'].")</td>";
	echo "<td align=center>$".$user['amount']."</td>";
	echo "<td align=center>".$user['count']."</td>";
	echo "<td align=center>".DispTimestamp($user['createdat'])."</td>";
	echo "<td align=center>".DispTimestamp($user['updatedat'])."</td>";
	
	echo "</tr>";
}

echo "</table>";

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>