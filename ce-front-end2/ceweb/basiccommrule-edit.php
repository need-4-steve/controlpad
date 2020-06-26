<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.display.php';
include 'includes/inc.pagination.php';
include 'includes/inc.select.php';

SystemSelectedCheck();

$basetype = "basiccommrule";
$pagvals = PagValidate("id", "asc");
$json = BuildQueryPage(CLIENT, $basetype, "id", $pagvals);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Edit Basic Commission Rules</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">
<?php
// Build search parameters //
$size["id"] = 3;
$size["generation"] = 3;
$size["qualifytype"] = "selectqualifytype";
$size["startthreshold"] = 3;
$size["endthreshold"] = 3;
$size["invtype"] = "selectinvtype";
$size["event"] = "selectevent";
$size["percent"] = 3;
$size["modulus"] = 3;
$size["paytype"] = 3;
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "ID";
$sort["generation"] = "Generation";
$sort["qualifytype"] = "Qualify Type";
$sort["startthreshold"] = "Start Threshold";
$sort["endthreshold"] = "End Threshold";
$sort["invtype"] = "Inventory Type";
$sort["event"] = "Event";
$sort["percent"] = "Percent";
$sort["modulus"] = "Modulus";
$sort["paytype"] = "Paytype";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['basiccommrule'] as $rule)
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
	echo "<td align=center>".$rule['generation']."</td>";
	echo "<td align=center>".DispQualifyType($rule['qualifytype'])."</td>";
	echo "<td align=center>".$rule['startthreshold']."</td>";
	echo "<td align=center>".$rule['endthreshold']."</td>";
	echo "<td align=center>".DispInvType($rule['invtype'])."</td>";
	echo "<td align=center>".DispEvent($rule['event'])."</td>";
	echo "<td align=center>".$rule['percent']."%</td>";
	echo "<td align=center>".$rule['modulus']."</td>";
	echo "<td align=center>".DispPayType($rule['paytype'])."</td>";
	echo "<td>".DispTimestamp($rule['createdat'])."</td>";
	echo "<td>".DispTimestamp($rule['updatedat'])."</td>";

	echo "<td align=center><input type=submit value='Edit'></td>";

	echo "</form>";
}

PagBottom($pagvar, $json['count']);

include 'includes/inc.footer.php';

?>