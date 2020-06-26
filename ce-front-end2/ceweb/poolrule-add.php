<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.select.php';

SystemSelectedCheck();

// Handle forced pool selection //
if (is_numeric($_SESSION['poolid']))
{
	ShowBannerMessage("Pool Selected: ".$_SESSION['poolid'], "white", "black");
}
else
{
	ShowMessage("A pool needs to be selected" , "red", "white");
	ShowMessage("<a href='pool-select.php'><u>Click here to select a pool</u></a>", "white", "blue");
	include 'includes/inc.footer.php';
	return;
}

// Build a list of input fields //
$fields[] = "id";
$fields[] = "poolid";
$fields[] = "startrank";
$fields[] = "endrank";
$fields[] = "qualifytype";
$fields[] = "qualifythreshold";

// Handle editpage logic //
$values = BuildEditPage(CLIENT, "poolrule", $fields);


echo '<div class="col-md-16 col-xs-12">';
if ($_GET['edit'] == "true")
	echo '	<h2>Edit Pool Rule</h2>';
else
	echo '	<h2>Add Pool Rule</h2>';
echo '	<div class="x_panel">';
echo '		<div class="x_content">';
echo '		<form class="form-horizontal form-label-left" method=POST action="">';

// Hidden //
if ($_GET['edit'] == "true")
{
	echo "<input type=hidden name='direction' value='edit'>";
	echo "<input type=hidden name='id' value='".$values['id']."'>";
	echo "<input type=hidden name='poolid' value='".$_SESSION['poolid']."'>";
}
else
{
	echo "<input type=hidden name='direction' value='add'>";
	echo "<input type=hidden name='poolid' value='".$_SESSION['poolid']."'>";
}

// Start Rank //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Start Rank</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Start Rank" name="startrank" value="'.$values['startrank'].'">';
echo '				</div>';
echo '			</div>';

// End Rank //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> End Rank</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="End Rank" name="endrank" value="'.$values['endrank'].'">';
echo '				</div>';
echo '			</div>';

// Qualify Type //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Type</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo SelectQualifyType("qualifytype", $values['qualifytype']);
echo '				</div>';
echo '			</div>';

// Qualify Threshold //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Threshold</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Qualify Threshold" name="qualifythreshold" value="'.$values['qualifythreshold'].'">';
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

include 'includes/inc.footer.php';

?>