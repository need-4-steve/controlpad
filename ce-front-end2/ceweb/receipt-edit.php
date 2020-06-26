<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.pagination.php';

// Needs to be here before we put anything on the page //
if ($_POST['direction'] == "downloadcsv")
{
    ini_set('memory_limit', '512M');
    // Chalk - Fatal error:  Allowed memory size of 134217728 bytes exhausted

    // Grab the csv file //
    $basetype = "receipt";
	$pagvals = PagValidate("wholesaleprice", "desc");
	$pagvals['limit'] = "999999999999"; // 1 less of a trillion //
	$json = BuildQueryPage(CLIENT, $basetype, "receiptid", $pagvals);

    if ($json['count'] <= 0)
    {
        // Flag error below //
        $error = true;
    } 
    else
    {
        $currentdate = date("m-d-Y");
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename=downline-report-'.$currentdate.'.csv');
        header('Pragma: no-cache');

        // Build Sort Parameters //
        echo "ID,";
        echo "ReceiptID,";
        echo "UserID,";
        echo "Firstname,";
        echo "Lastname,";
        echo "InvType,";
        echo "WholesalePrice,";
        echo "WholesaleDate,";
        echo "RetailPrice,";
        echo "RetailDate,";
        echo "Commissionable,";
        echo "MetadataOnadd,";
        echo "MetadataOnupdate,";
        echo "Disabled,";
        echo "CreatedAt,";
        echo "UpdatedAt";
        echo "\n";

        // Loop through each rule //
		foreach ($json['receipt'] as $receipt)
		{
			echo '"'.$receipt['id'].'",';
			echo '"'.$receipt['receiptid'].'",';
			echo '"'.$receipt['userid'].'",';
			echo '"'.$receipt['firstname'].'",';
			echo '"'.$receipt['lastname'].'",';
			echo '"'.$receipt['invtype'].'",';
			echo '"'.$receipt['wholesaleprice'].'",';
			echo '"'.$receipt['wholesaledate'].'",';
			echo '"'.$receipt['retailprice'].'",';
			echo '"'.$receipt['retaildate'].'",';
			echo '"'.$receipt['commissionable'].'",';
			echo '"'.$receipt['metadataonadd'].'",';
			echo '"'.$receipt['metadataonupdate'].'",';
			echo '"'.$receipt['disabled'].'",';
			echo '"'.$receipt['createdat'].'",';
			echo '"'.$receipt['updatedat'].'"';
			echo "\n";		    
		}
	}
	
    return;
}

include 'includes/inc.header.php';
include 'includes/inc.display.php';
include 'includes/inc.select.php';

SystemSelectedCheck();

$basetype = "receipt";
$pagvals = PagValidate("wholesaleprice", "desc");
$json = BuildQueryPage(CLIENT, $basetype, "receiptid", $pagvals);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Edit Receipts</small></h2>
	<div align=right>
        <form method="POST" action="">
        <input type="submit" value="Download csv">
        <input type="hidden" name="direction" value="downloadcsv"> 
        </form>
    </div>
<div class="clearfix"></div>
</div>

<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">
<?php
// Build search parameters //
$size["id"] = 3;
$size["receiptid"] = 3;
$size["userid"] = 3;
//$size["usertype"] = 3;
$size["wholesaleprice"] = 6;
$size["retailprice"] = 6;
$size["wholesaledate"] = "NULL";
$size["retaildate"] = "NULL";
$size["invtype"] = "selectinvtype";
$size["metadataonadd"] = 6;
$size["commissionable"] = 1;
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "ID";
$sort["receiptid"] = "Receipt ID";
$sort["userid"] = "User ID";
//$sort["usertype"] = "UserType";
$sort["wholesaleprice"] = "Wholesale";
$sort["retailprice"] = "Retail";
$sort["wholesaledate"] = "Wholesale Date";
$sort["retaildate"] = "Retail Date";
$sort["invtype"] = "Inventory Type";
$sort["metadataonadd"] = "MetaDataOnAdd";
$sort["commissionable"] = "Commissionable";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['receipt'] as $receipt)
{
	echo "<tr>";

	// Disable/Enable //
	if ($receipt['disabled'] == "t")
	{
		echo "<form method=POST action='".$basetype."-edit.php?direction=enable'>";
		echo "<input type=hidden name='id' value='".$receipt['id']."'>";
		echo "<td align=center><input type=submit value='Enable'></td>";
		echo "</form>";
	}
	else if ($receipt['disabled'] == "f")
	{
		echo "<form method=POST action='".$basetype."-edit.php?direction=disable'>";
		echo "<input type=hidden name='id' value='".$receipt['id']."'>";
		echo "<td align=center><input type=submit value='Disable'></td>";
		echo "</form>";
	}

	$fullwholesale += $receipt['wholesaleprice'];
	$fullretail += $receipt['retailprice'];

	echo "<td align=center>".$receipt['id']."</td>";
	echo "<td align=center>".$receipt['receiptid']."</td>";
	echo "<td align=center>".$receipt['firstname']." ".$receipt['lastname']." (".$receipt['userid'].")</td>";
	//echo "<td align=center>".$receipt['usertype']."</td>";
	
	//echo "<td align=center>$".number_format($receipt['wholesaleprice'], 2)."<br><i>".$fullwholesale."</i></td>";
	//echo "<td align=center>$".number_format($receipt['retailprice'], 2)."<br><i>".$fullretail."</i></td>";
	echo "<td align=center>$".number_format($receipt['wholesaleprice'], 2)."</td>";
	echo "<td align=center>$".number_format($receipt['retailprice'], 2)."</td>";

	echo "<td align=center>".DispDate($receipt['wholesaledate'])."</td>";
	echo "<td align=center>".DispDate($receipt['retaildate'])."</td>";
	echo "<td align=center>".DispInvType($receipt['invtype'])."</td>";
	echo "<td align=center>".$receipt['metadataonadd']."</td>";
	echo "<td align=center>".DispBoolYN($receipt['commissionable'])."</td>";
	echo "<td>".DispTimestamp($receipt['createdat'])."</td>";
	echo "<td>".DispTimestamp($receipt['updatedat'])."</td>";
	
	echo "<form method=POST action='".$basetype."-add.php?edit=true'>";
	echo "<td align=center><input type='submit' value='Edit'></td>";
	echo "<input type=hidden name='id' value='".$receipt['id']."'>";
	echo "</form>";

	echo "<form method=POST action='breakdown-view.php?search-receiptid=".$receipt['receiptid']."'>";
	echo "<td><input type=submit value='Breakdown'></td></tr>";
	echo "</form>";

	echo "</tr>";
}

//echo "<tr><td colspan=4><td align=center><b>$".number_format($fullwholesale, 2)."</b></td><td align=center><b>$".number_format($fullretail, 2)."</b></td><td colspan=9></td></tr>";

echo "</table>";

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>