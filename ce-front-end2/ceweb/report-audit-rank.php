<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

SystemSelectedCheck();

// Handle query //
$_POST["batchid"] = $_SESSION['batchid'];
$fields[] = "batchid";
$json = BuildAndPOST(CLIENT, "queryauditranks", $fields, $pagvals);
HandleResponse($json, SUCCESS_NOTHING);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Rank Audit Report - Batch: <?=$_SESSION['batchid']?></small></h2>	
<div class="clearfix"></div>
</div>
<div class="x_content">
    
   	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">
<?php

// First addup to grandtotal //
foreach ($json['auditranks'] as $value)
{
	$grandtotal += $value['total'];
}

echo "<tr><td align=center></td><td align=center><b>Payout</b></td><td align=center><b>Payout Percent</b></td></tr>";
foreach ($json['auditranks'] as $value)
{
	echo "<tr>";
	echo "<td align=center><b>Rank ".$value['rank']."</b></td>";
	echo "<td align=center>$".number_format($value['total'], 2)."</td>";
	echo "<td align=center>".round($value['total']/$grandtotal*100)."%</td>";
	echo "</tr>";
}
echo "</table>";


include 'includes/inc.footer.php';

?>