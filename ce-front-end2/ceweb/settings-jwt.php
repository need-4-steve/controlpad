 <?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.affiliate.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

SystemSelectedCheck();
 
?>
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Affiliate JWT Single Sign On</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table>
	
<?php
	if ($_POST['updateloginsite'] == "true")
	{
		// Grab what my current timezone settings is //
	    $fields[] = "varname";
	    $fields[] = "value";
	    $_POST['varname'] = "loginsite";
	    $_POST['value'] = $_POST['loginsite'];
		$json = BuildAndPOST(CLIENT, "settingsset", $fields, $pagvals);
		$loginsite = $json['settings'][0]['value'];
	}
	else if ($_POST['disableloginsite'] == "true")
	{
		// Grab what my current timezone settings is //
	    $fields[] = "varname";
	    $_POST['varname'] = "loginsite";
		$json = BuildAndPOST(CLIENT, "settingsdisable", $fields, $pagvals);
		$loginsite = $json['settings'][0]['value'];
	}
	else if ($_POST['updatelogoutsite'] == "true")
	{
		// Grab what my current timezone settings is //
	    $fields[] = "varname";
	    $fields[] = "value";
	    $_POST['varname'] = "logoutsite";
	    $_POST['value'] = $_POST['logoutsite'];
		$json = BuildAndPOST(CLIENT, "settingsset", $fields, $pagvals);
		$loginsite = $json['settings'][0]['value'];
	}
	else if ($_POST['disablelogoutsite'] == "true")
	{
		// Grab what my current timezone settings is //
	    $fields[] = "varname";
	    $_POST['varname'] = "logoutsite";
		$json = BuildAndPOST(CLIENT, "settingsdisable", $fields, $pagvals);
		$loginsite = $json['settings'][0]['value'];
	}

	// Grab what my current timezone settings is //
    $fields[] = "varname";
    $_POST['varname'] = "loginsite";
	$json = BuildAndPOST(CLIENT, "settingsget", $fields, $pagvals);
	//Pre($json);
	$loginsite = $json['settings'][0]['value'];

	$fields[] = "varname";
    $_POST['varname'] = "logoutsite";
	$json = BuildAndPOST(CLIENT, "settingsget", $fields, $pagvals);
	//Pre($json);
	$logoutsite = $json['settings'][0]['value'];

	//Pre($json);

	echo "<table>";

	// Delete loginsite //
	echo "<form method='POST' action=''>";
	echo "<tr><td><input type=submit value='Disable'></td>";
	echo "<input type='hidden' name='disableloginsite' value='true'>";
	echo "</form>";

	// Add/Update login site //
	echo "<form method='POST' action=''>";
	echo "<td align=right>&nbsp;<b>Login site:</b>&nbsp;</td><td><input type='edit' name='loginsite' value='".$loginsite."' size='80'></td><td><input type=submit value='Update'></td></tr>";
	echo "<tr><td><br></td></tr>";
	echo "<input type='hidden' name='updateloginsite' value='true'>";
	echo "</form>";

	// Delete logoutsite //
	echo "<form method='POST' action=''>";
	echo "<tr><td><input type=submit value='Disable'></td>";
	echo "<input type='hidden' name='disablelogoutsite' value='true'>";
	echo "</form>";

	// Add/Update logout site //
	echo "<form method='POST' action=''>";
	echo "<td align=right>&nbsp;<b>Logout site:</b>&nbsp;</td><td><input type='edit' name='logoutsite' value='".$logoutsite."' size='80'></td><td><input type=submit value='Update'></td></tr>";
	echo "<tr><td><br></td></tr>";
	echo "<input type='hidden' name='updatelogoutsite' value='true'>";
	echo "</form>";

	echo "</table>";
?>
	</tr>
	</table>
</div>
<?php

include 'includes/inc.footer.php';

?>