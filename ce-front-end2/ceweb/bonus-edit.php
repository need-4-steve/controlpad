<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

SystemSelectedCheck();
$basetype = "bonus";
$pagvals = PagValidate("userid", "asc");
$json = BuildQueryPage(CLIENT, $basetype, "id", $pagvals);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Edit Bonus</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">

<?php
// Build search parameters //
$size["id"] = 3;
$size["userid"] = 3;
$size["amount"] = 3;
$size["bonusdate"] = "NULL";
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "Bonus ID";
$sort["userid"] = "User ID";
$sort["amount"] = "Amount";
$sort["bonusdate"] = "Bonus Date";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['bonus'] as $bonus)
{
	echo "<tr>";

	// Disable/Enable //
	if ($bonus['disabled'] == "t")
	{
		echo "<form method=POST action='bonus-edit.php?direction=enable'>";
		echo "<input type=hidden name='id' value='".$bonus['id']."'>";
		echo "<td align=center><input type=submit value='Enable'></td>";
		echo "</form>";
	}
	else if ($bonus['disabled'] == "f")
	{
		echo "<form method=POST action='bonus-edit.php?direction=disable'>";
		echo "<input type=hidden name='id' value='".$bonus['id']."'>";
		echo "<td align=center><input type=submit value='Disable'></td>";
		echo "</form>";
	}

	echo "<td align=center>".$bonus['id']."</td>";
	echo "<td align=center>".$bonus['userid']."</td>";
	echo "<td align=center>$".$bonus['amount']."</td>";
	echo "<td align=center>".DispDate($bonus['bonusdate'])."</td>";
	echo "<td>".DispTimestamp($bonus['createdat'])."</td>";
	echo "<td>".DispTimestamp($bonus['updatedat'])."</td>";

	echo "<form method=POST action='bonus-add.php?edit=true'>";
	echo "<td align=center><input type='submit' value='Edit'></td>";
	echo "<input type=hidden name='id' value='".$bonus['id']."'>";
	echo "</form>";

	echo "</tr>";
}

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>