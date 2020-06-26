<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

SystemSelectedCheck();

// Handle query //
$pagvals = PagValidate("user_id", "asc");
$json = BuildAndPOST(CLIENT, "queryachvbonus", $fields, $pagvals);
HandleResponse($json, SUCCESS_NOTHING);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>View Achvievement Bonuses</small></h2>
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
$size["rank"] = 5;
$size["rankruleid"] = 5;
$size["amount"] = 5;
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "ID";
$sort["systemid"] = "System ID";
$sort["batchid"] = "Batch ID";
$sort["userid"] = "User ID";
$sort["rank"] = "Rank";
$sort["rankruleid"] = "RankRule ID";
$sort["amount"] = "Amount";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['achvbonus'] as $bonus)
{
	echo "<tr>";
	echo "<td></td>";
	echo "<td align=center>".$bonus['id']."</td>";
	echo "<td align=center>".$bonus['systemid']."</td>";
	echo "<td align=center>".$bonus['batchid']."</td>";
	echo "<td align=center>".$bonus['firstname']." ".$bonus['lastname']." (".$bonus['userid'].")</td>";
	echo "<td align=center>".$bonus['rank']."</td>";
	echo "<td align=center>".$bonus['rankruleid']."</td>";
	echo "<td align=center>$".number_format($bonus['amount'], 2)."</td>";
	echo "<td align=center>".DispTimestamp($bonus['createdat'])."</td>";
	echo "<td align=center>".DispTimestamp($bonus['updatedat'])."</td>";
}

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>