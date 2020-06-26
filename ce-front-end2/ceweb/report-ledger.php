<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.display.php';
include 'includes/inc.select.php';
include 'includes/inc.pagination.php';
include 'includes/inc.convert.php';

SystemSelectedCheck();

// Handle query //
$pagvals = PagValidate("id", "desc");
$json = BuildAndPOST(CLIENT, "queryledger", $fields, $pagvals);
HandleResponse($json, SUCCESS_NOTHING);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>View Ledger</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">

<?php
// Build search parameters //
$size["id"] = 5;
$size["systemid"] = 5;
$size["batchid"] = 5;
$size["ref_id"] = 5;
$size["userid"] = 5;
$size["ledgertype"] = "selectledgertype";
$size["amount"] = 5;
$size["event_date"] = "NULL";
$size["createdat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "ID";
$sort["systemid"] = "System ID";
$sort["batchid"] = "Batch ID";
$sort["ref_id"] = "Ref ID";
$sort["userid"] = "User ID";
$sort["ledgertype"] = "Ledger Type";
$sort["amount"] = "Amount";
$sort["event_date"] = "Event Date";
$sort["createdat"] = "Created";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['ledger'] as $ledger)
{
	echo "<tr>";
	echo "<td></td>";
	echo "<td align=center>".$ledger['id']."</td>";
	echo "<td align=center>".$ledger['systemid']."</td>";
	echo "<td align=center>".$ledger['batchid']."</td>";
	echo "<td align=center>".$ledger['refid']."</td>";
	echo "<td align=center>".$ledger['firstname']." ".$ledger['lastname']." (".$ledger['userid'].")</td>";
	echo "<td align=center>".DispLedgerType($ledger['ledgertype'])."</td>";
	echo "<td align=center>$".PerfectCents($ledger['amount'])."</td>";
	echo "<td align=center>".DispDate($ledger['eventdate'])."</td>";
	//echo "<td align=center>".$ledger['generation']."</td>";
	//echo "<td align=center>".$ledger['authorized']."</td>";	
	//echo "<td align=center>".$ledger['disabled']."</td>";
	echo "<td align=center>".DispTimestamp($ledger['createdat'])."</td>";
	//echo "<td align=center>".$ledger['updatedat']."</td>";

	echo "<form method=POST action='ledger-add.php?edit=true'>";
	echo "<td align=center><input type='submit' value='Edit'></td>";
	echo "<input type=hidden name='id' value='".$ledger['id']."'>";
	echo "</form>";
}

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>