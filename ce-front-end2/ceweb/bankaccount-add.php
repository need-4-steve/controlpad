<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.validate.php';

SystemSelectedCheck();

$values['userid'] = $_POST['userid'];
$values['accounttype'] = $_POST['accounttype'];
$values['routingnumber'] = $_POST['routingnumber'];
$values['accountnumber'] = $_POST['accountnumber'];
$values['holdername'] = $_POST['holdername'];

$fields[] = "userid";
$fields[] = "accounttype";
$fields[] = "routingnumber";
$fields[] = "accountnumber";
$fields[] = "holdername";

if (!empty($_POST['direction']))
{
	if (empty($_POST['userid']))
		$error = ShowError("There User ID is empty");
	else if (is_userid($_POST['userid']) == false)
		$error = ShowError("There User ID is invalid");
	else if (empty($_POST['accounttype']))
		$error = ShowError("There Account Type is empty");
	else if (($_POST['accounttype'] != 1) && ($_POST['accounttype'] != 2))
		$error = ShowError("The Account Type invalid");
	else if (empty($_POST['routingnumber']))
		$error = ShowError("There Routing Number is empty");
	else if ((is_numeric($_POST['routingnumber']) == false))
		$error = ShowError("There Routing Number is invalid");
	else if (empty($_POST['accountnumber']))
		$error = ShowError("There Account Number is empty");
	else if ((is_numeric($_POST['accountnumber']) == false))
		$error = ShowError("There Account Number is invalid");
	else if (empty($_POST['holdername']))
		$error = ShowError("There Holder Name is empty");
	else if ((is_alpha($_POST['holdername']) == false))
		$error = ShowError("There Holdername in invalid");

	$json = BuildAndPOST(CLIENT, "getbankaccount", $fields);
	if (empty($json['bankaccount']['id']))
		$norecord = true;
}

if ($error != true)
{
	// Handle edit page logic //
	$values = BuildEditPage(CLIENT, "bankaccount", $fields);
	if (empty($values['id']))
		$norecord = true;
	if (($_POST['edit'] == "true") && (empty($values['userid'])))
		$values['userid'] = $_POST['userid'];
}

echo '<div class="col-md-16 col-xs-12">';
echo '	<h2>Add Bank Account</h2>';
echo '	<div class="x_panel">';
echo '		<div class="x_content">';
echo '		<form class="form-horizontal form-label-left" method=POST action="">';

// Hidden //
if ($norecord == true)
	echo "<input type=hidden name='direction' value='add'>";
else
	echo "<input type=hidden name='direction' value='edit'>";

// User ID //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> User ID</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="User ID" name="userid" value="'.$values['userid'].'">';
echo '				</div>';
echo '			</div>';

// Account Type //
echo '			<div class="form-group">';
echo '                <label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Account Type</label>';
echo '                <div class="col-md-9 col-sm-9 col-xs-12">';
echo SelectBankAccountType("accounttype", $values['accounttype']);
echo '                </div>';
echo '          </div>';

// Routing Number //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Routing Number</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Routing Number" name="routingnumber" value="'.$values['routingnumber'].'">';
echo '				</div>';
echo '			</div>';

// Account Number //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Account Number</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Account Number" name="accountnumber" value="'.$values['accountnumber'].'">';
echo '				</div>';
echo '			</div>';

// Holder Name //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Holder Name</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Holder Name" name="holdername" value="'.$values['holdername'].'">';
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