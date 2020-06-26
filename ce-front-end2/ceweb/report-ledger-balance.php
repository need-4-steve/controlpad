<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

SystemSelectedCheck();

$pagvals = PagValidate("amount", "desc");
$json = BuildAndPOST(CLIENT, "queryledgerbalance", "", $pagvals);
HandleResponse($json, SUCCESS_NOTHING);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>View Ledger Balance</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">

<?php
// Build search parameters //
$size["id"] = 5;
$size["userid"] = 5;
$size["amount"] = 5;
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "ID";
$sort["userid"] = "User ID";
$sort["amount"] = "Amount";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['ledger'] as $user)
{
	echo "<tr>";
	echo "<td></td>";
	echo "<td align=center>".$user['id']."</td>";
	echo "<td align=center>".$user['firstname']." ".$user['lastname']." (".$user['userid'].")</td>";
	echo "<td align=center>$".$user['amount']."</td>";
	echo "<td>".DispTimestamp($user['createdat'])."</td>";
	echo "<td>".DispTimestamp($user['updatedat'])."</td>";
	
	echo "</tr>";
}

echo "</table>";

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>