<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.display.php';
include 'includes/inc.select.php';
include 'includes/inc.pagination.php';

SystemSelectedCheck();

$basetype = "rankrule";
$pagvals = PagValidate("rank", "asc");
$json = BuildQueryPage(CLIENT, $basetype, "id", $pagvals);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Edit Rank Rules</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">

	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">
<?php
// Build search parameters //
$fields["id"] = 3;
$fields["label"] = 10;
$fields["rank"] = 3;
$fields["qualifytype"] = "selectqualifytype";
$fields["qualifythreshold"] = 6;
$fields["achvbonus"] = 4;
$fields["breakage"] = 1;
$fields["rulegroup"] = 1;
$fields["createdat"] = "NULL";
$fields["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "ID";
$sort["rank"] = "Rank";
$sort["label"] = "Label";
$sort["qualifytype"] = "Qualify Type";
$sort["qualifythreshold"] = "Threshold";
$sort["achvbonus"] = "Achv Bonus";
$sort["breakage"] = "Breakage";
$sort["rulegroup"] = "Rule Group";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $fields, $pagvals);

// Loop through each rule //
foreach ($json['rankrule'] as $rule)
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

	echo "<td align=center>".$rule['id']."</td>";
	echo "<td align=center>".DispRankRule($rule['rank'])."</td>";
	echo "<td align=center>".$rule['label']."</td>";
	echo "<td>".DispQualifyType($rule['qualifytype'])."</td>";
	echo "<td align=center>".$rule['qualifythreshold']."</td>";
	echo "<td align=center>$".$rule['achvbonus']."</td>";
	echo "<td align=center>".DispBoolY($rule['breakage'])."</td>";
	echo "<td align=center>".$rule['rulegroup']."</td>";
	echo "<td>".DispTimestamp($rule['createdat'])."</td>";
	echo "<td>".DispTimestamp($rule['updatedat'])."</td>";
	
	echo "<form method='POST' action='".$basetype."-add.php?edit=true'>";
	echo "<input type=hidden name='id' value='".$rule['id']."'>";
	echo "<td align=center><input type='submit' value='Edit'></td>";
	echo "</form>";

	echo "</tr>";
}
echo "</table>";

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>