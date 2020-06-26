<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.convert.php';

// Needs to be here before we put anything on the page //
if ($_POST['direction'] == "downloadcsv")
{
	// Grab the csv file //
	$json = BuildAndPOST(CLIENT, "querypayments", $fields, $pagvals);
	if ($json['count'] <= 0)
	{
		// Flag error below //
		$error = true;
	}
	else
	{
		$currentdate = date("m-d-Y");
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename=payout.'.$currentdate.'.csv');
		header('Pragma: no-cache');

		// Build the .csv file on fly //
		echo "\"userid\",\"firstname\",\"lastname\",\"email\",\"amount\"\r\n"; // Show heading //

		// Show each entry //
		foreach ($json['payout'] as $userpay)
		{
			echo "\"".$userpay['userid']."\",\"".$userpay['firstname']."\",\"".$userpay['lastname']."\",\"".$userpay['email']."\",\"".PerfectCents($userpay['amount'])."\"\r\n";
		}

		exit(1);
	}
}

// Clear the ledger before loading page //
if ($_POST['direction'] == "clearledger")
{
	// Grab the csv file //
	$json = BuildAndPOST(CLIENT, "querypayments", $fields, $pagvals);
	if ($json['count'] <= 0)
	{
		// Flag error below //
		$error = true;
	}
	else
	{
		// What is the most recent batch_id //
		$pagvals['orderby'] = "id";
		$pagvals['orderdir'] = "desc";
		$batchesjson = BuildAndPOST(CLIENT, "querybatches", $fields, $pagvals);
		$mostrecentbatchid = $batchesjson['batches'][0]['id'];

		// Show each entry //
		foreach ($json['payout'] as $userpay)
		{
			//echo "\"".$userpay['userid']."\",\"".$userpay['firstname']."\",\"".$userpay['lastname']."\",\"".$userpay['email']."\",\"".number_format($userpay['amount'], 2)."\"\r\n";

			$fields = array();
			$values = array();

			// Add a negative ledger entry for each positive value //
			$fields[] = "systemid";
			$fields[] = "batchid";
			$fields[] = "userid";
			$fields[] = "ledgertype";
			$fields[] = "amount";
			$fields[] = "eventdate";
			$values["systemid"] = $_SESSION['systemid'];
			$values["batchid"] = $mostrecentbatchid; // This allows ClearBatch if a mistake is made //
			$values["userid"] = $userpay['userid'];
			$values["ledgertype"] = 8; // Custom Payout //
			$values["amount"] = "-".PerfectCents($userpay['amount']);
			$values["eventdate"] = date("Y-m-d");
			$headers = BuildHeader(CLIENT, "addledger", $fields, "", $values);
			$jsonledger = PostURL($headers, "false");
			if (HandleResponse($json, SUCCESS_NOTHING) == true)
			{
				//Pre($jsonledger); //["ledger"];
			}
			else
			{
				//echo "ERROR";
			}
		}
	}
}

include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

SystemSelectedCheck();

if ($error == true)
{
	ShowMessage($json['errors']['detail'], "red", "white");
}

if ($_POST['direction'] == "clearout")
{

}

// Handle query //
$pagvals = PagValidate("id", "desc");
$json = BuildAndPOST(CLIENT, "querypaymentstotal", $fields, $pagvals);
HandleResponse($json, SUCCESS_NOTHING);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Process Payments</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
<?php
	if (empty($json['grandtotal']['amount']))
		$json['grandtotal']['amount'] = "0";

	ShowMessage("The payout amount is $".number_format($json['grandtotal']['amount'], 2), "green", "white");
?>    
    <table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">
		<thead>
	        <tr>
<?php
if ($json['grandtotal']['amount'] > 0)
{
	echo '<form method="POST" action="">';
	echo '<td><b><input type="submit" value="Download .csv"></b></td>';
	echo '<input type="hidden" name="direction" value="downloadcsv">';
	echo '</form>';
	echo '</tr>';

	echo '<tr><td>&nbsp;</td></tr>';

	echo '<tr>';
	echo '<form method="POST" action="">';
	echo '<td><b><input type="submit" value="Clear Ledger"></b></td>';
	echo '<input type="hidden" name="direction" value="clearledger">';
	echo '</form>';
}
?>
	  		</tr>

    	</thead>

    </table>
<?php

include 'includes/inc.footer.php';

?>