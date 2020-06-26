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
	<h2>Affiliate Menu</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table>
	
<?php

	// Handle change of affilmenu //
	if ($_POST['submitted'] == "true")
	{
		$newjson .= "[";
		for ($index=0; $index < $_POST['itemcount']; $index++)
		{
			if ($_POST['type'.$index] == "folder")
			{
				$newjson .= '{"type":"folder","id":"'.$_POST['id'.$index].'","name":"'.$_POST['name'.$index].'","class":"'.$_POST['class'.$index].'"},';
			}
			else if ($_POST['type'.$index] == "link")
			{
				$newjson .= '{"type":"link","id":"'.$_POST['id'.$index].'","name":"'.$_POST['name'.$index].'","enabled":"'.$_POST['enabled'.$index].'","webpage":"'.$_POST['webpage'.$index].'"},';
			}

			unset($_POST['type'.$index]);
			unset($_POST['name'.$index]);
			unset($_POST['class'.$index]);
			unset($_POST['enabled'.$index]);

		}
		$newjson = rtrim($newjson, ",");
		$newjson .= "]";
		unset($_POST['submitted']);

		$fields[] = "varname";
		$fields[] = "value";
	    $_POST['varname'] = "affiliatemenu";
	    $_POST['value'] = $newjson;
		$json = BuildAndPOST(CLIENT, "settingsset", $fields, $pagvals);
		HandleResponse($json, SUCCESS_NOTHING);
		$jsonmenu = json_decode($newjson);
	}

	// Grab the affiliatehome value //
    $fields[] = "varname";
    $_POST['varname'] = "affiliatemenu";
	$json = BuildAndPOST(CLIENT, "settingsget", $fields, $pagvals);
	HandleResponse($json, SUCCESS_NOTHING);
	if (($json['errors'][status] == "400") && ($json['errors']['detail'] == "There are no records"))
		$jsonmenu = json_decode(AffilDefaultMenuJson());
	else
		$jsonmenu = json_decode($json['settings'][0]['value']);

	echo "<form method='POST' action=''>";
	echo '<div class="col-md-16 col-xs-12">';
	echo "<table border='0'>";

	echo "<tr><td align=center><b><font size='5'><u>Name</u></font></b></td><td></td><td align=center><b><font size='5'><u>Default</u></font></b></td><td></td><td align=center><b><font size='5'><u>Webpage</u></font></b></td></tr>";

	// Always start with the default menu as base //
	// This way any updates to the menu can be available //
	$jsondefaultmenu = json_decode(AffilDefaultMenuJson());

	$index = 0;
	foreach ($jsondefaultmenu as $item)
	{	
		$setitem = AffilMenuGet($item->id, $jsonmenu);
		if ($setitem == "")
		{
			$setitem = $item;
			$item->enabled = "";
		}

		if ($item->type == "folder")
		{
			echo "<tr><td>&nbsp;</td></tr>";
			echo "<input type='hidden' name='type".$index."' value='folder'>";
			echo "<input type='hidden' name='id".$index."' value='".$item->id."'>";
			echo "<input type='hidden' name='class".$index."' value='".$item->class."'>";
			echo "<tr><td><h2><b><u><input name='name".$index."' value='".$setitem->name."'></u></b></h2></td><td>&nbsp;&nbsp;&nbsp;</td><td>".$item->default."</td></tr>";

			//echo "<li><a><i class='".$item['class']."'></i>".$item['name']." <span class='fa fa-chevron-down'></span></a>\r\n";
		}
		else if ($item->type == "link")
		{
			//echo "\t<li><a href='".$item['webpage']."'>".$item['name']."</a></li>\r\n";

			if ($setitem->enabled == "on")
				$checked = "checked";
			else
				$checked = "";

			echo "<input type='hidden' name='type".$index."' value='link'>";
			echo "<input type='hidden' name='id".$index."' value='".$item->id."'>";
			echo '<tr><td><input type="checkbox" name="enabled'.$index.'" '.$checked.'> ';
			echo '<font size=3><input type="text" name="name'.$index.'" value="'.$setitem->name.'"></font></td><td>&nbsp;&nbsp;&nbsp;</td><td>'.$item->default.'</td><td>&nbsp;&nbsp;&nbsp;</td><td>'.$item->webpage.'</td></tr>';

			echo "<input type='hidden' name='webpage".$index."' value='".$item->webpage."'>";
		}

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