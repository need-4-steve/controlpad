<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.select.php';

SystemSelectedCheck();

/////////////////////////////////
// Handle adding of a rankrule //
/////////////////////////////////
if ($_POST['direction'] == 'reissue')
{
	$json = BuildAndPOST(CLIENT, "reissueapikey", $fields, $_POST);
	if (HandleResponse($json, EDIT_RECORD) == false)
	{
		$values = CopyArrayValues($fields, $_POST);
	}
}

echo '<div class="col-md-16 col-xs-12">';
echo '	<h2>Reissue ApiKey</h2>';
echo '	<div class="x_panel">';
echo '		<div class="x_content">';

if (!empty($json['apikey']))
{
	echo "Your New ApiKey is: ".$json['apikey']."<br><br>";
	echo "Please copy and use in your code for API access";
}
else
{
	//echo '	<div class="x_panel">';
	//echo '		<div class="x_content">';
	echo '		<form class="form-horizontal form-label-left" method=POST action="">';

	echo "<input type=hidden name='direction' value='reissue'>";

	// Button //
	//echo '			<div class="ln_solid"></div>';
	echo '			<div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-5">';
	echo '				<button type="submit" class="btn btn-success">Reissue API Key</button>';
	echo '			</div>';

	echo '		</form>';
	echo '	</div>';
}
echo '	</div>';
echo '</div>';

include 'includes/inc.footer.php';

?>