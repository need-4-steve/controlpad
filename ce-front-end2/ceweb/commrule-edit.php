<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.display.php';
include 'includes/inc.pagination.php';
include 'includes/inc.select.php';

SystemSelectedCheck();

$basetype = "commrule";
$pagvals = PagValidate("id", "asc");
$json = BuildQueryPage(CLIENT, $basetype, "id", $pagvals);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Edit Commission Rules</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">
<?php
// Build search parameters //
$size["id"] = 3;
$size["rank"] = 3;
$size["generation"] = 3;
//$size["qualifytype"] = 1;
//$size["qualifythreshold"] = 12;
$size["percent"] = 3;
$size["invtype"] = "selectinvtype";
$size["event"] = "selectevent";
$size["paytype"] = "NULL";
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "ID";
$sort["rank"] = "Rank";
$sort["generation"] = "Generation";
//$sort["qualifytype"] = "Qualify Type";
//$sort["qualifythreshold"] = "Qualify Threshold";
$sort["percent"] = "Percent";
$sort["invtype"] = "Inventory Type";
$sort["event"] = "Event";
$sort["paytype"] = "Paytype";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['commrule'] as $rule)
{
	echo "<tr>";

	// Disable/Enable //
	if ($rule['disabled'] == "t")
	{
		echo "<form method=POST action='".$basetype."-edit.php?direction=enable'>";
		echo "<input type=hidden name='id' value='".$rule['id']."'>";
		echo "<td align=center><input type=submit value='Enable'></td>";
		echo "</form>";
	}
	else if ($rule['disabled'] == "f")
	{
		echo "<form method=POST action='".$basetype."-edit.php?direction=disable'>";
		echo "<input type=hidden name='id' value='".$rule['id']."'>";
		echo "<td align=center><input type=submit value='Disable'></td>";
		echo "</form>";
	}

	echo "<form method=POST action='".$basetype."-add.php?edit=true'>";
	echo "<input type=hidden name='id' value='".$rule['id']."'>";

	echo "<td align=center>".$rule['id']."</td>";
	echo "<td align=center>".$rule['rank']."</td>";
	echo "<td align=center>".DispGeneration($rule['generation'])."</td>";
	//echo "<td align=center>".$rule['end_gen']."</td>";

	if ($_SESSION['commtype'] == 3) // Binary System Commission Type //
	{
		echo "<td align=center>".$rule['qualify_type']."</td>";
		echo "<td align=center>".$rule['qualify_threshold']."</td>";
	}
	
	echo "<td align=center>".$rule['percent']."%</td>";
	echo "<td align=center>".DispInvType($rule['invtype'])."</td>";
	echo "<td align=center>".DispEvent($rule['event'])."</td>";
	echo "<td align=center>".DispPayType($rule['paytype'])."</td>";
	echo "<td>".DispTimestamp($rule['createdat'])."</td>";
	echo "<td>".DispTimestamp($rule['updatedat'])."</td>";


	echo "<td align=center><input type=submit value='Edit'></td>";

	echo "</form>";
}

PagBottom($pagvar, $json['count']);

include 'includes/inc.footer.php';

?>