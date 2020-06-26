<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

SystemSelectedCheck();

// Handle query //
$pagvals = PagValidate("id", "desc");
$json = BuildAndPOST(CLIENT, "queryuserstats", $fields, $pagvals);
HandleResponse($json, SUCCESS_NOTHING);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>View Users Stats</small></h2>
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
$size["group_wholesale_sales"] = 5;
$size["customer_wholesale_sales"] = 5;
$size["affiliate_wholesale_sales"] = 5;
$size["reseller_wholesale_sales"] = 5;
$size["signupcount"] = 5;
$size["customercount"] = 5;
$size["affiliatecount"] = 5;
$size["resellercount"] = 5;
$size["createdat"] = "NULL";
//$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "ID";
$sort["systemid"] = "System ID";
$sort["batchid"] = "Batch ID";
$sort["userid"] = "User ID";
$sort["group_wholesale_sales"] = "All Wholesale";
$sort["customer_wholesale_sales"] = "Customer Wholesale";
$sort["affiliate_wholesale_sales"] = "Affiliate Wholesale";
$sort["reseller_wholesale_sales"] = "Reseller Wholesale";
$sort["signupcount"] = "Signup Count";
$sort["customercount"] = "Customer Count";
$sort["affiliatecount"] = "Affiliate Count";
$sort["resellercount"] = "Reseller Count";
$sort["createdat"] = "Created";
//$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['userstats'] as $stats)
{
	echo "<tr>";
	echo "<td></td>";
	echo "<td align=center>".$stats['id']."</td>";
	echo "<td align=center>".$stats['systemid']."</td>";
	echo "<td align=center>".$stats['batchid']."</td>";
	echo "<td align=center>".$stats['firstname']." ".$stats['lastname']." (".$stats['userid'].")</td>";
	echo "<td align=center>$".number_format($stats['groupwholesalesales'])."</td>";
	echo "<td align=center>$".number_format($stats['customerwholesalesales'])."</td>";
	echo "<td align=center>$".number_format($stats['affiliatewholesalesales'])."</td>";
	echo "<td align=center>$".number_format($stats['resellerwholesalesales'])."</td>";
	echo "<td align=center>".$stats['signupcount']."</td>";
	echo "<td align=center>".$stats['customercount']."</td>";
	echo "<td align=center>".$stats['affiliatecount']."</td>";
	echo "<td align=center>".$stats['resellercount']."</td>";	
	echo "<td align=center>".DispTimestamp($stats['createdat'])."</td>";
	//echo "<td align=center>".DispTimestamp($stats['updatedat'])."</td>";
	echo "</tr>";
}

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>