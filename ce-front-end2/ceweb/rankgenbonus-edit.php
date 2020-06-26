<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

SystemSelectedCheck();
$basetype = "rankgenbonusrule";
$pagvals = PagValidate("id", "asc");
$json = BuildQueryPage(CLIENT, $basetype, "id", $pagvals);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Edit Rank Gen Bonus</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">

<?php
// Build search parameters //
$size["id"] = "NULL";
$size["myrank"] = 3;
$size["userrank"] = 3;
$size["generation"] = 3;
$size["bonus"] = 5;
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "ID";
$sort["myrank"] = "My Rank";
$sort["userrank"] = "User Rank";
$sort["generation"] = "Generation";
$sort["bonus"] = "Bonus";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['rankgenbonusrules'] as $bonus)
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
	echo "<td align=center>".$bonus['myrank']."</td>";
	echo "<td align=center>".$bonus['userrank']."</td>";
	echo "<td align=center>".$bonus['generation']."</td>";
	echo "<td align=center>$".$bonus['bonus']."</td>";
	echo "<td>".DispTimestamp($bonus['createdat'])."</td>";
	echo "<td>".DispTimestamp($bonus['updatedat'])."</td>";

	echo "<form method=POST action='rankgenbonus-add.php?edit=true'>";
	echo "<td align=center><input type='submit' value='Edit'></td>";
	echo "<input type=hidden name='id' value='".$bonus['id']."'>";
	echo "</form>";

	echo "</tr>";
}

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>