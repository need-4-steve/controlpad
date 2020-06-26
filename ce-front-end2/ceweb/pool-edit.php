<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.select.php';
include 'includes/inc.display.php';

SystemSelectedCheck();
$basetype = "pool";
$pagvals = PagValidate("startdate", "desc");
$json = BuildQueryPage(CLIENT, $basetype, "id", $pagvals);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Edit Pools</small></h2>
<div class="clearfix"></div>
</div>

<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">
<?php
// Build search parameters //
$size["id"] = 3;
$size["amount"] = 3;
$size["startdate"] = "NULL";
$size["enddate"] = "NULL";
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "ID";
$sort["amount"] = "Amount";
$sort["startdate"] = "Start Date";
$sort["enddate"] = "End Date";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['pool'] as $pool)
{
	echo "<tr>";

	// Disable/Enable //
	if ($pool['disabled'] == "t")
	{
		echo "<form method=POST action='pools-edit.php?direction=enable'>";
		echo "<input type=hidden name='id' value='".$pool['id']."'>";
		echo "<td align=center><input type=submit value='Enable'></td>";
		echo "</form>";
	}
	else if ($pool['disabled'] == "f")
	{
		echo "<form method=POST action='pools-edit.php?direction=disable'>";
		echo "<input type=hidden name='id' value='".$pool['id']."'>";
		echo "<td align=center><input type=submit value='Disable'></td>";
		echo "</form>";
	}

	echo "<td align=center>".$pool['id']."</td>";
	echo "<td align=center>$".$pool['amount']."</td>";
	echo "<td align=center>".DispDate($pool['startdate'])."</td>";
	echo "<td align=center>".DispDate($pool['enddate'])."</td>";
	echo "<td>".DispTimestamp($pool['createdat'])."</td>";
	echo "<td>".DispTimestamp($pool['updatedat'])."</td>";

	echo "<form method=POST action='pool-add.php?edit=true'>";
	echo "<td align=center><input type='submit' value='Edit'></td>";
	echo "<input type=hidden name='id' value='".$pool['id']."'>";
	echo "</form>";

	echo "</tr>";
}

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>