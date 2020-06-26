<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.select.php';
include 'includes/inc.convert.php';

SystemSelectedCheck();

// Build a list of input fields //
$fields[] = "id";
$fields[] = "label";
$fields[] = "rank";
$fields[] = "qualifytype";
$fields[] = "qualifythreshold";
$fields[] = "achvbonus"; 
$fields[] = "breakage";
$fields[] = "rulegroup";
$fields[] = "sumrankstart";
$fields[] = "sumrankend";

// Handle checkbox correction // 
$_POST['breakage'] = ConvCheckbox($_POST['breakage']);

// Handle editpage logic //
$values = BuildEditPage(CLIENT, "rankrule", $fields);

echo '<div class="col-md-16 col-xs-12">';

// Heading //
if ($_GET['edit'] == "true")
	echo '	<h2>Edit Rank Rule</h2>';
else
	echo '	<h2>Add Rank Rule</h2>';

echo '	<div class="x_panel">';
echo '		<div class="x_content">';
echo '		<form class="form-horizontal form-label-left" method=POST action="">';

// Hidden //
if ($_GET['edit'] == "true")
{
	echo "<input type=hidden name='direction' value='edit'>";
	echo "<input type=hidden name='id' value='".$values['id']."'>";
}
else
	echo "<input type=hidden name='direction' value='add'>";

// Label //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Label</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Label" name="label" value="'.$values['label'].'"">';
echo '				</div>';
echo '			</div>';

// Rank //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Rank</label>';
//echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
//echo '					<input type="text" class="form-control" placeholder="Rank" name="rank" value="'.$values['rank'].'"">';
echo SelectRankRule("rank", $values['rank']);
//echo '				</div>';
echo '			</div>';

// Qualify Type //
echo '			<div class="form-group">';
echo '                <label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Qualify Type</label>';
echo '                <div class="col-md-9 col-sm-9 col-xs-12">';
echo SelectQualifyType("qualifytype", $values['qualifytype']);
echo '                </div>';
echo '          </div>';

// Qualify Threshold //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Threshold</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Qualify Threshold" name="qualifythreshold" value="'.$values['qualifythreshold'].'">';
echo '				</div>';
echo '			</div>';

// Achievement Bonus //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Achv Bonus</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Achievement Bonus" name="achvbonus" value="'.$values['achvbonus'].'">';
echo '				</div>';
echo '			</div>';

// Sum Rank Start //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Sum Rank Start</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Sum Rank Start" name="sumrankstart" value="'.$values['sumrankstart'].'">';
echo '				</div>';
echo '			</div>';

// Sum Rank End //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Sum Rank End</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Sum Rank End" name="sumrankend" value="'.$values['sumrankend'].'">';
echo '				</div>';
echo '			</div>';

// Breakage //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Breakage</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="checkbox" class="form-control" placeholder="Breakage" name="breakage">';
echo '				</div>';
echo '			</div>';

// Rule Group //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Rule Group</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Rule Group #" name="rulegroup" value="'.$values['rulegroup'].'">';
echo '				</div>';
echo '			</div>';

// Submit Button //
echo '			<div class="ln_solid"></div>';
echo '			<div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-5">';
echo '				<button type="submit" class="btn btn-success">Submit</button>';
echo '			</div>';

echo '		</form>';

// Delete Button //
if (!empty($_GET['id']))
{
	echo '			<div class="ln_solid"></div>';
	echo '			<div class="ln_solid"></div>';
	echo '		<form class="form-horizontal form-label-left" method=POST action="">';
	echo "<input type=hidden name='direction' value='delete'>";
	echo "<input type=hidden name='rankid' value='".$_GET['id']."'>";
	echo '			<div class="ln_solid"></div>';
	echo '			<div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-5">';
	echo '				<button type="submit" class="btn btn-success">Delete</button>';
	echo '			</div>';
	echo '		</form>';
}

echo '		</div>';
echo '	</div>';
echo '</div>';

include 'includes/inc.footer.php';

?>