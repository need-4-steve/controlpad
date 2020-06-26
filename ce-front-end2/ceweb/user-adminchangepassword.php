<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.select.php';
include 'includes/inc.date.php';

SystemSelectedCheck();
$password_success = false;

// Handle submitted //
if ($_POST['direction'] == "edit")
{
	if (empty($_POST['userid']))
	{
		ShowError("The userid is empty. Start Over");
	}
	else if ($_POST['newpassword'] != $_POST['retypepassword'])
	{
		ShowError("The passwords don't match. Try again");
	}
	else
	{
		$headers = [];
		$headers[] = "command: mypassreset";
		$headers[] = "authemail: ".$_SESSION['authemail'];
		$headers[] = "authpass: ".$_SESSION['authpass'];
		$headers[] = "systemid: ".$_SESSION['systemid'];
		$headers[] = "userid: ".$_POST['userid'];
		$headers[] = "password: ".$_POST['newpassword'];

		$json = PostURL($headers, "false");
		if (HandleResponse($json, PASSWORD_RESET) == true)
			$password_success = true;

		// Reset on Sim Server also //
		$json = PostURL($headers, "true");
        HandleResponse($json, SUCCESS_NOTHING);
	}
}

if ($password_success != true)
{
	echo '<div class="col-md-16 col-xs-12">';

	echo '	<h2>Change User Password</h2>';

	echo '	<div class="x_panel">';
	echo '		<div class="x_content">';
	echo '		<form class="form-horizontal form-label-left" method=POST action="">';

	// Hidden //
	echo "<input type=hidden name='direction' value='edit'>";
	echo "<input type=hidden name='userid' value='".$_POST['userid']."'>";

	// UserID //
	echo '			<div class="form-group">';
	echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> UserID</label>';
	echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
	echo '				<label class="control-label col-md-1 col-sm-1 col-sm-12">'.$_POST['userid'].'</label>';
	echo '				</div>';
	echo '			</div>';

	// New Password //
	echo '			<div class="form-group">';
	echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">New Password</label>';
	echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
	echo '					<input type="text" class="form-control" placeholder="New Password" name="newpassword">';
	echo '				</div>';
	echo '			</div>';

	// Re-Type Password //
	echo '			<div class="form-group">';
	echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Re-Type Password</label>';
	echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
	echo '					<input type="text" class="form-control" placeholder="Re-Type Password" name="retypepassword">';
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
}

include 'includes/inc.footer.php';

?>