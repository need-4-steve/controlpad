<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.pagination.php';

// Needs to be here before we put anything on the page //
if ($_POST['direction'] == "downloadcsv")
{
    ini_set('memory_limit', '512M');
    // Chalk - Fatal error:  Allowed memory size of 134217728 bytes exhausted

    // Grab the csv file //
    //$basetype = "receipt";
	//$pagvals = PagValidate("wholesaleprice", "desc");
	//$pagvals['limit'] = "999999999999"; // 1 less of a trillion //
	//$json = BuildQueryPage(CLIENT, $basetype, "receiptid", $pagvals);

    $fields = array();
    $fields[] = "batchid";
    $fields[] = "userid";

    $values['batchid'] = $_SESSION['batchid'];
    $values['userid'] = $_POST['search-userid'];

    $pagvals = PagValidate("sum", "desc");
    $headers = BuildHeader(CLIENT, "ordersumreceiptwhole", $fields, $pagvals, $values);

    $json = PostURL($headers, "false");

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
        echo "UserID,";
        echo "Order,";
        echo "Sum";
        echo "\n";

        // Loop through each rule //
		foreach ($json['orders'] as $receipt)
		{
			echo '"'.$receipt['userid'].'",';
			echo '"'.$receipt['order'].'",';
			echo '"'.$receipt['sum'].'"';
			echo "\n";		    
		}
	}
	
    return;
}

include 'includes/inc.header.php';
include 'includes/inc.display.php';
include 'includes/inc.select.php';

SystemSelectedCheck();

$fields = array();
$fields[] = "batchid";
$fields[] = "userid";

$values['batchid'] = $_SESSION['batchid'];
$values['userid'] = $_POST['search-userid'];

$pagvals = PagValidate("sum", "desc");
$headers = BuildHeader(CLIENT, "ordersumreceiptwhole", $fields, $pagvals, $values);

$json = PostURL($headers, "false");

//Pre($json);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Receipt Orders Report</small></h2>
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
$size["userid"] = 3;
$size["metadataonadd"] = "NULL";
$size["wholesale"] = "NULL";
$size["retail"] = "NULL";
$size["invtype"] = "NULL";
$size["qty"] = "NULL";

// Build Sort Parameters //
$sort["userid"] = "User ID";
$sort["metadataonadd"] = "Order Number";
$sort["wholesale"] = "Wholesale";
$sort["retail"] = "Retail";
$sort["invtype"] = "InvType";
$sort["qty"] = "QTY";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['orders'] as $order)
{
	echo "<tr>";

	echo "<td></td>";

	echo "<td align=center>".$order['firstname']." ".$order['lastname']." (".$order['userid'].")</td>";
	echo "<td align=center>".$order['order']."</td>";
	echo "<td align=center>".$order['wholesale']."</td>";
    echo "<td align=center>".$order['retail']."</td>";
    echo "<td align=center>".$order['invtype']."</td>";
    echo "<td align=center>".$order['count']."</td>";

	echo "</tr>";
}

echo "</table>";

//PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>