<?php

include "includes/inc.ce-comm.php";
include 'includes/inc.header.php';
include 'includes/inc.validate.php';

SystemSelectedCheck();

$userid = $_POST['userid'];

if (!empty($_POST['direction']))
{
	if (empty($_POST['userid']))
		$error = ShowError("There User ID is empty");
	else if (is_userid($_POST['userid']) == false)
		$error = ShowError("There User ID is invalid");
	else if (empty($_POST['amount1']))
		$error = ShowError("There Amount1 is empty");
	else if (is_currency($_POST['amount1']) == false)
		$error = ShowError("There Amount1 is invalid");
	else if (empty($_POST['amount2']))
		$error = ShowError("There Amount2 is empty");
	else if (is_currency($_POST['amount2']) == false)
		$error = ShowError("There Amount2 is invalid");
}

if (($_POST['direction'] == "validateaccount") && (!empty($userid)) && (empty($error)))
{
	$fields[] = "userid";
	$fields[] = "amount1";
	$fields[] = "amount2";
	$headers = BuildHeader(CLIENT, "validateaccount", $fields, $pagvals, $_POST);
	$json = PostURL($headers, "false");
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{

		$text = "The Bank Account has been validated";
		ShowBannerMessage($text, "green", "white");
		include 'includes/inc.footer.php';
		exit();
	}
}

echo '<div class="col-md-16 col-xs-12">';
echo '	<h2>Validate Bank Account</h2>';
echo '	<div class="x_panel">';
echo '		<div class="x_content">';
echo '		<form class="form-horizontal form-label-left" method=POST action="?userid='.$userid.'">';

// Hidden //
echo "<input type=hidden name='direction' value='validateaccount'>";

if (empty($userid))
{
	// User ID //
	echo '			<div class="form-group">';
	echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">User ID</label>';
	echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
	//echo '					<input type="hidden" class="form-control" placeholder="User ID" name="userid" value="'.$userid .'">'.$userid;
	echo '					<input type="text" class="form-control" placeholder="User ID" name="userid" value="'.$userid.'">';
	echo '				</div>';
	echo '			</div>';
}
else
{
	// User ID //
	echo '			<div class="form-group">';
	echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
	echo '					<input type="hidden" class="form-control" name="userid" value="'.$userid .'">';
	//echo '					<input type="text" class="form-control" placeholder="User ID" name="userid" value="'.$userid.'">';
	echo '				</div>';
	echo '			</div>';
}

// Amount 1 //
echo '			<div class="form-group">';
echo '                <label class="control-label col-md-3 col-sm-3 col-xs-12">Amount 1</label>';
echo '                <div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Example 0.12" name="amount1" value="'.$_POST['amount1'].'">';
echo '                </div>';
echo '          </div>';

// Amount 2 //
echo '			<div class="form-group">';
echo '                <label class="control-label col-md-3 col-sm-3 col-xs-12">Amount 2</label>';
echo '                <div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Example 0.39" name="amount2" value="'.$_POST['amount2'].'">';
echo '                </div>';
echo '          </div>';

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