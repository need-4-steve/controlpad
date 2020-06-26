<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.affiliate.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

SystemSelectedCheck();
$_POST["userid"] = $_SESSION['user_id'];
$fields[] = "userid";
$pagvals = PagValidate("id", "desc");
$json = BuildAndPOST(AFFILIATE, "mybonus", $fields, $pagvals);
HandleResponse($json, SUCCESS_NOTHING);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>My Bonus</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">

<?php
// Build search parameters //
$size["id"] = 3;
$size["userid"] = 3;
$size["amount"] = 3;
$size["bonusdate"] = "NULL";
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "Bonus ID";
$sort["userid"] = "User ID";
$sort["amount"] = "Amount";
$sort["bonusdate"] = "Bonus Date";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['bonus'] as $bonus)
{
	echo "<tr>";
	echo "<td></td>";
	echo "<td align=center>".$bonus['id']."</td>";
	echo "<td align=center>".$bonus['userid']."</td>";
	echo "<td align=center>$".number_format($bonus['amount'], 2)."</td>";
	echo "<td align=center>".DispDate($bonus['bonusdate'])."</td>";
	echo "<td>".DispTimestamp($bonus['createdat'])."</td>";
	echo "<td>".DispTimestamp($bonus['updatedat'])."</td>";

	echo "</tr>";
}

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>