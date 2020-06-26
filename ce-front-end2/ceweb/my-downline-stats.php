<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.affiliate.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';
include 'includes/inc.select.php';

SystemSelectedCheck();

// Handle Default Batch ID //
if(!isset($_SESSION['batchid']))
{
	$_SESSION['batchid'] = DefaultBatch();
}

// Handle query //
$_POST["userid"] = $_SESSION['user_id'];
$_POST['search-batchid'] = $_SESSION['batchid'];
$fields[] = "userid";
$pagvals = PagValidate("id", "desc");
$json = BuildAndPOST(AFFILIATE, "mydownstats", $fields, $pagvals);
HandleResponse($json, SUCCESS_NOTHING);

?>
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Downline Team Volume</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">

<?php

// Build search parameters //
//$size["id"] = 5;
//$size["systemid"] = 5;
$size["userid"] = 5;
$size["teamandmywholesale"] = 5;
//$size["groupwholesalesales"] = 5;
//$size["customerwholesalesales"] = 5;
//$size["affiliatewholesalesales"] = 5;
//$size["resellerwholesalesales"] = 5;
//$size["signupcount"] = 5;
//$size["customercount"] = 5;
//$size["affiliatecount"] = 5;
$size["resellercount"] = 5;
//$size["createdat"] = "NULL";
//$size["updatedat"] = "NULL";

// Build Sort Parameters //
//$sort["id"] = "ID";
//$sort["systemid"] = "System ID";
$sort["userid"] = "Designer ID";
$sort["teamandmywholesale"] = "Team Volume";
//$sort["groupwholesalesales"] = "All Wholesale";
//$sort["customerwholesalesales"] = "Customer Wholesale";
//$sort["affiliatewholesalesales"] = "Affiliate Wholesale";
//$sort["resellerwholesalesales"] = "Reseller Wholesale";
//$sort["signupcount"] = "All Count";
//$sort["customercount"] = "Customer Count";
//$sort["affiliatecount"] = "Affiliate Count";
$sort["resellercount"] = "Enterprise Designers";
//$sort["createdat"] = "Created";
//$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['userstats'] as $stats)
{
	echo "<tr>";
	echo "<td></td>";
	//echo "<td align=center>".$stats['id']."</td>";
	//echo "<td align=center>".$stats['systemid']."</td>";
	echo "<td align=center>".$stats['firstname']." ".$stats['lastname']." (".$stats['userid'].")</td>";
	echo "<td align=center>$".number_format($stats['teamandmywholesale'], 2)."</td>";
	//echo "<td align=center>$".number_format($stats['groupwholesalesales'], 2)."</td>";
	//echo "<td align=center>$".number_format($stats['customerwholesalesales'], 2)."</td>";
	//echo "<td align=center>$".number_format($stats['affiliatewholesalesales'], 2)."</td>";
	//echo "<td align=center>$".number_format($stats['resellerwholesalesales'], 2)."</td>";
	//echo "<td align=center>".$stats['signupcount']."</td>";
	//echo "<td align=center>".$stats['customercount']."</td>";
	//echo "<td align=center>".$stats['affiliatecount']."</td>";
	echo "<td align=center>".$stats['resellercount']."</td>";
	//echo "<td align=center>".DispTimestamp($stats['createdat'])."</td>";
	//echo "<td align=center>".DispTimestamp($stats['updatedat'])."</td>";
	echo "<td></td>";
	echo "</tr>";
}

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>
