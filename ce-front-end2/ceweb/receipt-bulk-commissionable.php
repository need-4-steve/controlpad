<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.convert.php';
include 'includes/inc.date.php';
include 'includes/inc.select.php';

SystemSelectedCheck();

if ($_POST['submitted'] == "true")
{
	if (empty($_POST['userid']))
		ShowError("UserID is empty");
	else if (empty($_POST['commissionable']))
		ShowError("Commissionable is empty");
	else if (empty($_POST['commissionable']))
		ShowError("Commissionable is empty");
	else if (empty($_POST['startdate']))
		ShowError("Startdate is empty");
	else if (empty($_POST['enddate']))
		ShowError("Enddate is empty");
	else
	{
		// Send off to API //
		// Build a list of input fields //
		$fields[] = "userid";
		$fields[] = "startdate";
		$fields[] = "enddate";
		$fields[] = "commissionable";

		// Handle editpage logic //
		$headers = BuildHeader(CLIENT, "commissionablereceiptbulk", $fields, "", $_POST);
	    $json = PostURL($headers, "false");

	    //Pre($json);

	    if (HandleResponse($json, SUCCESS_NOTHING) == true)
	    {
	    	ShowBannerMessage("The given receipts are now uncommissionable", "green", "white");
	    }
	}
}

echo '<div class="col-md-16 col-xs-12">';
echo '	<h2>Receipts Bulk Commissionable</h2>';
echo '	<div class="x_panel">';
echo '		<div class="x_content">';
echo '		<form class="form-horizontal form-label-left" method=POST action="">';

// UserID //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> UserID</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="UserID" name="userid" value="'.$_POST['userid'].'">';
echo '				</div>';
echo '			</div>';

// Start Date //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Start Date</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
ChooseDate("startdate", $_POST['startdate']);
echo '				</div>';
echo '			</div>';

// End Date //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> End Date</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
ChooseDate("enddate", $_POST['enddate']);
echo '				</div>';
echo '			</div>';

// Commissionable //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Commissionable</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '<select name="commissionable">';
echo '<option>';
echo '<option value="true">True';
echo '<option value="false">False';
echo '</select>';
echo '				</div>';
echo '			</div>';

// Submit Button //
echo '			<div class="ln_solid"></div>';
echo '			<div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-5">';
echo '				<button type="submit" class="btn btn-success">Submit</button>';
echo '			</div>';

echo "<input type=hidden name='submitted' value='true'>";

echo '		</form>';
echo '	</div>';
echo '</div>';

include 'includes/inc.footer.php';

?>