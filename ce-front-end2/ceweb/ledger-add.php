<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.select.php';
include 'includes/inc.date.php';

SystemSelectedCheck();

// Build a list of input fields //
$fields[] = "id";
$fields[] = "batchid";
$fields[] = "userid";
$fields[] = "ledgertype";
$fields[] = "amount";
$fields[] = "eventdate";

// Handle editpage logic //
$values = BuildEditPage(CLIENT, "ledger", $fields);
$values['signupdate'] = FixDate($values['signupdate']);

echo '<div class="col-md-16 col-xs-12">';

// Switch heading on edit vs add //
if (($_GET['edit'] == "true") || ($_POST['edit'] == "true"))
	echo '	<h2>Edit Ledger</h2>';
else
	echo '	<h2>Add Ledger</h2>';


echo '	<div class="x_panel">';
echo '		<div class="x_content">';
echo '		<form class="form-horizontal form-label-left" method=POST action="">';

// Hidden //
if (($_GET['edit'] == "true") || ($_POST['edit'] == "true"))
{
	echo "<input type=hidden name='edit' value='true'>";
	echo "<input type=hidden name='direction' value='edit'>";
	echo "<input type=hidden name='id' value='".$values['id']."'>";
}
else
	echo "<input type=hidden name='direction' value='add'>";

// BatchID //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> BatchID</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="BatchID" name="batchid" value="'.$values['batchid'].'">';
echo '				</div>';
echo '			</div>';

// UserID //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> UserID</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="UserID" name="userid" value="'.$values['userid'].'">';
echo '				</div>';
echo '			</div>';

// LedgerType //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">LedgerType</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
//echo '					<input type="text" class="form-control" placeholder="SponsorID" name="sponsorid" value="'.$values['sponsorid'].'">';
echo SelectLedgerType("ledgertype", $values['ledgertype']);
echo '				</div>';
echo '			</div>';

// Amount //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Amount</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Amount" name="amount" value="'.$values['amount'].'">';
echo '				</div>';
echo '			</div>';

// Event Date //
if (!empty($values['eventdate']))
{	
	$originalDate = $values['eventdate'];
	$values['eventdate'] = date("d-m-Y", strtotime($originalDate));
}
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Event Date</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
ChooseDate("eventdate", $values['eventdate']);
echo '				</div>';
echo '			</div>';

// Submit Button //
echo '			<div class="ln_solid"></div>';
echo '			<div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-5">';
echo '				<button type="submit" class="btn btn-success">Submit</button>';
echo '			</div>';

echo '		</div>';
echo '		</form>';
echo '	</div>';
echo '</div>';

include 'includes/inc.footer.php';

?>