<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.affiliate.php';
include 'includes/inc.header.php';
include 'includes/inc.display.php';
include 'includes/inc.select.php';
include 'includes/inc.pagination.php';

SystemSelectedCheck();

// Set default system for now //
$systemid = $_SESSION['systemid'];

// Handle parsing for pagenation //
$pagvals = PagValidate("id", "desc");
$pagvals['qstring'] .= "&receiptid=".$_GET['receiptid'];

/////////////////////////////////////////////////////////////
// Always run a query on load of page for most recent view //
/////////////////////////////////////////////////////////////
$_POST["userid"] = $_SESSION['user_id'];
$fields[] = "userid";
$json = BuildAndPOST(AFFILIATE, "mybreakdown", $fields, $pagvals);

// Display the fields //
?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>My Receipt Breakdown</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">

<?php
// Build search parameters //
$size["id"] = 5;
$size["batchid"] = 5;
$size["receiptid"] = 5;
$size["amount"] = 5;
$size["commruleid"] = 5;
$size["generation"] = 5;
$size["percent"] = 5;
$size["infinitybonus"] = 5;
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "ID";
$sort["batchid"] = "Batch ID";
$sort["receiptid"] = "Receipt ID";
$sort["amount"] = "Amount";
$sort["commruleid"] = "Comm Rule ID";
$sort["generation"] = "Generation";
$sort["percent"] = "Percent";
$sort["infinitybonus"] = "Infinity Bonus";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['breakdown'] as $breakdown)
{
	echo "<tr>";
	echo "<td></td>";
	//echo "<td align=center>".$breakdown['systemid']."</td>";
	echo "<td align=center>".$breakdown['id']."</td>";
	echo "<td align=center>".$breakdown['batchid']."</td>";	
	echo "<td align=center>".$breakdown['receiptid']."</td>";
	echo "<td align=center>$".$breakdown['amount']."</td>";
	echo "<td align=center>".$breakdown['commruleid']."</td>";
	echo "<td align=center>".$breakdown['generation']."</td>";
	echo "<td align=center>".$breakdown['percent']."%</td>";
	echo "<td align=center>".DispBoolY($breakdown['infinitybonus'])."</td>";
	echo "<td>".DispTimestamp($breakdown['createdat'])."</td>";
	echo "<td>".DispTimestamp($breakdown['updatedat'])."</td>";
	echo "</tr>";
}
echo "</table>";

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>