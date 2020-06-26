<?php

include "includes/inc.ce-comm.php";
include 'includes/inc.header.php';
include 'includes/inc.display.php';
include 'includes/inc.select.php';
include 'includes/inc.pagination.php';

$basetype = "system";
$pagvals = PagValidate("id", "asc");
$json = BuildQueryPage(CLIENT, $basetype, "id", $pagvals);

if (empty($json['count']))
{
	$text = "<b><u><a href='system-add.php'>A system needs to be created</a></u></b>";
	ShowMessage($text, "blue");
	include 'includes/inc.footer.php';
	exit();
}

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Select Systems</small></h2>
<div class="clearfix"></div>
</div>

<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">
<?php
// Build search parameters //
$size["id"] = 3;
$size["systemname"] = 10;
$size["commtype"] = "selectcommtype";
$size["disabled"] = 1;
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "System ID";
$sort["systemname"] = "System Name";
$sort["commtype"] = "Comm Type";
$sort["disabled"] = "Disabled";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['system'] as $system)
{
	echo "<tr>";
	echo "<td></td>";
	echo "<td align=center>".$system['id']."</td>";
	echo "<td align=center>".$system['systemname']."</td>";
	echo "<td align=center>".DispCommType($system['commtype'])."</td>";
	echo "<td align=center>".DispBoolY($system['disabled'])."</td>";
	echo "<td>".DispTimestamp($system['createdat'])."</td>";
	echo "<td>".DispTimestamp($system['updatedat'])."</td>";

	echo "<form method='POST' action=''>";
	echo "<td align=center><input type='submit' value='Select'></td>";
	echo "<input type=hidden name='direction' value='selected'>";
	echo "<input type=hidden name='systemid' value='".$system['id']."'>";
	echo "<input type=hidden name='systemname' value='".$system['systemname']."'>";
	echo "<input type=hidden name='commtype' value='".$system['commtype']."'>";
	echo "</form>";

	echo "</tr>";
}
echo "</table>";

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>