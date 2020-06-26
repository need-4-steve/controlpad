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
$pagvals = PagValidate("userid", "asc");
//$pagvals['qstring'] .= "&generation=".$_GET['generation'];

/////////////////////////////////////////////////////////////
// Always run a query on load of page for most recent view //
/////////////////////////////////////////////////////////////
$json = BuildAndPOST(CLIENT, "queryrankrulemissed", $fields, $pagvals);

// Display the fields //
?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Rank Rules Missed View</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">

<?php
// Build search parameters //
$size["batchid"] = 5;
$size["id"] = 5;
$size["userid"] = 5;
$size["ruleid"] = 5;
$size["rank"] = 5;
$size["qualifytype"] = 5;
$size["qualifythreshold"] = 5;
$size["actualvalue"] = 5;
$size["diff"] = 5;
$size["createdat"] = "NULL";

// Build Sort Parameters //
$sort["batchid"] = "Batch ID";
$sort["id"] = "ID";
$sort["userid"] = "User ID";
$sort["ruleid"] = "Rule ID";
$sort["rank"] = "Rank";
$sort["qualifytype"] = "Qualify Type";
$sort["qualifythreshold"] = "Qualify Threshold";
$sort["actualvalue"] = "Actual";
$sort["diff"] = "Difference";
$sort["createdat"] = "Created";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['rankrulesmissed'] as $record)
{
	echo "<tr>";
	echo "<td></td>";
	//echo "<td align=center>".$breakdown['systemid']."</td>";
	echo "<td align=center>".$record['batchid']."</td>";	
	echo "<td align=center>".$record['id']."</td>";
	echo "<td align=center>".$record['firstname']." ".$record['lastname']." (".$record['userid'].")</td>";
	echo "<td align=center>".$record['ruleid']."</td>";
	echo "<td align=center>".$record['rank']."</td>";
	echo "<td align=center>".$record['qualifytype']."</td>";
	echo "<td align=center>".$record['qualifythreshold']."</td>";
	echo "<td align=center>".$record['actualvalue']."</td>";
	echo "<td align=center>".$record['diff']."</td>";
	echo "<td>".DispTimestamp($record['createdat'])."</td>";
	echo "</tr>";
}
echo "</table>";

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>