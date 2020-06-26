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
	<h2>Edit Systems</small></h2>
<div class="clearfix"></div>
</div>

<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">
<?php
// Build search parameters //
$size["id"] = 3;
$size["systemname"] = 10;
$size["commtype"] = "selectcommtype";
$size["payouttype"] = "selectpayouttype";
$size["autoauthgrand"] = 3;
$size["infinitycap"] = 3;
$size["minpay"] = 3;
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "System ID";
$sort["systemname"] = "System Name";
$sort["commtype"] = "Comm Type";
$sort["payouttype"] = "Payout Type";
$sort["autoauthgrand"] = "Auto Authorize";
$sort["infinitycap"] = "Infinity Cap";
$sort["minpay"] = "Min Pay";
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
	//echo "<td align=center>".$system['stacktype']."</td>";
	echo "<td align=center>".DispCommType($system['commtype'])."</td>";
	echo "<td align=center>".DispPayoutType($system['payouttype'])."</td>";
	echo "<td align=center>".DispBoolYN($system['autoauthgrand'])."</td>";
	echo "<td align=center>".$system['infinitycap']."</td>";

	if (!empty($system['minpay']))
		echo "<td align=center>$".$system['minpay']."</td>";
	else
		echo "<td align=center>".$system['minpay']."</td>";
	//echo "<td align=center>".$system['updatedurl']."</td>";
	//echo "<td align=center>".$system['updatedusername']."</td>";
	//echo "<td align=center>".$system['updatedpassword']."</td>";
	//echo "<td align=center>".DispBoolY($system['disabled'])."</td>";
	echo "<td>".DispTimestamp($system['createdat'])."</td>";
	echo "<td>".DispTimestamp($system['updatedat'])."</td>";

	echo "<form method='POST' action='system-add.php?edit=true&systemid=".$system['id']."'>";
	echo "<td align=center><input type='submit' value='Edit'></td>";
	echo "</form>";

	echo "</tr>";
}
echo "</table>";

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>