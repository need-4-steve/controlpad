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
	<h2>Affiliate Downline Headings</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table>
	
<?php

	if ($_POST['submitted'] == "true")
	{
		$newjson .= "[";
		for ($index=0; $index < $_POST['itemcount']; $index++)
		{
			$newjson .= '{"column":"'.$_POST['column'.$index].'","heading":"'.$_POST['heading'.$index].'","default":"'.$_POST['default'.$index].'","enabledtable":"'.$_POST['table'.$index].'","enabledcsv":"'.$_POST['csv'.$index].'"},';

		}
		$newjson = rtrim($newjson, ",");
		$newjson .= "]";

		$fields[] = "varname";
		$fields[] = "value";
		$_POST['varname'] = "downlineheadings";
		$_POST['value'] = $newjson;
		$json = BuildAndPOST(CLIENT, "settingsset", $fields, $pagvals);
		HandleResponse($json, SUCCESS_NOTHING);
	}

	// Grab the downlineheadings value //
    $fields[] = "varname";
    $_POST['varname'] = "downlineheadings";
	$json = BuildAndPOST(CLIENT, "settingsget", $fields, $pagvals);
	HandleResponse($json, SUCCESS_NOTHING);
	if (($json['errors'][status] == "400") && ($json['errors']['detail'] == "There are no records"))
		$jsonheadings = json_decode(AffilDefaultDownlineJson());
	else
		$jsonheadings = json_decode($json['settings'][0]['value']);

	echo "<form method='POST' action=''>";
	echo '<div class="col-md-16 col-xs-12">';
	echo "<table border='0'>";

	echo "<tr><td>Table</td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>CSV</td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td align=center><b><font size='5'><u>Heading</u></font></b></td><td></td><td align=center><b><font size='5'><u>Default</u></font></b></td><td></td><td align=center><b><font size='5'><u>Column</u></font></b></td></tr>";

	// Always start with the default menu as base //
	// This way any updates to the menu can be available //
	// Except that's a luxury now and I need to hurry and finish this for ringbomb //
	$index = 0;
	foreach ($jsonheadings as $item)
	{	
		//$setitem = AffilMenuGet($item->id, $jsonmenu);
		//if ($setitem == "")
		//{
		//	$setitem = $item;
		//	$item->enabled = "";
		//}

		if ($item->enabledtable == "on")
			$tablechecked = "checked";
		else
			$tablechecked = "";

		if ($item->enabledcsv == "on")
			$csvchecked = "checked";
		else
			$csvchecked = "";

		echo "<input type='hidden' name='column".$index."' value='".$item->column."'>";
		echo "<input type='hidden' name='default".$index."' value='".$item->default."'>";
		echo '<tr>';
		echo '<td align=center><input type="checkbox" name="table'.$index.'" '.$tablechecked.'></td>';
		echo "<td></td>";
		echo '<td align=center><input type="checkbox" name="csv'.$index.'" '.$csvchecked.'></td>';
		echo "<td></td>";
		echo '<td><font size=3><input type="text" name="heading'.$index.'" value="'.$item->heading.'"></font></td><td>&nbsp;&nbsp;&nbsp;</td><td>'.$item->default.'</td><td>&nbsp;&nbsp;&nbsp;</td><td>'.$item->column.'</td></tr>';
		
		unset($setitem);
		$index++;
	}

	echo "<input type='hidden' name='itemcount' value='".$index."'>";
	echo "<tr><td>&nbsp;</td></tr>";
	echo "<tr><td align=center colspan='2'><input type='submit' value='Save'></td></tr>";
	echo "<input type='hidden' name='submitted' value='true'>";
	echo "</form>";
?>
	</tr>
	</table>
</div>
<?php

include 'includes/inc.footer.php';

?>