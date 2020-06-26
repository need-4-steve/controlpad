<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.select.php';
include 'includes/inc.date.php';

SystemSelectedCheck();

if ($_POST['submitted'] == "true")
{
	if (empty($_POST['userids']))
		ShowError("The UserIDs field is empty");
	else if (empty($_POST['disable']))
		ShowError("The Disable field is empty");
	else
	{
		$UserArray = explode("\n", $_POST['userids']);

		foreach ($UserArray as $userid)
		{
			$fields[0] = "userid";
			$values["userid"] = $userid;
			if ($_POST['disable'] == "true")
				$headers = BuildHeader(CLIENT, "disableuser", $fields, "", $values);
			else if ($_POST['disable'] == "false")
				$headers = BuildHeader(CLIENT, "enableuser", $fields, "", $values);
			$json = PostURL($headers);
			if (HandleResponse($json, SUCCESS_NOTHING) == true)
			{
			    
			}

			unset($fields[0]);
			unset($values["userid"]);
		}

		ShowBannerMessage("The given users are now disabled", "green", "white");

		unset($_POST['userids']);
		unset($_POST['disable']);
	}
}

echo '<div class="col-md-16 col-xs-12">';

echo '	<h2>User Bulk Disable</h2>';

echo '	<div class="x_panel">';
echo '		<div class="x_content">';
echo '		<form class="form-horizontal form-label-left" method=POST action="">';


// UserID //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> UserIDs</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<textarea cols="20" rows="20" class="form-control" placeholder="UserIDs" name="userids">'.$_POST['userids'].'</textarea>';
echo '				</div>';
echo '			</div>';

// Disable //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Disable</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '<select name="disable">';
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

echo '		</div>';
echo '		</form>';
echo '	</div>';
echo '</div>';

include 'includes/inc.footer.php';

?>