<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.select.php';
include 'includes/inc.display.php';
include 'includes/inc.pagination.php';

SystemSelectedCheck();

// Handle parsing for pagenation //
$pagvals = PagValidate("id", "asc");

// Handle forced pool selection //
if (is_numeric($_SESSION['poolid']))
{
	ShowBannerMessage("Pool Selected: ".$_SESSION['poolid'], "white", "black");
}
else
{
	ShowMessage("A pool needs to be selected", "red");
	ShowMessage("<a href='pool-select.php'><u>Click here to select a pool</u></a>", "blue");
	include 'includes/inc.footer.php';
	return;
}

$_POST["poolid"] = $_SESSION['poolid'];
$fields[] = "poolid";
$basetype = "poolrule";
$json = BuildQueryPage(CLIENT, $basetype, "id", $pagvals, $fields);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Edit Pool Rules</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">

<?php
// Build search parameters //
$size["id"] = 3;
$size["startrank"] = 3;
$size["endrank"] = 3;
$size["qualifytype"] = "selectqualifytype";
$size["qualifythreshold"] = 8;
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "ID";
$sort["startrank"] = "Start Rank";
$sort["endrank"] = "End Rank";
$sort["qualifytype"] = "QualifyType";
$sort["qualifythreshold"] = "QualifyThreshold";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['poolrule'] as $poolrule)
{
	echo "<tr>";

	// Disable/Enable //
	if ($poolrule['disabled'] == "t")
	{
		echo "<form method=POST action='".$basetype."-edit.php?direction=enable'>";
		echo "<input type=hidden name='id' value='".$poolrule['id']."'>";
		echo "<td align=center><input type=submit value='Enable'></td>";
		echo "</form>";
	}
	else if ($poolrule['disabled'] == "f")
	{
		echo "<form method=POST action='".$basetype."-edit.php?direction=disable'>";
		echo "<input type=hidden name='id' value='".$poolrule['id']."'>";
		echo "<td align=center><input type=submit value='Disable'></td>";
		echo "</form>";
	}

	echo "<td align=center>".$poolrule['id']."</td>";
	echo "<td align=center>".$poolrule['startrank']."</td>";
	echo "<td align=center>".$poolrule['endrank']."</td>";
	echo "<td align=center>".DispQualifyType($poolrule['qualifytype'])."</td>";
	echo "<td align=center>".$poolrule['qualifythreshold']."</td>";
	echo "<td>".DispTimestamp($poolrule['createdat'])."</td>";
	echo "<td>".DispTimestamp($poolrule['updatedat'])."</td>";

	echo "<form method=POST action='".$basetype."-add.php?edit=true'>";
	echo "<td align=center><input type='submit' value='Edit'></td>";
	echo "<input type=hidden name='id' value='".$poolrule['id']."'>";
	echo "</form>";

	echo "</tr>";
}

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>