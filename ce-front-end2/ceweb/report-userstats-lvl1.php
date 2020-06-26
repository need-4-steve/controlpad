<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

SystemSelectedCheck();

// Handle query //
$pagvals = PagValidate("id", "desc");
$json = BuildAndPOST(CLIENT, "queryuserstatslvl1", $fields, $pagvals);
HandleResponse($json, SUCCESS_NOTHING);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>View Users Stats Level 1</small></h2>
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
$size["mywholesalesales"] = 5;
$size["myretailsales"] = 5;
$size["personalsales"] = 5;
$size["signupcount"] = 5;
$size["affiliatecount"] = 5;
$size["resellercount"] = 5;
$size["customercount"] = 5;
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "ID";
$sort["systemid"] = "System ID";
$sort["batchid"] = "Batch ID";
$sort["userid"] = "User ID";
$sort["mywholesalesales"] = "My Wholesale";
$sort["myretailsales"] = "My Retail";
$sort["personalsales"] = "Personal Sales";
$sort["signupcount"] = "Signup Count";
$sort["affiliatecount"] = "Affiliate Count";
$sort["resellercount"] = "Reseller Count";
$sort["customercount"] = "Customer Count";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['userstatslvl1'] as $stats)
{
	echo "<tr>";
	echo "<td></td>";
	echo "<td align=center>".$stats['id']."</td>";
	echo "<td align=center>".$stats['systemid']."</td>";
	echo "<td align=center>".$stats['batchid']."</td>";
	echo "<td align=center>".$stats['firstname']." ".$stats['lastname']." (".$stats['userid'].")</td>";
	echo "<td align=center>$".$stats['mywholesalesales']."</td>";
	echo "<td align=center>$".$stats['myretailsales']."</td>";
	echo "<td align=center>$".number_format($stats['personalsales'])."</td>";
	echo "<td align=center>".$stats['signupcount']."</td>";
	echo "<td align=center>".$stats['affiliatecount']."</td>";
	echo "<td align=center>".$stats['resellercount']."</td>";
	echo "<td align=center>".$stats['customercount']."</td>";
	echo "<td align=center>".DispTimestamp($stats['createdat'])."</td>";
	echo "<td align=center>".DispTimestamp($stats['updatedat'])."</td>";
	echo "</tr>";
}

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>