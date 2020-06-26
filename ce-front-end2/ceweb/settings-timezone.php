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
	<h2>Timezone</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table>
	<tr><td align='right'><b>Timezone:</b>&nbsp;&nbsp;</td><td>
<?php
	
	// This is broken !!! //

	// Grab what my current timezone settings is //
	$pagvals = PagValidate("varname", "asc");
	$pagvals['limit'] = 10000;
	$pagvals['qstring'] = str_replace("limit=1", "limit=1", $pagvals['qstring']);
	$fields['search'] = "varname=timezone";
	$fields['sort'] = "id";
	$json = BuildAndPOST(CLIENT, "settingsquery", $fields, $pagvals);

	//Pre($json);

	$mytimezone = "America/Denver";
/*
	// Grab all the timezones //
	$pagvals = PagValidate("name", "asc");
	$pagvals['limit'] = 10000;
	$pagvals['qstring'] = str_replace("limit=10", "limit=10000", $pagvals['qstring']);
	$json = BuildAndPOST(CLIENT, "settingsgettz", $fields, $pagvals);
*/
	echo "<select>";
	foreach ($json['timezones'] as $record)
	{
		if ($record['name'] == $mytimezone)
			echo "<option selected value='".$record['name']."'>".$record['name']." - ".$record['abbrev']."</option>";
		else
			echo "<option value='".$record['name']."'>".$record['name']." - ".$record['abbrev']."</option>";
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