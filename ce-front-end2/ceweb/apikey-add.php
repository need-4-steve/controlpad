<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.select.php';

SystemSelectedCheck();

$systemid = $_SESSION['systemid'];


// Page not currently used //
// When we do set this page live //
// Then we need to sanitize the inputs //


/////////////////////////////////
// Handle adding of a rankrule //
/////////////////////////////////
if ($_POST['direction'] == 'add')
{
	$headers = [];
	$headers[] = "command: addapikey";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "label: ".$_POST['label'];

	$json = PostURL($headers, "false")."\n";
	HandleResponse($json);
	$newapikey = $json['apikeys'][0]['apikey'];

// New way of doing things //
//	$fields[] = "rankruleid";
//	$json = BuildAndPOST("disablerankrule", $_SESSION['systemid'], $fields, $_POST);
//	if (HandleResponse($json, DISABLE_RECORD) == false)
//	{
//		$values = CopyArrayValues($fields, $_POST);
//	}
}

echo '<div class="col-md-16 col-xs-12">';
echo '	<h2>Add ApiKey</h2>';
echo '	<div class="x_panel">';
echo '		<div class="x_content">';

if (!empty($newapikey))
{
	echo "Your New ApiKey is: ".$newapikey."<br><br>";
	echo "Please copy and use in your code for API access";
}
else
{
	echo '	<div class="x_panel">';
	echo '		<div class="x_content">';
	echo '		<form class="form-horizontal form-label-left" method=POST action="">';

	echo "<input type=hidden name='direction' value='add'>";

	// Label //
	echo '			<div class="form-group">';
	echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Label</label>';
	echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
	echo '					<input type="text" class="form-control" placeholder="Label" name="label">';
	echo '				</div>';
	echo '			</div>';

	// Submit Button //
	echo '			<div class="ln_solid"></div>';
	echo '			<div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-5">';
	echo '				<button type="submit" class="btn btn-success">Submit</button>';
	echo '			</div>';

	echo '		</form>';
	echo '	</div>';
}
echo '	</div>';
echo '</div>';

include 'includes/inc.footer.php';

?>