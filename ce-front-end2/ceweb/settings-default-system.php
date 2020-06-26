 <?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

SystemSelectedCheck();

?>
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Default System</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table>
	<tr><td align='right'><b>Default System:</b>&nbsp;&nbsp;</td><td>
<?php
	
	// This is broken !!! //

	// Grab what my current timezone settings is //
    $fields[] = "varname";
    $_POST['varname'] = "defaultsystem";
	$json = BuildAndPOST(CLIENT, "settingsget", $fields, $pagvals);

	$json['settings'][0]['value'];

	// Grab all the timezones //
	$pagvals = PagValidate("id", "asc");
	$pagvals['limit'] = 1000;
	$pagvals['qstring'] = str_replace("limit=10", "limit=1000", $pagvals['qstring']);
	$json = BuildAndPOST(CLIENT, "querysystem", $fields, $pagvals);

	//Pre($json);

	echo "<form method='POST' action='' >";
	echo "<select varname='defaultsystemid'>";
	foreach ($json['system'] as $record)
	{
		if ($record['systemname'] == $mytimezone)
			echo "<option selected value='".$record['id']."'>".$record['systemname']."</option>";
		else
			echo "<option value='".$record['id']."'>".$record['systemname']."</option>";
	}
	echo "</select></td><td>&nbsp;&nbsp;";
	echo "<input type='submit' value='Apply'>";
	echo "</td></tr>";

	//<select><option>UTC</option></select></td></tr>
?>
	</tr>
	</table>
</div>
<?php

include 'includes/inc.footer.php';

?>