<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';

$fields[] = "systemid";
$headers = BuildHeader(CLIENT, "statssystem", $fields, $pagvals, $_SESSION);
$json = PostURL($headers, "false");

$ledgerbalance = number_format($json['system']['ledgerbalance'], 2);
$commissions = number_format($json['system']['commissions'], 2);
$affiliatecount =  number_format($json['system']['affiliatecount']);
$customercount =  number_format($json['system']['customercount']);
$userscount = number_format($json['system']['affiliatecount']+$json['system']['customercount']);

$bonus =  number_format($json['system']['bonus']);
$signupbonus =  number_format($json['system']['signupbonus']);
$achvbonus =  number_format($json['system']['achvbonus']);

$retail =  number_format($json['system']['retail']);
$wholesale =  number_format($json['system']['wholesale']);

// Why aren't they full width? //

//<div class="x_panel">
//<img src='images/revenue2.png'>
//</div>
echo '<div class="row tile_count">';
echo '	<div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">';
echo '		<span class="count_top"><i class="fa fa-money"></i> Total Ledger Balance</span>';
echo '		<div class="count">$'.$ledgerbalance.'</div>';
echo '	</div>';
echo '	<div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">';
echo '		<span class="count_top"><i class="fa fa-money"></i> Total Commissions</span>';
echo '		<div class="count">$'.$commissions.'</div>';
echo '	</div>';
echo '</div>';


echo '<div class="row tile_count">';
echo '	<div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">';
echo '		<span class="count_top"><i class="fa fa-money"></i> Total Achvievement Bonus</span>';
echo '		<div class="count">$'.$achvbonus.'</div>';
echo '	</div>';
echo '	<div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">';
echo '		<span class="count_top"><i class="fa fa-money"></i> Total Signup Bonus</span>';
echo '		<div class="count">$'.$signupbonus.'</div>';
echo '	</div>';
echo '	<div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">';
echo '		<span class="count_top"><i class="fa fa-money"></i> Total Bonus</span>';
echo '		<div class="count">$'.$bonus.'</div>';
echo '	</div>';
echo '</div>';

echo '<div class="row tile_count">';
echo '	<div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">';
echo '		<span class="count_top"><i class="fa fa-money"></i> Total Wholesale</span>';
echo '		<div class="count">$'.$wholesale.'</div>';
echo '	</div>';
echo '	<div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">';
echo '		<span class="count_top"><i class="fa fa-money"></i> Total Retail</span>';
echo '		<div class="count">$'.$retail.'</div>';
echo '	</div>';
echo '</div>';

echo '<div class="row tile_count">';
echo '	<div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">';
echo '		<span class="count_top"><i class="fa fa-user"></i> Total Users Count</span>';
echo '		<div class="count">'.$userscount.'</div>';
echo '	</div>';
echo '	<div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">';
echo '		<span class="count_top"><i class="fa fa-user"></i> Total Affiliate Count</span>';
echo '		<div class="count">'.$affiliatecount.'</div>';
echo '	</div>';
echo '	<div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">';
echo '		<span class="count_top"><i class="fa fa-user"></i> Total Customer Count</span>';
echo '		<div class="count">'.$customercount.'</div>';
echo '	</div>';
echo '</div>';

//echo '	<div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">';
//echo '		<span class="count_top"><i class="fa fa-male"></i> Total Affiliates</span>';
//echo '		<div class="count">25,000</div>';
//echo '		<span class="count_bottom"><i class="green"><i class="fa fa-sort-asc"></i>3% </i> From last Week</span>';
//echo '	</div>';
//    <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
//        <span class="count_top"><i class="fa fa-child"></i> Total Customers</span>
//        <div class="count">75,000</div>
//        <span class="count_bottom"><i class="red"><i class="fa fa-sort-desc"></i>1% </i> From last Week</span>
//    </div>
echo '</div>';


/*
// Make sure a sytem been created //
$json = BuildAndPOST(CLIENT, "countsystem", "");
if ($json['count'] < 1)
{
	ShowMessage("A system needs to created", "red");
	ShowMessage("<a href='system-add.php'><u>Click here to create a system</u></a>", "blue");
	include 'includes/inc.footer.php';
	return;
}

// Make sure the system has been selected //
if (empty($_SESSION['systemid']))
{
	ShowMessage("A system needs to be selected", "red");
	ShowMessage("<a href='systems-select.php'><u>Click here to select a system</u></a>", "blue");
	include 'includes/inc.footer.php';
	return;
}

// Use pagvals for all //
$pagvals = PagValidate("id", "asc");

// Make sure rankrules have been added //
$basetype = "rankrule";
$json = BuildQueryPage(CLIENT, $basetype, "id", $pagvals);
if ($json['count'] < 1)
{
	ShowMessage("All of your rank rules need to be added", "red");
	ShowMessage("<a href='rankrule-add.php'><u>Click here to add rank rules</u></a>", "blue");
	include 'includes/inc.footer.php';
	return;
}

// Make sure commrules have been added //
$basetype = "commrule";
$json = BuildAndPOST(CLIENT, "querycommrule", $fields, $pagvals);
if ($json['count'] < 1)
{
	ShowMessage("All of your commission rules need to be added", "red");
	ShowMessage("<a href='commrule-add.php'><u>Click here to add commission rules</u></a>", "blue");
	include 'includes/inc.footer.php';
	return;
}

// Check batches on simulation server //

$json = BuildAndPOST(CLIENT, "querybatches", $fields, $pagvals);
if ($json['count'] < 1)
{
	ShowMessage("Typically running a simualtion is a good idea after your rank and commissions rules have been defined", "red");
	ShowMessage("<a href='simulation-enable.php?simulations=true'><u>Click here enable simulations</u></a>", "blue");
	include 'includes/inc.footer.php';
	return;
}
*/
// Simulations //

// Count users - Seed or copy //

// Count receipts - Seed or copy //

// Run simulation //

// Audit Reports //

// Authorize Payments //

// Process Payments //

/*

Simple wizard to walk customer through system. Such as first system creation<br>
<br>
Finish last of pagination
<br>
REPORTS - User reports from united to give better audit information<br>
<br>
DELETE records - Allow before first commission run to allow tweaking? More customer friendly<br>
ARCHIVE records - Edit display based on enable/disable flag?<br>
<br>
Allow ce_users and not just ce_systemusers to log into the system and have a small list of tools. Predict payout. Review commissions paid<br>
<br>
Allow customer to pay extra for their own dedicated server?<br>
Function to backup/restore and delete everyone else's data out<br>
Keeps ce_users records in memory to make split second response on commission prediction (Unicity fix)<br>
<br>
Test better way of spawning multi-thread on API connections till limit is hit (from settings.ini file)<br>
<br>
Allow customer IP filtering??? Help reduce hacking?<br>
*/

include 'includes/inc.footer.php';

?>
