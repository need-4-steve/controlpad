<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

SystemSelectedCheck();

// Handle query //
$pagvals = PagValidate("id", "desc");
$json = BuildAndPOST(CLIENT, "queryranks", $fields, $pagvals);
HandleResponse($json, SUCCESS_NOTHING);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>View Ranks</small></h2>
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
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "ID";
$sort["systemid"] = "System ID";
$sort["batchid"] = "Batch ID";
$sort["userid"] = "User ID";
$sort["rank"] = "Rank";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

//setlocale(LC_MONETARY, 'en_US');

// Loop through each rule //
foreach ($json['ranks'] as $rank)
{
	
	echo "<tr>";
	echo "<td></td>";
	echo "<td align=center>".$rank['id']."</td>";
	echo "<td align=center>".$rank['systemid']."</td>";
	echo "<td align=center>".$rank['batchid']."</td>";
	echo "<td align=center>".$rank['firstname']." ".$rank['lastname']." (".$rank['userid'].")</td>";
	echo "<td align=center>".$rank['rank']."</td>";
	echo "<td align=center>".DispTimestamp($rank['createdat'])."</td>";
	echo "<td align=center>".DispTimestamp($rank['updatedat'])."</td>";
	echo "</tr>";
}

echo "</table>";

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>