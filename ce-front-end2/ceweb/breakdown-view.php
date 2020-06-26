<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.display.php';
include 'includes/inc.select.php';
include 'includes/inc.pagination.php';

SystemSelectedCheck();

//Pre($_GET);
//$_GET['search-generation'] = 1;
//$_POST['search-generation'] = 1;


// Set default system for now //
$systemid = $_SESSION['systemid'];

// Handle parsing for pagenation //
$pagvals = PagValidate("userid");
$pagvals['qstring'] .= "&generation=".$_GET['generation'];

/////////////////////////////////////////////////////////////
// Always run a query on load of page for most recent view //
/////////////////////////////////////////////////////////////
$json = BuildAndPOST(CLIENT, "querybreakdown", $fields, $pagvals);

// Display the fields //
?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Breakdown View</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">

<?php
// Build search parameters //
$size["batchid"] = 5;
$size["id"] = 5;
$size["receiptid"] = 5;
$size["userid"] = 5;
$size["amount"] = 5;
$size["commruleid"] = 5;
$size["generation"] = 5;
$size["percent"] = 5;
$size["infinitybonus"] = 5;
$size["receiptidinternal"] = 5;
$size["metadataonadd"] = 5;
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["batchid"] = "Batch ID";
$sort["id"] = "ID";
$sort["receiptid"] = "Receipt ID";
$sort["userid"] = "User ID";
$sort["amount"] = "Amount";
$sort["commruleid"] = "Comm Rule ID";
$sort["generation"] = "Generation";
$sort["percent"] = "Percent";
$sort["infinitybonus"] = "Infinity Bonus";
$sort["receiptidinternal"] = "Receipt ID Internal";
$sort["metadataonadd"] = "Metadata OnAdd";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['breakdown'] as $breakdown)
{
	echo "<tr>";
	echo "<td></td>";
	//echo "<td align=center>".$breakdown['systemid']."</td>";
	echo "<td align=center>".$breakdown['batchid']."</td>";	
	echo "<td align=center>".$breakdown['id']."</td>";
	echo "<td align=center>".$breakdown['receiptid']."</td>";
	echo "<td align=center>".$breakdown['firstname']." ".$breakdown['lastname']." (".$breakdown['userid'].")</td>";
	
	//$sumamount += $breakdown['amount'];
	echo "<td align=center>$".$breakdown['amount']."</td>"; //<br><i>$".$sumamount."</i></td>";

	echo "<td align=center>".$breakdown['commruleid']."</td>";
	echo "<td align=center>".$breakdown['generation']."</td>";
	echo "<td align=center>".$breakdown['percent']."%</td>";
	echo "<td align=center>".DispBoolY($breakdown['infinitybonus'])."</td>";
	echo "<td align=center>".$breakdown['receiptidinternal']."</td>";
	echo "<td align=center>".$breakdown['metadataonadd']."</td>";
	echo "<td>".DispTimestamp($breakdown['createdat'])."</td>";
	echo "<td>".DispTimestamp($breakdown['updatedat'])."</td>";
	echo "</tr>";
}

//echo "<tr><td colspan=5></td><td align=center><b>$".$sumamount."</b></td><td colspan=6></td></tr>";

echo "</table>";

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>